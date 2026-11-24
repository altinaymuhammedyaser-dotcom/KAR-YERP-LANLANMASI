<?php
/**
 * Akilli Kariyer - Panel: Sinav Sonucu
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('dashboard/exams.php'));
    exit;
}

$examId = (int) ($_POST['exam_id'] ?? 0);
$exam   = \App\Core\Database::fetch("SELECT * FROM exams WHERE id = ? AND is_active = 1", [$examId]);
if (!$exam) {
    header('Location: ' . url('dashboard/exams.php'));
    exit;
}

$questions = \App\Core\Database::fetchAll("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY sort_order ASC, id ASC", [$examId]);

$totalPoints = 0;
$earnedPoints = 0;
$results = [];
foreach ($questions as $q) {
    $totalPoints += (int) $q['points'];
    $correct = json_decode($q['correct_answer'] ?? '[]', true) ?: [];
    $userAns = $_POST['answers'][$q['id']] ?? [];
    if (!is_array($userAns)) $userAns = [$userAns];
    sort($userAns); sort($correct);
    $isCorrect = $userAns === $correct;
    if ($isCorrect) $earnedPoints += (int) $q['points'];
    $results[] = [
        'id' => $q['id'],
        'question' => $q['question'],
        'user' => implode(', ', $userAns),
        'correct' => implode(', ', $correct),
        'isCorrect' => $isCorrect,
        'points' => (int) $q['points'],
    ];
}

$score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
$passed = $score >= (int) $exam['passing_score'];

$certificateCode = null;
if ($passed && (int) $exam['certificate']) {
    $certificateCode = strtoupper(substr(md5((string) $userId . $examId . microtime(true)), 0, 12));
    \App\Core\Database::execute(
        "INSERT INTO exam_results (user_id, exam_id, score, passed, answers, finished_at) VALUES (?, ?, ?, ?, ?, NOW())",
        [$userId, $examId, $score, (int) $passed, json_encode($results, JSON_UNESCAPED_UNICODE)]
    );
    $resultId = (int) \App\Core\Database::fetchColumn("SELECT LAST_INSERT_ID()");
    \App\Core\Database::execute(
        "INSERT INTO certificates (user_id, exam_id, certificate_code) VALUES (?, ?, ?)",
        [$userId, $examId, $certificateCode]
    );
    \App\Core\Database::execute(
        "INSERT INTO notifications (user_id, title, message, type, url) VALUES (?, ?, ?, 'success', ?)",
        [$userId, 'Sertifika Kazandınız!', '"' . e($exam['title']) . '" sınavından sertifika elde ettiniz.', '/dashboard/certificates.php']
    );
} else {
    \App\Core\Database::execute(
        "INSERT INTO exam_results (user_id, exam_id, score, passed, answers, finished_at) VALUES (?, ?, ?, ?, ?, NOW())",
        [$userId, $examId, $score, (int) $passed, json_encode($results, JSON_UNESCAPED_UNICODE)]
    );
    if (!$passed) {
        \App\Core\Database::execute(
            "INSERT INTO notifications (user_id, title, message, type, url) VALUES (?, ?, ?, 'warning', ?)",
            [$userId, 'Sınav Tamamlandı', '"' . e($exam['title']) . '" sınavından geçemediniz. Tekrar deneyebilirsiniz.', '/dashboard/exams.php']
        );
    }
}

$panelType     = 'user';
$panelActive   = 'exams';
$panelTitle    = 'Sınav Sonucu';
$panelSubtitle = e($exam['title']) . ' · %' . $score . ' başarı';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2><?= e($exam['title']) ?> – Sonuç</h2>
            <p class="pc-sub">Toplam puan: <strong><?= $earnedPoints ?> / <?= $totalPoints ?></strong> · Genel başarı: <strong style="color:<?= $passed ? 'var(--c-success)' : 'var(--c-danger)' ?>"><?= $passed ? 'BAŞARILI' : 'BAŞARISIZ' ?></strong></p>
        </div>
    </div>

    <div class="pc-body">
        <div class="panel-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:18px">
            <div class="panel-card" style="text-align:center">
                <div style="width:88px;height:88px;border-radius:50%;display:grid;place-items:center;margin:0 auto 10px;font-size:2rem;font-weight:800;color:<?= $passed ? 'var(--c-success)' : 'var(--c-danger)' ?>;border:8px solid <?= $passed ? 'var(--c-success-soft)' : 'var(--c-danger-soft)' ?>">
                    %<?= (int) $score ?>
                </div>
                <div class="text-muted text-sm">Genel Başarı</div>
            </div>
            <div class="panel-card" style="text-align:center">
                <div style="font-size:1.5rem;font-weight:800;color:var(--c-primary)"><?= $earnedPoints ?></div>
                <div class="text-muted text-sm">Aldığınız Puan</div>
            </div>
            <div class="panel-card" style="text-align:center">
                <div style="font-size:1.5rem;font-weight:800;color:var(--c-primary)"><?= $totalPoints ?></div>
                <div class="text-muted text-sm">Maksimum Puan</div>
            </div>
            <?php if ($passed && $certificateCode): ?>
            <div class="panel-card" style="text-align:center">
                <div style="font-size:1.5rem;font-weight:800;color:var(--c-success)"><?= $certificateCode ?></div>
                <div class="text-muted text-sm">Sertifika Kodu</div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($passed && $certificateCode): ?>
        <div class="panel-card" style="border:2px solid var(--c-success);background:var(--c-success-soft)">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:2rem">🎓</span>
                <div>
                    <strong style="color:var(--c-success-dark,#0E918B)">Tebrikler! Sertifika Kazandınız</strong>
                    <div class="text-muted text-sm" style="margin-top:4px">Sınav: <?= e($exam['title']) ?> · Kod: <code style="color:var(--c-success-dark,#0E918B)"><?= $certificateCode ?></code></div>
                </div>
                <a href="certificate-view.php?id=<?= (int) \App\Core\Database::fetchColumn("SELECT MAX(id) FROM certificates WHERE user_id = ? AND exam_id = ?", [$userId, $examId]) ?>" class="btn btn-accent">Sertifikayı Görüntüle</a>
            </div>
        </div>
        <?php elseif (!$passed): ?>
        <div class="panel-card" style="border:2px solid var(--c-danger);background:var(--c-danger-soft)">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:2rem">⚠️</span>
                <div>
                    <strong style="color:var(--c-danger-dark,#B83E34)">Üzgünüz, Geçemediniz</strong>
                    <div class="text-muted text-sm" style="margin-top:4px">Başarı skoru: %<?= (int) $exam['passing_score'] ?>. Tekrar deneyebilirsiniz.</div>
                </div>
                <a href="take-exam.php?id=<?= (int) $examId ?>" class="btn btn-outline">Tekrar Sınava Gır</a>
            </div>
        </div>
        <?php endif; ?>

        <h3 style="margin:24px 0 10px;color:var(--c-primary)">Cevap Analizi</h3>
        <div class="data-table-wrap" style="max-height:420px;overflow:auto">
            <table class="data-table">
                <thead>
                    <tr><th style="width:48%">Soru</th><th style="width:26%">Cevabınız</th><th style="width:26%">Doğru Cevap</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                    <tr>
                        <td class="td-nowrap"><?= e($r['question']) ?></td>
                        <td class="<?= $r['isCorrect'] ? 'text-success' : 'text-danger' ?>"><strong><?= e($r['user'] ?: 'Cevaplanmadı') ?></strong></td>
                        <td class="text-success"><strong><?= e($r['correct']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="panel-actions" style="margin-top:18px">
            <a href="exams.php" class="btn btn-outline">Sınavlara Dön</a>
            <?php if ($passed): ?>
                <a href="certificates.php" class="btn btn-accent">Tüm Sertifikalarım</a>
            <?php else: ?>
                <a href="take-exam.php?id=<?= (int) $examId ?>" class="btn btn-accent">Tekrar Dene</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
