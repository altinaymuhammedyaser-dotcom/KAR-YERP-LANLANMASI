<?php
/**
 * Akilli Kariyer - Meslek onerileri API
 *
 * GET  /api/recommendations.php            -> en guncel sonucun onerileri
 * GET  /api/recommendations.php?result_id= -> belirli sonucun onerileri
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Models\Result;
use App\Services\RecommendationService;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!Auth::check()) {
    json_response([
        'success'  => false,
        'message'  => 'Önerilerinizi görüntülemek için giriş yapmalısınız.',
        'redirect' => url('login.php'),
    ], 401);
}

$userId   = (int) Auth::id();
$resultId = (int) ($_GET['result_id'] ?? 0);

try {
    if ($resultId > 0) {
        $result = Result::find($resultId);
        if (!$result || (int) $result['user_id'] !== $userId) {
            json_response(['success' => false, 'message' => 'Sonuç bulunamadı.'], 404);
        }
    } else {
        $result = Result::latestForUser($userId);
        if (!$result) {
            json_response([
                'success' => false,
                'message' => 'Henüz tamamlanmış bir testiniz yok.',
                'redirect' => url('career-test.php'),
            ], 404);
        }
        $resultId = (int) $result['id'];
    }

    $detail = RecommendationService::resultDetail($resultId);
    $recommendations = [];

    foreach ($detail['recommendations'] as $rec) {
        $recommendations[] = [
            'career_id'   => (int) $rec['career_id'],
            'name'        => $rec['name'],
            'slug'        => $rec['slug'],
            'category'    => $rec['category_name'],
            'icon'        => $rec['icon'],
            'score'       => (float) $rec['match_score'],
            'rank'        => (int) $rec['rank'],
            'reason'      => $rec['reason'],
            'url'         => url('career-detail.php?slug=' . urlencode($rec['slug'])),
            'components'  => [
                'interest'    => (float) ($rec['components']['interest_score'] ?? 0),
                'ability'     => (float) ($rec['components']['ability_score'] ?? 0),
                'preference'  => (float) ($rec['components']['preference_score'] ?? 0),
                'environment' => (float) ($rec['components']['environment_score'] ?? 0),
                'skill'       => (float) ($rec['components']['skill_score'] ?? 0),
            ],
            'is_favorite' => Result::isFavorite($userId, (int) $rec['career_id']),
        ];
    }

    json_response([
        'success'        => true,
        'result_id'      => $resultId,
        'completed_at'   => $result['completed_at'],
        'dominant_type'  => $result['dominant_type'],
        'summary'        => $result['summary'],
        'recommendations' => $recommendations,
    ]);
} catch (\Throwable $e) {
    error_log('[API recommendations] ' . $e->getMessage());
    json_response([
        'success' => false,
        'message' => APP_DEBUG ? $e->getMessage() : 'Öneriler yüklenirken bir hata oluştu.',
    ], 500);
}
