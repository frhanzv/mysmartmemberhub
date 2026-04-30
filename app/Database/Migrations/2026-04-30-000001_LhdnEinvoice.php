<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * LHDN MyInvois e-Invoice schema additions.
 *
 * Adds:
 *  - tax/identity columns on members, membership_plans, invoices
 *  - einvoice_documents table to persist every submission/response/cancellation
 *
 * Settings table is untouched here; new supplier identity keys are upserted via
 * SettingSeeder so they show up in `php spark db:seed DatabaseSeeder` runs.
 */
class LhdnEinvoice extends Migration
{
    public function up(): void
    {
        $f = $this->forge;

        // ---------------- members: buyer identity for LHDN ----------------
        $f->addColumn('members', [
            'tin'                  => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => true, 'after' => 'ic_no'],
            'brn_or_nric'          => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => true, 'after' => 'tin'],
            'registration_type'    => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => true, 'default' => 'Individual', 'after' => 'brn_or_nric'],
            'sst_no'               => ['type' => 'VARCHAR', 'constraint' => 30,  'null' => true, 'after' => 'registration_type'],
            'address_line1'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'sst_no'],
            'address_line2'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'address_line1'],
            'city'                 => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'address_line2'],
            'postcode'             => ['type' => 'VARCHAR', 'constraint' => 10,  'null' => true, 'after' => 'city'],
            'state_code'           => ['type' => 'VARCHAR', 'constraint' => 2,   'null' => true, 'after' => 'postcode'],
            'country_code'         => ['type' => 'CHAR',    'constraint' => 3,   'null' => true, 'default' => 'MYS', 'after' => 'state_code'],
        ]);

        // ---------------- membership_plans: tax + classification ----------------
        $f->addColumn('membership_plans', [
            'classification_code' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => false, 'default' => '022',     'after' => 'price'],
            'tax_type'            => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => false, 'default' => '06',      'after' => 'classification_code'], // 06 = Not Applicable
            'tax_rate'            => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => false, 'default' => 0.00,  'after' => 'tax_type'],
            'unit_code'           => ['type' => 'VARCHAR', 'constraint' => 6, 'null' => false, 'default' => 'MON',     'after' => 'tax_rate'], // MON=month
        ]);

        // ---------------- invoices: LHDN status mirror ----------------
        $f->addColumn('invoices', [
            'einvoice_status'           => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'status'], // none|pending|valid|invalid|cancelled
            'einvoice_uuid'             => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'einvoice_status'],
            'einvoice_long_id'          => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'after' => 'einvoice_uuid'],
            'einvoice_submission_uid'   => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true, 'after' => 'einvoice_long_id'],
            'einvoice_validated_at'     => ['type' => 'DATETIME', 'null' => true, 'after' => 'einvoice_submission_uid'],
            'einvoice_cancellable_until'=> ['type' => 'DATETIME', 'null' => true, 'after' => 'einvoice_validated_at'],
        ]);

        // ---------------- einvoice_documents ----------------
        $f->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'document_type'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false], // invoice|credit_note|debit_note|refund_note
            'source_table'        => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false], // invoices
            'source_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'code_number'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false],
            'submission_uid'      => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'irbm_uuid'           => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'irbm_long_id'        => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'status'              => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'pending'], // pending|valid|invalid|cancelled
            'environment'         => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => false, 'default' => 'sandbox'], // sandbox|prod|stub
            'validated_at'        => ['type' => 'DATETIME', 'null' => true],
            'cancellable_until'   => ['type' => 'DATETIME', 'null' => true],
            'cancelled_at'        => ['type' => 'DATETIME', 'null' => true],
            'cancel_reason'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'request_payload'     => ['type' => 'LONGTEXT', 'null' => true],
            'response_payload'    => ['type' => 'LONGTEXT', 'null' => true],
            'error_payload'       => ['type' => 'TEXT',     'null' => true],
            'created_by'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addKey(['source_table', 'source_id']);
        $f->addKey('irbm_uuid');
        $f->addKey('submission_uid');
        $f->addKey('status');
        $f->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $f->createTable('einvoice_documents');

        // ---------------- payments: extend status enum to include 'reversed' ----------------
        // Original enum is ('pending','confirmed','rejected'); the LHDN refund flow needs 'reversed'.
        $this->db->query("ALTER TABLE payments MODIFY status ENUM('pending','confirmed','rejected','reversed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        $f = $this->forge;
        // Roll back any 'reversed' rows so the enum shrink is safe, then revert the enum.
        $this->db->query("UPDATE payments SET status = 'rejected' WHERE status = 'reversed'");
        $this->db->query("ALTER TABLE payments MODIFY status ENUM('pending','confirmed','rejected') NOT NULL DEFAULT 'pending'");
        $f->dropTable('einvoice_documents', true);

        foreach (['einvoice_status','einvoice_uuid','einvoice_long_id','einvoice_submission_uid','einvoice_validated_at','einvoice_cancellable_until'] as $col) {
            $f->dropColumn('invoices', $col);
        }
        foreach (['classification_code','tax_type','tax_rate','unit_code'] as $col) {
            $f->dropColumn('membership_plans', $col);
        }
        foreach (['tin','brn_or_nric','registration_type','sst_no','address_line1','address_line2','city','postcode','state_code','country_code'] as $col) {
            $f->dropColumn('members', $col);
        }
    }
}
