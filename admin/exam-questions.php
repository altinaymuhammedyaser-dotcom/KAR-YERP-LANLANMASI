<?php
/**
 * Akilli Kariyer - Admin: Sinav Soru Yonetimi
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireAdmin();

$examId = (int) ($_GET['id'] ?? 0);
if (!$examId) {
    header('Location: ' . url('admin/exams.php'));
    exit;
}

$exam = \App\Core\Database::fetch("SELECT * FROM exams WHERE id = ?", [$examId]);
if (!$exam) {
    header('Location: ' . url('admin/exams.php'));
    exit;
}

$panelType     = 'admin';
$panelActive   = 'exam-q';
$panelTitle    = 'Sınav Soruları — ' . e($exam['title']);
$panelSubtitle = 'Bu sınav için soruları yönetebilirsiniz';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2><?= e($exam['title']) ?> — Sorular</h2>
            <p class="pc-sub"><?= e($exam['description'] ?: 'Sınav için soru ekleyin veya düzenleyin.') ?></p>
        </div>
        <a href="#" class="btn btn-accent btn-sm" id="addQuestionBtn"><?= icon('plus', '', 16) ?> Yeni Soru Ekle</a>
    </div>

    <div class="pc-body">
        <div id="addQuestionForm" style="display:none;margin-bottom:20px">
            <form method="POST" action="admin/exam-question-save.php" class="panel-form">
                <input type="hidden" name="exam_id" value="<?= (int) $examId ?>">
                <div class="px-form-row">
                    <label>Soru Metni</label><textarea name="question" rows="2" required placeholder="Örn: Hangisi veritabanı yönetim sistemi örneğidir?"></textarea>
                    <label>Soru Tipi</label>
                    <select name="type" class="input">
                        <option value="single">Tek Seçenekli</option>
                        <option value="multiple">Çok Seçenekli</option>
                        <option value="true_false">Doğru / Yanlış</option>
                    </select>
                </div>
                <div style="margin-bottom:10px">
                    <label style="display:block;font-size:.8rem;font-weight:700;color:var(--c-muted);margin-bottom:4px">Cevap Seçenekleri (virgülle ayırın)</label>
                    <input type="text" name="options" class="input" required placeholder="Örn: MySQL, Photoshop, Excel, Word">
                    <small class="text-muted">Tek seçenekli ve çok seçenekli sorular için virgülle ayrılmış seçenekler girin. Doğru/yanlış soruları boş bırakabilirsiniz.</small>
                </div>
                <div class="px-form-row">
                    <label>Doğru Cevap(lar)</label><input type="text" name="correct_answer" required placeholder="Örn: MySQL (çokluysa virgülle ayırın: MySQL, PHP)">
                    <label>Puan</label><input type="number" name="points" value="10" min="1">
                </div>
                <div class="panel-actions" style="margin-top:8px">
                    <button type="submit" class="btn btn-accent">Soruyu Ekle</button>
                    <button type="button" class="btn btn-outline" id="cancelQuestion">İptal</button>
                </div>
            </form>
        </div>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Soru</th>
                        <th>Tip</th>
                        <th>Seçenekler</th>
                        <th>Doğru Cevap</th>
                        <th>Puan</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $questions = \App\Core\Database::fetchAll("
                        SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY sort_order ASC, id ASC
                    ", [$examId]) ?: [];
                    foreach ($questions as $qi => $q):
                        $options = json_decode($q['options'] ?? '[]', true) ?: [];
                        $correct = json_decode($q['correct_answer'] ?? '[]', true) ?: [];
                        if (!is_array($correct)) $correct = [$correct];
                    ?>
                    <tr>
                        <td class="td-nowrap"><?= $qi + 1 ?></td>
                        <td class="td-nowrap"><?= e($q['question']) ?></td>
                        <td class="td-nowrap"><span class="badge badge-muted"><?= $q['type'] === 'single' ? 'Tek' : ($q['type'] === 'multiple' ? 'Çoklu' : 'Doğru/Yanlış') ?></span></td>
                        <td class="td-nowrap"><?= $q['type'] === 'true_false' ? 'Doğru / Yanlış' : implode(', ', $options) ?></td>
                        <td class="td-nowrap"><?= implode(', ', $correct) ?></td>
                        <td class="td-nowrap"><?= (int) $q['points'] ?></td>
                        <td class="td-nowrap">
                            <a href="admin/exam-question-save.php?id=<?= (int) $q['id'] ?>&exam_id=<?= (int) $examId ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('Bu soruyu silmek istediğinizden emin misiniz?')">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$questions): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Henüz soru yok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>

<script>
    document.getElementById('addQuestionBtn').addEventListener('click', function () {
        var f = document.getElementById('addQuestionForm');
        f.style.display = f.style.display === 'none' ? 'block' : 'none';
    });
    document.getElementById('cancelQuestion').addEventListener('click', function () {
        document.getElementById('addQuestionForm').style.display = 'none';
    });
</script>
