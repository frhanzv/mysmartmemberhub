<?php

namespace App\Models;

class EinvoiceDocumentModel extends BaseModel
{
    protected $table      = 'einvoice_documents';
    protected $primaryKey = 'id';
    protected string $auditEntity = 'einvoice_documents';

    protected $allowedFields = [
        'document_type', 'source_table', 'source_id', 'code_number',
        'submission_uid', 'irbm_uuid', 'irbm_long_id',
        'status', 'environment',
        'validated_at', 'cancellable_until', 'cancelled_at', 'cancel_reason',
        'request_payload', 'response_payload', 'error_payload',
        'created_by',
    ];

    public function latestForInvoice(int $invoiceId): ?array
    {
        return $this->where('source_table', 'invoices')
                    ->where('source_id', $invoiceId)
                    ->orderBy('id', 'DESC')
                    ->first();
    }
}
