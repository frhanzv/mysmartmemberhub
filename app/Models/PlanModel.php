<?php

namespace App\Models;

class PlanModel extends BaseModel
{
    protected $table      = 'membership_plans';
    protected $primaryKey = 'id';
    protected string $auditEntity = 'membership_plans';

    protected $allowedFields = [
        'code', 'name', 'price', 'duration_months', 'description', 'is_active',
        'classification_code', 'tax_type', 'tax_rate', 'unit_code',
    ];

    public function active(): array
    {
        return $this->where('is_active', 1)->orderBy('name')->findAll();
    }
}
