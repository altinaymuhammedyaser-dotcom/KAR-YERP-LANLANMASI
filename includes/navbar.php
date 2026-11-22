<?php
/**
 * Akilli Kariyer - Mobil navigasyon paneli
 */

$activeNav = $activeNav ?? '';
$menu = [
    ['home',     'index.php',        'home',          'Ana Sayfa'],
    ['test',     'career-test.php',  'clipboard-check', 'Kariyer Testi'],
    ['careers',  'careers.php',      'briefcase',     'Meslekleri Keşfet'],
    ['interests','interests.php',    'compass',       'İlgi Alanları'],
    ['guide',    'career-guide.php', 'book-open',     'Kariyer Rehberi'],
    ['about',    'about.php',        'info',          'Hakkımızda'],
    ['contact',  'contact.php',      'mail',          'İletişim'],
];
?>
<div class="mobile-nav" id="mobile-nav" role="dialog" aria-modal="true" aria-label="Mobil menü">
  <div class="mobile-nav-panel">
    <div class="mobile-nav-head">
      <a href="<?= url('index.php') ?>" class="brand">
        <span class="brand-mark"><?= icon('compass', '', 20) ?></span>
        <span class="brand-text">
          <span class="brand-name">AKILLI KARİYER</span>
          <span class="brand-slogan"><?= e(setting('site_slogan', 'Geleceğini Keşfet')) ?></span>
        </span>
      </a>
      <button type="button" class="nav-toggle mn-close" aria-label="Menüyü kapat"><?= icon('x', '', 22) ?></button>
    </div>

    <nav class="mobile-nav-links" aria-label="Mobil menü">
      <?php foreach ($menu as $item): ?>
        <a href="<?= url($item[1]) ?>" class="<?= $activeNav === $item[0] ? 'active' : '' ?>">
          <?= icon($item[2], '', 18) ?> <?= e($item[3]) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="mobile-nav-foot">
      <?php if (is_logged_in()): $u = auth_user(); ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--c-light-blue);border-radius:14px;margin-bottom:6px">
          <?= svg_avatar($u->initials(), '#1C466D', 40) ?>
          <div style="min-width:0">
            <strong style="display:block;color:var(--c-primary);font-size:.92rem"><?= e($u->fullName()) ?></strong>
            <span style="font-size:.78rem;color:var(--c-muted)"><?= e($u->educationLabel()) ?></span>
          </div>
        </div>
        <a class="btn btn-primary btn-block" href="<?= url('dashboard/index.php') ?>">
          <?= icon('layout-dashboard', '', 18) ?> Panelim
        </a>
        <?php if (is_admin()): ?>
          <a class="btn btn-outline btn-block" href="<?= url('admin/index.php') ?>">
            <?= icon('shield-check', '', 18) ?> Yönetim Paneli
          </a>
        <?php endif; ?>
        <a class="btn btn-ghost btn-block" href="<?= url('logout.php') ?>">
          <?= icon('log-out', '', 18) ?> Çıkış Yap
        </a>
      <?php else: ?>
        <a class="btn btn-outline btn-block" href="<?= url('login.php') ?>"><?= icon('log-in', '', 18) ?> Giriş Yap</a>
        <a class="btn btn-accent btn-block" href="<?= url('register.php') ?>"><?= icon('user-plus', '', 18) ?> Ücretsiz Kayıt Ol</a>
      <?php endif; ?>
    </div>
  </div>
</div>
