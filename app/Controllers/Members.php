<?php

namespace App\Controllers;

use App\Libraries\AutoNumber;
use App\Libraries\ExcelExporter;
use App\Models\InvoiceModel;
use App\Models\MemberDocumentModel;
use App\Models\MemberModel;
use App\Models\PaymentModel;
use App\Models\PlanModel;

class Members extends BaseController
{
    public function index()
    {
        $members = new MemberModel();
        $members->refreshStatuses();

        $filters = [
            'q'        => $this->request->getGet('q'),
            'status'   => $this->request->getGet('status'),
            'plan_id'  => $this->request->getGet('plan_id'),
        ];
        $rows  = $members->searchPaginated($filters, 20);
        $pager = $members->pager;
        $plans = (new PlanModel())->active();

        return view('members/index', compact('rows', 'pager', 'filters', 'plans'));
    }

    public function show(int $id)
    {
        $member = (new MemberModel())
            ->select('members.*, mp.name AS plan_name, mp.code AS plan_code, mp.duration_months')
            ->join('membership_plans mp', 'mp.id = members.plan_id', 'left')
            ->find($id);
        if (! $member) {
            return redirect()->to('members')->with('error', 'Member not found.');
        }
        $payments = (new PaymentModel())
            ->where('member_id', $id)->orderBy('payment_date', 'DESC')->find();
        $invoices = (new InvoiceModel())
            ->where('member_id', $id)->orderBy('issued_at', 'DESC')->find();
        $documents = (new MemberDocumentModel())
            ->where('member_id', $id)->orderBy('id', 'DESC')->find();

        return view('members/show', compact('member', 'payments', 'invoices', 'documents'));
    }

    public function create()
    {
        return view('members/form', [
            'member' => null,
            'plans'  => (new PlanModel())->active(),
        ]);
    }

    public function store()
    {
        if (! can('member.create')) {
            return $this->fail403();
        }
        $rules = [
            'name'        => 'required|min_length[2]|max_length[160]',
            'plan_id'     => 'required|integer',
            'joined_date' => 'required|valid_date',
            'email'       => 'permit_empty|valid_email|max_length[160]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $plans = new PlanModel();
        $plan  = $plans->find((int) $this->request->getPost('plan_id'));
        if (! $plan) {
            return redirect()->back()->withInput()->with('error', 'Invalid plan.');
        }

        $joined = $this->request->getPost('joined_date');
        $expiry = date('Y-m-d', strtotime("$joined +" . (int) $plan['duration_months'] . ' months'));

        $data = [
            'membership_id' => AutoNumber::memberId(),
            'name'          => $this->request->getPost('name'),
            'ic_no'         => $this->request->getPost('ic_no'),
            'email'         => $this->request->getPost('email'),
            'phone'         => $this->request->getPost('phone'),
            'address'       => $this->request->getPost('address'),
            'plan_id'       => (int) $plan['id'],
            'joined_date'   => $joined,
            'expiry_date'   => $expiry,
            'status'        => 'active',
            'notes'         => $this->request->getPost('notes'),
            'created_by'    => current_user_id(),
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && ! $photo->hasMoved()) {
            $dir = WRITEPATH . 'uploads/photos';
            @mkdir($dir, 0775, true);
            $name = $photo->getRandomName();
            $photo->move($dir, $name);
            $data['photo_path'] = 'photos/' . $name;
        }

        $id = (new MemberModel())->insert($data, true);

        // Auto-create issued invoice for the plan price
        $tax     = (float) \App\Libraries\SettingsService::get('invoice.tax_percent', 0);
        $taxAmt  = round(((float) $plan['price']) * $tax / 100, 2);
        $total   = round(((float) $plan['price']) + $taxAmt, 2);
        (new InvoiceModel())->insert([
            'invoice_no'  => AutoNumber::invoiceNo(),
            'member_id'   => $id,
            'plan_id'     => $plan['id'],
            'amount'      => $plan['price'],
            'tax_percent' => $tax,
            'tax_amount'  => $taxAmt,
            'total'       => $total,
            'status'      => 'issued',
            'issued_at'   => date('Y-m-d'),
            'due_at'      => date('Y-m-d', strtotime('+14 days')),
            'created_by'  => current_user_id(),
        ]);

        return redirect()->to('members/' . $id)->with('success', 'Member registered.');
    }

    public function edit(int $id)
    {
        $member = (new MemberModel())->find($id);
        if (! $member) {
            return redirect()->to('members')->with('error', 'Member not found.');
        }
        return view('members/form', [
            'member' => $member,
            'plans'  => (new PlanModel())->active(),
        ]);
    }

    public function update(int $id)
    {
        if (! can('member.edit')) {
            return $this->fail403();
        }
        $members = new MemberModel();
        $member  = $members->find($id);
        if (! $member) {
            return redirect()->to('members')->with('error', 'Member not found.');
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'ic_no'       => $this->request->getPost('ic_no'),
            'email'       => $this->request->getPost('email'),
            'phone'       => $this->request->getPost('phone'),
            'address'     => $this->request->getPost('address'),
            'plan_id'     => (int) $this->request->getPost('plan_id'),
            'joined_date' => $this->request->getPost('joined_date'),
            'expiry_date' => $this->request->getPost('expiry_date'),
            'status'      => $this->request->getPost('status'),
            'notes'       => $this->request->getPost('notes'),
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && ! $photo->hasMoved()) {
            $dir = WRITEPATH . 'uploads/photos';
            @mkdir($dir, 0775, true);
            $name = $photo->getRandomName();
            $photo->move($dir, $name);
            $data['photo_path'] = 'photos/' . $name;
        }

        $members->update($id, $data);
        return redirect()->to('members/' . $id)->with('success', 'Member updated.');
    }

    public function delete(int $id)
    {
        if (! can('member.delete')) {
            return $this->fail403();
        }
        (new MemberModel())->delete($id);
        return redirect()->to('members')->with('success', 'Member deleted (soft).');
    }

    public function renew(int $id)
    {
        $members = new MemberModel();
        $m = $members->find($id);
        if (! $m) {
            return redirect()->back()->with('error', 'Member not found.');
        }
        $plan = (new PlanModel())->find($m['plan_id']);
        if (! $plan) {
            return redirect()->back()->with('error', 'No active plan on this member.');
        }
        $base = max($m['expiry_date'], date('Y-m-d'));
        $newExpiry = date('Y-m-d', strtotime("$base +" . (int) $plan['duration_months'] . ' months'));
        $members->update($id, ['expiry_date' => $newExpiry, 'status' => 'active']);
        return redirect()->back()->with('success', 'Renewed until ' . $newExpiry);
    }

    public function importForm()
    {
        return view('members/import');
    }

    public function import()
    {
        if (! can('member.import')) {
            return $this->fail403();
        }
        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Please choose a valid .xlsx file.');
        }
        $rows = ExcelExporter::readRows($file->getTempName());
        if (count($rows) < 2) {
            return redirect()->back()->with('error', 'Sheet is empty.');
        }
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        $idx    = array_flip($header);

        $plans = new PlanModel();
        $planByCode = array_column($plans->findAll(), null, 'code');

        $members = new MemberModel();
        $created = 0;
        for ($i = 1; $i < count($rows); $i++) {
            $r = $rows[$i];
            if (! trim((string) ($r[$idx['name'] ?? -1] ?? ''))) {
                continue;
            }
            $planCode = trim((string) ($r[$idx['plan_code'] ?? -1] ?? ''));
            $plan     = $planByCode[$planCode] ?? null;
            if (! $plan) {
                continue;
            }
            $joined = (string) ($r[$idx['joined_date'] ?? -1] ?? date('Y-m-d'));
            $joined = date('Y-m-d', strtotime($joined) ?: time());
            $expiry = date('Y-m-d', strtotime("$joined +" . (int) $plan['duration_months'] . ' months'));

            $members->insert([
                'membership_id' => AutoNumber::memberId(),
                'name'          => trim((string) $r[$idx['name']]),
                'ic_no'         => trim((string) ($r[$idx['ic_no'] ?? -1] ?? '')),
                'email'         => trim((string) ($r[$idx['email'] ?? -1] ?? '')),
                'phone'         => trim((string) ($r[$idx['phone'] ?? -1] ?? '')),
                'address'       => trim((string) ($r[$idx['address'] ?? -1] ?? '')),
                'plan_id'       => (int) $plan['id'],
                'joined_date'   => $joined,
                'expiry_date'   => $expiry,
                'status'        => 'active',
                'created_by'    => current_user_id(),
            ]);
            $created++;
        }
        return redirect()->to('members')->with('success', "Imported $created members.");
    }

    public function export()
    {
        if (! can('report.export')) {
            return $this->fail403();
        }
        $rows = (new MemberModel())
            ->select('membership_id, name, ic_no, email, phone, joined_date, expiry_date, status')
            ->findAll();
        ExcelExporter::download(
            'members_' . date('Ymd_His') . '.xlsx',
            ['Membership ID', 'Name', 'IC', 'Email', 'Phone', 'Joined', 'Expiry', 'Status'],
            array_map(fn ($r) => array_values($r), $rows)
        );
    }

    private function fail403()
    {
        return $this->response->setStatusCode(403)->setBody('Forbidden');
    }
}
