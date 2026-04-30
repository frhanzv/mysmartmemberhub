<?php

namespace App\Libraries;

use App\Models\NotificationModel;

/**
 * Tiny in-app notification dispatcher.
 *
 * Email/WhatsApp delivery can be added by extending dispatch().
 */
class Notifier
{
    public static function notify(?int $userId, string $type, string $title, string $message, ?string $link = null): int
    {
        return (int) (new NotificationModel())->insert([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
        ], true);
    }

    public static function broadcastByRole(string $roleSlug, string $type, string $title, string $message, ?string $link = null): void
    {
        $db = \Config\Database::connect();
        $rows = $db->table('users u')
            ->select('u.id')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.slug', $roleSlug)
            ->where('u.deleted_at', null)
            ->get()->getResultArray();

        foreach ($rows as $r) {
            self::notify((int) $r['id'], $type, $title, $message, $link);
        }
    }
}
