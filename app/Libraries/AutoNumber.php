<?php

namespace App\Libraries;

/**
 * Generates atomic auto-numbers (member ID, invoice no, receipt no).
 *
 * Counters live in the `settings` table under keys like
 *   counter.invoice.2026, counter.receipt.2026, counter.member
 *
 * Uses SELECT ... FOR UPDATE inside a transaction to avoid duplicates.
 */
class AutoNumber
{
    public static function memberId(int $width = 4): string
    {
        $n = self::nextCounter('counter.member');
        return 'MEM-' . str_pad((string) $n, $width, '0', STR_PAD_LEFT);
    }

    public static function invoiceNo(?int $year = null, int $width = 4): string
    {
        $year = $year ?: (int) date('Y');
        $n    = self::nextCounter('counter.invoice.' . $year);
        return 'INV-' . $year . '-' . str_pad((string) $n, $width, '0', STR_PAD_LEFT);
    }

    public static function receiptNo(?int $year = null, int $width = 4): string
    {
        $year = $year ?: (int) date('Y');
        $n    = self::nextCounter('counter.receipt.' . $year);
        return 'RCPT-' . $year . '-' . str_pad((string) $n, $width, '0', STR_PAD_LEFT);
    }

    private static function nextCounter(string $key): int
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $row = $db->query(
                'SELECT id, value FROM settings WHERE key_name = ? FOR UPDATE',
                [$key]
            )->getRowArray();

            $now = date('Y-m-d H:i:s');
            if (! $row) {
                $db->table('settings')->insert([
                    'key_name'   => $key,
                    'value'      => '1',
                    'type'       => 'int',
                    'group_name' => 'counter',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $db->transCommit();
                return 1;
            }

            $next = ((int) $row['value']) + 1;
            $db->table('settings')->where('id', $row['id'])->update([
                'value'      => (string) $next,
                'updated_at' => $now,
            ]);
            $db->transCommit();
            return $next;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
