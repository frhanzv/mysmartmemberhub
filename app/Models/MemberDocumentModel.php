<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberDocumentModel extends Model
{
    protected $table         = 'member_documents';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $allowedFields = ['member_id', 'file_path', 'file_name', 'mime', 'uploaded_by'];
    protected $returnType    = 'array';
}
