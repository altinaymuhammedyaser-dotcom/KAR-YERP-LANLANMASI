<?php
/**
 * Akilli Kariyer - Ilgi alanlari kesf sayfasi
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Career;
use App\Models\Result;
use App\Services\CareerMatchingService;

$user    = auth_user();
$myScores = [];
if ($user) {
    $latest = Result::latestForUser((int) $user->id);
    if ($latest) {
        $detail = \App\Services\RecommendationService::resultDetail((int) $latest['id']);
        $myScores = $detail['interest_scores'];
    }
}

$categories  = Career::categories();
$interests   = Career::availableInterests();
$careerCount = Career::totalCount();
$questionCount = \App\Models\Test::questionCount();

$pageTitle       = 'İlgi Alanları — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Teknoloji, bilim, sağlık, sanat, işletme ve daha fazlası: ilgi alanlarına göre keşfedebileceğiniz meslekler.';
$activeNav       = 'interests';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container">
      <div class="flex between wrap gap-16" style="align-items:flex-end">
        <div>
          <span class="eyebrow"><?= icon('compass', '', 15) ?> İlgi Alanları</span>
          <h1 style="margin-bottom:6px">İlgi Alanını Keşfet</h1>
          <p style="margin:0">
            İlgi alanların kariyerini belirleyen en önemli faktörlerden biridir.
            Her alanda hangi mesleklerin yer aldığını inceleyin.
          </p>
        </div>
        <a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('sparkles', '', 18) ?> İlgi Alanlarımı Analiz Et</a>
      </div>
    </div>
  </section>

  <section class="section-sm">
    <div class="container">
      <div class="grid g-4" style="margin-bottom:34px">
        <div class="stat-card">
          <span class="sc-icon accent"><?= icon('compass', '', 24) ?></span>
          <div><div class="sc-value"><?= count($interests) ?></div><div class="sc-label">İlgi Alanı</div></div>
        </div>
        <div class="stat-card">
          <span class="sc-icon"><?= icon('briefcase', '', 24) ?></span>
          <div><div class="sc-value"><?= $careerCount ?></div><div class="sc-label">Meslek</div></div>
        </div>
        <div class="stat-card">
          <span class="sc-icon success"><?= icon('layers', '', 24) ?></span>
          <div><div class="sc-value"><?= count($categories) ?></div><div class="sc-label">Kategori</div></div>
        </div>
        <div class="stat-card">
          <span class="sc-icon warning"><?= icon('clipboard-list', '', 24) ?></span>
          <div><div class="sc-value"><?= $questionCount ?></div><div class="sc-label">Test Sorusu</div></div>
        </div>
      </div>

      <div class="section-head left" style="margin-bottom:24px">
        <h2 style="margin-bottom:8px">İlgi Alanları ve Meslekler</h2>
        <p>Her ilgi alanının içindeki meslekleri keşfedin. Kendi puanınızı test sonuçlarınızdan görebilirsiniz.</p>
      </div>

      <div class="grid" style="gap:16px">
        <?php foreach ($interests as $interest): ?>
          <?php
          $key   = $interest['interest_key'];
          $label = CareerMatchingService::INTEREST_LABELS[$key] ?? $key;
          $count = (int) $interest['total'];
          $myScore = isset($myScores[$key]) ? round($myScores[$key] * 100) : null;
          $sample = Career::list(['interest' => $key, 'limit' => 4, 'sort' => 'popular']);
          ?>
          <div class="card">
            <div class="card-body">
              <div class="flex between wrap gap-16" style="align-items:flex-start">
                <div style="flex:1;min-width:260px">
                  <div class="flex gap-12 center" style="margin-bottom:10px">
                    <span style="width:46px;height:46px;border-radius:14px;background:var(--c-accent-soft);color:var(--c-accent-dark);display:grid;place-items:center;flex-shrink:0">
                      <?= icon('compass', '', 22) ?>
                    </span>
                    <div>
                      <h3 style="margin:0;font-size:1.12rem"><?= e($label) ?></h3>
                      <span class="text-sm text-muted-c"><?= $count ?> meslek bu alanda</span>
                    </div>
                  </div>

                  <div class="flex wrap gap-8">
                    <?php foreach ($sample as $s): ?>
                      <a href="<?= url('career-detail.php?slug=' . urlencode($s['slug'])) ?>" class="badge badge-muted">
                        <?= e($s['name']) ?>
                      </a>
                    <?php endforeach; ?>
                    <?php if ($count > 4): ?>
                      <a href="<?= url('careers.php?interest=' . urlencode($key)) ?>" class="badge badge-accent">
                        +<?= $count - 4 ?> meslek daha
                      </a>
                    <?php endif; ?>
                  </div>
                </div>

                <div style="min-width:190px">
                  <?php if ($myScore !== null): ?>
                    <div class="flex between center" style="font-size:.84rem;margin-bottom:6px">
                      <span style="font-weight:600;color:var(--c-text)">Senin puanın</span>
                      <strong style="color:var(--c-accent-dark)">%<?= $myScore ?></strong>
                    </div>
                    <div class="match-bar" style="margin-top:0">
                      <span style="width:<?= $myScore ?>%"></span>
                    </div>
                  <?php else: ?>
                    <a href="<?= url('career-test.php') ?>" class="btn btn-outline btn-sm btn-block">
                      <?= icon('clipboard-check', '', 15) ?> Puanımı Öğren
                    </a>
                  <?php endif; ?>
                  <a href="<?= url('careers.php?interest=' . urlencode($key)) ?>" class="btn btn-ghost btn-sm btn-block" style="margin-top:8px">
                    Tüm Meslekler <?= icon('arrow-right', '', 14) ?>
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
