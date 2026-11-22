<?php
/**
 * Akilli Kariyer - Kariyer testi servisi
 *
 * Test oturumunu baslatir, cevaplari kaydeder, kaldigi yerden devam ettirir
 * ve testi tamamladiginda sonuclari uretir.
 */

namespace App\Services;

use App\Core\Database;
use App\Models\Activity;
use App\Models\Result;
use App\Models\Test;
use App\Models\User;

class TestService
{
    /**
     * Kullanici icin yeni test oturumu baslatir (var olan yarim oturum varsa onu dondurur).
     */
    public static function start(int $userId): array
    {
        $existing = Test::activeSession($userId);
        if ($existing) {
            return $existing;
        }

        $questions = Test::questions();
        $total = count($questions);

        if ($total === 0) {
            throw new \RuntimeException('Test soruları tanımlanmamış.');
        }

        $limit = (int) \App\Models\Setting::int('test_questions_per_session', 0);
        if ($limit > 0 && $limit < $total) {
            $questions = array_slice($questions, 0, $limit);
            $total = $limit;
        }

        $sessionId = Test::createSession($userId, $total);

        Activity::log($userId, 'test_start', 'Kariyer testini başlattınız.', 'session', $sessionId);

        return Test::findSession($sessionId) ?? [];
    }

    /**
     * Soruyu dogrular ve cevabi kaydeder.
     *
     * @param array $input  ['question_id'=>int, 'option_ids'=>array]
     * @return array{ok:bool, message?:string, answered?:int, total?:int}
     */
    public static function answer(array $session, array $input): array
    {
        $questionId = (int) ($input['question_id'] ?? 0);
        $optionIds  = array_values(array_unique(array_map('intval', (array) ($input['option_ids'] ?? []))));

        $question = Test::findQuestion($questionId);
        if (!$question) {
            return ['ok' => false, 'message' => 'Soru bulunamadı.'];
        }

        $validOptionIds = array_map(static fn($o) => (int) $o['id'], $question['options']);
        $optionIds = array_values(array_intersect($optionIds, $validOptionIds));

        if (!$optionIds) {
            return ['ok' => false, 'message' => 'Lütfen en az bir seçenek işaretleyin.'];
        }

        $min = max(1, (int) $question['min_select']);
        $max = max($min, (int) $question['max_select']);

        if (count($optionIds) < $min) {
            return ['ok' => false, 'message' => 'Bu soru için en az ' . $min . ' seçenek işaretlemelisiniz.'];
        }
        if (count($optionIds) > $max) {
            return ['ok' => false, 'message' => 'Bu soru için en fazla ' . $max . ' seçenek işaretleyebilirsiniz.'];
        }

        // Secenek anahtarlari ve ortalama puan
        $keys = [];
        $scoreSum = 0;
        foreach ($question['options'] as $option) {
            if (in_array((int) $option['id'], $optionIds, true)) {
                $keys[] = $option['option_key'];
                $scoreSum += (int) $option['score_value'];
            }
        }
        $avgScore = (int) round($scoreSum / max(1, count($optionIds)));

        Test::saveAnswer((int) $session['id'], $questionId, $optionIds, $avgScore, $keys);

        $answered = (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM `test_answers` WHERE `session_id` = ?',
            [$session['id']]
        );

        Test::updateSession((int) $session['id'], ['answered_count' => $answered]);

        return [
            'ok'       => true,
            'answered' => $answered,
            'total'    => (int) $session['total_questions'],
        ];
    }

    /**
     * Testin tüm soruları cevaplanmış olup olmadığını kontrol eder.
     */
    public static function isComplete(array $session): bool
    {
        $answered = (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM `test_answers` WHERE `session_id` = ?',
            [$session['id']]
        );
        return $answered >= (int) $session['total_questions'] && (int) $session['total_questions'] > 0;
    }

    /**
     * Testi tamamlar ve sonuclari hesaplar.
     *
     * @return array{result_id:int, recommendations:array}
     */
    public static function complete(array $session, int $userId): array
    {
        $questions = Test::questions();
        $answers   = Test::answerMap((int) $session['id']);

        $matcher = new CareerMatchingService();
        $scores  = $matcher->calculateDimensionScores($answers, $questions);

        $limit = (int) \App\Models\Setting::int('recommendation_limit', 12);
        $recommendations = $matcher->match($scores, $limit > 0 ? $limit : 12);

        $assessment = $matcher->buildAssessment($scores, $recommendations);

        $meta = [
            'top_interests'   => $assessment['top_interests'],
            'top_abilities'   => $assessment['top_abilities'],
            'top_preferences' => $assessment['top_preferences'],
            'dominant_type'   => $assessment['dominant_type'],
            'overall_score'   => $recommendations ? $recommendations[0]['score'] : 0,
            'summary'         => self::buildSummary($assessment, $recommendations),
        ];

        $resultId = Database::transaction(function () use ($session, $userId, $scores, $meta, $recommendations) {
            $resultId = Result::create((int) $session['id'], $userId, $scores, $meta);

            $payload = [];
            foreach ($recommendations as $rec) {
                $payload[] = [
                    'career_id'  => (int) $rec['career']['id'],
                    'match_score' => $rec['score'],
                    'reason'     => $rec['reason'],
                    'components' => $rec['components'],
                ];
            }
            Result::saveRecommendations($resultId, $userId, $payload);

            Test::updateSession((int) $session['id'], [
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);

            return $resultId;
        });

        Activity::log($userId, 'test_complete', 'Kariyer testinizi tamamladınız.', 'result', $resultId);

        return [
            'result_id'      => $resultId,
            'recommendations' => $recommendations,
            'assessment'     => $assessment,
            'scores'         => $scores,
        ];
    }

    /**
     * Sonuc ozeti metni.
     */
    private static function buildSummary(array $assessment, array $recommendations): string
    {
        $interests = array_map(
            static fn($item) => $item['label'] ?? $item,
            $assessment['top_interests']
        );
        $abilities = array_map(
            static fn($item) => $item['label'] ?? $item,
            $assessment['top_abilities']
        );

        $top = $recommendations[0] ?? null;
        $careerName = $top ? $top['career']['name'] : '';

        return sprintf(
            'Kariyer profilin %s ağırlıklı. En güçlü ilgi alanların: %s. Öne çıkan yeteneklerin: %s. Bu profilinle en uyumlu görünen alan %s.',
            mb_strtolower($assessment['dominant_type']),
            $interests ? implode(', ', $interests) : 'belirgin değil',
            $abilities ? implode(', ', $abilities) : 'belirgin değil',
            $careerName !== '' ? $careerName : 'henüz netleşmedi'
        );
    }
}
