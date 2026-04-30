<?php

namespace App\Models;

class InvoiceModel extends BaseModel
{
    protected $table      = 'invoices';
    protected $primaryKey = 'id';
    protected string $auditEntity = 'invoices';

    protected $allowedFields = [
        'invoice_no', 'member_id', 'plan_id', 'amount', 'tax_percent',
        'tax_amount', 'total', 'status', 'issued_at', 'due_at',
        'pdf_path', 'notes', 'created_by',
    ];

    public function searchPaginated(array $filters, int $perPage = 20)
    {
        $b = $this->select('invoices.*, m.name AS member_name, m.membership_id, mp.name AS plan_name')
                  ->join('members m', 'm.id = invoices.member_id', 'left')
                  ->join('membership_plans mp', 'mp.id = invoices.plan_id', 'left');

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $b->groupStart()
              ->like('invoices.invoice_no', $q)
              ->orLike('m.name', $q)
              ->orLike('m.membership_id', $q)
              ->groupEnd();
        }
        if (! empty($filters['status'])) {
            $b->where('invoices.status', $filters['status']);
        }
        if (! empty($filters['month'])) {
            $b->where('DATE_FORMAT(invoices.issued_at, "%Y-%m")', $filters['month']);
        }
        return $b->orderBy('invoices.issued_at', 'DESC')->paginate($perPage);
    }

    public function outstanding(): array
    {
        return $this->select('invoices.*, m.name AS member_name, m.membership_id')
                    ->join('members m', 'm.id = invoices.member_id', 'left')
                    ->where('invoices.status', 'issued')
                    ->orderBy('invoices.due_at')
                    ->findAll();
    }
}
