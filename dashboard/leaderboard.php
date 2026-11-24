<?php
/**
 * Akilli Kariyer - Panel: Liderlik Tablosu
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$panelType     = 'user';
$panelActive   = 'leaderboard';
$panelTitle    = 'Liderlik Tablosu';
$panelSubtitle = 'Diğer kullanıcılarla sınav skorlarınızı karşılaştırın';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2>Liderlik Tablosu</h2>
            <p class="pc-sub">En yüksek sınav skorlarına sahip kullanıcılar listelenir.</p>
        </div>
    </div>

    <div class="pc-body">
        <?php
        $leaders = \App\Core\Database::fetchAll("
            SELECT u.first_name, u.last_name, u.email, MAX(er.score) AS best_score,
                   MAX(er.passed) AS passed_count, COUNT(*) AS exam_count
            FROM exam_results er
            JOIN users u ON u.id = er.user_id
            WHERE er.passed = 1
            GROUP BY u.id
            ORDER BY best_score DESC, exam_count DESC
            LIMIT 20
        ") ?: [];
        if (!$leaders):
        ?>
        <div class="empty-state" style="text-align:center;padding:40px 20px">
            <div class="es-ico"><?= icon('trophy', '', 40) ?></div>
            <h3>Henüz liderlik tablosu boş</h3>
            <p>Sınavlardan başarıyla geçerek tabloya girebilirsiniz.</p>
            <a href="exams.php" class="btn btn-accent"><?= icon('clipboard-list', '', 17) ?> Sınavlara Git</a>
        </div>
        <?php else: ?>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Sıra</th><th>Kullanıcı</th><th>En İyi Skor</th><th>Geçtiği Sınav</th></tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ($leaders as $l): ?>
                    <tr>
                        <td class="td-nowrap"><strong style="color:var(--c-primary)"><?= $rank++ ?></strong></td>
                        <td class="td-nowrap"><strong><?= e($l['first_name'] . ' ' . $l['last_name']) ?></strong><br><span class="text-xs text-muted"><?= e($l['email']) ?></span></td>
                        <td class="td-nowrap"><span class="badge badge-success">%<?= (int) $l['best_score'] ?></span></td>
                        <td class="td-nowrap"><?= (int) $l['exam_count'] ?> sınav</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
