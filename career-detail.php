<?php
/**
 * Akilli Kariyer - Meslek detay sayfasi
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Activity;
use App\Models\Career;
use App\Models\Result;
use App\Services\CareerMatchingService;

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    redirect(url('careers.php'));
}

$career = Career::findBySlug($slug);
if (!$career) {
    abort_page(404, 'Meslek Bulunamadı', 'Aradığınız meslek kaydı bulunamadı. Meslek kataloğundan tekrar seçebilirsiniz.', url('careers.php'));
}

$careerId = (int) $career['id'];

Career::incrementView($careerId);

$user     = auth_user();
$isFav    = false;
$matchRec = null;

if ($user) {
    $isFav = Result::isFavorite((int) $user->id, $careerId);
    Result::recordView((int) $user->id, $careerId);
    Activity::log((int) $user->id, 'view_career', $career['name'] . ' mesleğini incelediniz.', 'career', $careerId);
    $matchRec = \App\Services\RecommendationService::careerFitForUser((int) $user->id, $careerId);
}

$requirements = Career::requirements($careerId);
$tasks        = array_values(array_filter($requirements, static fn($r) => $r['requirement_type'] === 'task'));
$skills       = array_values(array_filter($requirements, static fn($r) => $r['requirement_type'] === 'skill'));
$departments  = array_values(array_filter($requirements, static fn($r) => $r['requirement_type'] === 'department'));
$advantages   = array_values(array_filter($requirements, static fn($r) => $r['requirement_type'] === 'advantage'));
$certificates = array_values(array_filter($requirements, static fn($r) => $r['requirement_type'] === 'certificate'));
$paths        = array_values(array_filter($requirements, static fn($r) => $r['requirement_type'] === 'path'));

$interestRows = Career::interests($careerId);
$abilityRows  = Career::abilities($careerId);
$prefRows     = Career::preferences($careerId);
$envRows      = Career::environments($careerId);
$related      = Career::related($careerId, 6);

$pageTitle       = $career['name'] . ' — Meslek Rehberi';
$pageDescription = str_limit($career['short_description'], 300, '');
$activeNav       = 'careers';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">

  <!-- ============================ HERO ============================ -->
  <section style="background:var(--grad-hero);color:#fff;position:relative;overflow:hidden;padding:44px 0 52px">
    <div style="position:absolute;inset:0;background:radial-gradient(circle at 85% 15%,rgba(22,184,176,.28),transparent 48%)"></div>
    <div class="container" style="position:relative;z-index:2">
      <nav aria-label="Breadcrumb" style="font-size:.84rem;color:rgba(255,255,255,.65);margin-bottom:22px">
        <a href="<?= url('index.php') ?>" style="color:rgba(255,255,255,.8)">Ana Sayfa</a>
        <span class="dot-sep" style="color:rgba(255,255,255,.4)">/</span>
        <a href="<?= url('careers.php') ?>" style="color:rgba(255,255,255,.8)">Meslekler</a>
        <span class="dot-sep" style="color:rgba(255,255,255,.4)">/</span>
        <a href="<?= url('careers.php?category=' . urlencode($career['category_slug'])) ?>" style="color:rgba(255,255,255,.8)"><?= e($career['category_name']) ?></a>
        <span class="dot-sep" style="color:rgba(255,255,255,.4)">/</span>
        <span style="color:#fff;font-weight:600"><?= e($career['name']) ?></span>
      </nav>

      <div class="grid g-2" style="gap:40px;align-items:center">
        <div>
          <span class="eyebrow light">
            <?= icon($career['icon'] ?? 'briefcase', '', 15) ?> <?= e($career['category_name']) ?>
          </span>
          <h1 style="color:#fff;margin-bottom:14px"><?= e($career['name']) ?></h1>
          <p style="color:rgba(255,255,255,.82);font-size:1.03rem;max-width:640px"><?= e($career['short_description']) ?></p>

          <div class="flex wrap gap-12" style="margin-top:24px">
            <span class="badge" style="background:rgba(255,255,255,.14);color:#fff">
              <?= icon('graduation-cap', '', 14) ?> <?= e(education_label($career['education_level'])) ?>
            </span>
            <span class="badge" style="background:rgba(255,255,255,.14);color:#fff">
              <?= icon('eye', '', 14) ?> <?= number_format_tr((int) $career['view_count']) ?> görüntülenme
            </span>
            <span class="badge" style="background:rgba(255,255,255,.14);color:#fff">
              <?= icon('briefcase', '', 14) ?> <?= e($career['working_areas'] ? str_limit($career['working_areas'], 52, '…') : 'Çeşitli sektörler') ?>
            </span>
          </div>

          <div class="flex wrap gap-12" style="margin-top:26px">
            <?php if ($user): ?>
              <button type="button" class="fav-btn <?= $isFav ? 'is-favorite' : '' ?>" data-fav="<?= $careerId ?>"
                      style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.25);color:#fff">
                <?= icon('heart', '', 17) ?> <span><?= $isFav ? 'Favorilerimde' : 'Favorilere Ekle' ?></span>
              </button>
              <a href="<?= url('career-test.php') ?>" class="btn btn-accent">
                <?= icon('sparkles', '', 18) ?> Uyum Puanımı Gör
              </a>
            <?php else: ?>
              <a href="<?= url('login.php') ?>" class="btn btn-accent">
                <?= icon('heart', '', 18) ?> Favorilere Ekle
              </a>
              <a href="<?= url('career-test.php') ?>" class="btn btn-light">
                <?= icon('sparkles', '', 18) ?> Kariyer Testi
              </a>
            <?php endif; ?>
          </div>
        </div>

        <div>
          <div class="career-hero-media">
            <?= career_art($career, 'hero') ?>
          </div>

          <?php if ($matchRec): ?>
            <div style="margin-top:16px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);border-radius:var(--r-lg);padding:18px 20px">
              <div class="flex between center">
                <div>
                  <strong style="color:#fff;display:block">Sizin Uyum Puanınız</strong>
                  <span style="font-size:.82rem;color:rgba(255,255,255,.65)">Kariyer testi sonuçlarınıza göre</span>
                </div>
                <div class="match-score">
                  <div class="ms-val" style="color:#5FE3D8">%<?= number_format_tr((float) $matchRec['match_score']) ?></div>
                  <div class="ms-lab" style="color:rgba(255,255,255,.6)">Uyum</div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================ İÇERİK ============================ -->
  <section class="section">
    <div class="container">
      <div class="layout-aside" style="gap:34px">

        <!-- Sol sütun -->
        <div class="grid" style="gap:22px">

          <!-- Genel tanıtım -->
          <article class="card">
            <div class="card-body">
              <h2 style="font-size:1.35rem">Genel Tanıtım</h2>
              <p style="font-size:1rem;line-height:1.85;color:#40536B"><?= e($career['description']) ?></p>
            </div>
          </article>

          <!-- Görevler -->
          <?php if ($tasks): ?>
            <article class="card">
              <div class="card-body">
                <h2 style="font-size:1.35rem">Mesleğin Görevleri</h2>
                <div class="grid" style="gap:12px;margin-top:6px">
                  <?php foreach ($tasks as $t): ?>
                    <div class="flex gap-12" style="align-items:flex-start">
                      <span style="width:24px;height:24px;border-radius:8px;background:var(--c-accent-soft);color:var(--c-accent-dark);display:grid;place-items:center;flex-shrink:0;margin-top:2px">
                        <?= icon('check', '', 14) ?>
                      </span>
                      <span style="color:#40536B;line-height:1.7"><?= e($t['content']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          <?php endif; ?>

          <!-- Beceriler -->
          <?php if ($skills): ?>
            <article class="card">
              <div class="card-body">
                <h2 style="font-size:1.35rem">Gerekli Beceriler</h2>
                <div class="grid g-2" style="gap:12px;margin-top:6px">
                  <?php foreach ($skills as $s): ?>
                    <div class="flex gap-10" style="align-items:flex-start">
                      <span style="color:var(--c-accent);flex-shrink:0;margin-top:2px"><?= icon('zap', '', 17) ?></span>
                      <span style="color:#40536B;line-height:1.65"><?= e($s['content']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          <?php endif; ?>

          <!-- Eğitim ve bölümler -->
          <div class="grid g-2" style="gap:22px">
            <article class="card">
              <div class="card-body">
                <h2 style="font-size:1.2rem"><?= icon('graduation-cap', '', 20) ?> Gerekli Eğitim</h2>
                <p style="font-size:.94rem;line-height:1.7"><?= e($career['education_detail'] ?: education_label($career['education_level'])) ?></p>
                <div class="divider"></div>
                <strong style="display:block;color:var(--c-primary);font-size:.9rem;margin-bottom:8px">Eğitim Seviyesi</strong>
                <span class="badge badge-accent"><?= icon('book-open', '', 13) ?> <?= e(education_label($career['education_level'])) ?></span>
              </div>
            </article>

            <article class="card">
              <div class="card-body">
                <h2 style="font-size:1.2rem"><?= icon('library', '', 20) ?> Bölüm Örnekleri</h2>
                <ul style="list-style:none;padding:0;display:grid;gap:9px;margin:0">
                  <?php foreach ($departments as $d): ?>
                    <li class="flex gap-10" style="align-items:center;margin:0">
                      <span style="color:var(--c-accent)"><?= icon('dot', '', 18) ?></span>
                      <span style="color:#40536B;font-size:.94rem"><?= e($d['content']) ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </article>
          </div>

          <!-- Çalışma alanları -->
          <article class="card">
            <div class="card-body">
              <h2 style="font-size:1.35rem"><?= icon('building-2', '', 21) ?> Çalışma Alanları</h2>
              <p style="font-size:.98rem;line-height:1.8;color:#40536B"><?= e($career['working_areas']) ?></p>
            </div>
          </article>

          <!-- Kariyer gelişim yolu -->
          <?php if ($paths): ?>
            <article class="card">
              <div class="card-body">
                <h2 style="font-size:1.35rem"><?= icon('trending-up', '', 21) ?> Kariyer Gelişim Yolları</h2>
                <div class="flex wrap gap-12" style="margin-top:16px">
                  <?php foreach ($paths as $i => $p): ?>
                    <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:210px">
                      <span style="width:34px;height:34px;border-radius:11px;background:var(--c-light-blue);color:var(--c-secondary);display:grid;place-items:center;font-weight:800;font-size:.85rem;flex-shrink:0"><?= $i + 1 ?></span>
                      <span style="font-size:.92rem;color:#40536B;font-weight:600;line-height:1.4"><?= e($p['content']) ?></span>
                    </div>
                    <?php if ($i < count($paths) - 1): ?>
                      <span style="color:var(--c-border-dark);display:none"><?= icon('chevron-right', '', 18) ?></span>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          <?php endif; ?>

          <!-- Avantajlar -->
          <?php if ($advantages): ?>
            <article class="card">
              <div class="card-body">
                <h2 style="font-size:1.35rem"><?= icon('thumbs-up', '', 21) ?> Avantajlar ve Çalışma Koşulları</h2>
                <div class="grid g-2" style="gap:12px;margin-top:6px">
                  <?php foreach ($advantages as $a): ?>
                    <div class="flex gap-10" style="align-items:flex-start">
                      <span style="color:var(--c-success);flex-shrink:0;margin-top:2px"><?= icon('check-circle-2', '', 17) ?></span>
                      <span style="color:#40536B;line-height:1.65"><?= e($a['content']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          <?php endif; ?>

          <!-- Sertifikalar -->
          <?php if ($certificates): ?>
            <article class="card">
              <div class="card-body">
                <h2 style="font-size:1.35rem"><?= icon('award', '', 21) ?> Faydalı Sertifikalar</h2>
                <div class="flex wrap gap-10" style="margin-top:8px">
                  <?php foreach ($certificates as $c): ?>
                    <span class="badge badge-info"><?= icon('badge-check', '', 13) ?> <?= e($c['content']) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          <?php endif; ?>

          <!-- Benzer meslekler -->
          <?php if ($related): ?>
            <article class="card">
              <div class="card-body">
                <h2 style="font-size:1.35rem"><?= icon('git-compare', '', 21) ?> Benzer Kariyer Seçenekleri</h2>
                <p style="font-size:.93rem">Aynı kategoride yer alan veya ortak ilgi alanlarını paylaşan meslekler.</p>
                <div class="grid g-3" style="gap:14px;margin-top:16px">
                  <?php foreach ($related as $r): ?>
                    <a href="<?= url('career-detail.php?slug=' . urlencode($r['slug'])) ?>" class="card card-hover" style="padding:16px;text-decoration:none">
                      <span class="cc-ico" style="width:42px;height:42px;border-radius:13px;background:var(--c-light-blue);color:var(--c-secondary);display:grid;place-items:center;margin-bottom:12px">
                        <?= icon($r['icon'] ?? 'briefcase', '', 20) ?>
                      </span>
                      <strong style="display:block;color:var(--c-primary);font-size:.95rem;margin-bottom:4px"><?= e($r['name']) ?></strong>
                      <span style="font-size:.8rem;color:var(--c-muted)"><?= e($r['category_name']) ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          <?php endif; ?>
        </div>

        <!-- Sağ sütun -->
        <aside class="grid sticky-side" style="gap:18px">
          <div class="card">
            <div class="card-body">
              <h4 style="margin-bottom:14px"><?= icon('info', '', 18) ?> Hızlı Bilgi</h4>
              <div class="grid" style="gap:13px">
                <div>
                  <span class="text-xs text-muted-c fw-700">Eğitim Seviyesi</span>
                  <div style="font-weight:700;color:var(--c-primary)"><?= e(education_label($career['education_level'])) ?></div>
                </div>
                <div class="divider" style="margin:2px 0"></div>
                <div>
                  <span class="text-xs text-muted-c fw-700">Tahmini Maaş Aralığı</span>
                  <div style="font-weight:700;color:var(--c-primary)"><?= e($career['salary_range'] ?: 'Değişken') ?></div>
                </div>
                <div class="divider" style="margin:2px 0"></div>
                <div>
                  <span class="text-xs text-muted-c fw-700">Kategori</span>
                  <div>
                    <a href="<?= url('careers.php?category=' . urlencode($career['category_slug'])) ?>" class="badge">
                      <?= e($career['category_name']) ?>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <?php if ($interestRows): ?>
            <div class="card">
              <div class="card-body">
                <h4 style="margin-bottom:14px"><?= icon('compass', '', 18) ?> İlgili İlgi Alanları</h4>
                <div class="grid" style="gap:10px">
                  <?php foreach ($interestRows as $i): ?>
                    <div>
                      <div class="flex between" style="font-size:.85rem;margin-bottom:4px">
                        <span style="color:var(--c-text);font-weight:600"><?= e(CareerMatchingService::INTEREST_LABELS[$i['interest_key']] ?? $i['interest_key']) ?></span>
                        <span style="color:var(--c-muted)"><?= (int) $i['weight'] ?>/5</span>
                      </div>
                      <div class="match-bar" style="margin-top:0">
                        <span style="width:<?= (int) $i['weight'] * 20 ?>%"></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($abilityRows): ?>
            <div class="card">
              <div class="card-body">
                <h4 style="margin-bottom:14px"><?= icon('brain', '', 18) ?> Öne Çıkan Yetenekler</h4>
                <div class="grid" style="gap:10px">
                  <?php foreach ($abilityRows as $a): ?>
                    <div>
                      <div class="flex between" style="font-size:.85rem;margin-bottom:4px">
                        <span style="color:var(--c-text);font-weight:600"><?= e(CareerMatchingService::ABILITY_LABELS[$a['ability_key']] ?? $a['ability_key']) ?></span>
                        <span style="color:var(--c-muted)"><?= (int) $a['weight'] ?>/5</span>
                      </div>
                      <div class="match-bar" style="margin-top:0">
                        <span style="width:<?= (int) $a['weight'] * 20 ?>%;background:linear-gradient(90deg,#1C466D,#16B8B0)"></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div class="card" style="background:var(--grad-primary);border:0;color:#fff">
            <div class="card-body">
              <h4 style="color:#fff;margin-bottom:8px"><?= icon('sparkles', '', 19) ?> Bu meslek sana uygun mu?</h4>
              <p style="color:rgba(255,255,255,.75);font-size:.9rem">
                4 boyutlu kariyer testini tamamlayarak kişisel uyum puanını öğren.
              </p>
              <a href="<?= url('career-test.php') ?>" class="btn btn-accent btn-block" style="margin-top:14px">
                <?= icon('clipboard-check', '', 18) ?> Testi Tamamla
              </a>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
