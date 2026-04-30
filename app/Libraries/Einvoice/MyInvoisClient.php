<?php

namespace App\Libraries\Einvoice;

use App\Libraries\SettingsService;
use Config\Services;
use Throwable;

/**
 * Thin client over the LHDN MyInvois REST API.
 *
 *  - Authenticates via OAuth 2.0 client_credentials at /connect/token.
 *  - Submits documents (JSON UBL v1.0).
 *  - Polls submission status.
 *  - Cancels documents.
 *
 * Three modes (driven by `einvoice.environment` setting):
 *  - sandbox : preprod-api.myinvois.hasil.gov.my
 *  - prod    : api.myinvois.hasil.gov.my
 *  - stub    : no network calls; returns a synthetic Valid response so we can dev/test
 *              the full flow offline. Audit + DB rows look identical to real ones.
 */
class MyInvoisClient
{
    public const ENV_SANDBOX = 'sandbox';
    public const ENV_PROD    = 'prod';
    public const ENV_STUB    = 'stub';

    private const HOSTS = [
        self::ENV_SANDBOX => 'https://preprod-api.myinvois.hasil.gov.my',
        self::ENV_PROD    => 'https://api.myinvois.hasil.gov.my',
    ];

    private string $env;
    private ?string $token = null;

    public function __construct(?string $env = null)
    {
        $this->env = $env ?? (string) SettingsService::get('einvoice.environment', self::ENV_STUB);
    }

    public function environment(): string { return $this->env; }
    public function isStub(): bool { return $this->env === self::ENV_STUB; }

    /**
     * Submit a single UBL JSON document.
     * @return array{submissionUid:string, accepted:array, rejected:array, status:int, raw:array}
     */
    public function submitDocument(array $ublDocument, string $codeNumber): array
    {
        $json = json_encode($ublDocument, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode UBL document: ' . json_last_error_msg());
        }
        $hash   = hash('sha256', $json); // hex per LHDN sample
        $base64 = base64_encode($json);

        $body = [
            'documents' => [[
                'format'       => 'JSON',
                'document'     => $base64,
                'documentHash' => $hash,
                'codeNumber'   => $codeNumber,
            ]],
        ];

        if ($this->isStub()) {
            return $this->stubSubmitResponse($codeNumber);
        }

        $resp = $this->request('POST', '/api/v1.0/documentsubmissions', $body);
        $accepted = $resp['body']['acceptedDocuments'] ?? [];
        $rejected = $resp['body']['rejectedDocuments'] ?? [];
        return [
            'submissionUid' => $resp['body']['submissionUID'] ?? '',
            'accepted'      => $accepted,
            'rejected'      => $rejected,
            'status'        => $resp['status'],
            'raw'           => $resp['body'],
        ];
    }

    /**
     * Fetch submission details (status, validation results).
     * @return array{status:string, documents:array, raw:array}
     */
    public function getSubmission(string $submissionUid): array
    {
        if ($this->isStub()) {
            return [
                'status'    => 'Valid',
                'documents' => [[
                    'uuid'       => 'STUB-UUID-' . substr(md5($submissionUid), 0, 12),
                    'longId'     => 'STUB-LONG-' . substr(md5($submissionUid . 'L'), 0, 24),
                    'status'     => 'Valid',
                    'dateTimeValidated' => gmdate('c'),
                ]],
                'raw' => ['mode' => 'stub'],
            ];
        }
        $resp = $this->request('GET', '/api/v1.0/documentsubmissions/' . rawurlencode($submissionUid));
        return [
            'status'    => $resp['body']['overallStatus'] ?? 'Unknown',
            'documents' => $resp['body']['documentSummary'] ?? [],
            'raw'       => $resp['body'],
        ];
    }

    /**
     * Cancel a document by IRBM UUID. Allowed only within 72h of validation.
     */
    public function cancelDocument(string $uuid, string $reason): array
    {
        if ($this->isStub()) {
            return ['status' => 200, 'body' => ['uuid' => $uuid, 'status' => 'Cancelled', 'reason' => $reason, 'mode' => 'stub']];
        }
        return $this->request('PUT', '/api/v1.0/documents/state/' . rawurlencode($uuid) . '/state', [
            'status' => 'Cancelled',
            'reason' => $reason,
        ]);
    }

    // --------------- internals ---------------

    private function request(string $method, string $path, ?array $body = null): array
    {
        $token = $this->ensureToken();
        $url   = $this->host() . $path;
        $client = Services::curlrequest(['timeout' => 30, 'http_errors' => false]);
        $opts = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
        ];
        if ($body !== null) {
            $opts['json'] = $body;
        }
        $resp = $client->request($method, $url, $opts);
        $status = $resp->getStatusCode();
        $rawBody = (string) $resp->getBody();
        $decoded = json_decode($rawBody, true) ?? [];
        if ($status >= 400) {
            throw new MyInvoisException("MyInvois $method $path failed: HTTP $status — " . substr($rawBody, 0, 500), $status, $decoded);
        }
        return ['status' => $status, 'body' => $decoded];
    }

    private function ensureToken(): string
    {
        if ($this->token !== null) { return $this->token; }
        $clientId     = (string) (env('MYINVOIS_CLIENT_ID') ?: '');
        $clientSecret = (string) (env('MYINVOIS_CLIENT_SECRET') ?: '');
        if ($clientId === '' || $clientSecret === '') {
            throw new MyInvoisException('MYINVOIS_CLIENT_ID / MYINVOIS_CLIENT_SECRET not configured. Use stub environment for offline dev.');
        }

        $client = Services::curlrequest(['timeout' => 15, 'http_errors' => false]);
        $resp = $client->request('POST', $this->host() . '/connect/token', [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'grant_type'    => 'client_credentials',
                'scope'         => 'InvoicingAPI',
            ],
        ]);
        $body = json_decode((string) $resp->getBody(), true) ?? [];
        if ($resp->getStatusCode() !== 200 || empty($body['access_token'])) {
            throw new MyInvoisException('MyInvois token request failed: HTTP ' . $resp->getStatusCode() . ' ' . substr((string) $resp->getBody(), 0, 300));
        }
        return $this->token = $body['access_token'];
    }

    private function host(): string
    {
        if (! isset(self::HOSTS[$this->env])) {
            throw new MyInvoisException("Cannot connect: environment is '{$this->env}' (use sandbox or prod).");
        }
        return self::HOSTS[$this->env];
    }

    private function stubSubmitResponse(string $codeNumber): array
    {
        $sub  = 'STUB-SUB-' . bin2hex(random_bytes(8));
        $uuid = 'STUB-UUID-' . bin2hex(random_bytes(8));
        return [
            'submissionUid' => $sub,
            'accepted'      => [['invoiceCodeNumber' => $codeNumber, 'uuid' => $uuid]],
            'rejected'      => [],
            'status'        => 202,
            'raw'           => ['mode' => 'stub', 'submissionUID' => $sub],
        ];
    }
}
