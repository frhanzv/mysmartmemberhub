<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table      = 'permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['slug', 'group_name', 'description'];
    protected $useTimestamps = false;
    protected $returnType    = 'array';

    public function groupedAll(): array
    {
        $rows = $this->orderBy('group_name')->orderBy('slug')->findAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['group_name']][] = $r;
        }
        return $out;
    }
}
