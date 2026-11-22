<?php
/**
 * Akilli Kariyer - Oneri servisi
 *
 * Hesaplanan eslesme sonuclarini saklar, tekrar hesaplar ve sunum icin hazirlar.
 */

namespace App\Services;

use App\Core\Database;
use App\Models\Result;
use App\Models\Setting;
use App\Models\Test;

class RecommendationService
{
    /**
     * Kullanicinin en guncel sonucunu getirir.
     */
    public static function latestResult(int $userId): ?array
    {
        return Result::latestForUser($userId);
    }

    /**
     * Sonuc detaylari: oneriler + puanlama kirilimlari.
     */
    public static function resultDetail(int $resultId): ?array
    {
        $result = Result::find($resultId);
        if (!$result) {
            return null;
        }

        $recommendations = Result::recommendations($resultId);

        foreach ($recommendations as &$rec) {
            $rec['components'] = Result::matchScores((int) $rec['id']) ?? [];
            $rec['is_favorite'] = Result::isFavorite((int) $result['user_id'], (int) $rec['career_id']);
        }
        unset($rec);

        $result['recommendations'] = $recommendations;
        $result['interest_scores']    = json_decode((string) $result['interest_scores'], true) ?: [];
        $result['ability_scores']     = json_decode((string) $result['ability_scores'], true) ?: [];
        $result['preference_scores']  = json_decode((string) $result['preference_scores'], true) ?: [];
        $result['environment_scores'] = json_decode((string) $result['environment_scores'], true) ?: [];

        // Oturum bilgilerinden soru sayisini ve sureyi al
        $session = \App\Models\Test::findSession((int) $result['session_id']);
        $result['total_questions'] = (int) ($session['total_questions'] ?? 0);
        $result['started_at']      = $session['started_at'] ?? null;
        $result['answered_count']  = (int) ($session['answered_count'] ?? 0);

        return $result;
    }

    /**
     * Kullanicinin tum sonuclari (liste).
     */
    public static function history(int $userId, int $limit = 20): array
    {
        return Result::forUser($userId, $limit);
    }

    /**
     * Eslestirme puanini yeniden hesaplar (admin ayar degistirdikten sonra).
     */
    public static function recalculateAll(): int
    {
        $sessions = Database::fetchAll(
            "SELECT tr.id AS result_id, tr.session_id, tr.user_id
             FROM `test_results` tr
             INNER JOIN `test_sessions` ts ON ts.id = tr.session_id
             WHERE ts.status = 'completed'"
        );

        $matcher = new CareerMatchingService();
        $updated = 0;

        foreach ($sessions as $row) {
            $questions = Test::questions();
            $answers   = Test::answerMap((int) $row['session_id']);
            $scores    = $matcher->calculateDimensionScores($answers, $questions);
            $limit     = (int) Setting::int('recommendation_limit', 12);
            $matches   = $matcher->match($scores, $limit > 0 ? $limit : 12);

            Database::transaction(function () use ($row, $matches) {
                Database::query('DELETE FROM `career_match_scores` WHERE `result_id` = ?', [$row['result_id']]);
                Database::query('DELETE FROM `career_recommendations` WHERE `result_id` = ?', [$row['result_id']]);

                $payload = [];
                foreach ($matches as $rec) {
                    $payload[] = [
                        'career_id'  => (int) $rec['career']['id'],
                        'match_score' => $rec['score'],
                        'reason'     => $rec['reason'],
                        'components' => $rec['components'],
                    ];
                }
                Result::saveRecommendations((int) $row['result_id'], (int) $row['user_id'], $payload);
            });

            $updated++;
        }

        return $updated;
    }

    /**
     * Bir meslegin kullaniciya gore karsilastirmali uyumluluk degeri.
     */
    public static function careerFitForUser(int $userId, int $careerId): ?array
    {
        $result = Result::latestForUser($userId);
        if (!$result) {
            return null;
        }
        foreach (Result::recommendations((int) $result['id']) as $rec) {
            if ((int) $rec['career_id'] === $careerId) {
                return $rec;
            }
        }
        return null;
    }
}
