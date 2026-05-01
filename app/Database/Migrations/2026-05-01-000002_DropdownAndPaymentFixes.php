<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Two related fixes flagged by Devin Review on PR #4:
 *
 *  1. The `payments.method` ENUM did not include `online`, but
 *     `DropdownOptionSeeder` seeds an "Online Payment" option with
 *     value `online`. Inserting a payment with that method failed
 *     because MySQL rejected it as an invalid enum value. We widen
 *     the ENUM to include `online`.
 *
 *  2. `dropdown_options` had no UNIQUE constraint on `(category, value)`,
 *     so `DropdownOptionSeeder` running with `ignore(true)->insertBatch()`
 *     was effectively a no-op against duplicates. Re-running
 *     `php spark db:seed DatabaseSeeder` would insert a second copy of
 *     all 51 rows, polluting every form's dropdown. We dedupe any
 *     pre-existing duplicates and add the missing UNIQUE key.
 */
class DropdownAndPaymentFixes extends Migration
{
    public function up(): void
    {
        // 1. Widen payments.method ENUM
        $this->forge->modifyColumn('payments', [
            'method' => [
                'name'       => 'method',
                'type'       => 'ENUM',
                'constraint' => ['cash', 'transfer', 'card', 'cheque', 'online', 'other'],
                'default'    => 'transfer',
            ],
        ]);

        // 2. Dedupe dropdown_options on (category, value), keeping the lowest id
        $this->db->query("
            DELETE d1 FROM dropdown_options d1
            INNER JOIN dropdown_options d2
              ON d1.category = d2.category
             AND d1.value    = d2.value
             AND d1.id       > d2.id
        ");

        // 3. Add the UNIQUE constraint (idempotent — skip if already present)
        $exists = $this->db->query(
            "SELECT 1 FROM information_schema.statistics
              WHERE table_schema = DATABASE()
                AND table_name   = 'dropdown_options'
                AND index_name   = 'uq_dropdown_options_category_value'"
        )->getRow();
        if (! $exists) {
            $this->db->query(
                'ALTER TABLE dropdown_options
                   ADD UNIQUE KEY uq_dropdown_options_category_value (category, value)'
            );
        }
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE dropdown_options DROP INDEX uq_dropdown_options_category_value');

        // Narrow payments.method ENUM back to the original 5 values
        $this->db->query("UPDATE payments SET method = 'other' WHERE method = 'online'");
        $this->forge->modifyColumn('payments', [
            'method' => [
                'name'       => 'method',
                'type'       => 'ENUM',
                'constraint' => ['cash', 'transfer', 'card', 'cheque', 'other'],
                'default'    => 'transfer',
            ],
        ]);
    }
}
