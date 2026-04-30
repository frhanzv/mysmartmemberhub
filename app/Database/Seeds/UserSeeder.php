<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $roles = $this->db->table('roles')->get()->getResultArray();
        $roleIds = array_column($roles, 'id', 'slug');

        $users = [
            ['Admin',            'admin',    'admin@mysmartmemberhub.test',    'Admin@123',    'super_admin'],
            ['Membership Staff', 'staff',    'staff@mysmartmemberhub.test',    'Staff@123',    'membership_staff'],
            ['Finance Officer',  'finance',  'finance@mysmartmemberhub.test',  'Finance@123',  'finance'],
            ['Manager',          'manager',  'manager@mysmartmemberhub.test',  'Manager@123',  'manager'],
        ];

        $rows = [];
        foreach ($users as [$name, $username, $email, $password, $roleSlug]) {
            $rows[] = [
                'name'          => $name,
                'username'      => $username,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'role_id'       => $roleIds[$roleSlug] ?? 1,
                'status'        => 'active',
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }
        $this->db->table('users')->ignore(true)->insertBatch($rows);
    }
}
