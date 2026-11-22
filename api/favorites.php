<?php
/**
 * Akilli Kariyer - Favori meslek API
 *
 * POST /api/favorites.php  { action: "add"|"remove"|"toggle", career_id: int }
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Models\Activity;
use App\Models\Career;
use App\Models\Result;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'message' => 'Geçersiz istek metodu.'], 405);
}

// CSRF kontrolu (JSON body veya header)
$token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if ($token === null) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw ?: '', true);
    if (is_array($json)) {
        $token = $json['_token'] ?? null;
    }
}
if (!CSRF::validate(is_string($token) ? $token : null)) {
    json_response(['success' => false, 'message' => 'Oturum doğrulaması başarısız oldu. Lütfen sayfayı yenileyin.'], 419);
}

if (!Auth::check()) {
    json_response([
        'success'  => false,
        'message'  => 'Favorilere eklemek için giriş yapmalısınız.',
        'redirect' => url('login.php'),
    ], 401);
}

$raw     = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$action   = (string) ($payload['action'] ?? 'toggle');
$careerId = (int) ($payload['career_id'] ?? 0);

if ($careerId <= 0) {
    json_response(['success' => false, 'message' => 'Geçersiz meslek.'], 422);
}

$career = Career::find($careerId);
if (!$career) {
    json_response(['success' => false, 'message' => 'Meslek bulunamadı.'], 404);
}

$userId = (int) Auth::id();

switch ($action) {
    case 'add':
        if (Result::isFavorite($userId, $careerId)) {
            json_response([
                'success'  => true,
                'favorite' => true,
                'count'    => Result::favoriteCount($userId),
                'message'  => 'Bu meslek zaten favorilerinizde.',
            ]);
        }
        Result::addFavorite($userId, $careerId);
        Activity::log($userId, 'favorite', $career['name'] . ' mesleğini favorilerinize eklediniz.', 'career', $careerId);
        $message = $career['name'] . ' favorilerinize eklendi.';
        $isFav = true;
        break;

    case 'remove':
        Result::removeFavorite($userId, $careerId);
        Activity::log($userId, 'unfavorite', $career['name'] . ' mesleğini favorilerinizden çıkardınız.', 'career', $careerId);
        $message = $career['name'] . ' favorilerinizden çıkarıldı.';
        $isFav = false;
        break;

    case 'toggle':
    default:
        $isFav = Result::toggleFavorite($userId, $careerId);
        Activity::log(
            $userId,
            $isFav ? 'favorite' : 'unfavorite',
            $career['name'] . ' mesleği ' . ($isFav ? 'favorilere eklendi' : 'favorilerden çıkarıldı') . '.',
            'career',
            $careerId
        );
        $message = $career['name'] . ($isFav ? ' favorilerinize eklendi.' : ' favorilerinizden çıkarıldı.');
        break;
}

json_response([
    'success'  => true,
    'favorite' => $isFav,
    'count'    => Result::favoriteCount($userId),
    'message'  => $message,
]);
