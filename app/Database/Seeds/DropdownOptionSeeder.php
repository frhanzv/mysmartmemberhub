<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DropdownOptionSeeder extends Seeder
{
    public function run(): void
    {
        $now  = date('Y-m-d H:i:s');
        $rows = [];

        // ── Payment methods ─────────────────────────────────
        foreach ([
            ['Cash',     'cash'],
            ['Bank Transfer', 'transfer'],
            ['Credit / Debit Card', 'card'],
            ['Cheque',   'cheque'],
            ['Online Payment', 'online'],
            ['Other',    'other'],
        ] as $i => $item) {
            $rows[] = ['category' => 'payment_method', 'label' => $item[0], 'value' => $item[1], 'sort_order' => $i + 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── Registration types (LHDN e-Invoice) ────────────
        foreach (['Individual', 'Company', 'Government', 'Foreign'] as $i => $rt) {
            $rows[] = ['category' => 'registration_type', 'label' => $rt, 'value' => $rt, 'sort_order' => $i + 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── Countries (top relevant) ────────────────────────
        $countries = [
            ['MYS', 'Malaysia'],
            ['SGP', 'Singapore'],
            ['IDN', 'Indonesia'],
            ['THA', 'Thailand'],
            ['PHL', 'Philippines'],
            ['BRN', 'Brunei'],
            ['VNM', 'Vietnam'],
            ['MMR', 'Myanmar'],
            ['KHM', 'Cambodia'],
            ['LAO', 'Laos'],
            ['IND', 'India'],
            ['CHN', 'China'],
            ['JPN', 'Japan'],
            ['KOR', 'South Korea'],
            ['AUS', 'Australia'],
            ['GBR', 'United Kingdom'],
            ['USA', 'United States'],
        ];
        foreach ($countries as $i => $c) {
            $rows[] = ['category' => 'country', 'label' => $c[1], 'value' => $c[0], 'sort_order' => $i + 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── Malaysian states (LHDN codes) ──────────────────
        $states = [
            ['01', 'Johor'],
            ['02', 'Kedah'],
            ['03', 'Kelantan'],
            ['04', 'Melaka'],
            ['05', 'Negeri Sembilan'],
            ['06', 'Pahang'],
            ['07', 'Pulau Pinang'],
            ['08', 'Perak'],
            ['09', 'Perlis'],
            ['10', 'Selangor'],
            ['11', 'Terengganu'],
            ['12', 'Sabah'],
            ['13', 'Sarawak'],
            ['14', 'WP Kuala Lumpur'],
            ['15', 'WP Labuan'],
            ['16', 'WP Putrajaya'],
            ['17', 'Not Applicable'],
        ];
        foreach ($states as $i => $s) {
            $rows[] = ['category' => 'state', 'label' => $s[1], 'value' => $s[0], 'sort_order' => $i + 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now];
        }

        // ── Tax types (LHDN) ───────────────────────────────
        $taxTypes = [
            ['01', '01 – Sales Tax'],
            ['02', '02 – Service Tax'],
            ['03', '03 – Tourism Tax'],
            ['04', '04 – High-Value Goods Tax'],
            ['05', '05 – Sales Tax + Service Tax'],
            ['06', '06 – Not Applicable'],
            ['E',  'E – Tax Exemption'],
        ];
        foreach ($taxTypes as $i => $t) {
            $rows[] = ['category' => 'tax_type', 'label' => $t[1], 'value' => $t[0], 'sort_order' => $i + 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now];
        }

        $this->db->table('dropdown_options')->ignore(true)->insertBatch($rows);
    }
}
