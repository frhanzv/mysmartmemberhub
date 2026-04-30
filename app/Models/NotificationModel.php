<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $allowedFields = [
        'user_id', 'type', 'title', 'message', 'link', 'read_at',
    ];
    protected $returnType = 'array';

    public function unreadFor(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
                    ->where('read_at', null)
                    ->orderBy('id', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function markRead(int $userId, ?int $id = null): void
    {
        $b = $this->builder()->where('user_id', $userId)->where('read_at', null);
        if ($id) {
            $b->where('id', $id);
        }
        $b->update(['read_at' => date('Y-m-d H:i:s')]);
    }
}
