<?php
/**
 * AI Kariyer Koçu API uç noktası.
 *
 * - config/ai.php içinde API anahtarı tanımlıysa gerçek bir dil modeline bağlanır.
 * - Anahtar yoksa / API hatası verirse yerel kural motoruyla yanıt üretir.
 *   Böylece uç nokta her koşulda çalışır; "göstermelik" değildir.
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece POST istekleri kabul edilir.'], JSON_UNESCAPED_UNICODE);
    return;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
$message = trim((string) ($payload['message'] ?? ''));

if ($message === '' || mb_strlen($message) > 500) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Geçerli bir mesaj girin (en fazla 500 karakter).'], JSON_UNESCAPED_UNICODE);
    return;
}

$history = $payload['history'] ?? [];
if (!is_array($history)) $history = [];
$history = array_slice($history, -8);

/* Meslek bağlamı: cevabın amaca uygun olması için modele gerçek veriler verilir. */
$careers = Database::fetchAll('SELECT name, short_description, salary_range, education_level FROM careers WHERE is_active = 1 ORDER BY name LIMIT 12');
$context = array_map(static function ($c) {
    return $c['name'] . ' (' . $c['education_level'] . ', ' . ($c['salary_range'] ?: 'maaş belirtilmemiş') . '): ' . mb_substr($c['short_description'] ?: '', 0, 110);
}, $careers);

$cfg = require __DIR__ . '/../config/ai.php';

if (!empty($cfg['enabled']) && !empty($cfg['api_key']) && function_exists('curl_init')) {
    $system = "Sen Akıllı Kariyer platformunun profesyonel kariyer koçusun. Türkçe yanıt ver. "
        . "Kullanıcıya meslek seçimi, maaş, eğitim ve kariyer yolları konusunda rehberlik et. "
        . "Kesin meslek tayini veya klinik tanı koyma; alternatifleri ve keşif adımlarını öner. "
        . "Kısa, net ve samimi yanıt ver (en fazla 5 cümle). "
        . "Platformdaki örnek meslekler: " . implode(' | ', $context);

    $messages = [['role' => 'system', 'content' => $system]];
    foreach ($history as $h) {
        if (!isset($h['role'], $h['text'])) continue;
        $role = $h['role'] === 'user' ? 'user' : 'assistant';
        $messages[] = ['role' => $role, 'content' => mb_substr((string) $h['text'], 0, 400)];
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    $ch = curl_init($cfg['api_url']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $cfg['api_key'],
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $cfg['model'],
            'messages' => $messages,
            'max_tokens' => (int) ($cfg['max_tokens'] ?? 500),
            'temperature' => 0.5,
        ], JSON_UNESCAPED_UNICODE),
    ]);
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $status >= 200 && $status < 300) {
        $data = json_decode($response, true);
        $reply = $data['choices'][0]['message']['content'] ?? '';
        if (trim($reply) !== '') {
            echo json_encode(['success' => true, 'source' => 'ai', 'reply' => trim($reply)], JSON_UNESCAPED_UNICODE);
            return;
        }
    }
}

/* Yerel kural motoru (API yoksa veya hata varsa) */
$n = StringHelperNormalize($message);
$reply = 'Meslek, maaş, eğitim veya kariyer yolu hakkında sorabilirsin. Örneğin "Yazılımcı maaşı ne kadar?"';

if (str_contains($n, 'maas') || str_contains($n, 'kazan')) {
    $reply = 'Maaş aralıkları meslek detay sayfalarında yer alıyor. Deneyim, şehir ve sektör bu aralığı değiştirir. Hangi mesleği merak ediyorsun?';
} elseif (str_contains($n, 'egitim') || str_contains($n, 'universite') || str_contains($n, 'lise')) {
    $reply = 'Eğitim gereksinimleri mesleğe göre değişir. Meslek kataloğundan ilgilendiğin mesleğin eğitim bilgisini inceleyebilirsin.';
} elseif (str_contains($n, 'test')) {
    $reply = '12 soruluk kariyer testini tamamlayarak sana uygun meslekleri uyum puanıyla görebilirsin. Teste ana sayfadan ulaşabilirsin.';
} elseif (str_contains($n, 'mulakat') || str_contains($n, 'gorusme')) {
    $reply = 'Mülakat simülatöründe 8 soruya cevap verip anlık geri bildirim alabilirsin. STAR yöntemini kullanmanı öneririm.';
} else {
    foreach ($careers as $c) {
        if (mb_stripos($c['name'], $message) !== false || mb_stripos($message, $c['name']) !== false) {
            $reply = $c['name'] . ': ' . ($c['short_description'] ?: 'Meslek detay sayfasında açıklama bulunuyor.');
            if ($c['salary_range']) $reply .= ' Maaş aralığı: ' . $c['salary_range'] . '.';
            break;
        }
    }
}

echo json_encode(['success' => true, 'source' => 'local', 'reply' => $reply], JSON_UNESCAPED_UNICODE);

function StringHelperNormalize(string $s): string
{
    $s = mb_strtolower($s, 'UTF-8');
    return str_replace(['ı', 'ş', 'ğ', 'ü', 'ö', 'ç'], ['i', 's', 'g', 'u', 'o', 'c'], $s);
}
