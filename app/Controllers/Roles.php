<?php

namespace App\Controllers;

use App\Models\PermissionModel;
use App\Models\RoleModel;

class Roles extends BaseController
{
    public function index()
    {
        $roles = (new RoleModel())->orderBy('name')->findAll();
        return view('roles/index', compact('roles'));
    }

    public function edit(int $id)
    {
        $roles = new RoleModel();
        $role  = $roles->find($id);
        if (! $role) {
            return redirect()->to('roles')->with('error', 'Not found.');
        }
        $assigned = array_column($roles->permissionsFor($id), 'id');
        $grouped  = (new PermissionModel())->groupedAll();
        return view('roles/form', compact('role', 'grouped', 'assigned'));
    }

    public function update(int $id)
    {
        $roles = new RoleModel();
        $roles->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);
        $perms = $this->request->getPost('permissions') ?? [];
        $roles->syncPermissions($id, array_map('intval', $perms));
        // bust cached permissions for everyone (cheapest: clear current session's cache)
        session()->remove('permissions');
        return redirect()->to('roles')->with('success', 'Role saved.');
    }
}
