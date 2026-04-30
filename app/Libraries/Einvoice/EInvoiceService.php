<?php

namespace App\Libraries\Einvoice;

use App\Libraries\SettingsService;
use App\Models\EinvoiceDocumentModel;
use App\Models\InvoiceModel;
use App\Models\MemberModel;
use App\Models\PlanModel;

/**
 * Orchestrates LHDN MyInvois e-Invoice operations against a domain Invoice.
 *
 * The DB is the source of truth: every submission/cancellation writes a row to
 * `einvoice_documents` with the full UBL payload + raw API response, so we can
 * always reconstruct what was sent and what came back.
 */
class EInvoiceService
{
    private MyInvoisClient $client;
    private InvoiceModel $invoices;
    private MemberModel $members;
    private PlanModel $plans;
    private EinvoiceDocumentModel $docs;

    public function __construct(?MyInvoisClient $client = null)
    {
        $this->client   = $client ?? new MyInvoisClient();
        $this->invoices = new InvoiceModel();
        $this->members  = new MemberModel();
        $this->plans    = new PlanModel();
        $this->docs     = new EinvoiceDocumentModel();
    }

    /**
     * Build, submit and persist an e-Invoice for the given internal invoice id.
     *
     * @return array{document:array, invoice:array} The latest einvoice_documents row + invoices row.
     */
    public function submitInvoice(int $invoiceId, ?int $userId = null): array
    {
        $inv = $this->loadInvoice($invoiceId);
        return $this->submitDocumentFor(
            $invoiceId,
            'invoice',
            (float) $inv['amount'],
            (float) $inv['tax_amount'],
            (float) $inv['tax_percent'],
            null,
            null,
            $userId
        );
    }

    /**
     * Issue a Refund Note that references a previously validated e-Invoice.
     * The provided $amount is the gross (tax-inclusive) refund amount and is
     * split into net + tax using the original invoice's tax_percent so the UBL
     * document reports figures consistent with the source invoice.
     */
    public function submitRefund(int $invoiceId, float $amount, ?int $userId = null): array
    {
        $inv = $this->loadInvoice($invoiceId);
        if (empty($inv['einvoice_uuid'])) {
            throw new \RuntimeException('Cannot issue refund note: original e-Invoice has not been validated.');
        }
        $taxPercent = (float) $inv['tax_percent'];
        if ($taxPercent > 0) {
            $taxAmount = round($amount * $taxPercent / (100 + $taxPercent), 2);
        } else {
            $taxAmount = 0.0;
        }
        $netAmount = round($amount - $taxAmount, 2);
        return $this->submitDocumentFor(
            $invoiceId,
            'refund_note',
            $netAmount,
            $taxAmount,
            $taxPercent,
            $inv['einvoice_uuid'],
            $inv['invoice_no'],
            $userId
        );
    }

    /**
     * Cancel the most recent valid e-Invoice document for an invoice. LHDN allows
     * cancellation only within 72 hours of validation.
     */
    public function cancelInvoice(int $invoiceId, string $reason, ?int $userId = null): array
    {
        $inv = $this->loadInvoice($invoiceId);
        $doc = $this->docs->latestForInvoice($invoiceId);
        if (! $doc || $doc['status'] !== 'valid' || empty($doc['irbm_uuid'])) {
            throw new \RuntimeException('No validated e-Invoice document to cancel.');
        }
        if (! empty($doc['cancellable_until']) && strtotime($doc['cancellable_until']) < time()) {
            throw new \RuntimeException('Cancellation window has expired (72 hours after validation). Issue a credit/refund note instead.');
        }
        $resp = $this->client->cancelDocument($doc['irbm_uuid'], $reason);
        $this->docs->update($doc['id'], [
            'status'         => 'cancelled',
            'cancelled_at'   => date('Y-m-d H:i:s'),
            'cancel_reason'  => $reason,
            'response_payload' => $this->jsonEncode($resp),
        ]);
        $this->invoices->update($invoiceId, [
            'einvoice_status' => 'cancelled',
        ]);
        return ['document' => $this->docs->find($doc['id']), 'invoice' => $this->invoices->find($invoiceId)];
    }

    /**
     * Re-poll the IRBM submission status for a submitted but not-yet-final document.
     */
    public function refreshStatus(int $invoiceId): array
    {
        $doc = $this->docs->latestForInvoice($invoiceId);
        if (! $doc || empty($doc['submission_uid'])) {
            throw new \RuntimeException('No submission to refresh.');
        }
        $info = $this->client->getSubmission($doc['submission_uid']);
        $first = $info['documents'][0] ?? null;
        $valid = ($info['status'] ?? '') === 'Valid' || ($first['status'] ?? '') === 'Valid';
        $invalid = ($info['status'] ?? '') === 'Invalid' || ($first['status'] ?? '') === 'Invalid';
        $update = ['response_payload' => $this->jsonEncode($info['raw'])];
        if ($valid && $first) {
            $update['status']            = 'valid';
            $update['irbm_uuid']         = $first['uuid']    ?? $doc['irbm_uuid'];
            $update['irbm_long_id']      = $first['longId']  ?? $doc['irbm_long_id'];
            $update['validated_at']      = date('Y-m-d H:i:s');
            $update['cancellable_until'] = date('Y-m-d H:i:s', time() + 72 * 3600);
            $this->invoices->update($invoiceId, [
                'einvoice_status'           => 'valid',
                'einvoice_uuid'             => $update['irbm_uuid'],
                'einvoice_long_id'          => $update['irbm_long_id'],
                'einvoice_validated_at'     => $update['validated_at'],
                'einvoice_cancellable_until'=> $update['cancellable_until'],
            ]);
        } elseif ($invalid) {
            $update['status']        = 'invalid';
            $update['error_payload'] = $this->jsonEncode($info['raw']);
            $this->invoices->update($invoiceId, ['einvoice_status' => 'invalid']);
        }
        $this->docs->update($doc['id'], $update);
        return ['document' => $this->docs->find($doc['id']), 'invoice' => $this->invoices->find($invoiceId)];
    }

    /**
     * Public viewing URL displayed in the QR code printed on the PDF.
     * Format per LHDN docs: https://myinvois.hasil.gov.my/{uuid}/share/{longId}
     */
    public function publicUrl(array $invoice): ?string
    {
        if (empty($invoice['einvoice_uuid'])) { return null; }
        $base = rtrim((string) SettingsService::get('einvoice.portal.base', 'https://preprod.myinvois.hasil.gov.my'), '/');
        $long = $invoice['einvoice_long_id'] ?? '';
        return $base . '/' . $invoice['einvoice_uuid'] . ($long !== '' ? '/share/' . $long : '');
    }

    // --------------- internals ---------------

    private function submitDocumentFor(
        int $invoiceId,
        string $type,
        float $netAmount,
        float $taxAmount,
        float $taxPercent,
        ?string $origUuid,
        ?string $origNo,
        ?int $userId
    ): array {
        $inv    = $this->loadInvoice($invoiceId);
        $member = $this->members->find($inv['member_id']);
        if (! $member) {
            throw new \RuntimeException("Member #{$inv['member_id']} not found for invoice #$invoiceId");
        }
        $plan = $inv['plan_id'] ? $this->plans->find($inv['plan_id']) : null;
        if (! $plan) {
            throw new \RuntimeException("Plan #{$inv['plan_id']} not found for invoice #$invoiceId");
        }

        $ubl = UblDocumentBuilder::build($type, $inv, $member, $plan, $netAmount, $taxAmount, $taxPercent, $origUuid, $origNo);
        $code = $type === 'invoice' ? $inv['invoice_no'] : ($inv['invoice_no'] . '-' . strtoupper(substr($type, 0, 2)));

        $now    = date('Y-m-d H:i:s');
        $docId  = $this->docs->insert([
            'document_type'    => $type,
            'source_table'     => 'invoices',
            'source_id'        => $invoiceId,
            'code_number'      => $code,
            'status'           => 'pending',
            'environment'      => $this->client->environment(),
            'request_payload'  => $this->jsonEncode($ubl),
            'created_by'       => $userId,
        ]);

        try {
            $resp = $this->client->submitDocument($ubl, $code);
        } catch (MyInvoisException $e) {
            $this->docs->update($docId, [
                'status'        => 'invalid',
                'error_payload' => $this->jsonEncode([
                    'message' => $e->getMessage(),
                    'http'    => $e->getCode(),
                    'body'    => $e->payload,
                ]),
            ]);
            $this->invoices->update($invoiceId, ['einvoice_status' => 'invalid']);
            throw $e;
        }

        $accepted = $resp['accepted'][0] ?? null;
        $rejected = $resp['rejected'][0] ?? null;

        // Only the original Invoice document drives the source invoice row's
        // LHDN status fields. Credit / debit / refund notes are tracked in
        // einvoice_documents only — they must NEVER overwrite the source
        // invoice's IRBM UUID / status, or we lose the link to the validated
        // original document on the LHDN portal.
        $isPrimary = ($type === 'invoice');

        if ($rejected) {
            $this->docs->update($docId, [
                'status'           => 'invalid',
                'submission_uid'   => $resp['submissionUid'],
                'response_payload' => $this->jsonEncode($resp['raw']),
                'error_payload'    => $this->jsonEncode($rejected),
            ]);
            if ($isPrimary) {
                $this->invoices->update($invoiceId, ['einvoice_status' => 'invalid']);
            }
            throw new MyInvoisException(
                'MyInvois rejected document: ' . ($rejected['error']['error'] ?? 'unknown'),
                422,
                $rejected
            );
        }

        $update = [
            'submission_uid'   => $resp['submissionUid'],
            'response_payload' => $this->jsonEncode($resp['raw']),
        ];
        $invUpdate = [
            'einvoice_status'         => 'pending',
            'einvoice_submission_uid' => $resp['submissionUid'],
        ];

        if ($accepted) {
            $update['irbm_uuid']    = $accepted['uuid'] ?? null;
            $invUpdate['einvoice_uuid'] = $accepted['uuid'] ?? null;
        }

        // Stub mode: synchronously mark valid so dev-mode flows can be tested without polling.
        if ($this->client->isStub()) {
            $update['status']            = 'valid';
            $update['irbm_long_id']      = 'STUB-LONG-' . substr(md5($code), 0, 24);
            $update['validated_at']      = $now;
            $update['cancellable_until'] = date('Y-m-d H:i:s', time() + 72 * 3600);
            $invUpdate['einvoice_status']            = 'valid';
            $invUpdate['einvoice_long_id']           = $update['irbm_long_id'];
            $invUpdate['einvoice_validated_at']      = $update['validated_at'];
            $invUpdate['einvoice_cancellable_until'] = $update['cancellable_until'];
        }

        $this->docs->update($docId, $update);
        if ($isPrimary) {
            $this->invoices->update($invoiceId, $invUpdate);
        }

        return [
            'document' => $this->docs->find($docId),
            'invoice'  => $this->invoices->find($invoiceId),
        ];
    }

    private function loadInvoice(int $invoiceId): array
    {
        $inv = $this->invoices->find($invoiceId);
        if (! $inv) {
            throw new \RuntimeException("Invoice #$invoiceId not found");
        }
        return $inv;
    }

    private function jsonEncode($value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
