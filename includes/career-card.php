<?php
/**
 * Akilli Kariyer - Meslek karti (yeniden kullanilabilir parca)
 *
 * Beklenen degisken: $job (careers sorgusundan gelen satir)
 */
?>
<article class="career-card card card-hover">
  <div class="cc-media">
    <?= career_art($job) ?>
    <span class="cc-cat"><?= e($job['category_name']) ?></span>
    <span class="cc-icon"><?= icon($job['icon'] ?? 'briefcase', '', 21) ?></span>
  </div>
  <div class="cc-body">
    <h3><a href="<?= url('career-detail.php?slug=' . urlencode($job['slug'])) ?>" style="color:inherit"><?= e($job['name']) ?></a></h3>
    <p class="cc-desc"><?= e(str_limit($job['short_description'], 118)) ?></p>
    <div class="cc-meta">
      <span><?= icon('graduation-cap', '', 15) ?> <?= e(education_label($job['education_level'])) ?></span>
      <span><?= icon('eye', '', 15) ?> <?= number_format_tr((int) $job['view_count']) ?></span>
    </div>
    <div class="cc-foot">
      <a href="<?= url('career-detail.php?slug=' . urlencode($job['slug'])) ?>" class="btn btn-primary btn-sm btn-block">
        <?= icon('arrow-right', '', 16) ?> Detayları İncele
      </a>
      <button type="button" class="btn btn-outline btn-sm btn-block" data-compare="<?= (int) $job['id'] ?>" title="Karşılaştır">
        <?= icon('git-compare', '', 16) ?>
      </button>
      <button type="button" class="fav-btn" data-fav="<?= (int) $job['id'] ?>"
              aria-label="Favorilere ekle" title="Favorilere ekle">
        <?= icon('heart', '', 16) ?>
      </button>
    </div>
  </div>
</article>
