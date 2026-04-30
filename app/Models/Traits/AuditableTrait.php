<?php

namespace App\Models\Traits;

use App\Models\AuditLogModel;

/**
 * Adds CRUD audit logging to a CI4 Model.
 *
 * Models using this trait must declare $auditEntity and register
 * the callbacks via $afterInsert / $afterUpdate / $afterDelete.
 */
trait AuditableTrait
{
    protected function logCreate(array $data): array
    {
        $this->writeAudit('create', $data['id'] ?? null, null, $data['data'] ?? null);
        return $data;
    }

    protected function logUpdate(array $data): array
    {
        $id = $data['id'][0] ?? ($data['id'] ?? null);
        $this->writeAudit('update', is_array($id) ? null : $id, null, $data['data'] ?? null);
        return $data;
    }

    protected function logDelete(array $data): array
    {
        $id = $data['id'][0] ?? ($data['id'] ?? null);
        $this->writeAudit('delete', is_array($id) ? null : $id);
        return $data;
    }

    protected function writeAudit(string $action, $entityId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            $entity = property_exists($this, 'auditEntity') && $this->auditEntity
                ? $this->auditEntity
                : ($this->table ?? 'unknown');

            $request = service('request');
            $userId = function_exists('current_user_id') ? current_user_id() : null;

            (new AuditLogModel())->insert([
                'user_id'    => $userId,
                'action'     => $action,
                'entity'     => $entity,
                'entity_id'  => $entityId !== null ? (string) $entityId : null,
                'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'ip'         => $request ? $request->getIPAddress() : null,
                'user_agent' => $request ? substr((string) $request->getUserAgent(), 0, 240) : null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Audit log failed: ' . $e->getMessage());
        }
    }
}
