<?php

namespace App\Models;

use App\Models\Traits\AuditableTrait;
use CodeIgniter\Model;

abstract class BaseModel extends Model
{
    use AuditableTrait;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $useSoftDeletes = true;

    protected $returnType = 'array';

    /** Override in subclasses for audit log entity name. */
    protected string $auditEntity = '';

    protected $afterInsert = ['logCreate'];
    protected $afterUpdate = ['logUpdate'];
    protected $afterDelete = ['logDelete'];
}
