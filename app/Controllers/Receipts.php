<?php

namespace App\Controllers;

use App\Libraries\ExcelExporter;
use App\Libraries\PdfGenerator;
use App\Libraries\SettingsService;
use App\Models\ReceiptModel;

class Receipts extends BaseController
{
    public function index()
    {
        $receipts = new ReceiptModel();
        $filters  = [
            'q'     => $this->request->getGet('q'),
            'month' => $this->request->getGet('month'),
        ];
        $rows  = $receipts->searchPaginated($filters, 20);
        $pager = $receipts->pager;
        return view('receipts/index', compact('rows', 'pager', 'filters'));
    }

    public function show(int $id)
    {
        $r = $this->loadFull($id);
        if (! $r) {
            return redirect()->to('receipts')->with('error', 'Not found.');
        }
        return view('receipts/show', ['r' => $r]);
    }

    public function pdf(int $id)
    {
        $r = $this->loadFull($id);
        if (! $r) {
            return redirect()->to('receipts')->with('error', 'Not found.');
        }
        $settings = SettingsService::all();
        $bin = PdfGenerator::fromView('pdf/receipt', compact('r', 'settings'));
        return $this->response->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $r['receipt_no'] . '.pdf"')
            ->setBody($bin);
    }

    public function export()
    {
        if (! module_enabled('exports')) { return module_disabled_response('exports'); }
        if (! can('report.export')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $rows = (new ReceiptModel())
            ->select('receipts.receipt_no, m.name AS member, m.membership_id, receipts.amount, receipts.issued_at, p.method, p.reference_no')
            ->join('members m', 'm.id = receipts.member_id')
            ->join('payments p', 'p.id = receipts.payment_id', 'left')
            ->orderBy('receipts.issued_at', 'DESC')->find();
        ExcelExporter::download(
            'receipts_' . date('Ymd_His') . '.xlsx',
            ['Receipt No', 'Member', 'Membership ID', 'Amount', 'Issued', 'Method', 'Reference'],
            array_map(fn ($r) => array_values($r), $rows)
        );
    }

    private function loadFull(int $id): ?array
    {
        return (new ReceiptModel())
            ->select('receipts.*, m.name AS member_name, m.membership_id, m.email, m.address, m.phone, p.method, p.reference_no, p.payment_date, i.invoice_no')
            ->join('members m', 'm.id = receipts.member_id')
            ->join('payments p', 'p.id = receipts.payment_id', 'left')
            ->join('invoices i', 'i.id = receipts.invoice_id', 'left')
            ->find($id);
    }
}
