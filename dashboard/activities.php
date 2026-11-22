<?php
/**
 * Akilli Kariyer - Panel: Aktivitelerim
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Models\Activity;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear') {
    CSRF::check();
    Database::query('DELETE FROM `user_activities` WHERE `user_id` = ?', [$userId]);
    set_flash('success', 'Aktivite geçmişiniz temizlendi.');
    redirect(url('dashboard/activities.php'));
}

$page   = max(1, (int) ($_GET['sayfa'] ?? 1));
$per    = 30;
$total  = (int) Database::fetchColumn('SELECT COUNT(*) FROM `user_activities` WHERE `user_id` = ?', [$userId]);
$offset = ($page - 1) * $per;

$activities = Activity::forUser($userId, $per);

$panelType     = 'user';
$panelActive   = 'activities';
$panelTitle    = 'Aktivitelerim';
$panelSubtitle = 'Hesabınızdaki işlem geçmişi';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
  <div class="pc-head">
    <div><h2>İşlem Geçmişi</h2><p class="pc-sub"><?= number_format_tr($total) ?> kayıt</p></div>
    <?php if ($activities): ?>
      <form method="post" data-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="clear">
        <button type="submit" class="btn btn-outline btn-sm" style="color:var(--c-danger)">
          <?= icon('trash-2', '', 15) ?> Geçmişi Temizle
        </button>
      </form>
    <?php endif; ?>
  </div>

  <div class="pc-body">
    <?php if (!$activities): ?>
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('activity', '', 28) ?></div>
        <h3>Henüz aktivite yok</h3>
        <p>Kariyer testinizi tamamladıkça ve meslek inceledikçe burada kayıt tutulur.</p>
        <a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('play', '', 17) ?> Testi Başlat</a>
      </div>
    <?php else: ?>
      <?php foreach ($activities as $act): ?>
        <div class="activity-item">
          <span class="ai-ico <?= e(Activity::colorFor($act['activity_type'])) ?>">
            <?= icon(Activity::iconFor($act['activity_type']), '', 17) ?>
          </span>
          <div class="flex-1">
            <div class="ai-txt"><?= e($act['description']) ?></div>
            <div class="ai-time"><?= format_date($act['created_at'], 'd F Y H:i') ?> • <?= time_ago($act['created_at']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
