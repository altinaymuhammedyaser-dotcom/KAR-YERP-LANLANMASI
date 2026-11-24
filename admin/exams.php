<?php
/**
 * Akilli Kariyer - Admin: Sinav Yonetimi
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireAdmin();

$panelType     = 'admin';
$panelActive   = 'exams';
$panelTitle    = 'Sınav Yönetimi';
$panelSubtitle = 'Sınavları oluşturun, düzenleyin ve yayınlayın';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2>Sınavlar</h2>
            <p class="pc-sub">Kullanıcılar için sınavlar oluşturabilir, düzenleyebilir veya pasif hale getirebilirsiniz.</p>
        </div>
        <a href="#" class="btn btn-accent btn-sm" id="addExamBtn"><?= icon('plus', '', 16) ?> Yeni Sınav Ekle</a>
    </div>

    <div class="pc-body">
        <div id="addExamForm" style="display:none;margin-bottom:20px">
            <form method="POST" action="admin/exam-save.php" class="panel-form">
                <div class="px-form-row">
                    <label>Sınav Adı</label><input type="text" name="title" required placeholder="Örn: Temel Beceriler Sınavı">
                    <label>Kategori</label><input type="text" name="category" placeholder="Örn: Teknoloji">
                </div>
                <div class="px-form-row">
                    <label>Açıklama</label><textarea name="description" rows="2" placeholder="Sınav açıklaması"></textarea>
                    <label>Süre (dakika)</label><input type="number" name="time_limit_minutes" value="30" min="1">
                </div>
                <div class="px-form-row">
                    <label>Başarı Skoru (%)</label><input type="number" name="passing_score" value="70" min="1" max="100">
                    <label style="display:flex;align-items:center;gap:8px">
                        <input type="checkbox" name="certificate" value="1" checked> Sertifika Verilsin
                    </label>
                </div>
                <div class="panel-actions" style="margin-top:8px">
                    <button type="submit" class="btn btn-accent">Sınavı Oluştur</button>
                    <button type="button" class="btn btn-outline" id="cancelExam">İptal</button>
                </div>
            </form>
        </div>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sınav Adı</th>
                        <th>Kategori</th>
                        <th>Soru</th>
                        <th>Süre</th>
                        <th>Başarı</th>
                        <th>Sertifika</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $exams = \App\Core\Database::fetchAll("
                        SELECT e.*, COUNT(q.id) AS question_count
                        FROM exams e
                        LEFT JOIN exam_questions q ON q.exam_id = e.id
                        GROUP BY e.id
                        ORDER BY e.created_at DESC
                    ") ?: [];
                    foreach ($exams as $exam):
                    ?>
                    <tr>
                        <td class="td-nowrap"><strong><?= e($exam['title']) ?></strong></td>
                        <td><span class="badge badge-muted"><?= e($exam['category'] ?: 'Genel') ?></span></td>
                        <td class="td-nowrap"><?= (int) $exam['question_count'] ?> soru</td>
                        <td class="td-nowrap"><?= (int) $exam['time_limit_minutes'] ?> dakika</td>
                        <td class="td-nowrap">%<?= (int) $exam['passing_score'] ?></td>
                        <td class="td-nowrap"><?= $exam['certificate'] ? '<span class="badge badge-success">Evet</span>' : '<span class="badge badge-muted">Hayır</span>' ?></td>
                        <td class="td-nowrap"><?= $exam['is_active'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Pasif</span>' ?></td>
                        <td class="td-nowrap">
                            <a href="admin/exam-questions.php?id=<?= (int) $exam['id'] ?>" class="btn btn-outline btn-sm">Sorular</a>
                            <a href="admin/exam-save.php?id=<?= (int) $exam['id'] ?>&action=toggle" class="btn btn-<?= $exam['is_active'] ? 'danger' : 'success' ?> btn-sm"><?= $exam['is_active'] ? 'Pasif Yap' : 'Aktif Yap' ?></a>
                            <a href="admin/exam-save.php?id=<?= (int) $exam['id'] ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('Bu sınavı ve tüm sonuçlarını silmek istediğinizden emin misiniz?')">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$exams): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Henüz sınav yok.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>

<script>
    document.getElementById('addExamBtn').addEventListener('click', function () {
        var f = document.getElementById('addExamForm');
        f.style.display = f.style.display === 'none' ? 'block' : 'none';
    });
    document.getElementById('cancelExam').addEventListener('click', function () {
        document.getElementById('addExamForm').style.display = 'none';
    });
</script>
