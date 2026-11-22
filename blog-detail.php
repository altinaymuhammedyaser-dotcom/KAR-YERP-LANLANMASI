<?php
/**
 * Akilli Kariyer - Blog yazi detayi
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Blog;

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    redirect(url('career-guide.php'));
}

$post = Blog::findBySlug($slug);
if (!$post) {
    abort_page(404, 'Yazı Bulunamadı', 'Aradığınız yazı bulunamadı. Kariyer rehberinden diğer yazılara göz atabilirsiniz.', url('career-guide.php'));
}

Blog::incrementView((int) $post['id']);

$related = Blog::related((int) $post['id'], (int) $post['category_id'], 3);
$categories = Blog::categories();

$pageTitle       = $post['title'];
$pageDescription = $post['excerpt'];
$activeNav       = 'guide';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container container-narrow">
      <nav aria-label="Breadcrumb" style="font-size:.84rem;color:var(--c-muted);margin-bottom:16px">
        <a href="<?= url('index.php') ?>">Ana Sayfa</a>
        <span class="dot-sep">/</span>
        <a href="<?= url('career-guide.php') ?>">Kariyer Rehberi</a>
        <span class="dot-sep">/</span>
        <a href="<?= url('career-guide.php?category=' . urlencode($post['category_slug'])) ?>"><?= e($post['category_name']) ?></a>
      </nav>

      <span class="badge" style="background:<?= e($post['category_color']) ?>22;color:<?= e($post['category_color']) ?>;margin-bottom:14px">
        <?= icon($post['category_icon'] ?? 'newspaper', '', 13) ?> <?= e($post['category_name']) ?>
      </span>

      <h1 style="margin-bottom:16px"><?= e($post['title']) ?></h1>

      <div class="blog-meta" style="margin-bottom:20px">
        <span><?= icon('user', '', 15) ?> <?= e(trim(($post['author_first_name'] ?? '') . ' ' . ($post['author_last_name'] ?? '')) ?: 'Akıllı Kariyer Editör') ?></span>
        <span><?= icon('calendar', '', 15) ?> <?= format_date($post['published_at']) ?></span>
        <span><?= icon('clock', '', 15) ?> <?= (int) $post['reading_time'] ?> dakika okuma</span>
        <span><?= icon('eye', '', 15) ?> <?= number_format_tr((int) $post['view_count']) ?> görüntülenme</span>
      </div>

      <div style="border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-md);margin-bottom:30px">
        <?= blog_art($post, 'hero') ?>
      </div>
    </div>
  </section>

  <section class="section-sm">
    <div class="container container-narrow">
      <div class="layout-aside" style="gap:30px">
        <article class="article">
          <p style="font-size:1.06rem;font-weight:600;color:var(--c-primary);line-height:1.7"><?= e($post['excerpt']) ?></p>
          <div class="divider"></div>
          <?= $post['content'] ?>
        </article>

        <aside class="grid sticky-side" style="gap:18px">
          <div class="card" style="background:var(--grad-primary);border:0;color:#fff">
            <div class="card-body">
              <h4 style="color:#fff;font-size:1rem"><?= icon('sparkles', '', 18) ?> Size uygun mesleği bulun</h4>
              <p style="color:rgba(255,255,255,.75);font-size:.88rem">
                46 soruluk kapsamlı kariyer testini tamamlayın; ilgi alanlarınıza ve yeteneklerinize
                göre kişiselleştirilmiş meslek önerileri alın.
              </p>
              <a href="<?= url('career-test.php') ?>" class="btn btn-accent btn-block btn-sm">Teste Başla</a>
            </div>
          </div>

          <?php if ($related): ?>
            <div class="card">
              <div class="card-body">
                <h4 style="margin-bottom:14px;font-size:.95rem"><?= icon('book-open', '', 17) ?> İlgili Yazılar</h4>
                <div class="grid" style="gap:12px">
                  <?php foreach ($related as $rel): ?>
                    <a href="<?= url('blog-detail.php?slug=' . urlencode($rel['slug'])) ?>" style="text-decoration:none">
                      <span style="display:block;font-weight:700;color:var(--c-primary);font-size:.9rem;line-height:1.45"><?= e($rel['title']) ?></span>
                      <span class="text-xs text-muted-c"><?= format_date($rel['published_at']) ?> • <?= (int) $rel['reading_time'] ?> dk</span>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div class="card">
            <div class="card-body">
              <h4 style="margin-bottom:12px;font-size:.95rem"><?= icon('folder-open', '', 17) ?> Kategoriler</h4>
              <div class="flex wrap gap-8">
                <?php foreach ($categories as $cat): ?>
                  <a href="<?= url('career-guide.php?category=' . urlencode($cat['slug'])) ?>" class="badge <?= $cat['slug'] === $post['category_slug'] ? 'badge-accent' : 'badge-muted' ?>">
                    <?= e($cat['name']) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </aside>
      </div>

      <div class="flex gap-10" style="margin-top:34px;flex-wrap:wrap;justify-content:center">
        <a href="<?= url('career-guide.php') ?>" class="btn btn-outline"><?= icon('arrow-left', '', 17) ?> Tüm Yazılar</a>
        <a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('clipboard-check', '', 17) ?> Kariyer Testini Tamamla</a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
