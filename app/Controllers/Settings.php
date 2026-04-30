<?php

namespace App\Controllers;

use App\Libraries\SettingsService;
use App\Models\SettingModel;

class Settings extends BaseController
{
    public function index()
    {
        $rows = (new SettingModel())->orderBy('group_name')->orderBy('key_name')->findAll();
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['group_name'] ?: 'general'][] = $r;
        }

        $dropdownGrouped = (new \App\Models\DropdownOptionModel())->allGrouped();
        $dropdownCategories = (new \App\Models\DropdownOptionModel())->categories();

        return view('settings/index', compact('grouped', 'dropdownGrouped', 'dropdownCategories'));
    }

    public function update()
    {
        $values = $this->request->getPost('settings') ?? [];
        foreach ($values as $key => $val) {
            SettingsService::set($key, $val);
        }

        // logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
            $dir = WRITEPATH . 'uploads/branding';
            @mkdir($dir, 0775, true);
            $name = $logo->getRandomName();
            $logo->move($dir, $name);
            SettingsService::set('branding.logo', 'branding/' . $name);
        }

        return redirect()->to('settings')->with('success', 'Settings saved.');
    }
}
