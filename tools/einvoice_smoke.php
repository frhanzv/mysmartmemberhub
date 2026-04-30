<?php

/**
 * Headless smoke test for the LHDN e-Invoice stub flow:
 *  - Seeds a member + plan + invoice
 *  - Submits via EInvoiceService in stub mode
 *  - Verifies invoices.einvoice_status == valid
 *  - Verifies einvoice_documents row created with stub UUID
 *  - Verifies UblDocumentBuilder produces a hashable JSON
 *
 * Usage: php tools/einvoice_smoke.php
 */
define('FCPATH', __DIR__ . '/../public/');
define('HOMEPATH', realpath(__DIR__ . '/..') . '/');
chdir(__DIR__ . '/..');
require __DIR__ . '/../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::preload($paths);
\CodeIgniter\Boot::bootTest($paths);

use App\Libraries\Einvoice\EInvoiceService;
use App\Libraries\Einvoice\MyInvoisClient;
use App\Libraries\Einvoice\UblDocumentBuilder;
use App\Libraries\SettingsService;
use App\Models\InvoiceModel;
use App\Models\MemberModel;
use App\Models\PlanModel;

// Force stub mode regardless of seeded environment
SettingsService::set('einvoice.environment', MyInvoisClient::ENV_STUB);

$invoices = new InvoiceModel();
$members  = new MemberModel();
$plans    = new PlanModel();

$plan = $plans->where('code', 'STD-1Y')->first();
if (! $plan) {
    fwrite(STDERR, "STD-1Y plan not seeded\n");
    exit(1);
}

$memberId = $members->insert([
    'membership_id'      => 'MEM-9001',
    'name'               => 'LHDN Smoke Test Bhd',
    'ic_no'              => '900101011234',
    'email'              => 'smoke@mysmartmemberhub.test',
    'phone'              => '+60123456789',
    'address'            => 'Level 10, Tower A',
    'plan_id'            => $plan['id'],
    'joined_date'        => date('Y-m-d'),
    'expiry_date'        => date('Y-m-d', strtotime('+1 year')),
    'status'             => 'active',
    'tin'                => 'C12345678900',
    'brn_or_nric'        => '202001012345',
    'registration_type'  => 'Company',
    'address_line1'      => 'Level 10, Tower A',
    'city'               => 'Kuala Lumpur',
    'postcode'           => '50000',
    'state_code'         => '14',
    'country_code'       => 'MYS',
], true);

$invoiceId = $invoices->insert([
    'invoice_no'  => 'INV-SMOKE-0001',
    'member_id'   => $memberId,
    'plan_id'     => $plan['id'],
    'amount'      => $plan['price'],
    'tax_percent' => 0,
    'tax_amount'  => 0,
    'total'       => $plan['price'],
    'status'      => 'issued',
    'issued_at'   => date('Y-m-d H:i:s'),
    'due_at'      => date('Y-m-d', strtotime('+14 days')),
], true);

// Sanity: builder produces JSON
$ubl = UblDocumentBuilder::build('invoice', $invoices->find($invoiceId), $members->find($memberId), $plan, (float) $plan['price']);
$json = json_encode($ubl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
assert(is_string($json) && $json !== false, 'UBL JSON must encode');
$decoded = json_decode($json, true);
assert(($decoded['Invoice'][0]['ID'][0]['_'] ?? null) === 'INV-SMOKE-0001', 'invoice ID round-trips');
assert(($decoded['Invoice'][0]['InvoiceTypeCode'][0]['_'] ?? null) === '01', 'invoice type code 01');
echo "OK builder produced ", strlen($json), " bytes; SHA256=", hash('sha256', $json), "\n";

$svc = new EInvoiceService(new MyInvoisClient(MyInvoisClient::ENV_STUB));
$res = $svc->submitInvoice($invoiceId, null);

assert($res['invoice']['einvoice_status'] === 'valid', 'invoice should be marked valid in stub mode');
assert(! empty($res['invoice']['einvoice_uuid']), 'IRBM UUID populated');
assert(! empty($res['invoice']['einvoice_long_id']), 'long ID populated');
assert(! empty($res['invoice']['einvoice_validated_at']), 'validated timestamp set');
assert($res['document']['status'] === 'valid', 'document row status valid');
assert($res['document']['environment'] === 'stub', 'environment recorded');
assert(strlen((string) $res['document']['request_payload']) > 100, 'request payload persisted');

// Public URL + cancel window
$url = $svc->publicUrl($res['invoice']);
assert(is_string($url) && str_contains($url, $res['invoice']['einvoice_uuid']), 'public URL includes UUID');
echo "OK public URL: $url\n";

// Cancel within 72h
$cancelled = $svc->cancelInvoice($invoiceId, 'Smoke test cancel');
assert($cancelled['invoice']['einvoice_status'] === 'cancelled', 'cancellation reflected');
echo "OK cancel flow\n";

echo "ALL SMOKE TESTS PASSED\n";
