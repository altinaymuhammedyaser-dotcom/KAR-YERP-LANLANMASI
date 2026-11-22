<?php
/**
 * Akilli Kariyer - Panel: Meslekleri kesfet (panel ici)
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Models\Career;
use App\Models\Result;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$search    = trim((string) ($_GET['q'] ?? ''));
$category  = trim((string) ($_GET['category'] ?? ''));
$education = trim((string) ($_GET['education'] ?? ''));
$sort      = trim((string) ($_GET['sort'] ?? 'popular'));
$page      = max(1, (int) ($_GET['sayfa'] ?? 1));

$filters = array_filter([
    'search'    => $search,
    'category'  => $category,
    'education' => $education,
], static fn($v) => $v !== '');

$total      = Career::count($filters);
$perPage    = 12;
$careers    = Career::list($filters + ['sort' => $sort, 'limit' => $perPage, 'offset' => ($page - 1) * $perPage]);
$categories = Career::categories();
$favIds     = Result::favoriteIds($userId);

$panelType     = 'user';
$panelActive   = 'explore';
$panelTitle    = 'Meslekleri Keşfet';
$panelSubtitle = 'İlgi alanınıza uygun meslekleri inceleyin ve favorilerinize ekleyin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card" style="margin-bottom:20px">
  <div class="pc-body">
    <form method="get" class="filter-row">
      <div class="field">
        <label for="eq">Meslek Ara</label>
        <span class="search-box">
          <?= icon('search', '', 18) ?>
          <input type="search" id="eq" name="q" class="input" value="<?= e($search) ?>" placeholder="Örn: yazılım, tasarım…">
        </span>
      </div>
      <div class="field">
        <label for="ecat">Kategori</label>
        <select id="ecat" name="category" class="select">
          <option value="">Tümü</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
              <?= e($cat['name']) ?> (<?= (int) $cat['career_count'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="eed">Eğitim</label>
        <select id="eed" name="education" class="select">
          <option value="">Tümü</option>
          <?php foreach (Career::educationLevels() as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $education === $k ? 'selected' : '' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="esort">Sıralama</label>
        <select id="esort" name="sort" class="select">
          <option value="popular"   <?= $sort === 'popular' ? 'selected' : '' ?>>En çok incelenen</option>
          <option value="name_asc"  <?= $sort === 'name_asc' ? 'selected' : '' ?>>İsme göre (A-Z)</option>
          <option value="newest"    <?= $sort === 'newest' ? 'selected' : '' ?>>En yeni</option>
        </select>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary"><?= icon('filter', '', 16) ?> Filtrele</button>
      </div>
    </form>
  </div>
</section>

<section class="panel-card">
  <div class="pc-head">
    <div><h2>Sonuçlar</h2><p class="pc-sub"><strong><?= number_format_tr($total) ?></strong> meslek bulundu</p></div>
  </div>
  <div class="pc-body">
    <?php if (!$careers): ?>
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('search-x', '', 28) ?></div>
        <h3>Sonuç bulunamadı</h3>
        <p>Arama kriterlerinize uygun meslek yok. Filtreleri değiştirmeyi deneyin.</p>
        <a href="<?= url('dashboard/explore.php') ?>" class="btn btn-primary">Filtreleri Temizle</a>
      </div>
    <?php else: ?>
      <div class="grid g-3">
        <?php foreach ($careers as $job): ?>
          <article class="career-card card card-hover">
            <div class="cc-media">
              <?= career_art($job) ?>
              <span class="cc-cat"><?= e($job['category_name']) ?></span>
              <span class="cc-icon"><?= icon($job['icon'] ?? 'briefcase', '', 21) ?></span>
            </div>
            <div class="cc-body">
              <h3><a href="<?= url('career-detail.php?slug=' . urlencode($job['slug'])) ?>" style="color:inherit"><?= e($job['name']) ?></a></h3>
              <p class="cc-desc"><?= e(str_limit($job['short_description'], 105)) ?></p>
              <div class="cc-meta">
                <span><?= icon('graduation-cap', '', 15) ?> <?= e(education_label($job['education_level'])) ?></span>
                <span><?= icon('eye', '', 15) ?> <?= number_format_tr((int) $job['view_count']) ?></span>
              </div>
              <div class="cc-foot">
                <a href="<?= url('career-detail.php?slug=' . urlencode($job['slug'])) ?>" class="btn btn-primary btn-sm btn-block">Detayları İncele</a>
                <button type="button" class="fav-btn <?= in_array((int) $job['id'], $favIds, true) ? 'is-favorite' : '' ?>" data-fav="<?= (int) $job['id'] ?>">
                  <?= icon('heart', '', 16) ?>
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php echo pagination($page, $total, url('dashboard/explore.php'), array_filter([
        'q' => $search, 'category' => $category, 'education' => $education, 'sort' => $sort,
      ])); ?>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
