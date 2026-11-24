<?php
/**
 * Akilli Kariyer - Kariyer testi API
 *
 * POST /api/test.php
 *   { action: "answer",   session_id: int, question_id: int, option_ids: [int] }
 *   { action: "complete", session_id: int }
 *   { action: "start" }
 *   { action: "status",   session_id: int }
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Models\Test;
use App\Services\TestService;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'message' => 'Geçersiz istek metodu.'], 405);
}

// CSRF dogrulama
$raw     = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$token = $payload['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!CSRF::validate(is_string($token) ? $token : null)) {
    json_response(['success' => false, 'message' => 'Oturum doğrulaması başarısız oldu. Lütfen sayfayı yenileyin.'], 419);
}

if (!Auth::check()) {
    json_response([
        'success'  => false,
        'message'  => 'Kariyer testine devam etmek için giriş yapmalısınız.',
        'redirect' => url('login.php'),
    ], 401);
}

$userId   = (int) Auth::id();
$action   = (string) ($payload['action'] ?? '');
$sessionId = (int) ($payload['session_id'] ?? 0);

/**
 * Oturumun kullaniciya ait oldugunu dogrular.
 */
function resolveSession(int $sessionId, int $userId): ?array
{
    if ($sessionId <= 0) {
        return null;
    }
    $session = Test::findSession($sessionId);
    if (!$session || (int) $session['user_id'] !== $userId) {
        return null;
    }
    return $session;
}

try {
    switch ($action) {
        case 'start':
            $session = TestService::start($userId);
            json_response([
                'success'   => true,
                'session_id' => (int) $session['id'],
                'total'     => (int) $session['total_questions'],
                'redirect'  => url('career-test.php'),
            ]);

        case 'status':
            $session = resolveSession($sessionId, $userId);
            if (!$session) {
                json_response(['success' => false, 'message' => 'Test oturumu bulunamadı.'], 404);
            }
            $answered = (int) Database::fetchColumn(
                'SELECT COUNT(*) FROM `test_answers` WHERE `session_id` = ?',
                [$sessionId]
            );
            json_response([
                'success'   => true,
                'answered'  => $answered,
                'total'     => (int) $session['total_questions'],
                'status'    => $session['status'],
            ]);

        case 'answer':
            $session = resolveSession($sessionId, $userId);
            if (!$session) {
                json_response(['success' => false, 'message' => 'Test oturumu bulunamadı.'], 404);
            }
            if ($session['status'] !== 'in_progress') {
                json_response(['success' => false, 'message' => 'Bu test oturumu tamamlanmış.'], 409);
            }

            $result = TestService::answer($session, $payload);
            if (!$result['ok']) {
                json_response(['success' => false, 'message' => $result['message']], 422);
            }

            json_response([
                'success'  => true,
                'answered' => $result['answered'],
                'total'    => $result['total'],
                'message'  => 'Cevabınız kaydedildi.',
            ]);

        case 'complete':
            $session = resolveSession($sessionId, $userId);
            if (!$session) {
                json_response(['success' => false, 'message' => 'Test oturumu bulunamadı.'], 404);
            }

            if ($session['status'] === 'completed') {
                $existing = \App\Models\Result::findBySession($sessionId);
                json_response([
                    'success'  => true,
                    'redirect' => url('test-result.php?session=' . $sessionId),
                    'result_id' => $existing ? (int) $existing['id'] : null,
                ]);
            }

            $completed = TestService::complete($session, $userId);

            // Farkli sorular modunda ikinci bir test bittiyse karsilastirma sayfasi dondur
            $comparisonUrl = null;
            $completedCount = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM test_sessions WHERE user_id = ? AND status = 'completed'",
                [$userId]
            );
            if ($completedCount >= 2) {
                $lastTwo = Database::fetchAll("
                    SELECT id FROM test_sessions 
                    WHERE user_id = ? AND status = 'completed' 
                    ORDER BY completed_at DESC 
                    LIMIT 2
                ", [$userId]);
                if (count($lastTwo) === 2) {
                    $comparisonUrl = url('test-compare.php?s1=' . (int) $lastTwo[0]['id'] . '&s2=' . (int) $lastTwo[1]['id']);
                }
            }

            json_response([
                'success'   => true,
                'result_id' => $completed['result_id'],
                'redirect'  => url('test-result.php?session=' . $sessionId),
                'comparisonUrl' => $comparisonUrl,
                'message'   => 'Testiniz tamamlandı. Sonuçlarınız hazır!',
            ]);

        default:
            json_response(['success' => false, 'message' => 'Bilinmeyen işlem.'], 400);
    }
} catch (\Throwable $e) {
    error_log('[API test] ' . $e->getMessage());
    json_response([
        'success' => false,
        'message' => APP_DEBUG ? $e->getMessage() : 'İşlem sırasında bir hata oluştu.',
    ], 500);
}
