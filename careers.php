<?php
/**
 * Akilli Kariyer - Meslek katalogu
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Career;

$search    = trim((string) ($_GET['q'] ?? ''));
$category  = trim((string) ($_GET['category'] ?? ''));
$education = trim((string) ($_GET['education'] ?? ''));
$interest  = trim((string) ($_GET['interest'] ?? ''));
$sort      = trim((string) ($_GET['sort'] ?? 'popular'));
$page      = max(1, (int) ($_GET['sayfa'] ?? 1));

$filters = array_filter([
    'search'    => $search,
    'category'  => $category,
    'education' => $education,
    'interest'  => $interest,
], static fn($v) => $v !== '');

$total     = Career::count($filters);
$perPage   = CAREERS_PER_PAGE;
$offset    = ($page - 1) * $perPage;
$careers   = Career::list($filters + ['sort' => $sort, 'limit' => $perPage, 'offset' => $offset]);
$categories = Career::categories();
$interests = Career::availableInterests();

$pageTitle       = 'Meslekleri Keşfet — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Teknoloji, sağlık, eğitim, mühendislik, sanat, hukuk, işletme, bilim, iletişim ve spor alanlarında ' . Career::totalCount() . ' mesleği detaylı bilgileriyle inceleyin.';
$activeNav       = 'careers';

$extraScripts = '<script src="' . asset('assets/js/careers.js') . '"></script>';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container">
      <div class="flex between wrap gap-16" style="align-items:flex-end">
        <div>
          <span class="eyebrow"><?= icon('briefcase', '', 15) ?> Meslek Kataloğu</span>
          <h1 style="margin-bottom:6px">Meslekleri Keşfet</h1>
          <p style="margin:0">
            <?= number_format_tr(Career::totalCount()) ?> meslek, <?= count($categories) ?> kategori içinde.
            Arayın, filtreleyin ve detayları inceleyin.
          </p>
        </div>
        <a href="<?= url('career-test.php') ?>" class="btn btn-accent">
          <?= icon('sparkles', '', 18) ?> Size Uygun Meslekleri Bul
        </a>
      </div>
    </div>
  </section>

  <section class="section-sm">
    <div class="container">
      <div class="filter-bar" style="margin-bottom:26px">
        <div class="filter-row" id="career-filters">
          <div class="field">
            <label for="f-search">Meslek Ara</label>
            <span class="search-box">
              <?= icon('search', '', 18) ?>
              <input type="search" id="f-search" class="input" name="q" value="<?= e($search) ?>"
                     placeholder="Örn: yazılım, hemşire, tasarım…" autocomplete="off">
            </span>
          </div>

          <div class="field">
            <label for="f-category">Kategori</label>
            <select id="f-category" class="select" name="category">
              <option value="">Tümü</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?> (<?= (int) $cat['career_count'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="f-education">Eğitim Seviyesi</label>
            <select id="f-education" class="select" name="education">
              <option value="">Tümü</option>
              <?php foreach (Career::educationLevels() as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $education === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="f-interest">İlgi Alanı</label>
            <select id="f-interest" class="select" name="interest">
              <option value="">Tümü</option>
              <?php foreach ($interests as $i): ?>
                <option value="<?= e($i['interest_key']) ?>" <?= $interest === $i['interest_key'] ? 'selected' : '' ?>>
                  <?= e(\App\Services\CareerMatchingService::INTEREST_LABELS[$i['interest_key']] ?? $i['interest_key']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field filter-actions">
            <label for="f-sort">Sıralama</label>
            <select id="f-sort" class="select" name="sort">
              <option value="popular"   <?= $sort === 'popular' ? 'selected' : '' ?>>En çok incelenen</option>
              <option value="name_asc"  <?= $sort === 'name_asc' ? 'selected' : '' ?>>İsme göre (A-Z)</option>
              <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>İsme göre (Z-A)</option>
              <option value="category"  <?= $sort === 'category' ? 'selected' : '' ?>>Kategoriye göre</option>
              <option value="newest"    <?= $sort === 'newest' ? 'selected' : '' ?>>En yeni</option>
            </select>
          </div>
        </div>

        <div class="flex between wrap gap-12" style="margin-top:16px">
          <div id="filter-chips" class="flex wrap gap-8"></div>
          <div style="font-size:.86rem;color:var(--c-muted)" id="result-count">
            <strong><?= number_format_tr($total) ?></strong> meslek bulundu
          </div>
        </div>
      </div>

      <div id="career-results" data-base="<?= e(BASE_PATH) ?>">
        <?php if (!$careers): ?>
          <div class="empty-state">
            <div class="es-ico"><?= icon('search-x', '', 30) ?></div>
            <h3>Sonuç bulunamadı</h3>
            <p>Arama kriterlerinize uygun meslek bulunamadı. Filtreleri değiştirerek tekrar deneyin.</p>
            <a href="<?= url('careers.php') ?>" class="btn btn-primary"><?= icon('refresh-cw', '', 18) ?> Filtreleri Temizle</a>
          </div>
        <?php else: ?>
          <div class="grid g-3">
            <?php foreach ($careers as $job): ?>
              <?php require __DIR__ . '/includes/career-card.php'; ?>
            <?php endforeach; ?>
          </div>

          <?php
          echo pagination($page, $total, url('careers.php'), array_filter([
              'q'         => $search,
              'category'  => $category,
              'education' => $education,
              'interest'  => $interest,
              'sort'      => $sort,
          ]));
          ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="section section-alt">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><?= icon('layers', '', 15) ?> Kategoriler</span>
        <h2>Meslek Kategorilerine Göre Keşfedin</h2>
      </div>
      <div class="cat-grid">
        <?php foreach ($categories as $cat): ?>
          <a href="<?= url('careers.php?category=' . urlencode($cat['slug'])) ?>" class="cat-card">
            <span class="cc-ico" style="background:<?= e($cat['color']) ?>"><?= icon($cat['icon'] ?? 'briefcase', '', 24) ?></span>
            <h4><?= e($cat['name']) ?></h4>
            <span><?= (int) $cat['career_count'] ?> meslek</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
