<?php
/**
 * Akilli Kariyer - Panel (kullanici / yonetici) ust cerceve
 *
 * $panelType : 'user' | 'admin'
 * $panelTitle, $panelSubtitle, $panelActions
 */

if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/../app/bootstrap.php';
}

use App\Core\Auth;
use App\Models\Result;

$panelType     = $panelType     ?? 'user';
$panelTitle    = $panelTitle    ?? 'Panel';
$panelSubtitle = $panelSubtitle ?? '';
$panelActions  = $panelActions  ?? '';
$panelActive   = $panelActive   ?? '';
$extraHead     = $extraHead     ?? '';

$panelUser = Auth::user();
$isAdmin   = Auth::isAdmin();

if ($panelType === 'admin') {
    Auth::requireAdmin();
    $pendingMessages = (int) \App\Core\Database::fetchColumn("SELECT COUNT(*) FROM `contact_messages` WHERE `status` = 'new'");
} else {
    Auth::requireLogin();
    $pendingMessages = 0;
}

$userNav = [
    ['index',            'index.php',            'layout-dashboard', 'Genel Bakış'],
    ['tests',            'tests.php',            'clipboard-check',  'Kariyer Testim'],
    ['results',          'results.php',          'file-bar-chart',   'Test Sonuçlarım'],
    ['recommendations',  'recommendations.php',  'sparkles',         'Meslek Önerilerim'],
    ['explore',          'explore.php',          'search',           'Meslekleri Keşfet'],
    ['favorites',        'favorites.php',        'heart',            'Favorilerim'],
    ['activities',       'activities.php',       'activity',         'Aktivitelerim'],
    ['personality-test', 'personality-test.php', 'brain',            'Kişilik Testi'],
    ['interview',        'interview-sim.php',    'mic',              'Mülakat Simülatörü'],
    ['profile',          'profile.php',          'user-cog',         'Profilim'],
    ['settings',         'settings.php',         'settings',         'Ayarlar'],
];

$adminNav = [
    ['index',       'index.php',       'layout-dashboard', 'Genel Bakış'],
    ['users',       'users.php',       'users',           'Kullanıcılar'],
    ['careers',     'careers.php',     'briefcase',       'Meslekler'],
    ['categories',  'categories.php',  'layers',          'Kategoriler'],
    ['questions',   'questions.php',   'clipboard-list',  'Test Soruları'],
    ['blog',        'blog.php',        'newspaper',       'Blog Yazıları'],
    ['messages',    'messages.php',    'mail',            'İletişim Mesajları'],
    ['settings',    'settings.php',    'settings',        'Sistem Ayarları'],
];
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($panelTitle) ?> — <?= $panelType === 'admin' ? 'Yönetim Paneli' : 'Hesap Paneli' ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/css/vendor-bootstrap.min.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/dashboard.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/career-coach.css') ?>">
<?= \App\Core\CSRF::meta() ?>
<link rel="icon" href="<?= asset('assets/images/favicon.svg') ?>" type="image/svg+xml">
<?= $extraHead ?>
</head>
<body>
<div class="panel-layout" id="panel-layout">

  <div class="nav-overlay" id="nav-overlay"></div>

  <aside class="panel-side" id="panel-side">
    <div class="panel-brand">
      <span class="brand-mark"><?= icon($panelType === 'admin' ? 'shield-check' : 'compass', '', 21) ?></span>
      <span>
        <span class="pb-name">AKILLI KARİYER</span>
        <span class="pb-sub"><?= $panelType === 'admin' ? 'Yönetim Paneli' : 'Hesap Paneli' ?></span>
      </span>
    </div>

    <nav class="panel-nav">
      <?php if ($panelType === 'admin'): ?>
        <div class="pn-label">Yönetim</div>
        <?php foreach ($adminNav as $item): ?>
          <a href="<?= url('admin/' . $item[1]) ?>" class="<?= $panelActive === $item[0] ? 'active' : '' ?>">
            <?= icon($item[2], '', 18) ?> <span><?= e($item[3]) ?></span>
            <?php if ($item[0] === 'messages' && $pendingMessages > 0): ?>
              <span class="pn-badge alert"><?= $pendingMessages ?></span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
        <div class="pn-label">Hızlı Erişim</div>
        <a href="<?= url('admin/career-edit.php') ?>"><?= icon('plus-circle', '', 18) ?> <span>Yeni Meslek Ekle</span></a>
        <a href="<?= url('admin/question-edit.php') ?>"><?= icon('plus-circle', '', 18) ?> <span>Yeni Soru Ekle</span></a>
        <a href="<?= url('admin/blog-edit.php') ?>"><?= icon('plus-circle', '', 18) ?> <span>Yeni Yazı Ekle</span></a>
        <div class="pn-label">Site</div>
        <a href="<?= url('index.php') ?>"><?= icon('external-link', '', 18) ?> <span>Siteyi Görüntüle</span></a>
      <?php else: ?>
        <div class="pn-label">Kariyer</div>
        <?php foreach ($userNav as $item): ?>
          <a href="<?= url('dashboard/' . $item[1]) ?>" class="<?= $panelActive === $item[0] ? 'active' : '' ?>">
            <?= icon($item[2], '', 18) ?> <span><?= e($item[3]) ?></span>
          </a>
        <?php endforeach; ?>
        <div class="pn-label">Hızlı Erişim</div>
        <a href="<?= url('career-test.php') ?>"><?= icon('clipboard-check', '', 18) ?> <span>Teste Başla</span></a>
        <a href="<?= url('index.php') ?>"><?= icon('external-link', '', 18) ?> <span>Siteyi Görüntüle</span></a>
        <?php if ($isAdmin): ?>
          <div class="pn-label">Yönetim</div>
          <a href="<?= url('admin/index.php') ?>"><?= icon('shield-check', '', 18) ?> <span>Yönetim Paneli</span></a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>

    <div class="panel-side-foot">
      <div class="psf-card">
        <strong><?= e($panelUser->fullName()) ?></strong>
        <p><?= e($panelUser->email) ?></p>
        <a href="<?= url('logout.php') ?>" class="btn btn-light btn-sm btn-block" style="font-size:.8rem">
          <?= icon('log-out', '', 15) ?> Çıkış Yap
        </a>
      </div>
    </div>
  </aside>

  <div class="panel-main">
    <header class="panel-topbar">
      <button type="button" class="panel-burger" id="panel-burger" aria-label="Menüyü aç">
        <?= icon('menu', '', 21) ?>
      </button>
      <div class="flex-1">
        <h1><?= e($panelTitle) ?></h1>
        <?php if ($panelSubtitle !== ''): ?>
          <p class="ptb-sub"><?= e($panelSubtitle) ?></p>
        <?php endif; ?>
      </div>
      <div class="ptb-actions">
        <?= $panelActions ?>
      </div>
    </header>

    <main class="panel-content" id="main-content">
