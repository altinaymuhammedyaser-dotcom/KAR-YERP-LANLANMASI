<?php
/**
 * Akilli Kariyer - Panel: Test sonuclarim
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Models\Result;
use App\Services\CareerMatchingService;
use App\Services\RecommendationService;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$results = Result::forUser($userId, 50);

$panelType     = 'user';
$panelActive   = 'results';
$panelTitle    = 'Test Sonuçlarım';
$panelSubtitle = 'Tüm kariyer testi geçmişiniz ve detaylı analizleriniz';
$panelActions  = '<a href="' . url('career-test.php') . '" class="btn btn-accent btn-sm">' . icon('plus', '', 16) . ' Yeni Test</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<?php if (!$results): ?>
  <section class="panel-card">
    <div class="pc-body">
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('file-bar-chart', '', 28) ?></div>
        <h3>Henüz test sonucunuz yok</h3>
        <p>Kariyer testini tamamlayın; sonuçlarınız burada saklanacak ve istediğiniz zaman tekrar görüntüleyebileceksiniz.</p>
        <a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('play', '', 17) ?> Testi Başlat</a>
      </div>
    </div>
  </section>
<?php else: ?>
  <section class="panel-card">
    <div class="pc-head">
      <div><h2>Sonuç Geçmişi</h2><p class="pc-sub"><?= count($results) ?> tamamlanmış test</p></div>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>#</th><th>Tarih</th><th>Profil Tipi</th><th>En Uygun Meslek</th><th>Uyum</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($results as $i => $r): ?>
            <?php $recs = Result::recommendations((int) $r['id']); $top = $recs[0] ?? null; ?>
            <tr>
              <td class="td-nowrap"><span class="badge badge-muted"><?= $i + 1 ?></span></td>
              <td class="td-nowrap">
                <strong style="display:block;font-size:.88rem"><?= format_date($r['completed_at'], 'd F Y') ?></strong>
                <span class="text-xs text-muted-c"><?= format_date($r['completed_at'], 'H:i') ?></span>
              </td>
              <td><span class="badge badge-info"><?= e($r['dominant_type']) ?></span></td>
              <td>
                <?php if ($top): ?>
                  <a href="<?= url('career-detail.php?slug=' . urlencode($top['slug'])) ?>" style="font-weight:600"><?= e($top['name']) ?></a>
                <?php else: ?>
                  <span class="text-muted-c">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($top): ?>
                  <strong style="color:var(--c-accent-dark);font-size:1rem">%<?= number_format_tr((float) $top['match_score']) ?></strong>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td>
                <div class="td-actions">
                  <a href="<?= url('test-result.php?session=' . (int) $r['session_id']) ?>" class="btn btn-outline btn-sm"><?= icon('eye', '', 14) ?> Görüntüle</a>
                  <a href="<?= url('api/result-pdf.php?session=' . (int) $r['session_id']) ?>" class="btn btn-outline btn-sm" title="PDF indir"><?= icon('file-down', '', 14) ?></a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Detayli karsilastirma -->
  <?php
  $chartLabels = [];
  $chartData   = [];
  foreach (array_slice($results, 0, 6) as $i => $r) {
      $recs = Result::recommendations((int) $r['id']);
      $top  = $recs[0] ?? null;
      $chartLabels[] = format_date($r['completed_at'], 'd.m.Y');
      $chartData[]   = $top ? round((float) $top['match_score'], 1) : 0;
  }
  ?>
  <section class="panel-card" style="margin-top:22px">
    <div class="pc-head"><h2>Uyum Puanı Gelişimi</h2></div>
    <div class="pc-body">
      <div class="chart-box" style="height:280px">
        <canvas data-chart='<?= e(json_encode([
          'type' => 'line',
          'data' => ['labels' => $chartLabels, 'datasets' => [[
            'label' => 'En Yüksek Uyum (%)',
            'data' => $chartData,
            'borderColor' => '#16B8B0',
            'backgroundColor' => 'rgba(22,184,176,.14)',
            'fill' => true,
            'tension' => 0.35,
            'pointBackgroundColor' => '#16B8B0',
            'pointRadius' => 4,
          ]]],
        ])) ?>'></canvas>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
