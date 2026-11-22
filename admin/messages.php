<?php
/**
 * Akilli Kariyer - Yonetim: Iletisim mesajlari
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $action = $_POST['action'] ?? '';
    $msgId  = (int) ($_POST['message_id'] ?? 0);

    if ($action === 'status' && $msgId > 0) {
        $newStatus = $_POST['status'] ?? 'read';
        if (!in_array($newStatus, ['new', 'read', 'answered'], true)) {
            $newStatus = 'read';
        }
        Database::query('UPDATE `contact_messages` SET `status` = ? WHERE `id` = ?', [$newStatus, $msgId]);
        set_flash('success', 'Mesaj durumu güncellendi.');
        redirect(url('admin/messages.php'));
    }

    if ($action === 'delete' && $msgId > 0) {
        Database::query('DELETE FROM `contact_messages` WHERE `id` = ?', [$msgId]);
        set_flash('success', 'Mesaj silindi.');
        redirect(url('admin/messages.php'));
    }
}

$statusFilter = trim((string) ($_GET['status'] ?? ''));

$where  = ['1=1'];
$params = [];
if ($statusFilter !== '') {
    $where[] = '`status` = ?';
    $params[] = $statusFilter;
}
$whereSql = implode(' AND ', $where);

$messages = Database::fetchAll("SELECT * FROM `contact_messages` WHERE $whereSql ORDER BY created_at DESC", $params);

$counts = [
    'new'      => (int) Database::fetchColumn("SELECT COUNT(*) FROM `contact_messages` WHERE `status` = 'new'"),
    'read'     => (int) Database::fetchColumn("SELECT COUNT(*) FROM `contact_messages` WHERE `status` = 'read'"),
    'answered' => (int) Database::fetchColumn("SELECT COUNT(*) FROM `contact_messages` WHERE `status` = 'answered'"),
    'total'    => (int) Database::fetchColumn('SELECT COUNT(*) FROM `contact_messages`'),
];

$panelType     = 'admin';
$panelActive   = 'messages';
$panelTitle    = 'İletişim Mesajları';
$panelSubtitle = 'Kullanıcılardan gelen mesajları yönetin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<div class="grid g-4" style="margin-bottom:20px">
  <a href="<?= url('admin/messages.php?status=new') ?>" class="mini-stat" style="text-decoration:none">
    <span class="ms-ico" style="background:var(--c-danger-soft);color:var(--c-danger)"><?= icon('mail', '', 20) ?></span>
    <div><div class="ms-val"><?= $counts['new'] ?></div><div class="ms-lab">Yeni</div></div>
  </a>
  <a href="<?= url('admin/messages.php?status=read') ?>" class="mini-stat" style="text-decoration:none">
    <span class="ms-ico" style="background:var(--c-warning-soft);color:var(--c-warning)"><?= icon('mail-open', '', 20) ?></span>
    <div><div class="ms-val"><?= $counts['read'] ?></div><div class="ms-lab">Okundu</div></div>
  </a>
  <a href="<?= url('admin/messages.php?status=answered') ?>" class="mini-stat" style="text-decoration:none">
    <span class="ms-ico" style="background:var(--c-success-soft);color:var(--c-success)"><?= icon('check-circle-2', '', 20) ?></span>
    <div><div class="ms-val"><?= $counts['answered'] ?></div><div class="ms-lab">Yanıtlandı</div></div>
  </a>
  <a href="<?= url('admin/messages.php') ?>" class="mini-stat" style="text-decoration:none">
    <span class="ms-ico"><?= icon('inbox', '', 20) ?></span>
    <div><div class="ms-val"><?= $counts['total'] ?></div><div class="ms-lab">Toplam</div></div>
  </a>
</div>

<section class="panel-card">
  <div class="pc-head"><div><h2>Mesajlar</h2><p class="pc-sub"><?= count($messages) ?> mesaj</p></div></div>

  <?php if (!$messages): ?>
    <div class="pc-body">
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('inbox', '', 28) ?></div>
        <h3>Mesaj bulunamadı</h3>
        <p>Bu filtrede mesaj yok.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="pc-body grid" style="gap:14px">
      <?php foreach ($messages as $m): ?>
        <div style="border:1px solid var(--c-border);border-radius:var(--r-md);padding:18px;background:<?= $m['status'] === 'new' ? '#FCFDFF' : '#fff' ?>">
          <div class="flex between wrap gap-12" style="align-items:flex-start">
            <div class="flex gap-12" style="align-items:flex-start;min-width:0">
              <span style="width:42px;height:42px;border-radius:12px;background:var(--c-light-blue);color:var(--c-secondary);display:grid;place-items:center;flex-shrink:0">
                <?= icon('mail', '', 19) ?>
              </span>
              <div style="min-width:0">
                <div style="font-weight:700;color:var(--c-primary)">
                  <?= e($m['name']) ?>
                  <?php if ($m['status'] === 'new'): ?><span class="badge badge-danger" style="margin-left:6px">Yeni</span><?php endif; ?>
                </div>
                <div class="text-xs text-muted-c">
                  <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a> •
                  <?= format_date($m['created_at'], 'd F Y H:i') ?>
                </div>
              </div>
            </div>
            <div class="flex gap-8" style="flex-shrink:0">
              <form method="post" style="display:inline" data-guard>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="status">
                <input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>">
                <select name="status" class="select" style="padding:7px 30px 7px 12px;font-size:.8rem;width:auto" onchange="this.form.submit()">
                  <?php foreach (['new' => 'Yeni', 'read' => 'Okundu', 'answered' => 'Yanıtlandı'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $m['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
              <form method="post" style="display:inline" data-guard onsubmit="return confirm('Bu mesaj silinecek. Devam edilsin mi?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--c-danger)" title="Sil"><?= icon('trash-2', '', 14) ?></button>
              </form>
            </div>
          </div>

          <?php if (!empty($m['subject'])): ?>
            <strong style="display:block;margin-top:12px;font-size:.94rem;color:var(--c-primary)"><?= e($m['subject']) ?></strong>
          <?php endif; ?>

          <p style="margin-top:8px;font-size:.9rem;line-height:1.7;color:#40536B"><?= nl2br(e($m['message'])) ?></p>

          <a href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?: 'Akıllı Kariyer')) ?>"
             class="btn btn-outline btn-sm" style="margin-top:12px">
            <?= icon('reply', '', 15) ?> E-posta ile Yanıtla
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
