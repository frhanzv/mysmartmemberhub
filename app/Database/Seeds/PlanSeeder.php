<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $plans = [
            ['STD-1Y',  'Standard – 1 Year',   120.00, 12, 'Standard yearly membership',   1],
            ['PRM-1Y',  'Premium – 1 Year',    300.00, 12, 'Premium yearly membership',    1],
            ['STD-3Y',  'Standard – 3 Years',  300.00, 36, '3-year discounted plan',       1],
            ['LIFE',    'Lifetime',           1500.00, 600, 'Lifetime membership',         1],
        ];

        $rows = [];
        foreach ($plans as [$code, $name, $price, $months, $desc, $active]) {
            $rows[] = [
                'code'            => $code,
                'name'            => $name,
                'price'           => $price,
                'duration_months' => $months,
                'description'     => $desc,
                'is_active'       => $active,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }
        $this->db->table('membership_plans')->ignore(true)->insertBatch($rows);
    }
}
