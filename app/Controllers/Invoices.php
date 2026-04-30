<?php

namespace App\Controllers;

use App\Libraries\AutoNumber;
use App\Libraries\Einvoice\EInvoiceService;
use App\Libraries\Einvoice\MyInvoisException;
use App\Libraries\ExcelExporter;
use App\Libraries\PdfGenerator;
use App\Libraries\SettingsService;
use App\Models\EinvoiceDocumentModel;
use App\Models\InvoiceModel;
use App\Models\MemberModel;
use App\Models\PlanModel;

class Invoices extends BaseController
{
    public function index()
    {
        $invoices = new InvoiceModel();
        $filters  = [
            'q'      => $this->request->getGet('q'),
            'status' => $this->request->getGet('status'),
            'month'  => $this->request->getGet('month'),
        ];
        $rows  = $invoices->searchPaginated($filters, 20);
        $pager = $invoices->pager;
        return view('invoices/index', compact('rows', 'pager', 'filters'));
    }

    public function show(int $id)
    {
        $i = $this->loadFull($id);
        if (! $i) {
            return redirect()->to('invoices')->with('error', 'Not found.');
        }
        $einvoiceDoc = (new EinvoiceDocumentModel())->latestForInvoice($id);
        $einvoicePublicUrl = (new EInvoiceService())->publicUrl($i);
        return view('invoices/show', [
            'i'                => $i,
            'einvoiceDoc'      => $einvoiceDoc,
            'einvoicePublicUrl'=> $einvoicePublicUrl,
        ]);
    }

    public function einvoiceSubmit(int $id)
    {
        if (! can('invoice.email') && ! can('invoice.generate')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        try {
            (new EInvoiceService())->submitInvoice($id, current_user_id());
            return redirect()->to('invoices/' . $id)->with('success', 'e-Invoice submitted to MyInvois.');
        } catch (MyInvoisException $e) {
            return redirect()->to('invoices/' . $id)->with('error', 'MyInvois rejected: ' . $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'einvoice submit failed for invoice {id}: {msg}', ['id' => $id, 'msg' => $e->getMessage()]);
            return redirect()->to('invoices/' . $id)->with('error', 'e-Invoice submission failed: ' . $e->getMessage());
        }
    }

    public function einvoiceCancel(int $id)
    {
        if (! can('invoice.email') && ! can('invoice.generate')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            return redirect()->to('invoices/' . $id)->with('error', 'Cancellation reason is required.');
        }
        try {
            (new EInvoiceService())->cancelInvoice($id, $reason, current_user_id());
            return redirect()->to('invoices/' . $id)->with('success', 'e-Invoice cancelled.');
        } catch (\Throwable $e) {
            return redirect()->to('invoices/' . $id)->with('error', 'Cancel failed: ' . $e->getMessage());
        }
    }

    public function einvoiceRefresh(int $id)
    {
        if (! can('invoice.email') && ! can('invoice.generate')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        try {
            (new EInvoiceService())->refreshStatus($id);
            return redirect()->to('invoices/' . $id)->with('success', 'e-Invoice status refreshed.');
        } catch (\Throwable $e) {
            return redirect()->to('invoices/' . $id)->with('error', 'Refresh failed: ' . $e->getMessage());
        }
    }

    public function generateForMember(int $memberId)
    {
        if (! can('invoice.generate')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $member = (new MemberModel())->find($memberId);
        if (! $member) {
            return redirect()->back()->with('error', 'Member not found.');
        }
        $plan = $member['plan_id'] ? (new PlanModel())->find($member['plan_id']) : null;
        if (! $plan) {
            return redirect()->back()->with('error', 'Member has no active plan.');
        }
        $tax    = (float) SettingsService::get('invoice.tax_percent', 0);
        $taxAmt = round($plan['price'] * $tax / 100, 2);
        $total  = round($plan['price'] + $taxAmt, 2);

        $id = (new InvoiceModel())->insert([
            'invoice_no'  => AutoNumber::invoiceNo(),
            'member_id'   => $memberId,
            'plan_id'     => $plan['id'],
            'amount'      => $plan['price'],
            'tax_percent' => $tax,
            'tax_amount'  => $taxAmt,
            'total'       => $total,
            'status'      => 'issued',
            'issued_at'   => date('Y-m-d'),
            'due_at'      => date('Y-m-d', strtotime('+14 days')),
            'created_by'  => current_user_id(),
        ], true);

        return redirect()->to('invoices/' . $id)->with('success', 'Invoice generated.');
    }

    public function pdf(int $id)
    {
        $i = $this->loadFull($id);
        if (! $i) {
            return redirect()->to('invoices')->with('error', 'Not found.');
        }
        $settings = SettingsService::all();
        $einvoice = (new EInvoiceService())->publicUrl($i);
        $qrDataUri = $einvoice ? \App\Libraries\Einvoice\QrRenderer::dataUri($einvoice) : null;
        $bin = PdfGenerator::fromView('pdf/invoice', compact('i', 'settings', 'einvoice', 'qrDataUri'));
        return $this->response->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $i['invoice_no'] . '.pdf"')
            ->setBody($bin);
    }

    public function email(int $id)
    {
        if (! can('invoice.email')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $i = $this->loadFull($id);
        if (! $i || empty($i['email'])) {
            return redirect()->back()->with('error', 'Member has no email on file.');
        }
        $settings = SettingsService::all();
        $bin = PdfGenerator::fromView('pdf/invoice', compact('i', 'settings'));

        $email = service('email');
        $email->setTo($i['email']);
        $email->setSubject('Invoice ' . $i['invoice_no']);
        $email->setMessage('Please find attached your invoice ' . $i['invoice_no']);
        $email->attach($bin, 'attachment', $i['invoice_no'] . '.pdf', 'application/pdf');

        // In dev, just log instead of sending
        log_message('info', 'Email invoice {no} to {to}', ['no' => $i['invoice_no'], 'to' => $i['email']]);
        @$email->send();

        return redirect()->back()->with('success', 'Invoice queued for email to ' . $i['email']);
    }

    public function export()
    {
        if (! can('report.export')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $rows = (new InvoiceModel())
            ->select('invoices.invoice_no, m.name AS member, m.membership_id, invoices.amount, invoices.tax_amount, invoices.total, invoices.status, invoices.issued_at, invoices.due_at')
            ->join('members m', 'm.id = invoices.member_id', 'left')
            ->orderBy('invoices.issued_at', 'DESC')
            ->find();
        ExcelExporter::download(
            'invoices_' . date('Ymd_His') . '.xlsx',
            ['Invoice No', 'Member', 'Membership ID', 'Amount', 'Tax', 'Total', 'Status', 'Issued', 'Due'],
            array_map(fn ($r) => array_values($r), $rows)
        );
    }

    private function loadFull(int $id): ?array
    {
        return (new InvoiceModel())
            ->select('invoices.*, m.name AS member_name, m.membership_id, m.email, m.address, m.phone, mp.name AS plan_name')
            ->join('members m', 'm.id = invoices.member_id')
            ->join('membership_plans mp', 'mp.id = invoices.plan_id', 'left')
            ->find($id);
    }
}
