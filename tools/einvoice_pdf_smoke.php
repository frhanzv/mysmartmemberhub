<?php

/** Verifies that an invoice PDF for a stub-validated invoice renders with QR. */
define('FCPATH', __DIR__ . '/../public/');
chdir(__DIR__ . '/..');
require __DIR__ . '/../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::preload($paths);
\CodeIgniter\Boot::bootTest($paths);

use App\Libraries\Einvoice\EInvoiceService;
use App\Libraries\Einvoice\MyInvoisClient;
use App\Libraries\Einvoice\QrRenderer;
use App\Libraries\PdfGenerator;
use App\Libraries\SettingsService;
use App\Models\InvoiceModel;
use App\Models\MemberModel;
use App\Models\PlanModel;

SettingsService::set('einvoice.environment', MyInvoisClient::ENV_STUB);

$invoices = new InvoiceModel();
$members  = new MemberModel();
$plans    = new PlanModel();

$plan = $plans->where('code', 'STD-1Y')->first();
$memberId = $members->insert([
    'membership_id' => 'MEM-PDF-1',
    'name' => 'PDF Test',
    'plan_id' => $plan['id'],
    'joined_date' => date('Y-m-d'),
    'expiry_date' => date('Y-m-d', strtotime('+1 year')),
    'status' => 'active',
    'tin' => 'C12345678900',
    'state_code' => '14',
    'country_code' => 'MYS',
], true);
$invoiceId = $invoices->insert([
    'invoice_no' => 'INV-PDF-1',
    'member_id' => $memberId,
    'plan_id' => $plan['id'],
    'amount' => $plan['price'],
    'tax_percent' => 0, 'tax_amount' => 0, 'total' => $plan['price'],
    'status' => 'issued',
    'issued_at' => date('Y-m-d H:i:s'),
    'due_at' => date('Y-m-d', strtotime('+14 days')),
], true);
(new EInvoiceService())->submitInvoice($invoiceId, null);

$row = (new InvoiceModel())
    ->select('invoices.*, m.name AS member_name, m.membership_id, m.email, m.phone, m.address, mp.name AS plan_name')
    ->join('members m', 'm.id = invoices.member_id')
    ->join('membership_plans mp', 'mp.id = invoices.plan_id', 'left')
    ->find($invoiceId);
$publicUrl = (new EInvoiceService())->publicUrl($row);
$qr = QrRenderer::dataUri($publicUrl);
assert(str_starts_with($qr, 'data:image/png;base64,'), 'QR should be a base64 PNG');
$bin = PdfGenerator::fromView('pdf/invoice', [
    'i' => $row,
    'settings' => SettingsService::all(),
    'einvoice' => $publicUrl,
    'qrDataUri' => $qr,
]);
assert(is_string($bin) && substr($bin, 0, 4) === '%PDF', 'PDF magic bytes expected');
echo "OK PDF rendered: ", strlen($bin), " bytes; QR data URI ok\n";
