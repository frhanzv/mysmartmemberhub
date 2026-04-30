<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDropdownOptions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'category'   => ['type' => 'VARCHAR', 'constraint' => 60],
            'label'      => ['type' => 'VARCHAR', 'constraint' => 120],
            'value'      => ['type' => 'VARCHAR', 'constraint' => 80],
            'sort_order'  => ['type' => 'INT', 'default' => 0],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['category', 'is_active', 'sort_order']);
        $this->forge->createTable('dropdown_options');
    }

    public function down(): void
    {
        $this->forge->dropTable('dropdown_options', true);
    }
}
