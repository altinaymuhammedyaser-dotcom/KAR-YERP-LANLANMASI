<?php
/**
 * Akilli Kariyer - Kullanici paneli: Genel bakis
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Models\Activity;
use App\Models\Career;
use App\Models\Result;
use App\Models\Test;
use App\Services\CareerMatchingService;
use App\Services\RecommendationService;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$results      = Result::forUser($userId, 20);
$latestResult = $results[0] ?? null;
$completed    = count($results);
$favorites    = Result::favoriteCount($userId);
$viewed       = Result::viewedCount($userId);
$activeSession = Test::activeSession($userId);

$recommendations = $latestResult
    ? array_slice(Result::recommendations((int) $latestResult['id']), 0, 5)
    : [];

$activities = Activity::forUser($userId, 8);
$recentViewed = Result::recentlyViewed($userId, 5);
$popular = Career::mostViewed(5);

$interestChart = [];
$abilityChart  = [];
if ($latestResult) {
    $detail = RecommendationService::resultDetail((int) $latestResult['id']);
    $graph = static function (array $values, array $labels): array {
        arsort($values);
        $out = ['labels' => [], 'data' => []];
        foreach ($values as $k => $v) {
            $out['labels'][] = $labels[$k] ?? $k;
            $out['data'][]   = round(((float) $v) * 100, 1);
        }
        return $out;
    };
    $interestChart = $graph($detail['interest_scores'], CareerMatchingService::INTEREST_LABELS);
    $abilityChart  = $graph($detail['ability_scores'], CareerMatchingService::ABILITY_LABELS);
}

$panelType     = 'user';
$panelActive   = 'index';
$panelTitle    = 'Merhaba, ' . $user->first_name . '!';
$panelSubtitle = 'Kariyer yolculuğuna devam etmeye hazır mısın?';
$panelActions  = '<a href="' . url('career-test.php') . '" class="btn btn-accent btn-sm">' . icon('clipboard-check', '', 16) . ' Kariyer Testi</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<?php if ($activeSession): ?>
  <div class="alert alert-info" style="align-items:center">
    <?= icon('info', '', 19) ?>
    <span class="flex-1">
      <strong>Yarım kalmış bir testiniz var.</strong>
      <?= (int) $activeSession['answered_count'] ?> / <?= (int) $activeSession['total_questions'] ?> soruyu cevapladınız.
    </span>
    <a href="<?= url('career-test.php') ?>" class="btn btn-primary btn-sm"><?= icon('play', '', 15) ?> Kaldığın Yerden Devam Et</a>
  </div>
<?php endif; ?>

<!-- İstatistik kartları -->
<div class="grid g-4" style="margin-bottom:24px">
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-accent-soft);color:var(--c-accent-dark)"><?= icon('clipboard-check', '', 20) ?></span>
    <div>
      <div class="ms-val"><?= $completed ?></div>
      <div class="ms-lab">Tamamlanan Test</div>
    </div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-danger-soft);color:var(--c-danger)"><?= icon('heart', '', 20) ?></span>
    <div>
      <div class="ms-val"><?= $favorites ?></div>
      <div class="ms-lab">Favori Meslek</div>
    </div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-success-soft);color:var(--c-success)"><?= icon('calendar', '', 20) ?></span>
    <div>
      <div class="ms-val" style="font-size:.98rem"><?= $latestResult ? format_date($latestResult['completed_at'], 'd M Y') : '—' ?></div>
      <div class="ms-lab">Son Test Tarihi</div>
    </div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico"><?= icon('eye', '', 20) ?></span>
    <div>
      <div class="ms-val"><?= $viewed ?></div>
      <div class="ms-lab">Keşfedilen Meslek</div>
    </div>
  </div>
</div>

<div class="layout-aside ratio" style="gap:22px">

  <!-- Sol -->
  <div class="grid" style="gap:22px">

    <!-- Öneriler -->
    <section class="panel-card">
      <div class="pc-head">
        <div>
          <h2><?= icon('sparkles', '', 18) ?> Sana Önerilen Meslekler</h2>
          <p class="pc-sub"><?= $latestResult ? 'Son test sonuçlarına göre en uyumlu alanlar' : 'Kariyer testini tamamlayarak önerileri görebilirsin' ?></p>
        </div>
        <?php if ($recommendations): ?>
          <a href="<?= url('dashboard/recommendations.php') ?>" class="btn btn-outline btn-sm">Tümü <?= icon('arrow-right', '', 15) ?></a>
        <?php endif; ?>
      </div>
      <div class="pc-body">
        <?php if (!$recommendations): ?>
          <div class="empty-state" style="padding:38px 20px;border:0;background:var(--c-bg)">
            <div class="es-ico"><?= icon('clipboard-check', '', 28) ?></div>
            <h3>Henüz öneri yok</h3>
            <p>Kariyer testini tamamlayarak kişiselleştirilmiş meslek önerilerine ulaş.</p>
            <a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('play', '', 17) ?> Testi Başlat</a>
          </div>
        <?php else: ?>
          <?php foreach ($recommendations as $i => $rec): ?>
            <div class="rec-row">
              <span class="rr-rank <?= $i === 0 ? 'top' : '' ?>"><?= $i + 1 ?></span>
              <div class="rr-info">
                <div class="rr-title"><?= e($rec['name']) ?></div>
                <div class="rr-meta">
                  <span><?= icon('layers', '', 13) ?> <?= e($rec['category_name']) ?></span>
                  <span><?= icon('graduation-cap', '', 13) ?> <?= e(education_label($rec['education_level'])) ?></span>
                </div>
              </div>
              <div class="rr-score">
                <div class="rrs-val">%<?= number_format_tr((float) $rec['match_score']) ?></div>
                <div class="rrs-lab">Uyum</div>
              </div>
              <a href="<?= url('career-detail.php?slug=' . urlencode($rec['slug'])) ?>" class="btn btn-outline btn-sm" style="flex-shrink:0">
                İncele
              </a>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Grafikler -->
    <?php if ($interestChart): ?>
      <div class="chart-grid-2">
        <section class="panel-card">
          <div class="pc-head"><h3><?= icon('compass', '', 17) ?> İlgi Alanı Dağılımı</h3></div>
          <div class="pc-body">
            <div class="chart-box" style="height:250px">
              <canvas data-chart='<?= e(json_encode([
                'type' => 'bar',
                'data' => ['labels' => array_slice($interestChart['labels'], 0, 8), 'datasets' => [[
                  'label' => 'İlgi', 'data' => array_slice($interestChart['data'], 0, 8),
                  'backgroundColor' => 'rgba(22,184,176,.85)', 'borderRadius' => 6, 'maxBarThickness' => 24,
                ]]],
              ])) ?>'></canvas>
            </div>
          </div>
        </section>

        <section class="panel-card">
          <div class="pc-head"><h3><?= icon('brain', '', 17) ?> Yetenek Puanları</h3></div>
          <div class="pc-body">
            <div class="chart-box" style="height:250px">
              <canvas data-chart='<?= e(json_encode([
                'type' => 'radar',
                'data' => ['labels' => array_slice($abilityChart['labels'], 0, 8), 'datasets' => [[
                  'label' => 'Yetenek', 'data' => array_slice($abilityChart['data'], 0, 8),
                  'borderColor' => '#1C466D', 'backgroundColor' => 'rgba(28,70,109,.16)',
                  'pointBackgroundColor' => '#1C466D', 'pointRadius' => 3,
                ]]],
                'options' => ['scales' => ['r' => [
                  'min' => 0, 'max' => 100,
                  'grid' => ['color' => 'rgba(20,43,74,.10)'],
                  'angleLines' => ['color' => 'rgba(20,43,74,.10)'],
                  'pointLabels' => ['color' => '#748399', 'font' => ['size' => 10, 'weight' => '600']],
                  'ticks' => ['display' => false],
                ]]],
              ])) ?>'></canvas>
            </div>
          </div>
        </section>
      </div>
    <?php endif; ?>

    <!-- Son test sonuçları -->
    <?php if ($results): ?>
      <section class="panel-card">
        <div class="pc-head">
          <div><h2><?= icon('history', '', 18) ?> Son Test Sonuçlarım</h2></div>
          <a href="<?= url('dashboard/results.php') ?>" class="btn btn-outline btn-sm">Tümü <?= icon('arrow-right', '', 15) ?></a>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr><th>Tarih</th><th>Profil Tipi</th><th>En Uygun Meslek</th><th>Skor</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($results, 0, 5) as $r): ?>
                <?php $recs = Result::recommendations((int) $r['id']); $top = $recs[0] ?? null; ?>
                <tr>
                  <td class="td-nowrap"><?= format_date($r['completed_at'], 'd M Y H:i') ?></td>
                  <td><span class="badge badge-info"><?= e($r['dominant_type']) ?></span></td>
                  <td><?= $top ? e($top['name']) : '—' ?></td>
                  <td><strong style="color:var(--c-accent-dark)"><?= $top ? '%' . number_format_tr((float) $top['match_score']) : '—' ?></strong></td>
                  <td class="td-actions">
                    <a href="<?= url('test-result.php?session=' . (int) $r['session_id']) ?>" class="btn btn-outline btn-sm">Görüntüle</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>
  </div>

  <!-- Sağ -->
  <div class="grid" style="gap:22px">

    <!-- Hızlı aksiyonlar -->
    <section class="panel-card">
      <div class="pc-head"><h3><?= icon('zap', '', 17) ?> Hızlı Başlangıç</h3></div>
      <div class="pc-body grid" style="gap:11px">
        <a href="<?= url('career-test.php') ?>" class="quick-action">
          <span class="qa-ico"><?= icon('clipboard-check', '', 19) ?></span>
          <span><strong>Kariyer Testini Başlat</strong><span>Kapsamlı değerlendirme</span></span>
        </a>
        <a href="<?= url('dashboard/explore.php') ?>" class="quick-action">
          <span class="qa-ico" style="background:var(--c-light-blue);color:var(--c-secondary)"><?= icon('search', '', 19) ?></span>
          <span><strong>Meslekleri Keşfet</strong><span>54 meslek, 10 kategori</span></span>
        </a>
        <a href="<?= url('dashboard/favorites.php') ?>" class="quick-action">
          <span class="qa-ico" style="background:var(--c-danger-soft);color:var(--c-danger)"><?= icon('heart', '', 19) ?></span>
          <span><strong>Favorilerim</strong><span><?= $favorites ?> meslek kayıtlı</span></span>
        </a>
      </div>
    </section>

    <!-- Kariyer gelişim önerileri -->
    <section class="panel-card">
      <div class="pc-head"><h3><?= icon('trending-up', '', 17) ?> Kariyer Gelişim Önerileri</h3></div>
      <div class="pc-body grid" style="gap:13px">
        <?php
        $tips = [];
        if (!$completed) {
            $tips[] = ['Kariyer testini tamamlayarak profil analizine ulaşın.', 'clipboard-check', 'accent'];
        } else {
            $tips[] = ['Test sonucunuzu PDF olarak indirip saklayın.', 'file-down', 'primary'];
        }
        if ($favorites < 3) {
            $tips[] = ['Beğendiğiniz meslekleri favorilere ekleyin, panelinizde toplayın.', 'heart', 'danger'];
        }
        if ($viewed < 5) {
            $tips[] = ['Farklı kategorilerden meslekler inceleyerek seçeneklerinizi genişletin.', 'compass', 'info'];
        }
        $tips[] = ['Kariyer rehberi yazılarıyla bilginizi derleyin.', 'book-open', 'success'];
        foreach (array_slice($tips, 0, 4) as $tip): ?>
          <div class="flex gap-12" style="align-items:flex-start">
            <span class="ai-ico <?= e($tip[2]) ?>" style="width:34px;height:34px;border-radius:11px;display:grid;place-items:center;background:var(--c-light-blue);color:var(--c-secondary);flex-shrink:0">
              <?= icon($tip[1], '', 16) ?>
            </span>
            <span style="font-size:.87rem;color:var(--c-text);line-height:1.6"><?= e($tip[0]) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Son aktiviteler -->
    <section class="panel-card">
      <div class="pc-head">
        <h3><?= icon('activity', '', 17) ?> Son Aktiviteler</h3>
        <a href="<?= url('dashboard/activities.php') ?>" class="btn btn-ghost btn-sm">Tümü</a>
      </div>
      <div class="pc-body">
        <?php if (!$activities): ?>
          <p style="font-size:.89rem;margin:0">Henüz aktivite yok. Kariyer testine başlayın!</p>
        <?php else: ?>
          <?php foreach ($activities as $act): ?>
            <div class="activity-item">
              <span class="ai-ico <?= e(Activity::colorFor($act['activity_type'])) ?>">
                <?= icon(Activity::iconFor($act['activity_type']), '', 17) ?>
              </span>
              <div class="flex-1">
                <div class="ai-txt"><?= e($act['description']) ?></div>
                <div class="ai-time"><?= time_ago($act['created_at']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Son görüntülenenler -->
    <?php if ($recentViewed): ?>
      <section class="panel-card">
        <div class="pc-head"><h3><?= icon('eye', '', 17) ?> Son İncelediklerin</h3></div>
        <div class="pc-body grid" style="gap:9px">
          <?php foreach ($recentViewed as $rv): ?>
            <a href="<?= url('career-detail.php?slug=' . urlencode($rv['slug'])) ?>" class="flex between center gap-10" style="padding:7px;border:1px solid var(--c-border);border-radius:12px;text-decoration:none">
              <span class="flex gap-10 center" style="min-width:0">
                <span class="career-thumb"><?= career_art($rv) ?></span>
                <span style="min-width:0">
                  <span style="display:block;font-weight:700;color:var(--c-primary);font-size:.87rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($rv['name']) ?></span>
                  <span class="text-xs text-muted-c"><?= time_ago($rv['viewed_at']) ?></span>
                </span>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Popüler meslekler -->
    <section class="panel-card">
      <div class="pc-head"><h3><?= icon('trending-up', '', 17) ?> Popüler Meslekler</h3></div>
      <div class="pc-body grid" style="gap:11px">
        <?php foreach ($popular as $p): ?>
          <a href="<?= url('career-detail.php?slug=' . urlencode($p['slug'])) ?>" class="flex between center gap-10" style="padding:7px;border:1px solid var(--c-border);border-radius:12px;text-decoration:none">
            <span class="flex gap-10 center" style="min-width:0">
              <span class="career-thumb">
                <?= career_art($p) ?>
              </span>
              <span style="min-width:0">
                <span style="display:block;font-weight:700;color:var(--c-primary);font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($p['name']) ?></span>
                <span class="text-xs text-muted-c"><?= e($p['category_name']) ?></span>
              </span>
            </span>
            <span class="text-xs text-muted-c" style="flex-shrink:0"><?= number_format_tr((int) $p['view_count']) ?> görüntülenme</span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
