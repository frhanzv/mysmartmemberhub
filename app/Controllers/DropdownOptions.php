<?php

namespace App\Controllers;

use App\Models\DropdownOptionModel;

class DropdownOptions extends BaseController
{
    public function index()
    {
        $model   = new DropdownOptionModel();
        $grouped = $model->allGrouped();
        return view('dropdown_options/index', compact('grouped'));
    }

    public function create()
    {
        $categories = (new DropdownOptionModel())->categories();
        return view('dropdown_options/form', [
            'option'     => null,
            'categories' => $categories,
        ]);
    }

    public function store()
    {
        $rules = [
            'category' => 'required|max_length[60]',
            'label'    => 'required|max_length[120]',
            'value'    => 'required|max_length[80]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $category = $this->request->getPost('category');
        // Support "new category" field
        if ($category === '__new__') {
            $category = trim($this->request->getPost('new_category'));
            if (! $category) {
                return redirect()->back()->withInput()->with('error', 'Category name is required.');
            }
            // Normalise: lowercase, underscores
            $category = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $category));
        }

        (new DropdownOptionModel())->insert([
            'category'   => $category,
            'label'      => $this->request->getPost('label'),
            'value'      => $this->request->getPost('value'),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('settings/dropdown-options')
            ->with('success', 'Option added.');
    }

    public function edit(int $id)
    {
        $model  = new DropdownOptionModel();
        $option = $model->find($id);
        if (! $option) {
            return redirect()->to('settings/dropdown-options')->with('error', 'Not found.');
        }
        $categories = $model->categories();
        return view('dropdown_options/form', compact('option', 'categories'));
    }

    public function update(int $id)
    {
        $model  = new DropdownOptionModel();
        $option = $model->find($id);
        if (! $option) {
            return redirect()->to('settings/dropdown-options')->with('error', 'Not found.');
        }

        $rules = [
            'category' => 'required|max_length[60]',
            'label'    => 'required|max_length[120]',
            'value'    => 'required|max_length[80]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $category = $this->request->getPost('category');
        if ($category === '__new__') {
            $category = trim($this->request->getPost('new_category'));
            if (! $category) {
                return redirect()->back()->withInput()->with('error', 'Category name is required.');
            }
            $category = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $category));
        }

        $model->update($id, [
            'category'   => $category,
            'label'      => $this->request->getPost('label'),
            'value'      => $this->request->getPost('value'),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('settings/dropdown-options')
            ->with('success', 'Option updated.');
    }

    public function delete(int $id)
    {
        (new DropdownOptionModel())->delete($id);
        return redirect()->to('settings/dropdown-options')
            ->with('success', 'Option deleted.');
    }
}
