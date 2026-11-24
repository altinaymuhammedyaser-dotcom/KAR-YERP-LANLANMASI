<?php
/**
 * Akilli Kariyer - Panel: Sinava Basla
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$examId = (int) ($_GET['id'] ?? 0);
if (!$examId) {
    header('Location: ' . url('dashboard/exams.php'));
    exit;
}

$exam = \App\Core\Database::fetch("
    SELECT * FROM exams WHERE id = ? AND is_active = 1
", [$examId]);
if (!$exam) {
    $_SESSION['error'] = 'Sınav bulunamadı veya aktif değil.';
    header('Location: ' . url('dashboard/exams.php'));
    exit;
}

$questions = \App\Core\Database::fetchAll("
    SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY sort_order ASC, id ASC
", [$examId]);

$panelType     = 'user';
$panelActive   = 'exams';
$panelTitle    = 'Sınav: ' . e($exam['title']);
$panelSubtitle = 'Süre: ' . (int) $exam['time_limit_minutes'] . ' dakika · Başarı skoru: %' . (int) $exam['passing_score'];

require_once __DIR__ . '/../includes/panel-header.php';
?>

<style>
    .exam-wrap { max-width: 860px; margin: 0 auto; }
    .exam-progress { display:flex; gap:8px; margin-bottom:16px; }
    .exam-progress .step { flex:1; height:10px; border-radius:99px; background:var(--c-border); overflow:hidden; }
    .exam-progress .step .fill { height:100%; background:linear-gradient(90deg,#16B8B0,#1C466D); width:0%; transition:width .3s; }
    .exam-card { border:1px solid var(--c-border); border-radius:16px; background:var(--c-bg-card,#fff); padding:22px; margin-bottom:18px; }
    .exam-q { font-size:1.08rem; font-weight:700; color:var(--c-text); margin-bottom:14px; line-height:1.5; }
    .exam-q .badge-q { display:inline-block; background:var(--c-accent-soft); color:var(--c-accent-dark); border-radius:9px; padding:4px 10px; font-size:.75rem; font-weight:800; margin-right:8px; }
    .exam-options { display:grid; gap:9px; margin-top:10px; }
    .exam-opt { border:1.5px solid var(--c-border); border-radius:12px; background:var(--c-bg); padding:12px 14px; cursor:pointer; transition:.2s; display:flex; align-items:center; gap:10px; font-size:.88rem; color:var(--c-text); }
    .exam-opt:hover { border-color:var(--c-acnet,#16B8B0); transform:translateY(-2px); }
    .exam-opt input { margin:0; }
    .exam-opt.selected { border-color:var(--c-accent); background:linear-gradient(145deg,var(--c-accent-soft),transparent); }
    .exam-timer { font-size:.9rem; color:var(--c-accent-dark); font-weight:800; background:var(--c-accent-soft); padding:8px 14px; border-radius:10px; display:inline-block; margin-bottom:14px; }
    .exam-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:22px; }
    .exam-actions .btn { min-width:140px; }
</style>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2><?= e($exam['title']) ?></h2>
            <p class="pc-sub"><?= e($exam['description'] ?: 'Sınavı tamamlayarak puan kazanın ve sertifika edinin.') ?></p>
        </div>
    </div>

    <div class="pc-body">
        <div class="exam-wrap">
            <div class="exam-progress" id="examProgress">
                <?php for ($i = 1; $i <= count($questions); $i++): ?>
                    <div class="step"><div class="fill" id="fill<?= $i ?>" style="width:0%"></div></div>
                <?php endfor; ?>
            </div>
            <div class="exam-timer" id="examTimer">Süre: <?= (int) $exam['time_limit_minutes'] ?>:00</div>

            <form id="examForm" method="POST" action="dashboard/exam-result.php">
                <input type="hidden" name="exam_id" value="<?= (int) $exam['id'] ?>">
                <?php foreach ($questions as $qi => $q): ?>
                    <div class="exam-card" id="qCard<?= $q['id'] ?>">
                        <div class="exam-q"><span class="badge-q">Soru <?= $qi + 1 ?></span><?= e($q['question']) ?></div>
                        <div class="exam-options">
                            <?php
                            $options = json_decode($q['options'] ?? '[]', true) ?: [];
                            $isMulti = $q['type'] === 'multiple';
                            $name = $isMulti ? "answers[{$q['id']}][]" : "answers[{$q['id']}]";
                            foreach ($options as $oi => $opt):
                                $checked = ''; // Başlangıçta hiç seçili değil
                            ?>
                            <label class="exam-opt">
                                <input type="<?= $isMulti ? 'checkbox' : 'radio' ?>" name="<?= htmlspecialchars($name) ?>" value="<?= e($opt) ?>" required>
                                <span><?= e($opt) ?></span>
                            </label>
                            <?php endforeach; ?>
                            <?php if ($q['type'] === 'true_false'): ?>
                                <label class="exam-opt">
                                    <input type="radio" name="<?= htmlspecialchars($name) ?>" value="Doğru" required> <span>Doğru</span>
                                </label>
                                <label class="exam-opt">
                                    <input type="radio" name="<?= htmlspecialchars($name) ?>" value="Yanlış" required> <span>Yanlış</span>
                                </label>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted text-xs mt-2" style="display:none" id="qErr<?= $q['id'] ?>">Lütfen bu soruyu cevaplayın.</div>
                    </div>
                <?php endforeach; ?>

                <div class="exam-actions">
                    <a href="exams.php" class="btn btn-outline">İptal Et</a>
                    <button type="submit" class="btn btn-accent" id="submitExam"><?= icon('play', '', 16) ?> Sınavı Bitir</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    const totalSeconds = <?= (int) $exam['time_limit_minutes'] * 60 ?>;
    let remaining = totalSeconds;
    const timerEl = document.getElementById('examTimer');
    const fills = document.querySelectorAll('.exam-progress .fill');
    const qCount = <?= count($questions) ?>;

    function updateTimer() {
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        timerEl.textContent = 'Süre: ' + m + ':' + (s < 10 ? '0' : '') + s;
        const pct = Math.round(((totalSeconds - remaining) / totalSeconds) * 100);
        fills.forEach((f, idx) => {
            const target = Math.round(((idx + 1) / qCount) * 100);
            f.style.width = (pct >= target ? target : Math.floor(pct * ((idx + 1) / qCount))) + '%';
        });
        if (remaining <= 0) {
            document.getElementById('examForm').submit();
        } else {
            remaining--;
            setTimeout(updateTimer, 1000);
        }
    }
    updateTimer();

    document.getElementById('examForm').addEventListener('submit', function (e) {
        const inputs = this.querySelectorAll('input:checked');
        if (inputs.length < <?= count($questions) * 0.5 ?>) {
            e.preventDefault();
            alert('Lütfen en az yarısını cevaplayın.');
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
