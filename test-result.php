<?php
/**
 * Akilli Kariyer - Test sonuc ekrani
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Models\Result;
use App\Services\CareerMatchingService;
use App\Services\RecommendationService;

$user = Auth::user();
if (!$user) {
    Auth::redirectLogin();
}

$userId = (int) $user->id;

$sessionId = (int) ($_GET['session'] ?? 0);
$resultId  = (int) ($_GET['result'] ?? 0);

if ($resultId > 0) {
    $result = Result::find($resultId);
} else {
    $result = $sessionId > 0 ? Result::findBySession($sessionId) : Result::latestForUser($userId);
}

if (!$result || (int) $result['user_id'] !== $userId) {
    abort_page(404, 'Sonuç Bulunamadı', 'İstediğiniz test sonucu bulunamadı. Panelinizden tekrar deneyebilirsiniz.', url('dashboard/results.php'));
}

$detail = RecommendationService::resultDetail((int) $result['id']);
$recommendations = $detail['recommendations'];

$matcher  = new CareerMatchingService();
$scores   = [
    'interest'    => $detail['interest_scores'],
    'ability'     => $detail['ability_scores'],
    'preference'  => $detail['preference_scores'],
    'environment' => $detail['environment_scores'],
];
$assessment = $matcher->buildAssessment($scores, array_map(static fn($r) => [
    'career' => $r,
    'components' => $r['components'],
], $recommendations));

/**
 * Bir boyut dizisini grafik icin siralar.
 */
$graphData = static function (array $values, array $labels): array {
    $keys = array_keys($values);
    usort($keys, static fn($a, $b) => ($values[$b] ?? 0) <=> ($values[$a] ?? 0));
    $out = ['labels' => [], 'data' => []];
    foreach ($keys as $k) {
        $out['labels'][] = $labels[$k] ?? $k;
        $out['data'][]   = round(($values[$k] ?? 0) * 100, 1);
    }
    return $out;
};

$interestChart = $graphData($detail['interest_scores'], CareerMatchingService::INTEREST_LABELS);
$abilityChart  = $graphData($detail['ability_scores'], CareerMatchingService::ABILITY_LABELS);
$prefChart     = $graphData($detail['preference_scores'], CareerMatchingService::PREFERENCE_LABELS);
$envChart      = $graphData($detail['environment_scores'], CareerMatchingService::ENVIRONMENT_LABELS);

$topFive = array_slice($recommendations, 0, 5);
$chartLabels = array_map(static fn($r) => $r['name'], $topFive);
$chartData   = array_map(static fn($r) => round((float) $r['match_score'], 1), $topFive);

$pageTitle       = 'Kariyer Profilin Hazır — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Kariyer testiniz tamamlandı. Size uygun meslek önerileri ve kişisel değerlendirmeniz hazır.';
$activeNav       = 'test';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">

  <!-- ============================ SONUÇ BAŞLIĞI ============================ -->
  <section class="result-hero">
    <div class="container">
      <div class="flex between wrap gap-24" style="align-items:center">
        <div>
          <span class="eyebrow light"><?= icon('sparkles', '', 15) ?> Analiz Tamamlandı</span>
          <h1>Senin Kariyer Profilin Hazır!</h1>
          <p style="font-size:1.05rem;max-width:640px"><?= e($result['summary']) ?></p>
          <div class="result-meta">
            <span><?= icon('user', '', 16) ?> <?= e($user->fullName()) ?></span>
            <span><?= icon('calendar', '', 16) ?> <?= format_date($result['completed_at']) ?></span>
            <span><?= icon('clipboard-list', '', 16) ?> <?= (int) ($detail['total_questions'] ?? 0) ?> soru</span>
            <span><?= icon('target', '', 16) ?> <?= e($result['dominant_type']) ?></span>
          </div>
        </div>
        <div class="flex wrap gap-10">
          <a href="<?= url('api/result-pdf.php?session=' . (int) $result['session_id']) ?>" class="btn btn-accent">
            <?= icon('file-down', '', 18) ?> PDF İndir
          </a>
          <a href="<?= url('dashboard/results.php') ?>" class="btn btn-light">
            <?= icon('folder-open', '', 18) ?> Hesabımda Sakla
          </a>
          <a href="<?= url('career-test.php?restart=1') ?>" class="btn btn-light">
            <?= icon('refresh-cw', '', 18) ?> Testi Yeniden Yap
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">

      <!-- ÖZET KARTLAR -->
      <div class="grid g-4" style="margin-bottom:34px">
        <div class="stat-card">
          <span class="sc-icon accent"><?= icon('sparkles', '', 24) ?></span>
          <div>
            <div class="sc-value"><?= count($recommendations) ?></div>
            <div class="sc-label">Meslek Önerisi</div>
          </div>
        </div>
        <div class="stat-card">
          <span class="sc-icon success"><?= icon('target', '', 24) ?></span>
          <div>
            <div class="sc-value">%<?= number_format_tr((float) ($recommendations[0]['match_score'] ?? 0)) ?></div>
            <div class="sc-label">En Yüksek Uyum</div>
          </div>
        </div>
        <div class="stat-card">
          <span class="sc-icon"><?= icon('brain', '', 24) ?></span>
          <div>
            <div class="sc-value"><?= e($result['dominant_type']) ?></div>
            <div class="sc-label">Dominant Profil</div>
          </div>
        </div>
        <div class="stat-card">
          <span class="sc-icon warning"><?= icon('clock', '', 24) ?></span>
          <div>
            <div class="sc-value"><?= format_date($result['completed_at'], 'H:i') ?></div>
            <div class="sc-label">Tamamlanma Saati</div>
          </div>
        </div>
      </div>

      <!-- EN UYGUN 5 MESHLEK -->
      <div class="section-head left" style="margin-bottom:26px">
        <span class="eyebrow"><?= icon('award', '', 15) ?> Sıralı Öneriler</span>
        <h2 style="margin-bottom:8px">Size En Uygun 5 Meslek</h2>
        <p>İlgi alanı, yetenek, çalışma tercihi ve beceri bileşenlerinin ağırlıklı ortalamasıyla sıralandı.</p>
      </div>

      <div class="grid" style="gap:16px;margin-bottom:44px">
        <?php foreach ($topFive as $i => $rec): ?>
          <article class="match-card">
            <div class="mc-rank">
              <span class="rnum">#<?= $i + 1 ?></span>
              <span class="rlabel">Sıra</span>
            </div>
            <div class="mc-main">
              <div class="mc-head">
                <div class="flex gap-14" style="min-width:0;flex:1">
                  <span class="career-thumb" style="width:58px;height:58px;border-radius:14px"><?= career_art($rec) ?></span>
                  <div style="min-width:0">
                    <h3 class="mc-title">
                      <a href="<?= url('career-detail.php?slug=' . urlencode($rec['slug'])) ?>" style="color:inherit"><?= e($rec['name']) ?></a>
                    </h3>
                    <div class="flex wrap gap-8" style="margin-top:6px">
                      <span class="badge"><?= e($rec['category_name']) ?></span>
                      <span class="badge badge-muted"><?= icon('graduation-cap', '', 12) ?> <?= e(education_label($rec['education_level'])) ?></span>
                      <?php if (!empty($rec['salary_range'])): ?>
                        <span class="badge badge-success"><?= icon('wallet', '', 12) ?> <?= e($rec['salary_range']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="match-score">
                  <div class="ms-val">%<?= number_format_tr((float) $rec['match_score']) ?></div>
                  <div class="ms-lab">Uyum Puanı</div>
                </div>
              </div>

              <div class="match-bar"><span style="width:<?= min(100, (float) $rec['match_score']) ?>%"></span></div>

              <div class="match-breakdown">
                <?php
                $comp = $rec['components'];
                $labels = ['interest' => 'İlgi', 'ability' => 'Yetenek', 'preference' => 'Tercih', 'environment' => 'Ortam', 'skill' => 'Beceri'];
                foreach ($labels as $key => $label):
                    $val = (float) ($comp[$key . '_score'] ?? 0);
                ?>
                  <span class="badge <?= $val >= 70 ? 'badge-success' : ($val >= 50 ? 'badge-warning' : 'badge-muted') ?>">
                    <?= e($label) ?> %<?= number_format_tr($val) ?>
                  </span>
                <?php endforeach; ?>
              </div>

              <p class="mc-reason"><?= icon('quote', '', 15) ?> <?= e($rec['reason']) ?></p>

              <div class="flex wrap gap-10" style="margin-top:14px">
                <a href="<?= url('career-detail.php?slug=' . urlencode($rec['slug'])) ?>" class="btn btn-primary btn-sm">
                  <?= icon('arrow-right', '', 16) ?> Detayları Görüntüle
                </a>
                <button type="button" class="fav-btn <?= !empty($rec['is_favorite']) ? 'is-favorite' : '' ?>" data-fav="<?= (int) $rec['career_id'] ?>">
                  <?= icon('heart', '', 16) ?> <span><?= !empty($rec['is_favorite']) ? 'Favoride' : 'Favorilere Ekle' ?></span>
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- GRAFİKLER -->
      <div class="section-head left" style="margin-bottom:26px">
        <span class="eyebrow"><?= icon('bar-chart-3', '', 15) ?> Detaylı Analiz</span>
        <h2 style="margin-bottom:8px">Profil Grafikleri</h2>
        <p>Dört boyuttaki puan dağılımınız ve meslek uyumluluk karşılaştırması.</p>
      </div>

      <div class="grid g-2" style="margin-bottom:22px">
        <div class="chart-card">
          <h4>İlgi Alanı Dağılımı</h4>
          <p class="ch-sub">En yüksek ilgi alanınızdan başlayarak sıralandı.</p>
          <div class="chart-box">
            <canvas data-chart='<?= e(json_encode([
              'type' => 'bar',
              'data' => [
                'labels' => $interestChart['labels'],
                'datasets' => [[
                  'label' => 'İlgi Puanı',
                  'data' => $interestChart['data'],
                  'backgroundColor' => 'rgba(22,184,176,.85)',
                  'borderRadius' => 6,
                  'maxBarThickness' => 26,
                ]],
              ],
            ])) ?>'></canvas>
          </div>
        </div>

        <div class="chart-card">
          <h4>Yetenek Puanları</h4>
          <p class="ch-sub">Öne çıkan güçlü yanlarınız ve gelişim alanlarınız.</p>
          <div class="chart-box">
            <canvas data-chart='<?= e(json_encode([
              'type' => 'radar',
              'data' => [
                'labels' => $abilityChart['labels'],
                'datasets' => [[
                  'label' => 'Yetenek',
                  'data' => $abilityChart['data'],
                  'borderColor' => '#1C466D',
                  'backgroundColor' => 'rgba(28,70,109,.16)',
                  'pointBackgroundColor' => '#1C466D',
                  'pointRadius' => 3,
                ]],
              ],
              'options' => [
                'scales' => [
                  'r' => [
                    'min' => 0, 'max' => 100,
                    'grid' => ['color' => 'rgba(20,43,74,.10)'],
                    'angleLines' => ['color' => 'rgba(20,43,74,.10)'],
                    'pointLabels' => ['color' => '#748399', 'font' => ['size' => 11, 'weight' => '600']],
                    'ticks' => ['display' => false],
                  ],
                ],
              ],
            ])) ?>'></canvas>
          </div>
        </div>

        <div class="chart-card">
          <h4>Çalışma Tercihi Analizi</h4>
          <p class="ch-sub">Hangi çalışma biçimine daha yatkınsınız?</p>
          <div class="chart-box">
            <canvas data-chart='<?= e(json_encode([
              'type' => 'doughnut',
              'data' => [
                'labels' => $prefChart['labels'],
                'datasets' => [[
                  'label' => 'Tercih',
                  'data' => $prefChart['data'],
                ]],
              ],
            ])) ?>'></canvas>
          </div>
        </div>

        <div class="chart-card">
          <h4>Çalışma Ortamı Tercihi</h4>
          <p class="ch-sub">Sizi en çok motive eden ortamlar.</p>
          <div class="chart-box">
            <canvas data-chart='<?= e(json_encode([
              'type' => 'polarArea',
              'data' => [
                'labels' => $envChart['labels'],
                'datasets' => [[
                  'label' => 'Ortam',
                  'data' => $envChart['data'],
                ]],
              ],
            ])) ?>'></canvas>
          </div>
        </div>
      </div>

      <div class="chart-card" style="margin-bottom:44px">
        <h4>Meslek Uygunluk Karşılaştırması</h4>
        <p class="ch-sub">En uygun 5 mesleğin toplam uyum puanları.</p>
        <div class="chart-box" style="height:300px">
          <canvas data-chart='<?= e(json_encode([
            'type' => 'bar',
            'data' => [
              'labels' => $chartLabels,
              'datasets' => [[
                'label' => 'Uyum Puanı (%)',
                'data' => $chartData,
                'backgroundColor' => 'rgba(20,43,74,.88)',
                'borderRadius' => 8,
                'maxBarThickness' => 46,
              ]],
            ],
          ])) ?>'></canvas>
        </div>
      </div>

      <!-- KİŞİSEL DEĞERLENDİRME -->
      <div id="personal-assessment" class="section-head left" style="margin-bottom:26px">
        <span class="eyebrow"><?= icon('user-check', '', 15) ?> Kişisel Değerlendirme</span>
        <h2 style="margin-bottom:8px">Güçlü Yönlerin ve Gelişim Alanların</h2>
        <p>Sonuçların, cevaplarına dayanan bir öz değerlendirmedir. Öneriler, keşfetme fırsatı sunar.</p>
      </div>

      <div class="grid g-2" style="margin-bottom:22px">
        <div class="card">
          <div class="card-body">
            <h4 style="margin-bottom:16px"><?= icon('thumbs-up', '', 19) ?> Güçlü Yönlerin</h4>

            <div style="margin-bottom:20px">
              <span class="text-xs text-muted-c fw-700">Öne Çıkan İlgi Alanların</span>
              <div class="flex wrap gap-8" style="margin-top:9px">
                <?php foreach ($assessment['top_interests'] as $key): ?>
                  <span class="badge badge-accent"><?= e(CareerMatchingService::INTEREST_LABELS[$key] ?? $key) ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="divider"></div>

            <div>
              <span class="text-xs text-muted-c fw-700">Öne Çıkan Yeteneklerin</span>
              <div class="flex wrap gap-8" style="margin-top:9px">
                <?php foreach ($assessment['top_abilities'] as $key): ?>
                  <span class="badge badge-success"><?= e(CareerMatchingService::ABILITY_LABELS[$key] ?? $key) ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="divider"></div>

            <div>
              <span class="text-xs text-muted-c fw-700">Çalışma Tarzı Eğilimin</span>
              <div class="flex wrap gap-8" style="margin-top:9px">
                <?php foreach ($assessment['top_preferences'] as $key): ?>
                  <span class="badge badge-info"><?= e(CareerMatchingService::PREFERENCE_LABELS[$key] ?? $key) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h4 style="margin-bottom:16px"><?= icon('trending-up', '', 19) ?> Geliştirilebilecek Beceriler</h4>
            <div class="grid" style="gap:14px">
              <?php foreach ($assessment['improvements'] as $imp): ?>
                <div>
                  <div class="flex between center" style="margin-bottom:5px">
                    <strong style="color:var(--c-primary);font-size:.93rem"><?= e($imp['skill']) ?></strong>
                    <span class="badge badge-muted">%<?= $imp['score'] ?></span>
                  </div>
                  <p style="font-size:.85rem;margin:0;line-height:1.6"><?= e($imp['suggestion']) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="grid g-2">
        <div class="card">
          <div class="card-body">
            <h4 style="margin-bottom:16px"><?= icon('graduation-cap', '', 19) ?> Önerilen Eğitim Alanları</h4>
            <div class="grid" style="gap:10px">
              <?php foreach ($assessment['departments'] as $dept): ?>
                <div class="flex gap-10" style="align-items:center">
                  <span style="width:28px;height:28px;border-radius:9px;background:var(--c-light-blue);color:var(--c-secondary);display:grid;place-items:center;flex-shrink:0">
                    <?= icon('book-open', '', 14) ?>
                  </span>
                  <span style="font-size:.93rem;color:#40536B;font-weight:600"><?= e($dept) ?></span>
                </div>
              <?php endforeach; ?>
              <?php if (!$assessment['departments']): ?>
                <p style="font-size:.92rem;margin:0">Önerilen eğitim alanı bulunamadı.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h4 style="margin-bottom:16px"><?= icon('git-compare', '', 19) ?> Keşfedebileceğin Alternatifler</h4>
            <div class="grid" style="gap:11px">
              <?php foreach (array_slice($recommendations, 5, 5) as $alt): ?>
                <a href="<?= url('career-detail.php?slug=' . urlencode($alt['slug'])) ?>"
                   class="flex between center gap-12" style="padding:11px 13px;border:1px solid var(--c-border);border-radius:12px;text-decoration:none">
                  <span class="flex gap-10 center">
                    <span style="color:var(--c-secondary)"><?= icon($alt['icon'] ?? 'briefcase', '', 18) ?></span>
                    <span style="font-weight:700;color:var(--c-primary);font-size:.92rem"><?= e($alt['name']) ?></span>
                  </span>
                  <span class="badge badge-accent">%<?= number_format_tr((float) $alt['match_score']) ?></span>
                </a>
              <?php endforeach; ?>
              <?php if (count($recommendations) <= 5): ?>
                <p style="font-size:.92rem;margin:0">
                  Daha fazla alternatif için meslek kataloğunu inceleyebilirsiniz.
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- UYARI -->
      <div class="alert alert-info" style="margin-top:30px">
        <?= icon('info', '', 19) ?>
        <span>
          <strong>Önemli Not:</strong> Bu sonuçlar kesin bir meslek belirleme veya bilimsel kişilik tanısı
          değildir. Size ilgi alanlarınız, yetenekleriniz ve çalışma alışkanlıklarınız üzerinden
          keşif fırsatı sunan rehber niteliğindedir. Kararı vermeden önce mesleklerin detay sayfalarını,
          eğitim seçeneklerini ve çalışma koşullarını mutlaka inceleyin.
        </span>
      </div>

      <div class="flex wrap gap-12" style="margin-top:26px;justify-content:center">
        <a href="<?= url('dashboard/results.php') ?>" class="btn btn-primary"><?= icon('folder-open', '', 18) ?> Tüm Sonuçlarım</a>
        <a href="<?= url('dashboard/recommendations.php') ?>" class="btn btn-outline"><?= icon('sparkles', '', 18) ?> Önerilerim</a>
        <a href="<?= url('career-test.php?restart=1') ?>" class="btn btn-outline"><?= icon('refresh-cw', '', 18) ?> Testi Yeniden Yap</a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
