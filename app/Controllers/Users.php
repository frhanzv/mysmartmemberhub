<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;

class Users extends BaseController
{
    public function index()
    {
        $users = (new UserModel())
            ->select('users.*, r.name AS role_name')
            ->join('roles r', 'r.id = users.role_id', 'left')
            ->orderBy('users.id', 'DESC')
            ->paginate(20);
        $pager = (new UserModel())->pager;
        return view('users/index', compact('users', 'pager'));
    }

    public function create()
    {
        return view('users/form', ['user' => null, 'roles' => (new RoleModel())->findAll()]);
    }

    public function edit(int $id)
    {
        $user = (new UserModel())->find($id);
        if (! $user) {
            return redirect()->to('users')->with('error', 'Not found.');
        }
        return view('users/form', ['user' => $user, 'roles' => (new RoleModel())->findAll()]);
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
            'name'     => 'required|max_length[120]',
            'username' => 'required|max_length[80]',
            'email'    => 'required|valid_email|max_length[160]',
            'role_id'  => 'required|integer',
        ];
        if (! $id) {
            $rules['password'] = 'required|min_length[8]';
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'role_id'  => (int) $this->request->getPost('role_id'),
            'status'   => $this->request->getPost('status') ?: 'active',
        ];
        $pw = (string) $this->request->getPost('password');
        if ($pw !== '') {
            $data['password_hash'] = password_hash($pw, PASSWORD_BCRYPT);
        }

        $users = new UserModel();
        if ($id) {
            $users->update($id, $data);
        } else {
            $users->insert($data);
        }
        return redirect()->to('users')->with('success', 'User saved.');
    }

    public function delete(int $id)
    {
        if ($id === current_user_id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }
        (new UserModel())->delete($id);
        return redirect()->to('users')->with('success', 'User deleted.');
    }
}
