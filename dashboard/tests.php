<?php
/**
 * Akilli Kariyer - Panel: Kariyer testlerim
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Models\Result;
use App\Models\Test;
use App\Services\TestService;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

// Yeniden test baslatma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'restart') {
    CSRF::check();
    Database::query("UPDATE `test_sessions` SET `status` = 'abandoned' WHERE `user_id` = ? AND `status` = 'in_progress'", [$userId]);
    $session = TestService::start($userId);
    redirect(url('career-test.php'));
}

// Silme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    CSRF::check();
    $sessionId = (int) ($_POST['session_id'] ?? 0);
    $row = Test::findSession($sessionId);
    if ($row && (int) $row['user_id'] === $userId) {
        Test::deleteSession($sessionId);
        \App\Models\Activity::log($userId, 'test_delete', 'Bir test oturumunu sildiniz.', 'session', $sessionId);
        set_flash('success', 'Test oturumu silindi.');
    } else {
        set_flash('error', 'Test oturumu bulunamadı.');
    }
    redirect(url('dashboard/tests.php'));
}

$sessions       = Test::sessions($userId, 30);
$activeSession  = Test::activeSession($userId);
$questionCount  = Test::questionCount();
$completedCount = Result::countForUser($userId);

$panelType     = 'user';
$panelActive   = 'tests';
$panelTitle    = 'Kariyer Testim';
$panelSubtitle = 'Test oturumlarınızı yönetin ve yeni testler başlatın';
$panelActions  = '<a href="' . url('career-test.php') . '" class="btn btn-accent btn-sm">' . icon('plus', '', 16) . ' Yeni Test</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<div class="grid g-3" style="margin-bottom:22px">
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-accent-soft);color:var(--c-accent-dark)"><?= icon('clipboard-list', '', 20) ?></span>
    <div><div class="ms-val"><?= $questionCount ?></div><div class="ms-lab">Toplam Soru</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-success-soft);color:var(--c-success)"><?= icon('check-circle-2', '', 20) ?></span>
    <div><div class="ms-val"><?= $completedCount ?></div><div class="ms-lab">Tamamlanan Test</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-warning-soft);color:var(--c-warning)"><?= icon('clock', '', 20) ?></span>
    <div><div class="ms-val"><?= count(array_filter($sessions, static fn($s) => $s['status'] === 'in_progress')) ?></div><div class="ms-lab">Devam Eden Test</div></div>
  </div>
</div>

<?php if ($activeSession): ?>
  <div class="alert alert-info" style="align-items:center">
    <?= icon('play-circle', '', 19) ?>
    <span class="flex-1">
      <strong>Yarım kalmış testiniz var.</strong>
      <?= (int) $activeSession['answered_count'] ?> / <?= (int) $activeSession['total_questions'] ?> soruyu cevapladınız.
    </span>
    <a href="<?= url('career-test.php') ?>" class="btn btn-primary btn-sm"><?= icon('play', '', 15) ?> Devam Et</a>
  </div>
<?php endif; ?>

<section class="panel-card">
  <div class="pc-head">
    <div>
      <h2>Test Oturumlarım</h2>
      <p class="pc-sub">Tüm test geçmişiniz ve durumları</p>
    </div>
  </div>

  <?php if (!$sessions): ?>
    <div class="pc-body">
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('clipboard-check', '', 28) ?></div>
        <h3>Henüz test oturumunuz yok</h3>
        <p>Kariyer testini başlatarak kariyer profilinizi oluşturun ve size uygun meslekleri keşfedin.</p>
        <a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('play', '', 17) ?> Testi Başlat</a>
      </div>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Durum</th><th>Başlangıç</th><th>İlerleme</th><th>Sonuç</th><th>Tarih</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sessions as $s): ?>
            <tr>
              <td>
                <?php if ($s['status'] === 'completed'): ?>
                  <span class="badge badge-success"><?= icon('check', '', 12) ?> Tamamlandı</span>
                <?php elseif ($s['status'] === 'in_progress'): ?>
                  <span class="badge badge-warning"><?= icon('clock', '', 12) ?> Devam Ediyor</span>
                <?php else: ?>
                  <span class="badge badge-muted"><?= icon('x', '', 12) ?> Bırakıldı</span>
                <?php endif; ?>
              </td>
              <td class="td-nowrap"><?= format_date($s['started_at'], 'd M Y H:i') ?></td>
              <td style="min-width:150px">
                <div class="flex between" style="font-size:.8rem;margin-bottom:4px">
                  <span><?= (int) $s['answered_count'] ?> / <?= (int) $s['total_questions'] ?></span>
                  <span style="color:var(--c-muted)">%<?= (int) round(((int) $s['answered_count'] / max(1, (int) $s['total_questions'])) * 100) ?></span>
                </div>
                <div class="progress-bar" style="height:6px">
                  <span style="width:<?= (int) round(((int) $s['answered_count'] / max(1, (int) $s['total_questions'])) * 100) ?>%"></span>
                </div>
              </td>
              <td>
                <?php if ($s['status'] === 'completed'): ?>
                  <span class="badge badge-info"><?= e($s['dominant_type'] ?? 'Profil') ?></span>
                <?php else: ?>
                  <span class="text-muted-c">—</span>
                <?php endif; ?>
              </td>
              <td class="td-nowrap"><?= $s['completed_at'] ? format_date($s['completed_at'], 'd M Y H:i') : '—' ?></td>
              <td>
                <div class="td-actions">
                  <?php if ($s['status'] === 'in_progress'): ?>
                    <a href="<?= url('career-test.php') ?>" class="btn btn-primary btn-sm"><?= icon('play', '', 14) ?> Devam</a>
                  <?php elseif ($s['status'] === 'completed'): ?>
                    <a href="<?= url('test-result.php?session=' . (int) $s['id']) ?>" class="btn btn-outline btn-sm"><?= icon('eye', '', 14) ?> Sonuç</a>
                  <?php endif; ?>
                  <form method="post" style="display:inline" data-guard>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="session_id" value="<?= (int) $s['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="color:var(--c-danger)" title="Sil">
                      <?= icon('trash-2', '', 14) ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<section class="panel-card" style="margin-top:22px">
  <div class="pc-head">
    <div><h2>Testi Yeniden Başlat</h2><p class="pc-sub">Mevcut devam eden testinizi sıfırlar ve yeni bir test oturumu oluşturur.</p></div>
  </div>
  <div class="pc-body">
    <form method="post" data-guard>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="restart">
      <button type="submit" class="btn btn-accent"><?= icon('refresh-cw', '', 17) ?> Yeni Test Oturumu Başlat</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
