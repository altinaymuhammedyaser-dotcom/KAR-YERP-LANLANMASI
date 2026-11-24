<?php
/**
 * Akilli Kariyer - Panel: Sertifikalarim
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$panelType     = 'user';
$panelActive   = 'certificates';
$panelTitle    = 'Sertifikalarım';
$panelSubtitle = 'Kazandığınız sınav sertifikalarını görüntüleyin ve indirin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2>Sertifikalarım</h2>
            <p class="pc-sub">Başarılı olduğunuz sınavlardan elde ettiğiniz sertifikalar burada listelenir.</p>
        </div>
    </div>

    <div class="pc-body">
        <?php
        $certs = \App\Core\Database::fetchAll("
            SELECT c.*, e.title AS exam_title, e.category
            FROM certificates c
            JOIN exams e ON e.id = c.exam_id
            WHERE c.user_id = ?
            ORDER BY c.issued_at DESC
        ", [$userId]) ?: [];
        if (!$certs):
        ?>
        <div class="empty-state" style="text-align:center;padding:40px 20px">
            <div class="es-ico"><?= icon('award', '', 40) ?></div>
            <h3>Henüz sertifikanız yok</h3>
            <p>Sınavlardan başarıyla geçerek sertifika kazanabilirsiniz.</p>
            <a href="exams.php" class="btn btn-accent"><?= icon('clipboard-list', '', 17) ?> Sınavlara Git</a>
        </div>
        <?php else: ?>
        <div class="panel-grid" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
            <?php foreach ($certs as $cert): ?>
            <div class="panel-card certificate-card" style="position:relative;overflow:hidden;padding:22px">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
                    <div>
                        <h3 style="color:var(--c-primary);font-size:1.05rem;margin:0 0 4px"><?= e($cert['exam_title']) ?></h3>
                        <span class="badge badge-muted" style="font-size:.72rem"><?= e($cert['category'] ?: 'Genel') ?></span>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:1.1rem;font-weight:800;color:var(--c-primary)"><?= e($cert['certificate_code']) ?></div>
                        <div class="text-muted text-xs"><?= format_date($cert['issued_at'], 'd F Y') ?></div>
                    </div>
                </div>
                <p class="text-muted text-sm" style="margin-top:10px">Bu sertifika, başarıyla tamamladığınız sınavla verilmiştir.</p>
                <div class="panel-actions" style="margin-top:14px">
                    <a href="certificate-view.php?id=<?= (int) $cert['id'] ?>" class="btn btn-accent btn-sm">Görüntüle</a>
                    <a href="certificate-download.php?id=<?= (int) $cert['id'] ?>" class="btn btn-outline btn-sm"><?= icon('download', '', 16) ?> İndir</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
