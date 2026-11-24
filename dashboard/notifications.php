<?php
/**
 * Akilli Kariyer - Panel: Bildirimler
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id) {
        \App\Core\Database::execute("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);
    }
    header('Location: ' . url('dashboard/notifications.php'));
    exit;
}

$notifications = \App\Core\Database::fetchAll("
    SELECT * FROM notifications WHERE user_id = ? ORDER BY is_read ASC, created_at DESC
", [$userId]) ?: [];

$panelType     = 'user';
$panelActive   = 'notifications';
$panelTitle    = 'Bildirimler';
$panelSubtitle = 'Sınav, sertifika ve sistem bildirimleriniz';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2>Bildirimler</h2>
            <p class="pc-sub">Sınav sonuçları, sertifikalar ve sistem güncellemeleri burada listelenir.</p>
        </div>
    </div>

    <div class="pc-body">
        <?php if (!$notifications): ?>
        <div class="empty-state" style="text-align:center;padding:40px 20px">
            <div class="es-ico"><?= icon('bell', '', 40) ?></div>
            <h3>Henüz bildiriminiz yok</h3>
            <p>Yeni bildirimler buraya gelecektir.</p>
        </div>
        <?php else: ?>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>Bildirim</th><th>İçerik</th><th>Tarih</th><th>Durum</th><th>İşlem</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $n): ?>
                    <tr class="<?= $n['is_read'] ? 'text-muted' : '' ?>">
                        <td class="td-nowrap"><strong><?= e($n['title']) ?></strong></td>
                        <td class="td-nowrap"><?= e($n['message'] ?: '-') ?></td>
                        <td class="td-nowrap"><span class="badge badge-muted"><?= format_date($n['created_at'], 'd F Y, H:i') ?></span></td>
                        <td class="td-nowrap"><?= $n['is_read'] ? '<span class="badge badge-success">Okundu</span>' : '<span class="badge badge-accent">Yeni</span>' ?></td>
                        <td>
                            <?php if (!$n['is_read']): ?>
                                <form method="POST" style="margin:0" onsubmit="return confirm('Okundu olarak işaretlemek istediğinizden emin misiniz?')">
                                    <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                                    <input type="hidden" name="mark_read" value="1">
                                    <button type="submit" class="btn btn-outline btn-sm"><?= icon('check', '', 16) ?> Okundu</button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs text-muted">-</span>
                            <?php endif; ?>
                            <?php if ($n['url']): ?>
                                <a href="<?= e($n['url']) ?>" class="btn btn-accent btn-sm">Git</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
