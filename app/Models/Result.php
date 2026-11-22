<?php
/**
 * Akilli Kariyer - Test sonuclari ve favoriler modeli
 */

namespace App\Models;

use App\Core\Database;

class Result
{
    /* ------------------------------------------------------------------ */
    /* Sonuclar                                                             */
    /* ------------------------------------------------------------------ */

    public static function findBySession(int $sessionId): ?array
    {
        return Database::fetch('SELECT * FROM `test_results` WHERE `session_id` = ? LIMIT 1', [$sessionId]);
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM `test_results` WHERE `id` = ? LIMIT 1', [$id]);
    }

    public static function latestForUser(int $userId): ?array
    {
        return Database::fetch(
            'SELECT * FROM `test_results` WHERE `user_id` = ? ORDER BY `completed_at` DESC LIMIT 1',
            [$userId]
        );
    }

    public static function forUser(int $userId, int $limit = 20): array
    {
        return Database::fetchAll(
            'SELECT tr.*, ts.total_questions, ts.started_at
             FROM `test_results` tr
             INNER JOIN `test_sessions` ts ON ts.id = tr.session_id
             WHERE tr.user_id = ? ORDER BY tr.completed_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public static function countForUser(int $userId): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM `test_results` WHERE `user_id` = ?', [$userId]);
    }

    public static function create(int $sessionId, int $userId, array $scores, array $meta): int
    {
        return Database::insert(
            'INSERT INTO `test_results`
             (`session_id`,`user_id`,`interest_scores`,`ability_scores`,`preference_scores`,`environment_scores`,
              `top_interests`,`top_abilities`,`top_preferences`,`dominant_type`,`overall_score`,`summary`,`completed_at`,`created_at`)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())',
            [
                $sessionId,
                $userId,
                json_encode($scores['interest'] ?? [], JSON_UNESCAPED_UNICODE),
                json_encode($scores['ability'] ?? [], JSON_UNESCAPED_UNICODE),
                json_encode($scores['preference'] ?? [], JSON_UNESCAPED_UNICODE),
                json_encode($scores['environment'] ?? [], JSON_UNESCAPED_UNICODE),
                implode(',', $meta['top_interests'] ?? []),
                implode(',', $meta['top_abilities'] ?? []),
                implode(',', $meta['top_preferences'] ?? []),
                $meta['dominant_type'] ?? null,
                $meta['overall_score'] ?? 0,
                $meta['summary'] ?? null,
            ]
        );
    }

    /* ------------------------------------------------------------------ */
    /* Oneriler                                                             */
    /* ------------------------------------------------------------------ */

    public static function saveRecommendations(int $resultId, int $userId, array $recommendations): void
    {
        foreach ($recommendations as $index => $rec) {
            $recId = Database::insert(
                'INSERT INTO `career_recommendations` (`result_id`,`user_id`,`career_id`,`match_score`,`rank`,`reason`,`created_at`)
                 VALUES (?,?,?,?,?,?,NOW())',
                [$resultId, $userId, $rec['career_id'], $rec['match_score'], $index + 1, $rec['reason'] ?? null]
            );

            Database::query(
                'INSERT INTO `career_match_scores`
                 (`recommendation_id`,`user_id`,`career_id`,`interest_score`,`ability_score`,`preference_score`,`environment_score`,`skill_score`,`created_at`)
                 VALUES (?,?,?,?,?,?,?,?,NOW())',
                [
                    $recId, $userId, $rec['career_id'],
                    $rec['components']['interest'] ?? 0,
                    $rec['components']['ability'] ?? 0,
                    $rec['components']['preference'] ?? 0,
                    $rec['components']['environment'] ?? 0,
                    $rec['components']['skill'] ?? 0,
                ]
            );
        }
    }

    public static function recommendations(int $resultId): array
    {
        return Database::fetchAll(
            'SELECT r.*, c.name, c.slug, c.short_description, c.icon, c.image, c.education_level, c.education_detail,
                    c.salary_range, c.working_areas, c.view_count,
                    cat.name AS category_name, cat.slug AS category_slug, cat.color AS category_color
             FROM `career_recommendations` r
             INNER JOIN `careers` c ON c.id = r.career_id
             INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE r.result_id = ? ORDER BY r.rank ASC',
            [$resultId]
        );
    }

    public static function matchScores(int $recommendationId): ?array
    {
        return Database::fetch('SELECT * FROM `career_match_scores` WHERE `recommendation_id` = ? LIMIT 1', [$recommendationId]);
    }

    public static function recommendationsForUser(int $userId, ?int $resultId = null): array
    {
        if ($resultId) {
            return self::recommendations($resultId);
        }
        $row = self::latestForUser($userId);
        return $row ? self::recommendations((int) $row['id']) : [];
    }

    /* ------------------------------------------------------------------ */
    /* Favoriler                                                            */
    /* ------------------------------------------------------------------ */

    public static function isFavorite(int $userId, int $careerId): bool
    {
        return (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM `favorite_careers` WHERE `user_id` = ? AND `career_id` = ?',
            [$userId, $careerId]
        ) > 0;
    }

    public static function addFavorite(int $userId, int $careerId): bool
    {
        $exists = self::isFavorite($userId, $careerId);
        if ($exists) {
            return false;
        }
        Database::query(
            'INSERT INTO `favorite_careers` (`user_id`,`career_id`) VALUES (?,?)',
            [$userId, $careerId]
        );
        return true;
    }

    public static function removeFavorite(int $userId, int $careerId): bool
    {
        return Database::update(
            'DELETE FROM `favorite_careers` WHERE `user_id` = ? AND `career_id` = ?',
            [$userId, $careerId]
        ) > 0;
    }

    public static function toggleFavorite(int $userId, int $careerId): bool
    {
        if (self::isFavorite($userId, $careerId)) {
            self::removeFavorite($userId, $careerId);
            return false;
        }
        self::addFavorite($userId, $careerId);
        return true;
    }

    public static function favoriteIds(int $userId): array
    {
        $rows = Database::fetchAll('SELECT `career_id` FROM `favorite_careers` WHERE `user_id` = ?', [$userId]);
        return array_map('intval', array_column($rows, 'career_id'));
    }

    public static function favorites(int $userId): array
    {
        return Database::fetchAll(
            'SELECT f.id AS favorite_id, f.created_at AS favorited_at, c.*, cat.name AS category_name,
                    cat.slug AS category_slug, cat.color AS category_color
             FROM `favorite_careers` f
             INNER JOIN `careers` c ON c.id = f.career_id
             INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE f.user_id = ? AND c.is_active = 1
             ORDER BY f.created_at DESC',
            [$userId]
        );
    }

    public static function favoriteCount(int $userId): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(*) FROM `favorite_careers` WHERE `user_id` = ?', [$userId]);
    }

    /* ------------------------------------------------------------------ */
    /* Goruntulenen meslekler ve aktiviteler                                */
    /* ------------------------------------------------------------------ */

    public static function recordView(int $userId, int $careerId): void
    {
        $exists = (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM `viewed_careers` WHERE `user_id` = ? AND `career_id` = ?',
            [$userId, $careerId]
        );
        if ($exists) {
            Database::query('UPDATE `viewed_careers` SET `viewed_at` = NOW() WHERE `user_id` = ? AND `career_id` = ?', [$userId, $careerId]);
        } else {
            Database::query('INSERT INTO `viewed_careers` (`user_id`,`career_id`,`viewed_at`) VALUES (?,?,NOW())', [$userId, $careerId]);
        }
    }

    public static function viewedCount(int $userId): int
    {
        return (int) Database::fetchColumn('SELECT COUNT(DISTINCT `career_id`) FROM `viewed_careers` WHERE `user_id` = ?', [$userId]);
    }

    public static function recentlyViewed(int $userId, int $limit = 5): array
    {
        return Database::fetchAll(
            'SELECT v.viewed_at, c.*, cat.name AS category_name, cat.color AS category_color
             FROM `viewed_careers` v
             INNER JOIN `careers` c ON c.id = v.career_id
             INNER JOIN `career_categories` cat ON cat.id = c.category_id
             WHERE v.user_id = ? AND c.is_active = 1
             GROUP BY c.id ORDER BY v.viewed_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public static function logActivity(int $userId, string $type, string $description, ?string $refType = null, ?int $refId = null): void
    {
        Database::query(
            'INSERT INTO `user_activities` (`user_id`,`activity_type`,`description`,`reference_type`,`reference_id`)
             VALUES (?,?,?,?,?)',
            [$userId, $type, mb_substr($description, 0, 255), $refType, $refId]
        );
    }

    public static function activities(int $userId, int $limit = 10): array
    {
        return Database::fetchAll(
            'SELECT * FROM `user_activities` WHERE `user_id` = ? ORDER BY `created_at` DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }
}
