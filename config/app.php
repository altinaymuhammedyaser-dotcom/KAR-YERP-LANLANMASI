<?php
/**
 * Akilli Kariyer - Uygulama ayarlari
 */

define('APP_NAME', 'Akıllı Kariyer');
define('APP_SLUG', 'akilli-kariyer');
define('APP_VERSION', '1.0.0');
define('APP_ROOT', dirname(__DIR__));

define('APP_DEBUG', true);
define('APP_TIMEZONE', 'Europe/Istanbul');
define('APP_LOCALE', 'tr_TR');

/**
 * Uygulama URL kok dizini.
 *
 * Otomatik hesaplanir: uygulama web kokunun altinda bir alt klasorde ise
 * (orn. htdocs/akilli-kariyer) o klasor adi kullanilir.
 *
 * Manuel olarak degistirmek isterseniz asagidaki satiri duzenleyin:
 *   define('BASE_PATH_OVERRIDE', '/akilli-kariyer');
 */
if (!defined('BASE_PATH_OVERRIDE')) {
    define('BASE_PATH_OVERRIDE', '');
}

if (!defined('BASE_PATH')) {
    $basePath = rtrim((string) BASE_PATH_OVERRIDE, '/');

    if ($basePath === '') {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
        $scriptFile = isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : false;
        $appReal    = str_replace('\\', '/', (string) realpath(APP_ROOT));

        if ($scriptFile && $appReal !== '' && str_starts_with(str_replace('\\', '/', $scriptFile), $appReal)) {
            $relative = str_replace('\\', '/', substr(str_replace('\\', '/', $scriptFile), strlen($appReal)));
            if ($relative !== '' && str_ends_with($scriptName, $relative)) {
                $basePath = rtrim(substr($scriptName, 0, strlen($scriptName) - strlen($relative)), '/');
            }
        }

        if ($basePath === '' && isset($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT']));
            $appDir  = str_replace('\\', '/', (string) realpath(APP_ROOT));
            if ($docRoot !== '' && $appDir !== '' && str_starts_with($appDir, $docRoot)) {
                $basePath = rtrim(substr($appDir, strlen($docRoot)), '/');
            }
        }
    }

    define('BASE_PATH', $basePath);
}

/** Sayfalama ayarlari */
define('CAREERS_PER_PAGE', 9);
define('BLOG_PER_PAGE', 6);
define('ADMIN_PER_PAGE', 15);

/** Dosya yukleme ayarlari */
define('UPLOAD_DIR', APP_ROOT . '/storage/uploads');
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024); // 2 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

/** Oturum ayarlari */
define('SESSION_NAME', 'AKKARDIYER_SESS');
define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 1 hafta
define('REMEMBER_LIFETIME', 60 * 60 * 24 * 30); // 30 gun

date_default_timezone_set(APP_TIMEZONE);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}
