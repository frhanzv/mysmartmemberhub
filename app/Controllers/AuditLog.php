<?php

namespace App\Controllers;

use App\Models\AuditLogModel;

class AuditLog extends BaseController
{
    public function index()
    {
        $audit = new AuditLogModel();
        $b = $audit->select('audit_logs.*, u.name AS user_name')
                   ->join('users u', 'u.id = audit_logs.user_id', 'left');
        if ($entity = $this->request->getGet('entity')) {
            $b->where('audit_logs.entity', $entity);
        }
        if ($action = $this->request->getGet('action')) {
            $b->where('audit_logs.action', $action);
        }
        if ($q = $this->request->getGet('q')) {
            $b->groupStart()->like('u.name', $q)->orLike('audit_logs.entity_id', $q)->groupEnd();
        }
        $rows  = $b->orderBy('audit_logs.id', 'DESC')->paginate(50);
        $pager = $audit->pager;
        return view('audit/index', compact('rows', 'pager'));
    }
}
