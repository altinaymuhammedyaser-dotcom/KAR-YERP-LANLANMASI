<?php
/**
 * Akilli Kariyer - Kariyer raporu PDF indirme
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Models\Result;
use App\Services\CareerMatchingService;
use App\Services\RecommendationService;
use App\Services\ReportService;

$user = Auth::user();
if (!$user) {
    Auth::redirectLogin();
}

$userId    = (int) $user->id;
$sessionId = (int) ($_GET['session'] ?? 0);
$resultId  = (int) ($_GET['result'] ?? 0);

if ($resultId > 0) {
    $result = Result::find($resultId);
} elseif ($sessionId > 0) {
    $result = Result::findBySession($sessionId);
} else {
    $result = Result::latestForUser($userId);
}

if (!$result || (int) $result['user_id'] !== $userId) {
    abort_page(404, 'Rapor Bulunamadı', 'İstediğiniz rapor bulunamadı. Lütfen test sonuçlarınızdan tekrar deneyin.', url('dashboard/results.php'));
}

$detail = RecommendationService::resultDetail((int) $result['id']);

$matcher   = new CareerMatchingService();
$scores    = [
    'interest'    => $detail['interest_scores'],
    'ability'     => $detail['ability_scores'],
    'preference'  => $detail['preference_scores'],
    'environment' => $detail['environment_scores'],
];
$assessment = $matcher->buildAssessment($scores, array_map(static fn($r) => [
    'career'     => $r,
    'components' => $r['components'],
], $detail['recommendations']));

$service = new ReportService($result, $detail['recommendations'], $assessment, [
    'full_name' => $user->fullName(),
    'email'     => $user->email,
]);

$filename = 'kariyer-raporu-' . $user->id . '-' . date('Ymd', strtotime((string) $result['completed_at'])) . '.pdf';

try {
    $service->build($filename);
} catch (\Throwable $e) {
    error_log('[PDF] ' . $e->getMessage());
    abort_page(500, 'Rapor Oluşturulamadı', 'PDF raporu oluşturulurken bir hata oluştu: ' . (APP_DEBUG ? $e->getMessage() : 'Lütfen tekrar deneyin.'), url('dashboard/results.php'));
}
