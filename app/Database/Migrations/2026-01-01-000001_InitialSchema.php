<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up(): void
    {
        $f = $this->forge;

        // ---------------- roles ----------------
        $f->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('slug');
        $f->createTable('roles');

        // ---------------- permissions ----------------
        $f->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'group_name'  => ['type' => 'VARCHAR', 'constraint' => 60],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('slug');
        $f->createTable('permissions');

        // ---------------- role_permissions ----------------
        $f->addField([
            'role_id'       => ['type' => 'INT', 'unsigned' => true],
            'permission_id' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $f->addPrimaryKey(['role_id', 'permission_id']);
        $f->addForeignKey('role_id', 'roles', 'id', '', 'CASCADE');
        $f->addForeignKey('permission_id', 'permissions', 'id', '', 'CASCADE');
        $f->createTable('role_permissions');

        // ---------------- users ----------------
        $f->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 120],
            'username'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'email'             => ['type' => 'VARCHAR', 'constraint' => 160],
            'password_hash'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'role_id'           => ['type' => 'INT', 'unsigned' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['active', 'disabled'], 'default' => 'active'],
            'last_login_at'     => ['type' => 'DATETIME', 'null' => true],
            'reset_token'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'reset_expires_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('username');
        $f->addUniqueKey('email');
        $f->addForeignKey('role_id', 'roles', 'id');
        $f->createTable('users');

        // ---------------- membership_plans ----------------
        $f->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'code'            => ['type' => 'VARCHAR', 'constraint' => 40],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 120],
            'price'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'duration_months' => ['type' => 'INT', 'default' => 12],
            'description'     => ['type' => 'TEXT', 'null' => true],
            'is_active'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('code');
        $f->createTable('membership_plans');

        // ---------------- members ----------------
        $f->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'membership_id' => ['type' => 'VARCHAR', 'constraint' => 20],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 160],
            'ic_no'         => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'phone'         => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'address'       => ['type' => 'TEXT', 'null' => true],
            'plan_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'joined_date'   => ['type' => 'DATE'],
            'expiry_date'   => ['type' => 'DATE'],
            'status'        => ['type' => 'ENUM', 'constraint' => ['active', 'expired', 'suspended'], 'default' => 'active'],
            'photo_path'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'notes'         => ['type' => 'TEXT', 'null' => true],
            'created_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('membership_id');
        $f->addKey('status');
        $f->addKey('expiry_date');
        $f->addForeignKey('plan_id', 'membership_plans', 'id', '', 'SET NULL');
        $f->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $f->createTable('members');

        // ---------------- invoices ----------------
        $f->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'invoice_no'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'member_id'    => ['type' => 'INT', 'unsigned' => true],
            'plan_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'tax_percent'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'tax_amount'   => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'total'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'status'       => ['type' => 'ENUM', 'constraint' => ['draft', 'issued', 'paid', 'cancelled'], 'default' => 'issued'],
            'issued_at'    => ['type' => 'DATE'],
            'due_at'       => ['type' => 'DATE'],
            'pdf_path'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'notes'        => ['type' => 'TEXT', 'null' => true],
            'created_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('invoice_no');
        $f->addKey('status');
        $f->addForeignKey('member_id', 'members', 'id');
        $f->addForeignKey('plan_id', 'membership_plans', 'id', '', 'SET NULL');
        $f->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $f->createTable('invoices');

        // ---------------- payments ----------------
        $f->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'member_id'    => ['type' => 'INT', 'unsigned' => true],
            'invoice_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'payment_date' => ['type' => 'DATE'],
            'method'       => ['type' => 'ENUM', 'constraint' => ['cash', 'transfer', 'card', 'cheque', 'other'], 'default' => 'transfer'],
            'reference_no' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'proof_path'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'confirmed', 'rejected'], 'default' => 'pending'],
            'notes'        => ['type' => 'TEXT', 'null' => true],
            'approved_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'approved_at'  => ['type' => 'DATETIME', 'null' => true],
            'receipt_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addKey('status');
        $f->addKey('payment_date');
        $f->addForeignKey('member_id', 'members', 'id');
        $f->addForeignKey('invoice_id', 'invoices', 'id', '', 'SET NULL');
        $f->addForeignKey('approved_by', 'users', 'id', '', 'SET NULL');
        $f->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $f->createTable('payments');

        // ---------------- receipts ----------------
        $f->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'receipt_no' => ['type' => 'VARCHAR', 'constraint' => 30],
            'payment_id' => ['type' => 'INT', 'unsigned' => true],
            'invoice_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'member_id'  => ['type' => 'INT', 'unsigned' => true],
            'amount'     => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'issued_at'  => ['type' => 'DATETIME'],
            'pdf_path'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('receipt_no');
        $f->addForeignKey('payment_id', 'payments', 'id');
        $f->addForeignKey('invoice_id', 'invoices', 'id', '', 'SET NULL');
        $f->addForeignKey('member_id', 'members', 'id');
        $f->addForeignKey('created_by', 'users', 'id', '', 'SET NULL');
        $f->createTable('receipts');

        // ---------------- settings ----------------
        $f->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'key_name'   => ['type' => 'VARCHAR', 'constraint' => 120],
            'value'      => ['type' => 'TEXT', 'null' => true],
            'type'       => ['type' => 'ENUM', 'constraint' => ['string', 'int', 'decimal', 'bool', 'file', 'json'], 'default' => 'string'],
            'group_name' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addUniqueKey('key_name');
        $f->createTable('settings');

        // ---------------- audit_logs ----------------
        $f->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action'     => ['type' => 'VARCHAR', 'constraint' => 40],
            'entity'     => ['type' => 'VARCHAR', 'constraint' => 60],
            'entity_id'  => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'old_values' => ['type' => 'JSON', 'null' => true],
            'new_values' => ['type' => 'JSON', 'null' => true],
            'ip'         => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addKey('entity');
        $f->addKey('user_id');
        $f->addKey('created_at');
        $f->createTable('audit_logs');

        // ---------------- notifications ----------------
        $f->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 60],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 160],
            'message'    => ['type' => 'TEXT'],
            'link'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'read_at'    => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addKey('user_id');
        $f->createTable('notifications');

        // ---------------- member_documents ----------------
        $f->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'member_id'   => ['type' => 'INT', 'unsigned' => true],
            'file_path'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_name'   => ['type' => 'VARCHAR', 'constraint' => 160],
            'mime'        => ['type' => 'VARCHAR', 'constraint' => 80],
            'uploaded_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $f->addPrimaryKey('id');
        $f->addForeignKey('member_id', 'members', 'id', '', 'CASCADE');
        $f->addForeignKey('uploaded_by', 'users', 'id', '', 'SET NULL');
        $f->createTable('member_documents');
    }

    public function down(): void
    {
        foreach (['member_documents', 'notifications', 'audit_logs', 'settings', 'receipts',
                 'payments', 'invoices', 'members', 'membership_plans', 'users',
                 'role_permissions', 'permissions', 'roles'] as $t) {
            $this->forge->dropTable($t, true);
        }
    }
}
