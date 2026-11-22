<?php
/**
 * Akilli Kariyer - Kimlik dogrulama ve yetki yonetimi
 */

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?User $currentUser = null;
    private static bool $resolved = false;

    /* ------------------------------------------------------------------ */
    /* Oturum acma / kapama                                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Kullaniciyi giris yaptirir. Oturum sabitleme (fixation) korumasi icin
     * session id yenilenir.
     */
    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        $user = User::findByEmail($email);

        if (!$user) {
            // Kullanici bulunamasa bile sahte bir hash dogrulayarak zamanlama farkini azaltiriz.
            password_verify($password, '$2y$12$usesomesillystringforsalt0000000000000000000000000000000000');
            self::logAttempt($email, false);
            return false;
        }

        if (!$user->is_active) {
            self::logAttempt($email, false);
            return false;
        }

        if (!password_verify($password, $user->password)) {
            self::logAttempt($email, false);
            return false;
        }

        // Sifre algoritmasi degistiyse hash'i yenile
        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
            User::update($user->id, ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        }

        self::logAttempt($email, true);
        self::login($user, $remember);
        return true;
    }

    /**
     * Oturum olusturur.
     */
    public static function login(User $user, bool $remember = false): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']    = (int) $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['logged_in']  = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();

        self::$currentUser = $user;
        self::$resolved   = true;

        if ($remember) {
            self::createRememberCookie($user);
        }

        User::update($user->id, [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip'  => self::ip(),
        ]);
    }

    /**
     * Oturumu sonlandirir.
     */
    public static function logout(): void
    {
        self::deleteRememberCookie();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();

        self::$currentUser = null;
        self::$resolved   = true;
    }

    /* ------------------------------------------------------------------ */
    /* Durum sorgulari                                                      */
    /* ------------------------------------------------------------------ */

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u->id : null;
    }

    public static function user(): ?User
    {
        if (self::$resolved) {
            return self::$currentUser;
        }
        self::$resolved = true;

        $id = $_SESSION['user_id'] ?? null;

        if (!$id && self::hasRememberCookie()) {
            $user = self::loginFromRememberCookie();
            if ($user) {
                return $user;
            }
        }

        if (!$id) {
            return null;
        }

        $user = User::find((int) $id);

        if (!$user || !$user->is_active) {
            self::logout();
            return null;
        }

        self::$currentUser = $user;
        return $user;
    }

    public static function isAdmin(): bool
    {
        $u = self::user();
        return $u ? $u->hasRole('admin') : false;
    }

    public static function isEditor(): bool
    {
        $u = self::user();
        return $u ? ($u->hasRole('admin') || $u->hasRole('editor')) : false;
    }

    /**
     * Yonetim paneline erisim kontrolu.
     */
    public static function requireAdmin(): void
    {
        if (!self::check()) {
            self::redirectLogin();
        }
        if (!self::isAdmin()) {
            abort_page(403, 'Yetkisiz Erişim', 'Bu sayfaya erişim yetkiniz bulunmuyor.');
        }
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            self::redirectLogin();
        }
    }

    /**
     * Oturum zaman asimi ve yonlendirme.
     */
    public static function redirectLogin(): void
    {
        $target = $_SERVER['REQUEST_URI'] ?? '/';
        $_SESSION['redirect_after_login'] = $target;
        header('Location: ' . BASE_PATH . '/login.php');
        exit;
    }

    /* ------------------------------------------------------------------ */
    /* Sifreleme                                                            */
    /* ------------------------------------------------------------------ */

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /* ------------------------------------------------------------------ */
    /* Hatirlama / sifre sifirlama                                          */
    /* ------------------------------------------------------------------ */

    private static function createRememberCookie(User $user): void
    {
        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $expires = time() + REMEMBER_LIFETIME;

        $token = $selector . ':' . $validator;
        $hashed = hash('sha256', $validator);

        Database::query(
            'INSERT INTO `password_resets` (`user_id`,`email`,`token`,`expires_at`,`ip_address`) VALUES (?,?,?,?,?)',
            [$user->id, $user->email, $hashed, date('Y-m-d H:i:s', $expires), self::ip()]
        );
        // Eski kayitlari temizle
        Database::query('DELETE FROM `password_resets` WHERE `user_id` = ? AND `expires_at` < NOW()', [$user->id]);

        setcookie('akkk_remember', $token, [
            'expires'  => $expires,
            'path'     => '/',
            'httponly' => true,
            'secure'   => self::isHttps(),
            'samesite' => 'Lax',
        ]);
    }

    private static function hasRememberCookie(): bool
    {
        return !empty($_COOKIE['akkk_remember']);
    }

    private static function loginFromRememberCookie(): ?User
    {
        $raw = $_COOKIE['akkk_remember'] ?? '';
        if (!str_contains($raw, ':')) {
            return null;
        }
        [$selector, $validator] = explode(':', $raw, 2);

        $row = Database::fetch(
            'SELECT * FROM `password_resets` WHERE `token` = ? AND `expires_at` > NOW() AND `used_at` IS NULL LIMIT 1',
            [hash('sha256', $validator)]
        );

        if (!$row) {
            self::deleteRememberCookie();
            return null;
        }

        $user = User::find((int) $row['user_id']);
        if (!$user || !$user->is_active) {
            self::deleteRememberCookie();
            return null;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = (int) $user->id;
        $_SESSION['logged_in'] = true;
        self::$currentUser = $user;
        self::$resolved   = true;

        return $user;
    }

    private static function deleteRememberCookie(): void
    {
        if (!empty($_COOKIE['akkk_remember'])) {
            setcookie('akkk_remember', '', time() - 3600, '/', '', false, true);
            unset($_COOKIE['akkk_remember']);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Brute force korumasi                                                */
    /* ------------------------------------------------------------------ */

    /**
     * Kullanici/IP basina kalan basarisiz deneme sayisini dondurur.
     */
    public static function failedAttempts(string $email): int
    {
        $maxAttempts = (int) self::setting('max_login_attempts', 5);
        $minutes = (int) self::setting('login_lockout_minutes', 15);

        $count = (int) Database::fetchColumn(
            'SELECT COUNT(*) FROM `login_attempts`
             WHERE `email` = ? AND `ip_address` = ? AND `was_successful` = 0
               AND `created_at` > DATE_SUB(NOW(), INTERVAL ? MINUTE)',
            [$email, self::ip(), $minutes]
        );

        return $count >= $maxAttempts ? $count : 0;
    }

    public static function isLockedOut(string $email): bool
    {
        return self::failedAttempts($email) > 0;
    }

    private static function logAttempt(string $email, bool $success): void
    {
        Database::query(
            'INSERT INTO `login_attempts` (`email`,`ip_address`,`user_agent`,`was_successful`) VALUES (?,?,?,?)',
            [mb_substr($email, 0, 190), self::ip(), mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), $success ? 1 : 0]
        );
    }

    public static function ip(): string
    {
        return mb_substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }

    private static function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::get($key, $default);
    }
}
