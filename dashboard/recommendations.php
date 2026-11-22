<?php
/**
 * Akilli Kariyer - Panel: Meslek onerilerim
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Models\Result;
use App\Services\RecommendationService;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$results = Result::forUser($userId, 10);
$selectedResultId = (int) ($_GET['result'] ?? ($results[0]['id'] ?? 0));

$detail = $selectedResultId ? RecommendationService::resultDetail($selectedResultId) : null;
$recommendations = $detail['recommendations'] ?? [];

$panelType     = 'user';
$panelActive   = 'recommendations';
$panelTitle    = 'Meslek Önerilerim';
$panelSubtitle = 'Kariyer profilinize göre sıralanan meslek önerileri';
$panelActions  = '<a href="' . url('career-test.php') . '" class="btn btn-accent btn-sm">' . icon('refresh-cw', '', 16) . ' Testi Yenile</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<?php if (!$results): ?>
  <section class="panel-card">
    <div class="pc-body">
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('sparkles', '', 28) ?></div>
        <h3>Henüz meslek öneriniz yok</h3>
        <p>Kariyer testini tamamlayın; ilgi alanlarınız ve yetenekleriniz analiz edilerek size özel meslek önerileri oluşturulur.</p>
        <a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('play', '', 17) ?> Testi Başlat</a>
      </div>
    </div>
  </section>
<?php else: ?>

  <?php if (count($results) > 1): ?>
    <section class="panel-card" style="margin-bottom:20px">
      <div class="pc-head">
        <div><h2>Sonuç Dönemi Seçin</h2><p class="pc-sub">Farklı testlerinizin önerilerini karşılaştırın</p></div>
      </div>
      <div class="pc-body">
        <div class="flex wrap gap-10">
          <?php foreach ($results as $r): ?>
            <a href="<?= url('dashboard/recommendations.php?result=' . (int) $r['id']) ?>"
               class="btn <?= (int) $r['id'] === $selectedResultId ? 'btn-accent' : 'btn-outline' ?> btn-sm">
              <?= format_date($r['completed_at'], 'd M Y') ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($detail): ?>
    <section class="panel-card" style="margin-bottom:20px">
      <div class="pc-head">
        <div>
          <h2>Profil Özeti</h2>
          <p class="pc-sub"><?= format_date($detail['completed_at'], 'd F Y H:i') ?> • <?= e($detail['dominant_type']) ?></p>
        </div>
        <div class="flex gap-8">
          <a href="<?= url('test-result.php?session=' . (int) $detail['session_id']) ?>" class="btn btn-outline btn-sm"><?= icon('bar-chart-3', '', 15) ?> Detaylı Rapor</a>
          <a href="<?= url('api/result-pdf.php?session=' . (int) $detail['session_id']) ?>" class="btn btn-outline btn-sm"><?= icon('file-down', '', 15) ?> PDF</a>
        </div>
      </div>
      <div class="pc-body">
        <div class="alert alert-info" style="margin:0">
          <?= icon('info', '', 18) ?><span><?= e($detail['summary']) ?></span>
        </div>
      </div>
    </section>

    <section class="panel-card">
      <div class="pc-head">
        <div><h2>Sıralı Meslek Önerileri</h2><p class="pc-sub"><?= count($recommendations) ?> meslek, uyum puanına göre sıralandı</p></div>
      </div>
      <div class="pc-body">
        <?php foreach ($recommendations as $i => $rec): ?>
          <?php $comp = $rec['components']; ?>
          <div class="rec-row" style="align-items:flex-start">
            <span class="rr-rank <?= $i < 3 ? 'top' : '' ?>"><?= $i + 1 ?></span>
            <span class="career-thumb" style="width:54px;height:54px"><?= career_art($rec) ?></span>
            <div class="rr-info">
              <div class="rr-title"><?= e($rec['name']) ?></div>
              <div class="rr-meta">
                <span><?= icon('layers', '', 13) ?> <?= e($rec['category_name']) ?></span>
                <span><?= icon('graduation-cap', '', 13) ?> <?= e(education_label($rec['education_level'])) ?></span>
                <?php if (!empty($rec['salary_range'])): ?>
                  <span><?= icon('wallet', '', 13) ?> <?= e($rec['salary_range']) ?></span>
                <?php endif; ?>
              </div>
              <p style="font-size:.85rem;color:var(--c-muted);margin:9px 0 0;line-height:1.6"><?= e($rec['reason']) ?></p>
              <div class="flex wrap gap-8" style="margin-top:10px">
                <?php
                $labels = ['interest' => 'İlgi', 'ability' => 'Yetenek', 'preference' => 'Tercih', 'environment' => 'Ortam', 'skill' => 'Beceri'];
                foreach ($labels as $key => $label):
                    $val = (float) ($comp[$key . '_score'] ?? 0);
                ?>
                  <span class="badge <?= $val >= 70 ? 'badge-success' : ($val >= 50 ? 'badge-warning' : 'badge-muted') ?>">
                    <?= e($label) ?> %<?= number_format_tr($val) ?>
                  </span>
                <?php endforeach; ?>
              </div>
              <div class="match-bar" style="margin-top:10px;max-width:320px">
                <span style="width:<?= min(100, (float) $rec['match_score']) ?>%"></span>
              </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;flex-shrink:0">
              <div class="rr-score">
                <div class="rrs-val">%<?= number_format_tr((float) $rec['match_score']) ?></div>
                <div class="rrs-lab">Uyum</div>
              </div>
              <a href="<?= url('career-detail.php?slug=' . urlencode($rec['slug'])) ?>" class="btn btn-primary btn-sm">İncele</a>
              <button type="button" class="fav-btn <?= !empty($rec['is_favorite']) ? 'is-favorite' : '' ?>" data-fav="<?= (int) $rec['career_id'] ?>">
                <?= icon('heart', '', 15) ?>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
