<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $defaults = [
            ['company.name',              'MySmartMemberHub Sdn Bhd',       'string',  'company'],
            ['company.address',           'No. 1, Jalan Contoh, Kuala Lumpur', 'string', 'company'],
            ['company.phone',             '+60 3-1234 5678',                'string',  'company'],
            ['company.email',             'admin@mysmartmemberhub.test',    'string',  'company'],
            ['branding.logo',             '',                               'file',    'branding'],
            ['invoice.footer',            'Thank you for your support!',    'string',  'invoice'],
            ['invoice.tax_percent',       '0',                              'decimal', 'invoice'],
            ['payment.instructions',      "Bank: Maybank\nAcc: 1234567890\nName: MySmartMemberHub",
                                                                            'string',  'payment'],
            ['system.session_timeout_minutes', '120',                       'int',     'system'],
            ['counter.member',            '0',                              'int',     'counter'],
            ['counter.invoice.' . date('Y'),  '0',                          'int',     'counter'],
            ['counter.receipt.' . date('Y'),  '0',                          'int',     'counter'],
        ];

        $rows = [];
        foreach ($defaults as [$key, $value, $type, $group]) {
            $rows[] = [
                'key_name'   => $key,
                'value'      => $value,
                'type'       => $type,
                'group_name' => $group,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->db->table('settings')->ignore(true)->insertBatch($rows);
    }
}
