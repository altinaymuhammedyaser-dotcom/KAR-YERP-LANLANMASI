<?php
/**
 * Akilli Kariyer - Kariyer testi ekrani
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Test;
use App\Services\TestService;

$user = Auth::user();

if (!$user) {
    Auth::redirectLogin();
}

// YariM kalan oturum varsa onu kullan, yoksa yenisini baslat
$session = Test::activeSession((int) $user->id);
if (!$session) {
    try {
        $session = TestService::start((int) $user->id);
    } catch (\Throwable $e) {
        abort_page(500, 'Test Başlatılamadı', $e->getMessage(), url('dashboard/index.php'));
    }
}

$questions = Test::questions();
$answerMap = Test::answerMap((int) $session['id']);

// Kacirilan ilk sorudan devam et
$currentIndex = 0;
foreach ($questions as $i => $q) {
    if (!isset($answerMap[(int) $q['id']])) {
        $currentIndex = $i;
        break;
    }
    $currentIndex = $i + 1;
}
if ($currentIndex >= count($questions)) {
    $currentIndex = count($questions) - 1;
}

// API icin soru verisini hazirla
$clientQuestions = [];
foreach ($questions as $q) {
    $opts = [];
    foreach ($q['options'] as $o) {
        $opts[] = [
            'id'          => (int) $o['id'],
            'option_text' => $o['option_text'],
            'option_key'  => $o['option_key'],
            'score_value' => (int) $o['score_value'],
        ];
    }
    $clientQuestions[] = [
        'id'            => (int) $q['id'],
        'question_text' => $q['question_text'],
        'helper_text'   => $q['helper_text'],
        'question_type' => $q['question_type'],
        'min_select'    => (int) $q['min_select'],
        'max_select'    => (int) $q['max_select'],
        'category_name' => $q['category_name'],
        'category_icon' => $q['category_icon'],
        'options'       => $opts,
    ];
}

$clientAnswers = [];
foreach ($answerMap as $qid => $data) {
    $clientAnswers[(string) $qid] = [
        'option_ids'  => array_map('intval', $data['option_ids']),
        'option_keys' => $data['option_keys'],
    ];
}

$categories = Test::categories();
$answeredCount = count($answerMap);

$pageTitle       = 'Kariyer Testi — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'İlgi alanları, yetenekler, çalışma tercihleri ve çalışma ortamı boyutlarında size uygun meslekleri keşfedin.';
$activeNav       = 'test';
$bodyClass       = 'test-page';

$extraScripts = '<script src="' . asset('assets/js/test.js') . '"></script>';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content" class="section-sm">
  <div class="container">

    <div class="flex between wrap gap-16" style="margin-bottom:24px;align-items:flex-end">
      <div>
        <span class="eyebrow"><?= icon('clipboard-check', '', 15) ?> <?= count($questions) ?> Soruluk Değerlendirme</span>
        <h1 style="margin-bottom:6px">Kariyer Testi</h1>
        <p style="margin:0">Dürüst ve kendinizi olduğunuz gibi tanımlayarak cevaplayın. Sonuçlar tam da bu yansımanıza göre şekillenir.</p>
      </div>
      <div class="flex gap-10">
        <span class="badge badge-success"><?= icon('save', '', 14) ?> <span id="save-note-text">Cevaplar otomatik kaydediliyor</span></span>
      </div>
    </div>

    <div
      id="test-root"
      data-base="<?= e(BASE_PATH) ?>"
      data-csrf="<?= e(CSRF::token()) ?>"
      data-session="<?= (int) $session['id'] ?>"
      data-total="<?= count($questions) ?>"
      data-current="<?= (int) $currentIndex ?>"
      data-questions='<?= e(json_encode($clientQuestions, JSON_UNESCAPED_UNICODE)) ?>'
      data-answers='<?= e(json_encode($clientAnswers, JSON_UNESCAPED_UNICODE)) ?>'
    >
      <div class="test-layout">

        <!-- Sol panel: ilerleme -->
        <aside class="test-side">
          <h4>Test İlerlemesi</h4>

          <div class="progress-ring-wrap">
            <div class="progress-ring">
              <svg width="84" height="84" viewBox="0 0 84 84">
                <defs>
                  <linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#16B8B0"/>
                    <stop offset="100%" stop-color="#1C466D"/>
                  </linearGradient>
                </defs>
                <circle class="pr-bg" cx="42" cy="42" r="34" fill="none" stroke-width="8"/>
                <circle class="pr-fg" id="progress-ring" cx="42" cy="42" r="34" fill="none"
                        stroke-width="8" stroke-linecap="round"
                        style="stroke-dasharray:213.6;stroke-dashoffset:213.6"/>
              </svg>
              <div class="pr-label" id="progress-ring-label">0%</div>
            </div>
            <div class="ps-meta">
              <strong id="answered-count">0 / <?= count($questions) ?></strong>
              soru cevaplandı
            </div>
          </div>

          <div class="progress-bar"><span id="progress-bar" style="width:0%"></span></div>
          <div class="text-xs text-muted-c" style="margin-top:7px">Tamamlanma: <strong id="progress-label">%0</strong></div>

          <div class="test-cats">
            <?php foreach ($categories as $cat): ?>
              <div class="test-cat-item" data-category="<?= e($cat['name']) ?>">
                <span class="tci-dot"></span>
                <span><?= e($cat['name']) ?></span>
                <span style="margin-left:auto;font-size:.76rem"><?= (int) $cat['question_count'] ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="divider"></div>

          <a href="<?= url('dashboard/index.php') ?>" class="btn btn-outline btn-sm btn-block">
            <?= icon('arrow-left', '', 16) ?> Testten Çık
          </a>
          <p class="text-xs text-muted-c" style="margin-top:12px;text-align:center">
            Cevaplarınız otomatik kaydedilir. Çıktığınızda kaldığınız yerden devam edebilirsiniz.
          </p>
        </aside>

        <!-- Soru alanı -->
        <div id="question-area">
          <div class="question-card">
            <div style="display:grid;place-items:center;padding:50px 0">
              <div class="progress-ring">
                <svg width="84" height="84" viewBox="0 0 84 84">
                  <circle class="pr-bg" cx="42" cy="42" r="34" fill="none" stroke-width="8"/>
                  <circle class="pr-fg" cx="42" cy="42" r="34" fill="none" stroke="#16B8B0" stroke-width="8"
                          stroke-linecap="round" style="stroke-dasharray:213.6;stroke-dashoffset:160"/>
                </svg>
              </div>
              <p style="margin-top:16px">Sorular yükleniyor…</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
