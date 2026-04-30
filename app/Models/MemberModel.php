<?php

namespace App\Models;

class MemberModel extends BaseModel
{
    protected $table      = 'members';
    protected $primaryKey = 'id';
    protected string $auditEntity = 'members';

    protected $allowedFields = [
        'membership_id', 'name', 'ic_no', 'email', 'phone', 'address',
        'plan_id', 'joined_date', 'expiry_date', 'status', 'photo_path',
        'notes', 'created_by',
        'tin', 'brn_or_nric', 'registration_type', 'sst_no',
        'address_line1', 'address_line2', 'city', 'postcode',
        'state_code', 'country_code',
    ];

    /**
     * Recompute member status based on expiry_date. Returns affected rows.
     */
    public function refreshStatuses(): int
    {
        $today = date('Y-m-d');
        $affected = 0;

        $affected += $this->db->table($this->table)
            ->where('expiry_date <', $today)
            ->where('status !=', 'expired')
            ->where('status !=', 'suspended')
            ->where('deleted_at', null)
            ->update(['status' => 'expired', 'updated_at' => date('Y-m-d H:i:s')]);

        $affected += $this->db->table($this->table)
            ->where('expiry_date >=', $today)
            ->where('status', 'expired')
            ->where('deleted_at', null)
            ->update(['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')]);

        return $affected;
    }

    public function searchPaginated(array $filters, int $perPage = 20)
    {
        $b = $this->select('members.*, mp.name AS plan_name, mp.code AS plan_code')
                  ->join('membership_plans mp', 'mp.id = members.plan_id', 'left');

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $b->groupStart()
              ->like('members.name', $q)
              ->orLike('members.email', $q)
              ->orLike('members.phone', $q)
              ->orLike('members.ic_no', $q)
              ->orLike('members.membership_id', $q)
              ->groupEnd();
        }
        if (! empty($filters['status'])) {
            $b->where('members.status', $filters['status']);
        }
        if (! empty($filters['plan_id'])) {
            $b->where('members.plan_id', (int) $filters['plan_id']);
        }
        return $b->orderBy('members.created_at', 'DESC')->paginate($perPage);
    }
}
