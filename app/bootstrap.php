<?php
/**
 * Akilli Kariyer - Uygulama önyükleyici
 *
 * Tüm sayfalar bu dosyayı dahil ederek uygulama çekirdeğine erişir.
 */

declare(strict_types=1);

define('APP_START_TIME', microtime(true));

// 1) Yapılandırma
require_once __DIR__ . '/../config/app.php';

// 2) Otomatik yükleyici
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file = APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $relative . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// 3) Yardimcilar
require_once APP_ROOT . '/app/Helpers/functions.php';

// 4) Oturum ayarlari
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// 5) Hata yakalama
set_exception_handler(static function (Throwable $e): void {
    error_log('[APP] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

    if (!headers_sent()) {
        http_response_code(500);
    }

    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => APP_DEBUG ? $e->getMessage() : 'Beklenmeyen bir hata oluştu.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (APP_DEBUG && !headers_sent()) {
        echo '<pre style="background:#F6F8FC;padding:24px;font-family:monospace;white-space:pre-wrap">';
        echo htmlspecialchars($e->getMessage() . "\n\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        echo '</pre>';
        exit;
    }

    abort_page(500, 'Beklenmeyen Hata', 'İşleminiz sırasında bir sorun oluştu. Lütfen sayfayı yenileyip tekrar deneyin.');
});

set_error_handler(static function (int $severity, string $message, string $file = '', int $line = 0): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// 6) Guvenlik basliklari
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// 7) Oturum yaslandirma kontrolu
if (isset($_SESSION['last_activity']) && (time() - (int) $_SESSION['last_activity']) > SESSION_LIFETIME) {
    \App\Core\Auth::logout();
    session_start();
}
$_SESSION['last_activity'] = time();
