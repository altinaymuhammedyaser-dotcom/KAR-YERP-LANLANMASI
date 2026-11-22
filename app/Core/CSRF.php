<?php
/**
 * Akilli Kariyer - CSRF korumasi
 *
 * Form bazli ve AJAX istekleri icin tek kullanimlik (ROTATE edilebilir) token uretir.
 */

namespace App\Core;

final class CSRF
{
    private const SESSION_KEY = '_csrf_token';
    private const ISSUED_KEY  = '_csrf_issued';

    /**
     * Oturum icin bir token uretir (yoksa) ve dondurur.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            self::regenerate();
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Yeni token uretir (form gonderimi sonrasi ve kritik islemlerde).
     */
    public static function regenerate(): string
    {
        $_SESSION[self::SESSION_KEY]   = bin2hex(random_bytes(32));
        $_SESSION[self::ISSUED_KEY]    = time();
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Gelen tokeni dogrular.
     */
    public static function validate(?string $token): bool
    {
        $sessionToken = $_SESSION[self::SESSION_KEY] ?? '';
        if ($sessionToken === '' || $token === null || $token === '') {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Form POST veya JSON govdesinden tokeni alip dogrular.
     * Basarisizsa istegi durdurur.
     */
    public static function check(): void
    {
        $token = $_POST['_token'] ?? null;

        if ($token === null && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        if ($token === null && isset($_SERVER['HTTP_X_XSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_XSRF_TOKEN'];
        }
        if ($token === null) {
            $raw = file_get_contents('php://input');
            if ($raw !== false && $raw !== '') {
                $json = json_decode($raw, true);
                if (is_array($json) && isset($json['_token'])) {
                    $token = $json['_token'];
                }
            }
        }

        if (!self::validate(is_string($token) ? $token : null)) {
            self::deny();
        }
    }

    /**
     * Gecersiz token durumunda istegi sonlandirir.
     */
    public static function deny(): void
    {
        if (self::wantsJson()) {
            self::json(['success' => false, 'message' => 'Oturum doğrulaması başarısız oldu. Lütfen sayfayı yenileyin.'], 419);
        }
        http_response_code(419);
        self::flash('error', 'Oturum doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.');
        self::redirect($_SERVER['HTTP_REFERER'] ?? BASE_PATH . '/');
    }

    /**
     * HTML form icin gizli token alani.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Meta etiketi ile token'i sayfaya aktarir (fetch API kullanimi icin).
     */
    public static function meta(): string
    {
        return '<meta name="csrf-token" content="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    private static function wantsJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    private static function json(array $data, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    private static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
