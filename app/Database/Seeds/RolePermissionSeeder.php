<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // ---- permissions ----
        $permissions = [
            ['member',  'member.view',         'View members'],
            ['member',  'member.create',       'Create members'],
            ['member',  'member.edit',         'Edit members'],
            ['member',  'member.delete',       'Delete members'],
            ['member',  'member.import',       'Bulk import members'],
            ['plan',    'plan.manage',         'Manage membership plans'],
            ['payment', 'payment.view',        'View payments'],
            ['payment', 'payment.create',      'Record payments'],
            ['payment', 'payment.edit',        'Edit payments'],
            ['payment', 'payment.approve',     'Approve / reject payments'],
            ['payment', 'payment.delete',      'Delete payments'],
            ['invoice', 'invoice.view',        'View invoices'],
            ['invoice', 'invoice.generate',    'Generate invoices'],
            ['invoice', 'invoice.email',       'Email invoices'],
            ['receipt', 'receipt.view',        'View receipts'],
            ['receipt', 'receipt.generate',    'Generate receipts'],
            ['report',  'report.view',         'View reports'],
            ['report',  'report.export',       'Export reports'],
            ['user',    'user.manage',         'Manage users'],
            ['role',    'role.manage',         'Manage roles & permissions'],
            ['setting', 'setting.manage',      'Manage system settings'],
            ['audit',   'audit.view',          'View audit log'],
        ];

        $permData = [];
        foreach ($permissions as $p) {
            $permData[] = ['group_name' => $p[0], 'slug' => $p[1], 'description' => $p[2]];
        }
        $this->db->table('permissions')->ignore(true)->insertBatch($permData);

        // ---- roles ----
        $roles = [
            ['name' => 'Super Admin',       'slug' => 'super_admin',       'description' => 'Full access',                'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Membership Staff',  'slug' => 'membership_staff',  'description' => 'Manage members and payments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Finance',           'slug' => 'finance',           'description' => 'Invoices, receipts, reports', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manager',           'slug' => 'manager',           'description' => 'Read-only dashboard & reports', 'created_at' => $now, 'updated_at' => $now],
        ];
        $this->db->table('roles')->ignore(true)->insertBatch($roles);

        // ---- role -> permission mapping ----
        $rolePerms = [
            'super_admin'      => '*',
            'membership_staff' => [
                'member.view', 'member.create', 'member.edit', 'member.delete', 'member.import',
                'payment.view', 'payment.create', 'payment.edit',
                'invoice.view', 'receipt.view',
            ],
            'finance' => [
                'member.view',
                'payment.view', 'payment.approve',
                'invoice.view', 'invoice.generate', 'invoice.email',
                'receipt.view', 'receipt.generate',
                'report.view', 'report.export',
            ],
            'manager' => [
                'member.view', 'payment.view', 'invoice.view', 'receipt.view',
                'report.view', 'audit.view',
            ],
        ];

        $allPerms = $this->db->table('permissions')->get()->getResultArray();
        $permIdBySlug = array_column($allPerms, 'id', 'slug');

        $allRoles = $this->db->table('roles')->get()->getResultArray();
        $roleIdBySlug = array_column($allRoles, 'id', 'slug');

        $rows = [];
        foreach ($rolePerms as $roleSlug => $perms) {
            $roleId = $roleIdBySlug[$roleSlug] ?? null;
            if (! $roleId) {
                continue;
            }
            $list = $perms === '*' ? array_keys($permIdBySlug) : $perms;
            foreach ($list as $slug) {
                if (isset($permIdBySlug[$slug])) {
                    $rows[] = ['role_id' => $roleId, 'permission_id' => $permIdBySlug[$slug]];
                }
            }
        }
        if ($rows) {
            $this->db->table('role_permissions')->ignore(true)->insertBatch($rows);
        }
    }
}
