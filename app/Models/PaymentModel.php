<?php

namespace App\Models;

class PaymentModel extends BaseModel
{
    protected $table      = 'payments';
    protected $primaryKey = 'id';
    protected string $auditEntity = 'payments';

    protected $allowedFields = [
        'member_id', 'invoice_id', 'amount', 'payment_date', 'method',
        'reference_no', 'proof_path', 'status', 'notes',
        'approved_by', 'approved_at', 'receipt_id', 'created_by',
    ];

    public function searchPaginated(array $filters, int $perPage = 20)
    {
        $b = $this->select('payments.*, m.name AS member_name, m.membership_id, i.invoice_no')
                  ->join('members m', 'm.id = payments.member_id', 'left')
                  ->join('invoices i', 'i.id = payments.invoice_id', 'left');

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $b->groupStart()
              ->like('m.name', $q)
              ->orLike('m.membership_id', $q)
              ->orLike('payments.reference_no', $q)
              ->orLike('i.invoice_no', $q)
              ->groupEnd();
        }
        if (! empty($filters['status'])) {
            $b->where('payments.status', $filters['status']);
        }
        if (! empty($filters['month'])) {
            $b->where('DATE_FORMAT(payments.payment_date, "%Y-%m")', $filters['month']);
        }
        return $b->orderBy('payments.payment_date', 'DESC')->paginate($perPage);
    }

    /**
     * Try to auto-match a payment to an unpaid invoice for the same member with matching amount.
     */
    public function autoMatchInvoice(int $memberId, float $amount): ?int
    {
        $row = $this->db->table('invoices')
            ->where('member_id', $memberId)
            ->where('status', 'issued')
            ->where('total', $amount)
            ->where('deleted_at', null)
            ->orderBy('issued_at', 'ASC')
            ->get()->getRowArray();
        return $row ? (int) $row['id'] : null;
    }
}
