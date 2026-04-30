<?php

namespace App\Controllers;

use App\Models\PlanModel;

class Plans extends BaseController
{
    public function index()
    {
        $plans = (new PlanModel())->orderBy('name')->paginate(20);
        $pager = (new PlanModel())->pager;
        return view('plans/index', compact('plans', 'pager'));
    }

    public function create()
    {
        return view('plans/form', ['plan' => null]);
    }

    public function edit(int $id)
    {
        $plan = (new PlanModel())->find($id);
        if (! $plan) {
            return redirect()->to('plans')->with('error', 'Not found.');
        }
        return view('plans/form', ['plan' => $plan]);
    }

    public function store()
    {
        return $this->save();
    }

    public function update(int $id)
    {
        return $this->save($id);
    }

    private function save(?int $id = null)
    {
        $rules = [
            'code' => 'required|max_length[40]',
            'name' => 'required|max_length[120]',
            'price' => 'required|numeric',
            'duration_months' => 'required|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }
        $data = [
            'code'                => $this->request->getPost('code'),
            'name'                => $this->request->getPost('name'),
            'price'               => $this->request->getPost('price'),
            'duration_months'     => (int) $this->request->getPost('duration_months'),
            'description'         => $this->request->getPost('description'),
            'is_active'           => $this->request->getPost('is_active') ? 1 : 0,
            'classification_code' => $this->request->getPost('classification_code') ?: '022',
            'tax_type'            => $this->request->getPost('tax_type') ?: '06',
            'tax_rate'            => (float) $this->request->getPost('tax_rate'),
            'unit_code'           => $this->request->getPost('unit_code') ?: 'MON',
        ];
        $plans = new PlanModel();
        if ($id) {
            $plans->update($id, $data);
        } else {
            $plans->insert($data);
        }
        return redirect()->to('plans')->with('success', 'Plan saved.');
    }

    public function delete(int $id)
    {
        (new PlanModel())->delete($id);
        return redirect()->to('plans')->with('success', 'Plan deleted.');
    }
}
