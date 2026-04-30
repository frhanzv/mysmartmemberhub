<?php

namespace App\Controllers;

use App\Libraries\AutoNumber;
use App\Libraries\ExcelExporter;
use App\Libraries\Notifier;
use App\Libraries\PdfGenerator;
use App\Libraries\SettingsService;
use App\Models\InvoiceModel;
use App\Models\MemberModel;
use App\Models\PaymentModel;
use App\Models\ReceiptModel;

class Payments extends BaseController
{
    public function index()
    {
        $payments = new PaymentModel();
        $filters  = [
            'q'      => $this->request->getGet('q'),
            'status' => $this->request->getGet('status'),
            'month'  => $this->request->getGet('month'),
        ];
        $rows  = $payments->searchPaginated($filters, 20);
        $pager = $payments->pager;
        return view('payments/index', compact('rows', 'pager', 'filters'));
    }

    public function show(int $id)
    {
        $p = (new PaymentModel())
            ->select('payments.*, m.name AS member_name, m.membership_id, i.invoice_no, i.total AS invoice_total')
            ->join('members m', 'm.id = payments.member_id', 'left')
            ->join('invoices i', 'i.id = payments.invoice_id', 'left')
            ->find($id);
        if (! $p) {
            return redirect()->to('payments')->with('error', 'Not found.');
        }
        $receipt = $p['receipt_id'] ? (new ReceiptModel())->find($p['receipt_id']) : null;
        return view('payments/show', compact('p', 'receipt'));
    }

    public function create()
    {
        $memberId = (int) ($this->request->getGet('member_id') ?? 0);
        $member   = $memberId ? (new MemberModel())->find($memberId) : null;
        $invoices = $memberId
            ? (new InvoiceModel())->where('member_id', $memberId)->where('status', 'issued')->find()
            : [];
        $members  = (new MemberModel())->select('id, name, membership_id')->orderBy('name')->find();
        return view('payments/form', compact('member', 'invoices', 'members'));
    }

    public function store()
    {
        if (! can('payment.create')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $rules = [
            'member_id'    => 'required|integer',
            'amount'       => 'required|numeric',
            'payment_date' => 'required|valid_date',
            'method'       => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $payments = new PaymentModel();
        $memberId = (int) $this->request->getPost('member_id');
        $amount   = (float) $this->request->getPost('amount');
        $invId    = (int) $this->request->getPost('invoice_id');
        if (! $invId) {
            $invId = (int) $payments->autoMatchInvoice($memberId, $amount);
        }

        $data = [
            'member_id'    => $memberId,
            'invoice_id'   => $invId ?: null,
            'amount'       => $amount,
            'payment_date' => $this->request->getPost('payment_date'),
            'method'       => $this->request->getPost('method'),
            'reference_no' => $this->request->getPost('reference_no'),
            'notes'        => $this->request->getPost('notes'),
            'status'       => 'pending',
            'created_by'   => current_user_id(),
        ];

        $proof = $this->request->getFile('proof');
        if ($proof && $proof->isValid() && ! $proof->hasMoved()) {
            $dir = WRITEPATH . 'uploads/proofs';
            @mkdir($dir, 0775, true);
            $name = $proof->getRandomName();
            $proof->move($dir, $name);
            $data['proof_path'] = 'proofs/' . $name;
        }

        $id = $payments->insert($data, true);

        Notifier::broadcastByRole('finance', 'payment.pending',
            'Payment pending approval', "RM " . number_format($amount, 2) . " from member #$memberId",
            site_url('payments/' . $id));

        return redirect()->to('payments/' . $id)->with('success', 'Payment recorded (pending approval).');
    }

    public function approve(int $id)
    {
        if (! can('payment.approve')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $payments = new PaymentModel();
        $p = $payments->find($id);
        if (! $p) {
            return redirect()->back()->with('error', 'Not found.');
        }
        if ($p['status'] === 'confirmed') {
            return redirect()->back()->with('warning', 'Already confirmed.');
        }

        $payments->update($id, [
            'status'      => 'confirmed',
            'approved_by' => current_user_id(),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        helper('module');
        $rcptNo = null;

        // generate receipt (only if Receipts module is on)
        if (module_enabled('receipts')) {
            $receipts = new ReceiptModel();
            $rcptNo   = AutoNumber::receiptNo();
            $rcptId   = $receipts->insert([
                'receipt_no' => $rcptNo,
                'payment_id' => $id,
                'invoice_id' => $p['invoice_id'],
                'member_id'  => $p['member_id'],
                'amount'     => $p['amount'],
                'issued_at'  => date('Y-m-d H:i:s'),
                'created_by' => current_user_id(),
            ], true);

            $pdfPath = $this->renderReceiptPdf((int) $rcptId);
            $receipts->update($rcptId, ['pdf_path' => $pdfPath]);
            $payments->update($id, ['receipt_id' => $rcptId]);

            Notifier::notify((int) $p['created_by'], 'receipt.ready',
                'Receipt issued', "Receipt $rcptNo is ready.", site_url('receipts/' . $rcptId));
        }

        // Mark invoice paid if Invoices module is on
        if ($p['invoice_id'] && module_enabled('invoices')) {
            (new InvoiceModel())->update($p['invoice_id'], ['status' => 'paid']);
        }

        $msg = $rcptNo
            ? "Payment confirmed. Receipt $rcptNo issued."
            : 'Payment confirmed. (Receipts module disabled — no receipt generated.)';
        return redirect()->to('payments/' . $id)->with('success', $msg);
    }

    public function reject(int $id)
    {
        if (! can('payment.approve')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        (new PaymentModel())->update($id, [
            'status'      => 'rejected',
            'approved_by' => current_user_id(),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->back()->with('success', 'Payment rejected.');
    }

    /** Reverse a confirmed payment. If the related invoice has a validated
     *  e-Invoice, automatically issue a Refund Note to LHDN. */
    public function reverse(int $id)
    {
        if (! can('payment.approve')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $reason = trim((string) $this->request->getPost('reason')) ?: 'Refund';
        $payments = new PaymentModel();
        $p = $payments->find($id);
        if (! $p || $p['status'] !== 'confirmed') {
            return redirect()->back()->with('error', 'Only confirmed payments can be reversed.');
        }
        $payments->update($id, ['status' => 'reversed', 'approved_at' => date('Y-m-d H:i:s')]);

        $messages = ['Payment reversed.'];
        if ($p['invoice_id']) {
            (new InvoiceModel())->update($p['invoice_id'], ['status' => 'issued']);
            $inv = (new InvoiceModel())->find($p['invoice_id']);
            if ($inv && ! empty($inv['einvoice_uuid']) && ($inv['einvoice_status'] ?? '') === 'valid') {
                try {
                    (new \App\Libraries\Einvoice\EInvoiceService())
                        ->submitRefund($p['invoice_id'], (float) $p['amount'], current_user_id());
                    $messages[] = 'Refund Note issued to LHDN.';
                } catch (\Throwable $e) {
                    $messages[] = 'Refund Note submission failed: ' . $e->getMessage();
                }
            }
        }
        return redirect()->back()->with('success', implode(' ', $messages));
    }

    public function delete(int $id)
    {
        if (! can('payment.delete')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        (new PaymentModel())->delete($id);
        return redirect()->to('payments')->with('success', 'Payment deleted.');
    }

    public function export()
    {
        if (! module_enabled('exports')) { return module_disabled_response('exports'); }
        if (! can('report.export')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }
        $rows = (new PaymentModel())
            ->select('payments.id, m.name AS member, m.membership_id, payments.amount, payments.payment_date, payments.method, payments.reference_no, payments.status')
            ->join('members m', 'm.id = payments.member_id', 'left')
            ->orderBy('payments.payment_date', 'DESC')
            ->find();
        ExcelExporter::download(
            'payments_' . date('Ymd_His') . '.xlsx',
            ['ID', 'Member', 'Membership ID', 'Amount', 'Date', 'Method', 'Reference', 'Status'],
            array_map(fn ($r) => array_values($r), $rows)
        );
    }

    private function renderReceiptPdf(int $receiptId): string
    {
        $r = (new ReceiptModel())
            ->select('receipts.*, m.name AS member_name, m.membership_id, m.email, p.method, p.reference_no, p.payment_date, i.invoice_no')
            ->join('members m', 'm.id = receipts.member_id')
            ->join('payments p', 'p.id = receipts.payment_id', 'left')
            ->join('invoices i', 'i.id = receipts.invoice_id', 'left')
            ->find($receiptId);
        $settings = SettingsService::all();
        return PdfGenerator::saveFromView('pdf/receipt', compact('r', 'settings'),
            'receipts', $r['receipt_no']);
    }
}
