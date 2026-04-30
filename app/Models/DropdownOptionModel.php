<?php

namespace App\Models;

use CodeIgniter\Model;

class DropdownOptionModel extends Model
{
    protected $table         = 'dropdown_options';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['category', 'label', 'value', 'sort_order', 'is_active'];
    protected $returnType    = 'array';

    /**
     * Get active options for a given category, sorted by sort_order.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public function byCategory(string $category): array
    {
        return $this->where('category', $category)
                    ->where('is_active', 1)
                    ->orderBy('sort_order')
                    ->orderBy('label')
                    ->findAll();
    }

    /**
     * Get all options grouped by category (for management page).
     *
     * @return array<string, array>
     */
    public function allGrouped(): array
    {
        $rows = $this->orderBy('category')
                     ->orderBy('sort_order')
                     ->orderBy('label')
                     ->findAll();
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['category']][] = $r;
        }
        ksort($grouped);
        return $grouped;
    }

    /**
     * Get distinct category names.
     *
     * @return list<string>
     */
    public function categories(): array
    {
        $rows = $this->select('category')
                     ->distinct()
                     ->orderBy('category')
                     ->findAll();
        return array_column($rows, 'category');
    }
}
