<?php

namespace App\Models;

class ReceiptModel extends BaseModel
{
    protected $table      = 'receipts';
    protected $primaryKey = 'id';
    protected string $auditEntity = 'receipts';

    protected $useSoftDeletes = false;
    protected $deletedField   = '';

    protected $allowedFields = [
        'receipt_no', 'payment_id', 'invoice_id', 'member_id',
        'amount', 'issued_at', 'pdf_path', 'created_by',
    ];

    public function searchPaginated(array $filters, int $perPage = 20)
    {
        $b = $this->select('receipts.*, m.name AS member_name, m.membership_id, p.method, i.invoice_no')
                  ->join('members m', 'm.id = receipts.member_id', 'left')
                  ->join('payments p', 'p.id = receipts.payment_id', 'left')
                  ->join('invoices i', 'i.id = receipts.invoice_id', 'left');

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $b->groupStart()
              ->like('receipts.receipt_no', $q)
              ->orLike('m.name', $q)
              ->orLike('m.membership_id', $q)
              ->groupEnd();
        }
        if (! empty($filters['month'])) {
            $b->where('DATE_FORMAT(receipts.issued_at, "%Y-%m")', $filters['month']);
        }
        return $b->orderBy('receipts.issued_at', 'DESC')->paginate($perPage);
    }
}
