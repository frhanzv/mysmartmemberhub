<?php

namespace App\Libraries;

class SettingsService
{
    private static ?array $cache = null;

    public static function all(bool $refresh = false): array
    {
        if ($refresh || self::$cache === null) {
            $rows = \Config\Database::connect()
                ->table('settings')->get()->getResultArray();
            self::$cache = [];
            foreach ($rows as $r) {
                self::$cache[$r['key_name']] = $r['value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, $default = null)
    {
        $all = self::all();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, $value): void
    {
        $db  = \Config\Database::connect();
        $row = $db->table('settings')->where('key_name', $key)->get()->getRowArray();
        $now = date('Y-m-d H:i:s');
        if ($row) {
            $db->table('settings')->where('id', $row['id'])
               ->update(['value' => (string) $value, 'updated_at' => $now]);
        } else {
            $db->table('settings')->insert([
                'key_name'   => $key,
                'value'      => (string) $value,
                'type'       => 'string',
                'group_name' => 'general',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        self::$cache = null;
    }
}
