<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['key_name', 'value', 'type', 'group_name'];
    protected $returnType    = 'array';
}
