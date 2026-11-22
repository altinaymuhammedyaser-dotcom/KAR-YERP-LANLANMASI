<?php
/**
 * Akilli Kariyer - Blog / Kariyer rehberi listesi
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Blog;

$search   = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$page     = max(1, (int) ($_GET['sayfa'] ?? 1));

$filters = array_filter(['category' => $category, 'search' => $search], static fn($v) => $v !== '');

$total      = Blog::count($filters);
$perPage    = BLOG_PER_PAGE;
$posts      = Blog::posts($filters + ['limit' => $perPage, 'offset' => ($page - 1) * $perPage]);
$categories = Blog::categories();

$pageTitle       = 'Kariyer Rehberi — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Meslek seçimi, üniversite, yetenek geliştirme ve kariyer planlaması üzerine rehber yazıları.';
$activeNav       = 'guide';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container">
      <div class="flex between wrap gap-16" style="align-items:flex-end">
        <div>
          <span class="eyebrow"><?= icon('book-open', '', 15) ?> Kariyer Rehberi</span>
          <h1 style="margin-bottom:6px">Meslek Seçiminden Kariyer Planına</h1>
          <p style="margin:0">Doğru mesleği seçmek, doğru bölümü seçmek ve kariyerini planlamak için pratik rehberler.</p>
        </div>
        <a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('clipboard-check', '', 18) ?> Kariyer Testine Başla</a>
      </div>
    </div>
  </section>

  <section class="section-sm">
    <div class="container">
      <div class="layout-aside left">

        <!-- Yan menü -->
        <aside class="grid sticky-side" style="gap:18px">
          <div class="card">
            <div class="card-body">
              <h4 style="margin-bottom:14px;font-size:.95rem"><?= icon('list', '', 17) ?> Kategoriler</h4>
              <div class="grid" style="gap:5px">
                <a href="<?= url('career-guide.php') ?>" class="flex between center" style="padding:9px 11px;border-radius:10px;font-size:.87rem;font-weight:600;<?= $category === '' ? 'background:var(--c-accent-soft);color:var(--c-accent-dark)' : 'color:var(--c-text)' ?>">
                  Tüm Yazılar <span class="badge badge-muted"><?= Blog::totalCount() ?></span>
                </a>
                <?php foreach ($categories as $cat): ?>
                  <a href="<?= url('career-guide.php?category=' . urlencode($cat['slug'])) ?>" class="flex between center" style="padding:9px 11px;border-radius:10px;font-size:.87rem;font-weight:600;<?= $category === $cat['slug'] ? 'background:var(--c-accent-soft);color:var(--c-accent-dark)' : 'color:var(--c-text)' ?>">
                    <?= e($cat['name']) ?> <span class="badge badge-muted"><?= (int) $cat['post_count'] ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="card" style="background:var(--grad-primary);border:0;color:#fff">
            <div class="card-body">
              <h4 style="color:#fff;font-size:1rem"><?= icon('sparkles', '', 18) ?> Testi Tamamlayın</h4>
              <p style="color:rgba(255,255,255,.75);font-size:.88rem">46 soruluk kapsamlı test ile size uygun meslekleri keşfedin.</p>
              <a href="<?= url('career-test.php') ?>" class="btn btn-accent btn-block btn-sm">Hemen Başla</a>
            </div>
          </div>
        </aside>

        <!-- İçerik -->
        <div>
          <div class="flex between wrap gap-12" style="margin-bottom:22px">
            <form method="get" class="search-box" style="flex:1;min-width:220px;max-width:340px">
              <?php if ($category !== ''): ?>
                <input type="hidden" name="category" value="<?= e($category) ?>">
              <?php endif; ?>
              <?= icon('search', '', 18) ?>
              <input type="search" name="q" class="input" value="<?= e($search) ?>" placeholder="Yazılarda ara…">
            </form>
            <span class="text-sm text-muted-c">
              <strong><?= number_format_tr($total) ?></strong> yazı bulundu
            </span>
          </div>

          <?php if (!$posts): ?>
            <div class="empty-state">
              <div class="es-ico"><?= icon('search-x', '', 30) ?></div>
              <h3>Yazı bulunamadı</h3>
              <p>Arama kriterlerinize uygun bir yazı bulunamadı. Farklı bir kategori deneyin.</p>
              <a href="<?= url('career-guide.php') ?>" class="btn btn-primary"><?= icon('refresh-cw', '', 17) ?> Tüm Yazılar</a>
            </div>
          <?php else: ?>
            <div class="grid g-2">
              <?php foreach ($posts as $post): ?>
                <article class="blog-card">
                  <div class="bc-media"><?= blog_art($post) ?></div>
                  <div class="bc-body">
                    <div class="flex between center" style="gap:8px">
                      <span class="badge" style="background:<?= e($post['category_color']) ?>22;color:<?= e($post['category_color']) ?>">
                        <?= e($post['category_name']) ?>
                      </span>
                      <span class="text-xs text-muted-c"><?= format_date($post['published_at']) ?></span>
                    </div>
                    <h3><a href="<?= url('blog-detail.php?slug=' . urlencode($post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
                    <p><?= e(str_limit($post['excerpt'], 145)) ?></p>
                    <div class="blog-meta">
                      <span><?= icon('clock', '', 14) ?> <?= (int) $post['reading_time'] ?> dk okuma</span>
                      <span><?= icon('eye', '', 14) ?> <?= number_format_tr((int) $post['view_count']) ?></span>
                    </div>
                    <a href="<?= url('blog-detail.php?slug=' . urlencode($post['slug'])) ?>" class="btn btn-outline btn-sm" style="align-self:flex-start">
                      Devamını Oku <?= icon('arrow-right', '', 15) ?>
                    </a>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>

            <?php echo pagination($page, $total, url('career-guide.php'), array_filter(['q' => $search, 'category' => $category])); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
