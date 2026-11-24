<?php
/**
 * Akilli Kariyer - Panel: Iki sinavin karsilastirmasi
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$s1 = (int) ($_GET['s1'] ?? 0);
$s2 = (int) ($_GET['s2'] ?? 0);

if (!$s1 || !$s2 || $s1 === $s2) {
    header('Location: ' . url('dashboard/index.php'));
    exit;
}

// Iki oturumu da dogrula
$session1 = \App\Core\Database::fetch("SELECT * FROM test_sessions WHERE id = ? AND user_id = ?", [$s1, $userId]);
$session2 = \App\Core\Database::fetch("SELECT * FROM test_sessions WHERE id = ? AND user_id = ?", [$s2, $userId]);

if (!$session1 || !$session2) {
    header('Location: ' . url('dashboard/index.php'));
    exit;
}

// Her iki oturumun sorularini ve cevaplarini getir
$questions1 = \App\Core\Database::fetchAll("
    SELECT tq.id, tq.question_text, tq.question_type, ta.option_ids, ta.option_keys
    FROM test_questions tq
    LEFT JOIN test_answers ta ON ta.question_id = tq.id AND ta.session_id = ?
    WHERE tq.session_id = ?
    ORDER BY tq.sort_order ASC, tq.id ASC
", [$s1, $s1]) ?: [];

$questions2 = \App\Core\Database::fetchAll("
    SELECT tq.id, tq.question_text, tq.question_type, ta.option_ids, ta.option_keys
    FROM test_questions tq
    LEFT JOIN test_answers ta ON ta.question_id = tq.id AND ta.session_id = ?
    WHERE tq.session_id = ?
    ORDER BY tq.sort_order ASC, tq.id ASC
", [$s2, $s2]) ?: [];

// Soru metni eslestirme icin bir harita olustur (ayni soru metni = ayni soru)
$qMap1 = [];
foreach ($questions1 as $q) {
    $qMap1[$q['question_text']] = $q;
}
$qMap2 = [];
foreach ($questions2 as $q) {
    $qMap2[$q['question_text']] = $q;
}

$allQuestionTexts = array_unique(array_merge(array_keys($qMap1), array_keys($qMap2)));

$panelType     = 'user';
$panelActive   = 'results';
$panelTitle    = 'Sınav Karşılaştırması';
$panelSubtitle = 'İki farklı denemenizin doğru/yanlış karşılaştırması';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<style>
    .compare-wrap { max-width: 1100px; margin: 0 auto; }
    .compare-header { display:flex; gap:16px; margin-bottom:20px; }
    .compare-col { flex:1; border:1px solid var(--c-border); border-radius:16px; padding:18px; background:var(--c-bg-card,#fff); }
    .compare-col h3 { margin:0 0 8px; font-size:1.1rem; }
    .compare-score { font-size:1.75rem; font-weight:800; margin:6px 0; }
    .compare-score.pass { color:var(--c-success) }
    .compare-score.fail { color:var(--c-danger) }
    .compare-question { border:1px solid var(--c-border); border-radius:12px; padding:14px; margin-bottom:14px; background:var(--c-bg-card,#fff); }
    .compare-q-text { font-weight:700; color:var(--c-text); margin-bottom:10px; line-height:1.45; }
    .compare-answers { display:flex; gap:10px; }
    .compare-ans { flex:1; border-radius:10px; padding:10px; font-size:.82rem; text-align:center; }
    .compare-ans.correct { background:var(--c-success-soft); color:var(--c-success); border:1px solid var(--c-success) }
    .compare-ans.wrong { background:var(--c-danger-soft); color:var(--c-danger); border:1px solid var(--c-danger) }
    .compare-ans.empty { background:var(--c-bg); color:var(--c-muted); border:1px dashed var(--c-border) }
    .compare-ans .label { display:block; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px }
    .compare-ans .val { font-size:1rem; font-weight:700 }
    .compare-same { border-left:4px solid var(--c-accent) }
    .compare-diff { border-left:4px solid var(--c-danger) }
    .compare-stats { display:flex; gap:16px; margin-bottom:20px; }
    .compare-stat { flex:1; background:var(--c-bg-card,#fff); border:1px solid var(--c-border); border-radius:14px; padding:16px; text-align:center }
    .compare-stat .big { font-size:1.9rem; font-weight:800; color:var(--c-primary) }
    .compare-stat .small { font-size:.75rem; color:var(--c-muted) }
</style>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2>Sınav Karşılaştırması</h2>
            <p class="pc-sub">İki farklı denemenizin doğru/yanlış karşılaştırması</p>
        </div>
        <a href="dashboard/results.php" class="btn btn-outline btn-sm">Test Sonuçlarına Dön</a>
    </div>

    <div class="pc-body">
        <div class="compare-stats">
            <div class="compare-stat">
                <div class="big"><?= (int) $session1['score'] ?>%</div>
                <div class="small"><?= e($session1['title']) ?></div>
            </div>
            <div class="compare-stat" style="border-left:1px solid var(--c-border)">
                <div class="big" style="color:var(--c-accent-dark)"><?= (int) $session2['score'] ?>%</div>
                <div class="small" style="color:var(--c-accent-dark)">2. Deneme</div>
            </div>
            <?php
            $diff = abs((int) $session1['score'] - (int) $session2['score']);
            ?>
            <div class="compare-stat">
                <div class="big"><?= $diff ?>%</div>
                <div class="small">Puan Farkı</div>
            </div>
        </div>

        <div class="compare-header">
            <div class="compare-col">
                <h3><?= e($session1['title']) ?></h3>
                <div class="compare-score <?= $session1['passed'] ? 'pass' : 'fail' ?>">
                    %<?= (int) $session1['score'] ?> · <?= $session1['passed'] ? 'BAŞARILI' : 'BAŞARISIZ' ?>
                </div>
                <div class="text-muted text-sm"><?= format_date($session1['completed_at'], 'd F Y, H:i') ?></div>
            </div>
            <div class="compare-col">
                <h3>2. Deneme</h3>
                <div class="compare-score <?= $session2['passed'] ? 'pass' : 'fail' ?>">
                    %<?= (int) $session2['score'] ?> · <?= $session2['passed'] ? 'BAŞARILI' : 'BAŞARISIZ' ?>
                </div>
                <div class="text-muted text-sm"><?= format_date($session2['completed_at'], 'd F Y, H:i') ?></div>
            </div>
        </div>

        <h3 style="margin:24px 0 12px;color:var(--c-primary)">Soru Bazlı Karşılaştırma</h3>
        <div class="compare-wrap">
            <?php foreach ($allQuestionTexts as $qText): 
                $q1 = $qMap1[$qText] ?? null;
                $q2 = $qMap2[$qText] ?? null;
                $has1 = !empty($q1);
                $has2 = !empty($q2);
                $bothHave = $has1 && $has2;
                
                // Dogru mu?
                $q1Correct = $has1 && !empty($q1['option_ids']);
                $q2Correct = $has2 && !empty($q2['option_ids']);
                
                $sameResult = ($q1Correct === $q2Correct);
                ?>
                <div class="compare-question <?= $bothHave && !$sameResult ? 'compare-diff' : '' ?>">
                    <div class="compare-q-text"><?= e($qText) ?></div>
                    <div class="compare-answers">
                        <?php if ($has1): ?>
                        <div class="compare-ans <?= $q1Correct ? 'correct' : 'wrong' ?>">
                            <span class="label">1. Deneme</span>
                            <div class="val"><?= e(implode(', ', json_decode($q1['option_keys'] ?? '[]', true))) ?></div>
                            <div style="font-size:.65rem"><?= $q1Correct ? 'Doğru' : 'Yanlış' ?></div>
                        </div>
                        <?php else: ?>
                        <div class="compare-ans empty">
                            <span class="label">1. Deneme</span>
                            <div class="val">-</div>
                            <div style="font-size:.65rem">Yok</div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($has2): ?>
                        <div class="compare-ans <?= $q2Correct ? 'correct' : 'wrong' ?>">
                            <span class="label">2. Deneme</span>
                            <div class="val"><?= e(implode(', ', json_decode($q2['option_keys'] ?? '[]', true))) ?></div>
                            <div style="font-size:.65rem"><?= $q2Correct ? 'Doğru' : 'Yanlış' ?></div>
                        </div>
                        <?php else: ?>
                        <div class="compare-ans empty">
                            <span class="label">2. Deneme</span>
                            <div class="val">-</div>
                            <div style="font-size:.65rem">Yok</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($bothHave && !$sameResult): ?>
                    <div style="margin-top:8px;color:var(--c-danger);font-size:.75rem;font-weight:700">
                        ⚠ Farklı sonuç: 1. deneme <?= $q1Correct ? 'doğru' : 'yanlış' ?>, 2. deneme <?= $q2Correct ? 'doğru' : 'yanlış' ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="panel-actions" style="margin-top:24px">
            <a href="dashboard/results.php" class="btn btn-outline">Sonuçlara Dön</a>
            <a href="career-test.php" class="btn btn-accent">Yeni Test Başlat</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
