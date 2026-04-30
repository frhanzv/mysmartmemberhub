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

            // ----- LHDN MyInvois supplier identity -----
            ['einvoice.enabled',          '0',                              'bool',    'einvoice'],
            ['einvoice.environment',      'sandbox',                        'string',  'einvoice'], // sandbox|prod|stub
            ['einvoice.supplier.tin',     'EI00000000010',                  'string',  'einvoice'], // sandbox test TIN
            ['einvoice.supplier.brn',     '202001012345',                   'string',  'einvoice'],
            ['einvoice.supplier.brn_scheme','BRN',                          'string',  'einvoice'], // BRN|NRIC|PASSPORT|ARMY
            ['einvoice.supplier.sst_no',  '',                               'string',  'einvoice'], // optional
            ['einvoice.supplier.msic',    '94991',                          'string',  'einvoice'], // membership orgs
            ['einvoice.supplier.activity','Activities of membership organisations n.e.c.', 'string', 'einvoice'],
            ['einvoice.supplier.address1','No. 1, Jalan Contoh',            'string',  'einvoice'],
            ['einvoice.supplier.address2','',                               'string',  'einvoice'],
            ['einvoice.supplier.city',    'Kuala Lumpur',                   'string',  'einvoice'],
            ['einvoice.supplier.postcode','50000',                          'string',  'einvoice'],
            ['einvoice.supplier.state',   '14',                             'string',  'einvoice'], // 14 = WP Kuala Lumpur
            ['einvoice.supplier.country', 'MYS',                            'string',  'einvoice'],
            ['einvoice.supplier.email',   'admin@mysmartmemberhub.test',    'string',  'einvoice'],
            ['einvoice.supplier.phone',   '+60312345678',                   'string',  'einvoice'],
            ['einvoice.portal.base',      'https://preprod.myinvois.hasil.gov.my', 'string', 'einvoice'],

            // ----- Module enable/disable (Super Admin only) -----
            // 'core' modules are always enabled (members, plans, dashboard, users, roles, settings).
            // The keys below control optional modules + cross-cutting features.
            ['module.payments.enabled',      '1', 'bool', 'module'],
            ['module.invoices.enabled',      '1', 'bool', 'module'],
            ['module.receipts.enabled',      '1', 'bool', 'module'],
            ['module.reports.enabled',       '1', 'bool', 'module'],
            ['module.einvoice.enabled',      '0', 'bool', 'module'], // LHDN MyInvois — off by default until creds wired
            ['module.notifications.enabled', '1', 'bool', 'module'],
            ['module.audit_log.enabled',     '1', 'bool', 'module'],
            ['module.exports.enabled',       '1', 'bool', 'module'], // Excel/CSV/PDF export buttons
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
