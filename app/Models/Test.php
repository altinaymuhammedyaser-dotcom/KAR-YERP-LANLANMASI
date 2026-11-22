<?php
/**
 * Akilli Kariyer - Kariyer testi modeli
 */

namespace App\Models;

use App\Core\Database;

class Test
{
    /* ------------------------------------------------------------------ */
    /* Sorular                                                              */
    /* ------------------------------------------------------------------ */

    public static function categories(): array
    {
        return Database::fetchAll(
            'SELECT tc.*, (SELECT COUNT(*) FROM `test_questions` q WHERE q.category_id = tc.id AND q.is_active = 1) AS question_count
             FROM `test_categories` tc WHERE tc.is_active = 1 ORDER BY tc.sort_order ASC'
        );
    }

    public static function categoryBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM `test_categories` WHERE `slug` = ? LIMIT 1', [$slug]);
    }

    /**
     * Aktif sorulari ve seceneklerini dondurur.
     */
    public static function questions(): array
    {
        $questions = Database::fetchAll(
            'SELECT q.*, tc.name AS category_name, tc.slug AS category_slug, tc.icon AS category_icon,
                    tc.dimension_type, tc.weight AS category_weight
             FROM `test_questions` q
             INNER JOIN `test_categories` tc ON tc.id = q.category_id
             WHERE q.is_active = 1 AND tc.is_active = 1
             ORDER BY tc.sort_order ASC, q.sort_order ASC, q.id ASC'
        );

        if (!$questions) {
            return [];
        }

        $ids = array_column($questions, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $options = Database::fetchAll(
            "SELECT * FROM `test_options` WHERE `question_id` IN ($placeholders) ORDER BY `sort_order` ASC",
            $ids
        );

        $grouped = [];
        foreach ($options as $option) {
            $grouped[(int) $option['question_id']][] = $option;
        }

        foreach ($questions as &$question) {
            $question['options'] = $grouped[(int) $question['id']] ?? [];
        }

        return $questions;
    }

    public static function questionCount(): int
    {
        return (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM `test_questions` q INNER JOIN `test_categories` tc ON tc.id = q.category_id
             WHERE q.is_active = 1 AND tc.is_active = 1'
        );
    }

    public static function findQuestion(int $id): ?array
    {
        $question = Database::fetch(
            'SELECT q.*, tc.name AS category_name, tc.slug AS category_slug, tc.icon AS category_icon, tc.dimension_type
             FROM `test_questions` q INNER JOIN `test_categories` tc ON tc.id = q.category_id
             WHERE q.id = ? LIMIT 1',
            [$id]
        );

        if (!$question) {
            return null;
        }

        $question['options'] = Database::fetchAll(
            'SELECT * FROM `test_options` WHERE `question_id` = ? ORDER BY `sort_order` ASC',
            [$id]
        );

        return $question;
    }

    /* ------------------------------------------------------------------ */
    /* Oturumlar                                                            */
    /* ------------------------------------------------------------------ */

    public static function createSession(int $userId, int $totalQuestions): int
    {
        return Database::insert(
            'INSERT INTO `test_sessions` (`user_id`,`session_token`,`status`,`current_index`,`total_questions`,`answered_count`,`started_at`)
             VALUES (?,?,?,0,?,0,NOW())',
            [$userId, bin2hex(random_bytes(32)), 'in_progress', $totalQuestions]
        );
    }

    public static function findSession(int $id): ?array
    {
        return Database::fetch('SELECT * FROM `test_sessions` WHERE `id` = ? LIMIT 1', [$id]);
    }

    /**
     * Kullanicinin yarim kalan test oturumu.
     */
    public static function activeSession(int $userId): ?array
    {
        return Database::fetch(
            "SELECT * FROM `test_sessions`
             WHERE `user_id` = ? AND `status` = 'in_progress'
             ORDER BY `updated_at` DESC LIMIT 1",
            [$userId]
        );
    }

    public static function latestSession(int $userId): ?array
    {
        return Database::fetch(
            'SELECT * FROM `test_sessions` WHERE `user_id` = ? ORDER BY `created_at` DESC LIMIT 1',
            [$userId]
        );
    }

    public static function sessions(int $userId, int $limit = 20): array
    {
        return Database::fetchAll(
            'SELECT ts.*, tr.id AS result_id, tr.overall_score, tr.dominant_type, tr.completed_at AS result_completed
             FROM `test_sessions` ts
             LEFT JOIN `test_results` tr ON tr.session_id = ts.id
             WHERE ts.user_id = ? ORDER BY ts.started_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public static function updateSession(int $id, array $data): int
    {
        $allowed = ['status', 'current_index', 'answered_count', 'completed_at', 'total_questions'];
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sets[] = "`$key` = ?";
                $params[] = $value;
            }
        }
        if (!$sets) {
            return 0;
        }
        $params[] = $id;
        return Database::update('UPDATE `test_sessions` SET ' . implode(', ', $sets) . ' WHERE `id` = ?', $params);
    }

    /* ------------------------------------------------------------------ */
    /* Cevaplar                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Soruya cevap kaydeder (varsa gunceller).
     */
    public static function saveAnswer(int $sessionId, int $questionId, array $optionIds, ?int $scoreValue, array $optionKeys): void
    {
        Database::query(
            'INSERT INTO `test_answers` (`session_id`,`question_id`,`option_ids`,`option_keys`,`score_value`,`created_at`,`updated_at`)
             VALUES (?,?,?,?,?,NOW(),NOW())
             ON DUPLICATE KEY UPDATE `option_ids` = VALUES(`option_ids`), `option_keys` = VALUES(`option_keys`),
                                     `score_value` = VALUES(`score_value`), `updated_at` = NOW()',
            [
                $sessionId,
                $questionId,
                $optionIds ? implode(',', $optionIds) : null,
                $optionKeys ? implode(',', $optionKeys) : null,
                $scoreValue,
            ]
        );

        $count = (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM `test_answers` WHERE `session_id` = ?',
            [$sessionId]
        );
        Database::query('UPDATE `test_sessions` SET `answered_count` = ? WHERE `id` = ?', [$count, $sessionId]);
    }

    public static function answers(int $sessionId): array
    {
        return Database::fetchAll('SELECT * FROM `test_answers` WHERE `session_id` = ?', [$sessionId]);
    }

    /**
     * Cevaplar soru id => secenek id listesi olarak dondurur.
     */
    public static function answerMap(int $sessionId): array
    {
        $rows = self::answers($sessionId);
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['question_id']] = [
                'option_ids' => array_filter(explode(',', (string) $row['option_ids'])),
                'option_keys' => array_filter(explode(',', (string) $row['option_keys'])),
                'score'      => $row['score_value'] !== null ? (int) $row['score_value'] : null,
            ];
        }
        return $map;
    }

    public static function deleteSession(int $sessionId): void
    {
        Database::query('DELETE FROM `test_sessions` WHERE `id` = ?', [$sessionId]);
    }
}
