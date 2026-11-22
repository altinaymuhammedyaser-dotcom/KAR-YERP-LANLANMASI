<?php
/**
 * Akilli Kariyer - Panel: Favorilerim
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Models\Result;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$favorites = Result::favorites($userId);

$panelType     = 'user';
$panelActive   = 'favorites';
$panelTitle    = 'Favorilerim';
$panelSubtitle = 'Kaydettiğiniz meslekleri buradan yönetin';
$panelActions  = '<a href="' . url('dashboard/explore.php') . '" class="btn btn-outline btn-sm">' . icon('search', '', 16) . ' Meslek Keşfet</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
  <div class="pc-head">
    <div>
      <h2>Favori Mesleklerim</h2>
      <p class="pc-sub"><?= count($favorites) ?> meslek kayıtlı</p>
    </div>
  </div>

  <?php if (!$favorites): ?>
    <div class="pc-body">
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico" style="background:var(--c-danger-soft);color:var(--c-danger)"><?= icon('heart', '', 28) ?></div>
        <h3>Henüz favori mesleğiniz yok</h3>
        <p>Beğendiğiniz mesleklerdeki kalp ikonuna dokunarak favorilerinize ekleyebilirsiniz.</p>
        <a href="<?= url('dashboard/explore.php') ?>" class="btn btn-accent"><?= icon('search', '', 17) ?> Meslekleri Keşfet</a>
      </div>
    </div>
  <?php else: ?>
    <div class="pc-body">
      <div class="grid g-3">
        <?php foreach ($favorites as $job): ?>
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
                <span><?= icon('clock', '', 15) ?> <?= time_ago($job['favorited_at']) ?></span>
              </div>
              <div class="cc-foot">
                <a href="<?= url('career-detail.php?slug=' . urlencode($job['slug'])) ?>" class="btn btn-primary btn-sm btn-block">
                  Detayları İncele
                </a>
                <button type="button" class="fav-btn is-favorite" data-fav="<?= (int) $job['id'] ?>" title="Favorilerden çıkar">
                  <?= icon('heart', '', 16) ?>
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
