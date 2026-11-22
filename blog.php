<?php
/**
 * Akilli Kariyer - Blog listesi (public)
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Blog;

$search   = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$page     = max(1, (int) ($_GET['sayfa'] ?? 1));

$filters = array_filter(['category' => $category, 'search' => $search], static fn($v) => $v !== '');

$total      = Blog::count($filters);
$posts      = Blog::posts($filters + ['limit' => BLOG_PER_PAGE, 'offset' => ($page - 1) * BLOG_PER_PAGE]);
$categories = Blog::categories();

$pageTitle       = 'Kariyer Rehberi — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Meslek seçimi, üniversite seçimi ve kariyer planlaması üzerine kapsamlı rehber yazıları.';
$activeNav       = 'guide';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container">
      <div class="flex between wrap gap-16" style="align-items:flex-end">
        <div>
          <span class="eyebrow"><?= icon('newspaper', '', 15) ?> Blog</span>
          <h1 style="margin-bottom:6px">Kariyer Rehberi</h1>
          <p style="margin:0"><?= Blog::totalCount() ?> rehber yazısı ile kariyer planınızı şekillendirin.</p>
        </div>
        <form method="get" class="search-box" style="max-width:320px;width:100%">
          <?= icon('search', '', 18) ?>
          <input type="search" name="q" class="input" value="<?= e($search) ?>" placeholder="Yazılarda ara…">
        </form>
      </div>

      <div class="flex wrap gap-8" style="margin-top:20px">
        <a href="<?= url('blog.php') ?>" class="badge <?= $category === '' ? 'badge-accent' : 'badge-muted' ?>">Tümü</a>
        <?php foreach ($categories as $cat): ?>
          <a href="<?= url('blog.php?category=' . urlencode($cat['slug'])) ?>" class="badge <?= $category === $cat['slug'] ? 'badge-accent' : 'badge-muted' ?>">
            <?= e($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section-sm">
    <div class="container">
      <?php if (!$posts): ?>
        <div class="empty-state">
          <div class="es-ico"><?= icon('search-x', '', 30) ?></div>
          <h3>Yazı bulunamadı</h3>
          <p>Arama kriterlerinize uygun bir yazı bulunamadı.</p>
          <a href="<?= url('blog.php') ?>" class="btn btn-primary"><?= icon('refresh-cw', '', 17) ?> Tüm Yazılar</a>
        </div>
      <?php else: ?>
        <div class="grid g-3">
          <?php foreach ($posts as $post): ?>
            <article class="blog-card">
              <div class="bc-media"><?= blog_art($post) ?></div>
              <div class="bc-body">
                <span class="badge" style="background:<?= e($post['category_color']) ?>22;color:<?= e($post['category_color']) ?>">
                  <?= e($post['category_name']) ?>
                </span>
                <h3><a href="<?= url('blog-detail.php?slug=' . urlencode($post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
                <p><?= e(str_limit($post['excerpt'], 130)) ?></p>
                <div class="blog-meta">
                  <span><?= icon('calendar', '', 14) ?> <?= format_date($post['published_at']) ?></span>
                  <span><?= icon('clock', '', 14) ?> <?= (int) $post['reading_time'] ?> dk</span>
                </div>
                <a href="<?= url('blog-detail.php?slug=' . urlencode($post['slug'])) ?>" class="btn btn-outline btn-sm" style="align-self:flex-start">
                  Devamını Oku <?= icon('arrow-right', '', 15) ?>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <?php echo pagination($page, $total, url('blog.php'), array_filter(['q' => $search, 'category' => $category])); ?>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
