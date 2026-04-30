<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Backfill `module.*.enabled` settings for existing installs.
 *
 * The same keys are defined in SettingSeeder so a fresh `migrate:refresh && db:seed`
 * already provisions them — this migration ensures upgrades don't have to re-run
 * the seeder. Each insert is idempotent.
 */
class ModuleToggles extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        $defaults = [
            ['module.payments.enabled',      '1', 'bool', 'module'],
            ['module.invoices.enabled',      '1', 'bool', 'module'],
            ['module.receipts.enabled',      '1', 'bool', 'module'],
            ['module.reports.enabled',       '1', 'bool', 'module'],
            ['module.einvoice.enabled',      '0', 'bool', 'module'],
            ['module.notifications.enabled', '1', 'bool', 'module'],
            ['module.audit_log.enabled',     '1', 'bool', 'module'],
            ['module.exports.enabled',       '1', 'bool', 'module'],
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

    public function down(): void
    {
        $this->db->table('settings')
            ->whereIn('key_name', [
                'module.payments.enabled',
                'module.invoices.enabled',
                'module.receipts.enabled',
                'module.reports.enabled',
                'module.einvoice.enabled',
                'module.notifications.enabled',
                'module.audit_log.enabled',
                'module.exports.enabled',
            ])->delete();
    }
}
