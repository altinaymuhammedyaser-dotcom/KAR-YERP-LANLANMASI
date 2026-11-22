<?php
/**
 * Akilli Kariyer - Sayfa basligi (head + header)
 *
 * Sayfa tarafindan tanimlanabilecek degiskenler:
 *   $pageTitle, $pageDescription, $bodyClass, $activeNav, $extraHead, $extraScripts
 */

if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/../app/bootstrap.php';
}

$pageTitle       = $pageTitle       ?? (setting('site_name', 'Akıllı Kariyer') . ' — ' . setting('site_slogan', 'Geleceğini Keşfet'));
$pageDescription = $pageDescription ?? setting('site_description', 'İlgi alanlarınızı ve yeteneklerinizi analiz ederek size uygun meslekleri keşfedin.');
$bodyClass       = $bodyClass       ?? '';
$activeNav       = $activeNav       ?? '';
$extraHead       = $extraHead       ?? '';
$canonicalUrl    = $canonicalUrl    ?? ('http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/'));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e(str_limit($pageDescription, 300, '')) ?>">
<meta name="theme-color" content="#142B4A">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e(str_limit($pageDescription, 300, '')) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="tr_TR">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="<?= asset('assets/css/vendor-bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/career-coach.css') ?>">
<?= \App\Core\CSRF::meta() ?>
<link rel="icon" href="<?= asset('assets/images/favicon.svg') ?>" type="image/svg+xml">
<?= $extraHead ?>
</head>
<body class="<?= e($bodyClass) ?>">
<a href="#main-content" class="sr-only">İçeriğe geç</a>

<header class="site-header">
  <div class="container header-inner">
    <a href="<?= url('index.php') ?>" class="brand" aria-label="Akıllı Kariyer ana sayfa">
      <span class="brand-mark"><?= icon('compass', '', 22) ?></span>
      <span class="brand-text">
        <span class="brand-name">AKILLI KARİYER</span>
        <span class="brand-slogan"><?= e(setting('site_slogan', 'Geleceğini Keşfet')) ?></span>
      </span>
    </a>

    <nav class="main-nav" aria-label="Ana menü">
      <ul>
        <li><a href="<?= url('index.php') ?>" class="<?= $activeNav === 'home' ? 'active' : '' ?>">Ana Sayfa</a></li>
        <li><a href="<?= url('career-test.php') ?>" class="<?= $activeNav === 'test' ? 'active' : '' ?>">Kariyer Testi</a></li>
        <li><a href="<?= url('careers.php') ?>" class="<?= $activeNav === 'careers' ? 'active' : '' ?>">Meslekleri Keşfet</a></li>
        <li><a href="<?= url('interests.php') ?>" class="<?= $activeNav === 'interests' ? 'active' : '' ?>">İlgi Alanları</a></li>
        <li><a href="<?= url('career-guide.php') ?>" class="<?= $activeNav === 'guide' ? 'active' : '' ?>">Kariyer Rehberi</a></li>
        <li><a href="<?= url('about.php') ?>" class="<?= $activeNav === 'about' ? 'active' : '' ?>">Hakkımızda</a></li>
      </ul>
    </nav>

    <div class="header-actions">
      <button type="button" class="dark-mode-toggle" id="dark-mode-toggle" title="Karanlık/Aydınlık modu" aria-label="Tema değiştir">
        <svg data-lucide="sun" style="width:20px;height:20px"></svg>
        <svg data-lucide="moon" style="width:20px;height:20px;display:none"></svg>
      </button>
      <?php if (is_logged_in()): $u = auth_user(); ?>
        <a href="<?= url('dashboard/index.php') ?>" class="btn btn-ghost btn-sm d-none d-md-inline-flex">
          <?= icon('layout-dashboard', '', 17) ?> Panelim
        </a>
        <div class="user-menu">
          <button type="button" class="user-menu-btn" aria-haspopup="true" aria-expanded="false">
            <?= svg_avatar($u->initials(), '#1C466D', 34) ?>
            <span class="um-text">
              <span class="um-name"><?= e($u->first_name) ?></span>
              <span class="um-sub"><?= e($u->educationLabel()) ?></span>
            </span>
            <?= icon('chevron-down', '', 15) ?>
          </button>
          <div class="user-dropdown">
            <div class="ud-head">
              <strong><?= e($u->fullName()) ?></strong>
              <span><?= e($u->email) ?></span>
            </div>
            <a href="<?= url('dashboard/index.php') ?>"><?= icon('layout-dashboard', '', 17) ?> Genel Bakış</a>
            <a href="<?= url('dashboard/results.php') ?>"><?= icon('file-bar-chart', '', 17) ?> Test Sonuçlarım</a>
            <a href="<?= url('dashboard/recommendations.php') ?>"><?= icon('sparkles', '', 17) ?> Meslek Önerilerim</a>
            <a href="<?= url('dashboard/favorites.php') ?>"><?= icon('heart', '', 17) ?> Favorilerim</a>
            <a href="<?= url('dashboard/profile.php') ?>"><?= icon('user-cog', '', 17) ?> Profilim</a>
            <?php if (is_admin()): ?>
              <div class="ud-sep"></div>
              <a href="<?= url('admin/index.php') ?>"><?= icon('shield-check', '', 17) ?> Yönetim Paneli</a>
            <?php endif; ?>
            <div class="ud-sep"></div>
            <a href="<?= url('logout.php') ?>" class="ud-danger"><?= icon('log-out', '', 17) ?> Çıkış Yap</a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= url('login.php') ?>" class="btn btn-ghost btn-sm">Giriş Yap</a>
        <a href="<?= url('register.php') ?>" class="btn btn-accent btn-sm">Ücretsiz Kayıt Ol</a>
      <?php endif; ?>

      <button type="button" class="nav-toggle" aria-label="Menüyü aç" aria-expanded="false" aria-controls="mobile-nav">
        <?= icon('menu', '', 22) ?>
      </button>
    </div>
  </div>
</header>

<?php require_once __DIR__ . '/navbar.php'; ?>
