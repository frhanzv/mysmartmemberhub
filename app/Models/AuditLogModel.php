<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $allowedFields = [
        'user_id', 'action', 'entity', 'entity_id',
        'old_values', 'new_values', 'ip', 'user_agent',
    ];
    protected $returnType = 'array';
}
