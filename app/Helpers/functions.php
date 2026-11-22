<?php
/**
 * Akilli Kariyer - Global yardimci fonksiyonlar
 */

use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Setting;

if (!function_exists('e')) {
    /** XSS korumali cikti */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /** Uygulama ici URL uretir */
    function url(string $path = ''): string
    {
        $base = BASE_PATH === '/' ? '' : BASE_PATH;
        $path = ltrim($path, '/');
        return $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    /** Statik dosya URL'si (onbellek kirilma surumu ile) */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $file = APP_ROOT . '/' . $path;
        $version = is_file($file) ? (string) filemtime($file) : APP_VERSION;
        return url($path) . '?v=' . $version;
    }
}

if (!function_exists('redirect')) {
    /** Yonlendirme */
    function redirect(string $url, int $code = 302): void
    {
        if (!preg_match('#^https?://#i', $url)) {
            $url = url($url);
        }
        header('Location: ' . $url, true, $code);
        exit;
    }
}

if (!function_exists('back')) {
    /** Onceki sayfaya donus */
    function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        redirect($ref !== '' ? $ref : url('index.php'));
    }
}

if (!function_exists('csrf_field')) {
    /** Form icin CSRF gizli alani */
    function csrf_field(): string
    {
        return CSRF::field();
    }
}

if (!function_exists('csrf_token')) {
    /** CSRF token degeri */
    function csrf_token(): string
    {
        return CSRF::token();
    }
}

if (!function_exists('setting')) {
    /** Site ayari okuma */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('auth_user')) {
    /** Oturumdaki kullanici */
    function auth_user(): ?\App\Models\User
    {
        return Auth::user();
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return Auth::check();
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return Auth::isAdmin();
    }
}

if (!function_exists('flash')) {
    /** Flash mesaji oku (ve temizle) */
    function flash(?string $key = null): ?string
    {
        if ($key === null) {
            return null;
        }
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }
}

if (!function_exists('set_flash')) {
    function set_flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }
}

if (!function_exists('old')) {
    /** Form eski girdi degeri */
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('keep_old_input')) {
    function keep_old_input(array $data): void
    {
        unset($data['_token'], $data['password'], $data['password_confirmation'], $data['current_password'], $data['new_password']);
        $_SESSION['_old'] = $data;
    }
}

if (!function_exists('clear_old_input')) {
    function clear_old_input(): void
    {
        unset($_SESSION['_old']);
    }
}

if (!function_exists('field_error')) {
    /** Alan bazli hata mesaji */
    function field_error(string $field): ?string
    {
        $errors = $_SESSION['_errors'] ?? [];
        return $errors[$field] ?? null;
    }
}

if (!function_exists('set_errors')) {
    function set_errors(array $errors): void
    {
        $_SESSION['_errors'] = $errors;
    }
}

if (!function_exists('clear_errors')) {
    function clear_errors(): void
    {
        unset($_SESSION['_errors']);
    }
}

if (!function_exists('has_error')) {
    function has_error(string $field): bool
    {
        return isset($_SESSION['_errors'][$field]);
    }
}

if (!function_exists('str_limit')) {
    /** Metni kisaltir */
    function str_limit(?string $text, int $limit = 140, string $end = '…'): string
    {
        $text = trim(strip_tags((string) $text));
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        $cut = mb_substr($text, 0, $limit);
        $pos = mb_strrpos($cut, ' ');
        if ($pos !== false && $pos > $limit * 0.6) {
            $cut = mb_substr($cut, 0, $pos);
        }
        return rtrim($cut, ' .,;:') . $end;
    }
}

if (!function_exists('excerpt')) {
    /** HTML icerikten ozet uretir */
    function excerpt(?string $html, int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $html)));
        return str_limit($text, $limit);
    }
}

if (!function_exists('format_date')) {
    /** Tarih formatlama (TR) */
    function format_date(?string $date, string $format = 'd F Y'): string
    {
        if (!$date || str_starts_with($date, '0000')) {
            return '-';
        }
        $ts = strtotime($date);
        if ($ts === false) {
            return '-';
        }
        $months = [
            1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
            'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık',
        ];
        $monthsEn = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $out = date($format, $ts);
        $out = str_replace($monthsEn, $months, $out);
        return str_replace(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'], $out);
    }
}

if (!function_exists('time_ago')) {
    /** "3 saat once" bicimi */
    function time_ago(?string $date): string
    {
        if (!$date) {
            return '-';
        }
        $ts = strtotime($date);
        if ($ts === false) {
            return '-';
        }
        $diff = time() - $ts;
        if ($diff < 0) {
            $diff = 0;
        }
        if ($diff < 60) {
            return 'az önce';
        }
        $units = [
            31536000 => 'yıl',
            2592000  => 'ay',
            604800   => 'hafta',
            86400    => 'gün',
            3600     => 'saat',
            60       => 'dakika',
        ];
        foreach ($units as $seconds => $label) {
            if ($diff >= $seconds) {
                $count = (int) floor($diff / $seconds);
                return $count . ' ' . $label . ' önce';
            }
        }
        return 'az önce';
    }
}

if (!function_exists('number_format_tr')) {
    /** Binlik ayracli sayi */
    function number_format_tr(int|float $number): string
    {
        return number_format((float) $number, 0, ',', '.');
    }
}

if (!function_exists('education_label')) {
    /** Egitim seviyesi etiketi */
    function education_label(?string $level): string
    {
        $map = [
            'ortaokul'   => 'Ortaokul',
            'lise'       => 'Lise',
            'universite' => 'Üniversite',
            'diger'      => 'Diğer',
        ];
        return $map[$level ?? ''] ?? 'Belirtilmedi';
    }
}

if (!function_exists('education_short')) {
    /** Egitim seviyesi kisa etiketi */
    function education_short(?string $level): string
    {
        $map = [
            'ortaokul'   => 'İlkokul / Ortaokul',
            'lise'       => 'Lise',
            'universite' => 'Üniversite',
            'diger'      => 'Diğer',
        ];
        return $map[$level ?? ''] ?? 'Belirtilmedi';
    }
}

if (!function_exists('is_current_page')) {
    /** aktif menu kontrolu */
    function is_current_page(string $path): bool
    {
        $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
        $target  = basename(parse_url($path, PHP_URL_PATH) ?: $path);
        return $current === $target;
    }
}

if (!function_exists('abort_page')) {
    /** Ozel hata sayfasi gosterir */
    function abort_page(int $code, string $title, string $message = '', ?string $backUrl = null): void
    {
        http_response_code($code);
        $backUrl = $backUrl ?? url('index.php');
        $icon = match ($code) {
            403 => 'shield-alert',
            404 => 'search-x',
            500 => 'server-crash',
            default => 'alert-triangle',
        };
        $title = e($title);
        $message = e($message);
        $back = e($backUrl);
        echo <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$code} - {$title}</title>
<link rel="stylesheet" href="{$back}";
<style>
 body{margin:0;min-height:100vh;display:grid;place-items:center;background:#F6F8FC;color:#172B42;font-family:"Segoe UI",system-ui,-apple-system,sans-serif;padding:24px}
 .card{background:#fff;border:1px solid #E6ECF5;border-radius:20px;padding:48px;max-width:520px;text-align:center;box-shadow:0 20px 50px rgba(20,43,74,.08)}
 .code{font-size:64px;font-weight:800;color:#142B4A;line-height:1}
 h1{font-size:22px;margin:12px 0 8px}
 p{color:#748399;line-height:1.7;margin:0 0 24px}
 a.btn{display:inline-block;background:#142B4A;color:#fff;text-decoration:none;padding:12px 26px;border-radius:12px;font-weight:600}
</style>
</head>
<body><div class="card">
 <div class="code">{$code}</div>
 <h1>{$title}</h1>
 <p>{$message}</p>
 <a class="btn" href="{$back}">Ana sayfaya dön</a>
</div></body></html>
HTML;
        exit;
    }
}

if (!function_exists('icon')) {
    /** Lucide ikon (yerel kutuphane ile) */
    function icon(string $name, string $class = '', int $size = 20): string
    {
        return sprintf(
            '<i data-lucide="%s" class="%s" style="width:%dpx;height:%dpx;stroke-width:2" aria-hidden="true"></i>',
            e($name),
            e($class),
            $size,
            $size
        );
    }
}

if (!function_exists('svg_avatar')) {
    /** Kullanici avatari (bas harf) */
    function svg_avatar(string $initials, string $color = '#1C466D', int $size = 40): string
    {
        $initials = e(mb_strtoupper($initials));
        $color = e($color);
        $fontSize = (int) round($size * 0.38);
        $half = $size / 2;
        $gradId = 'av' . $size . '_' . substr(md5($initials . $color), 0, 5);
        return <<<SVG
<svg class="avatar-svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}" role="img" aria-label="Profil">
  <defs><linearGradient id="{$gradId}" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0%" stop-color="{$color}"/><stop offset="100%" stop-color="#16B8B0"/>
  </linearGradient></defs>
  <circle cx="{$half}" cy="{$half}" r="{$half}" fill="url(#{$gradId})"/>
  <text x="50%" y="50%" dy="0.35em" text-anchor="middle" fill="#fff" font-family="Segoe UI,system-ui,sans-serif" font-size="{$fontSize}" font-weight="700">{$initials}</text>
</svg>
SVG;
    }
}

if (!function_exists('career_art')) {
    /**
     * Meslek icin gorsel uretir.
     *
     * Veritabaninda gorsel varsa fotografi kullanir; yoksa prosedurel SVG
     * illustrasyonu uretir (yedek olarak daima calisir).
     */
    function career_art(array $career, string $variant = 'card'): string
    {
        $photo = career_photo_url($career['image'] ?? null);
        $alt   = e($career['name'] ?? 'Meslek');

        if ($photo) {
            $loading = $variant === 'hero' ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"';
            return sprintf(
                '<img class="career-photo" src="%s" alt="%s" %s width="900" height="600" decoding="async">',
                e($photo),
                $alt,
                $loading
            );
        }

        return career_art_svg($career, $variant);
    }
}

if (!function_exists('career_photo_url')) {
    /**
     * Meslek fotografinin URL'sini dondurur (dosya diskte mevcutsa).
     *
     * Desteklenen yollar:
     *   images/careers/...  -> assets/images/careers/...
     *   careers/...        -> assets/images/careers/...
     *   uploads/...        -> storage/uploads/...
     */
    function career_photo_url(?string $image): ?string
    {
        if (!$image) {
            return null;
        }
        if (str_starts_with($image, 'http')) {
            return $image;
        }

        $image = ltrim($image, '/');

        if (str_starts_with($image, 'uploads/')) {
            $relative = 'storage/' . $image;
        } elseif (str_starts_with($image, 'images/')) {
            $relative = 'assets/' . $image;
        } elseif (str_starts_with($image, 'careers/')) {
            $relative = 'assets/images/' . $image;
        } else {
            $relative = 'assets/images/' . $image;
        }

        if (!is_file(APP_ROOT . '/' . $relative)) {
            return null;
        }

        return asset($relative);
    }
}

if (!function_exists('career_art_svg')) {
    /**
     * Prosedurel SVG illustrasyonu (fotograf bulunamazsa yedek).
     */
    function career_art_svg(array $career, string $variant = 'card'): string
    {
        $color  = $career['category_color'] ?? '#1C466D';
        $accent = '#16B8B0';
        $name   = $career['icon'] ?? 'briefcase';
        $slug   = preg_replace('/[^a-z0-9]/', '', strtolower($name)) ?: 'icon';
        $id     = substr(md5($slug . $variant . ($career['id'] ?? 0)), 0, 8);

        return <<<SVG
<svg class="career-art" viewBox="0 0 400 260" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{$name} illustrasyonu">
  <defs>
    <linearGradient id="bg{$id}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$color}"/>
      <stop offset="100%" stop-color="#0C1E35"/>
    </linearGradient>
    <linearGradient id="ac{$id}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$accent}"/>
      <stop offset="100%" stop-color="#7FE3DC"/>
    </linearGradient>
    <radialGradient id="gl{$id}" cx="0.75" cy="0.15" r="0.7">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0.28"/>
      <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="400" height="260" fill="url(#bg{$id})"/>
  <rect width="400" height="260" fill="url(#gl{$id})"/>
  <circle cx="330" cy="42" r="86" fill="{$accent}" opacity="0.12"/>
  <circle cx="52" cy="228" r="64" fill="{$accent}" opacity="0.10"/>
  <path d="M0 210 Q 100 176 200 206 T 400 190 L400 260 L0 260 Z" fill="#ffffff" opacity="0.05"/>
  <g transform="translate(140,72)">
    <rect x="0" y="0" width="120" height="104" rx="18" fill="#ffffff" opacity="0.10"/>
    <rect x="0" y="0" width="120" height="104" rx="18" fill="none" stroke="{$accent}" stroke-opacity="0.55" stroke-width="1.5"/>
    <circle cx="60" cy="46" r="22" fill="url(#ac{$id})" opacity="0.92"/>
    <path d="M38 80 h44 M34 92 h52" stroke="#ffffff" stroke-opacity="0.55" stroke-width="4" stroke-linecap="round"/>
  </g>
</svg>
SVG;
    }
}

if (!function_exists('blog_art')) {
    /** Blog kapak illustrasyonu */
    function blog_art(array $post, string $variant = 'card'): string
    {
        $color  = $post['category_color'] ?? '#1C466D';
        $accent = '#16B8B0';
        $id     = substr(md5(($post['slug'] ?? 'blog') . $variant), 0, 8);

        return <<<SVG
<svg class="blog-art" viewBox="0 0 400 240" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Kapak görseli">
  <defs>
    <linearGradient id="bb{$id}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$color}"/>
      <stop offset="100%" stop-color="#101F33"/>
    </linearGradient>
  </defs>
  <rect width="400" height="240" fill="url(#bb{$id})"/>
  <circle cx="340" cy="40" r="72" fill="{$accent}" opacity="0.14"/>
  <circle cx="40" cy="210" r="58" fill="{$accent}" opacity="0.10"/>
  <g opacity="0.9">
    <rect x="56" y="72" width="180" height="12" rx="6" fill="#ffffff" opacity="0.75"/>
    <rect x="56" y="98" width="240" height="9" rx="4.5" fill="#ffffff" opacity="0.40"/>
    <rect x="56" y="118" width="200" height="9" rx="4.5" fill="#ffffff" opacity="0.28"/>
    <rect x="56" y="146" width="86" height="26" rx="13" fill="{$accent}" opacity="0.85"/>
  </g>
</svg>
SVG;
    }
}

if (!function_exists('pagination')) {
    /** Sayfalama linkleri uretir */
    function pagination(int $current, int $total, string $baseUrl, array $query = []): string
    {
        if ($total <= 1) {
            return '';
        }
        $pages = (int) ceil($total / CAREERS_PER_PAGE);
        $html  = '<nav class="pagination-nav" aria-label="Sayfalama"><ul class="pagination">';

        $mk = static function (int $p) use ($current, $baseUrl, $query): string {
            $q = array_merge($query, ['sayfa' => $p]);
            return $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . http_build_query($q);
        };

        $html .= '<li class="page-item' . ($current <= 1 ? ' disabled' : '') . '">'
               . '<a href="' . e($mk(max(1, $current - 1))) . '" aria-label="Önceki sayfa">' . icon('chevron-left', '', 16) . '</a></li>';

        $start = max(1, $current - 2);
        $end   = min($pages, $start + 4);
        $start = max(1, $end - 4);

        for ($i = $start; $i <= $end; $i++) {
            $html .= '<li class="page-item' . ($i === $current ? ' active' : '') . '">'
                   . '<a href="' . e($mk($i)) . '">' . $i . '</a></li>';
        }

        $html .= '<li class="page-item' . ($current >= $pages ? ' disabled' : '') . '">'
               . '<a href="' . e($mk(min($pages, $current + 1))) . '" aria-label="Sonraki sayfa">' . icon('chevron-right', '', 16) . '</a></li>';
        $html .= '</ul></nav>';

        return $html;
    }
}

if (!function_exists('slugify')) {
    /**
     * Turkce karakterleri normalize ederek URL uyumlu slug uretir.
     */
    function slugify(string $text, string $fallback = 'icerik'): string
    {
        $tr = [
            'ı' => 'i', 'İ' => 'i', 'ş' => 's', 'Ş' => 's', 'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u', 'ö' => 'o', 'Ö' => 'o', 'ç' => 'c', 'Ç' => 'c',
            'â' => 'a', 'Â' => 'a', 'î' => 'i', 'Î' => 'i', 'û' => 'u', 'Û' => 'u',
        ];
        $text = strtr($text, $tr);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
        $text = trim($text, '-');
        return $text !== '' ? $text : $fallback;
    }
}

if (!function_exists('json_response')) {
    /** JSON yanit doner */
    function json_response(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('require_post')) {
    /** Yalnizca POST isteklerini kabul eder */
    function require_post(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            json_response(['success' => false, 'message' => 'Geçersiz istek metodu.'], 405);
        }
    }
}
