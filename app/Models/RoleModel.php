<?php

namespace App\Models;

class RoleModel extends BaseModel
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';
    protected string $auditEntity = 'roles';

    protected $allowedFields = ['name', 'slug', 'description'];

    public function permissionsFor(int $roleId): array
    {
        $rows = $this->db->table('role_permissions rp')
            ->select('p.id, p.slug, p.group_name, p.description')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->get()->getResultArray();
        return $rows;
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->table('role_permissions')->where('role_id', $roleId)->delete();
        if ($permissionIds) {
            $rows = array_map(fn ($pid) => ['role_id' => $roleId, 'permission_id' => (int) $pid], $permissionIds);
            $this->db->table('role_permissions')->insertBatch($rows);
        }
    }
}
