<?php
/**
 * Akilli Kariyer - Panel: Sinavlar
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$panelType     = 'user';
$panelActive   = 'exams';
$panelTitle    = 'Sınavlar';
$panelSubtitle = 'Sınavları çözün, puan kazanın ve sertifika edinin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2>Mevcut Sınavlar</h2>
            <p class="pc-sub">Aşağıdaki sınavlardan birini seçerek bilginizi test edebilirsiniz.</p>
        </div>
        <a href="#" class="btn btn-accent btn-sm" id="refreshExams"><?= icon('refresh-cw', '', 16) ?> Yenile</a>
    </div>

    <div class="pc-body">
        <div class="data-table-wrap">
            <table class="data-table" id="examsTable">
                <thead>
                    <tr>
                        <th>Sınav Adı</th>
                        <th>Kategori</th>
                        <th>Soru</th>
                        <th>Süre</th>
                        <th>Başarı Skoru</th>
                        <th>Sertifika</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $exams = \App\Core\Database::fetchAll("
                        SELECT e.*, COUNT(q.id) AS question_count
                        FROM exams e
                        LEFT JOIN exam_questions q ON q.exam_id = e.id
                        WHERE e.is_active = 1
                        GROUP BY e.id
                        ORDER BY e.created_at DESC
                    ") ?: [];
                    foreach ($exams as $exam):
                        $hasResult = (int) \App\Core\Database::fetchColumn(
                            "SELECT COUNT(*) FROM exam_results WHERE user_id = ? AND exam_id = ?",
                            [$userId, $exam['id']]
                        );
                    ?>
                    <tr>
                        <td class="td-nowrap"><strong><?= e($exam['title']) ?></strong></td>
                        <td><span class="badge badge-muted"><?= e($exam['category'] ?: 'Genel') ?></span></td>
                        <td class="td-nowrap"><?= (int) $exam['question_count'] ?> soru</td>
                        <td class="td-nowrap"><?= (int) $exam['time_limit_minutes'] ?> dakika</td>
                        <td class="td-nowrap">%<?= (int) $exam['passing_score'] ?></td>
                        <td class="td-nowrap"><?= $exam['certificate'] ? '<span class="badge badge-success">Var</span>' : '<span class="badge badge-muted">Yok</span>' ?></td>
                        <td>
                            <?php if ($hasResult): ?>
                                <a href="exam-result.php?id=<?= (int) $exam['id'] ?>" class="btn btn-outline btn-sm">Sonucu Gör</a>
                            <?php else: ?>
                                <a href="take-exam.php?id=<?= (int) $exam['id'] ?>" class="btn btn-accent btn-sm">Sınava Başla</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$exams): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Henüz aktif sınav yok.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
