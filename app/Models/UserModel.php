<?php

namespace App\Models;

class UserModel extends BaseModel
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected string $auditEntity = 'users';

    protected $allowedFields = [
        'name', 'username', 'email', 'password_hash', 'role_id',
        'status', 'last_login_at', 'reset_token', 'reset_expires_at',
    ];

    public function findForLogin(string $login): ?array
    {
        return $this->where('email', $login)
                    ->orWhere('username', $login)
                    ->where('status', 'active')
                    ->first();
    }

    public function withRole(int $id): ?array
    {
        $row = $this->db->table('users u')
            ->select('u.*, r.slug AS role_slug, r.name AS role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $id)
            ->where('u.deleted_at', null)
            ->get()->getRowArray();
        return $row ?: null;
    }
}
