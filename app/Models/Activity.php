<?php
/**
 * Akilli Kariyer - Kullanici aktivite modeli
 */

namespace App\Models;

use App\Core\Database;

class Activity
{
    public static function log(int $userId, string $type, string $description, ?string $refType = null, ?int $refId = null): void
    {
        Database::query(
            'INSERT INTO `user_activities` (`user_id`,`activity_type`,`description`,`reference_type`,`reference_id`)
             VALUES (?,?,?,?,?)',
            [$userId, $type, mb_substr($description, 0, 255), $refType, $refId]
        );
    }

    public static function forUser(int $userId, int $limit = 10): array
    {
        return Database::fetchAll(
            'SELECT * FROM `user_activities` WHERE `user_id` = ? ORDER BY `created_at` DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public static function iconFor(string $type): string
    {
        return match ($type) {
            'test_start'      => 'play-circle',
            'test_complete'   => 'check-circle',
            'favorite'        => 'heart',
            'unfavorite'      => 'heart-off',
            'view_career'     => 'eye',
            'registration'    => 'user-plus',
            'profile_update'  => 'user-cog',
            'pdf_download'    => 'file-down',
            'favorite_add'    => 'heart',
            'favorite_remove' => 'heart-off',
            'login'           => 'log-in',
            default           => 'activity',
        };
    }

    public static function colorFor(string $type): string
    {
        return match ($type) {
            'test_complete'   => 'success',
            'favorite'        => 'danger',
            'view_career'     => 'info',
            'registration'    => 'primary',
            default           => 'muted',
        };
    }
}
