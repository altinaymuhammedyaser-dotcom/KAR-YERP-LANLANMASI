<?php
/**
 * Akilli Kariyer - Yonetim paneli: Genel bakis
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Models\Activity;

Auth::requireAdmin();

$stats = [
    'users'        => (int) Database::fetchColumn('SELECT COUNT(*) FROM `users`'),
    'active_users' => (int) Database::fetchColumn('SELECT COUNT(*) FROM `users` WHERE `is_active` = 1'),
    'careers'      => (int) Database::fetchColumn('SELECT COUNT(*) FROM `careers`'),
    'sessions'     => (int) Database::fetchColumn('SELECT COUNT(*) FROM `test_sessions`'),
    'tests'        => (int) Database::fetchColumn('SELECT COUNT(*) FROM `test_results`'),
    'questions'    => (int) Database::fetchColumn('SELECT COUNT(*) FROM `test_questions`'),
    'posts'        => (int) Database::fetchColumn('SELECT COUNT(*) FROM `blog_posts`'),
    'categories'   => (int) Database::fetchColumn('SELECT COUNT(*) FROM `career_categories`'),
    'favorites'    => (int) Database::fetchColumn('SELECT COUNT(*) FROM `favorite_careers`'),
    'messages'     => (int) Database::fetchColumn('SELECT COUNT(*) FROM `contact_messages`'),
    'new_messages' => (int) Database::fetchColumn("SELECT COUNT(*) FROM `contact_messages` WHERE `status` = 'new'"),
    'recommendations' => (int) Database::fetchColumn('SELECT COUNT(*) FROM `career_recommendations`'),
];

// Son 14 gunluk kayit grafigi
$registrations = Database::fetchAll(
    "SELECT DATE(`created_at`) AS gun, COUNT(*) AS adet
     FROM `users` WHERE `created_at` >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
     GROUP BY DATE(`created_at`) ORDER BY gun ASC"
);
$regMap = [];
foreach ($registrations as $r) {
    $regMap[$r['gun']] = (int) $r['adet'];
}
$regLabels = [];
$regData   = [];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $regLabels[] = format_date($date, 'd.m');
    $regData[]   = $regMap[$date] ?? 0;
}

// Haftalik test sayisi
$weeklyTests = Database::fetchAll(
    "SELECT YEARWEEK(`completed_at`, 3) AS hafta, COUNT(*) AS adet
     FROM `test_results` WHERE `completed_at` >= DATE_SUB(CURDATE(), INTERVAL 56 DAY)
     GROUP BY hafta ORDER BY hafta ASC"
);
$weekLabels = [];
$weekData   = [];
$weekStart  = (int) date('oW', strtotime('-56 days'));
foreach ($weeklyTests as $w) {
    $weekLabels[] = 'H' . (int) $w['hafta'];
    $weekData[]   = (int) $w['adet'];
}
if (!$weekLabels) {
    for ($i = 7; $i >= 0; $i--) {
        $weekLabels[] = 'H-' . $i;
        $weekData[]   = 0;
    }
}

// En cok onerilen meslekler
$topRecommended = Database::fetchAll(
    "SELECT c.id, c.name, c.slug, COUNT(r.id) AS adet, AVG(r.match_score) AS ort
     FROM `career_recommendations` r
     INNER JOIN `careers` c ON c.id = r.career_id
     GROUP BY c.id ORDER BY adet DESC LIMIT 8"
);

// En cok incelenen meslekler
$topViewed = Database::fetchAll(
    "SELECT id, name, slug, view_count FROM `careers` ORDER BY view_count DESC LIMIT 8"
);

// Kategori dagilimi
$categoryDist = Database::fetchAll(
    "SELECT cc.name, cc.color, COUNT(c.id) AS adet
     FROM `career_categories` cc
     LEFT JOIN `careers` c ON c.category_id = cc.id AND c.is_active = 1
     GROUP BY cc.id ORDER BY adet DESC"
);

// Soru kategorileri
$questionDist = Database::fetchAll(
    "SELECT tc.name, COUNT(q.id) AS adet FROM `test_categories` tc
     LEFT JOIN `test_questions` q ON q.category_id = tc.id
     GROUP BY tc.id ORDER BY adet DESC"
);

$recentUsers = Database::fetchAll(
    "SELECT u.id, u.first_name, u.last_name, u.email, u.created_at, u.is_active,
            (SELECT COUNT(*) FROM test_results tr WHERE tr.user_id = u.id) AS test_sayi
     FROM `users` u ORDER BY u.created_at DESC LIMIT 6"
);

$recentMessages = Database::fetchAll(
    "SELECT * FROM `contact_messages` ORDER BY created_at DESC LIMIT 5"
);

$auditLogs = Database::fetchAll(
    "SELECT a.*, u.first_name, u.last_name FROM `audit_logs` a
     LEFT JOIN `users` u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 8"
);

$panelType     = 'admin';
$panelActive   = 'index';
$panelTitle    = 'Yönetim Paneli';
$panelSubtitle = 'Platform genel durumu ve son hareketler';
$panelActions  = '<a href="' . url('admin/career-edit.php') . '" class="btn btn-accent btn-sm">' . icon('plus', '', 16) . ' Yeni Meslek</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<!-- Ana istatistikler -->
<div class="grid g-4" style="margin-bottom:22px">
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-accent-soft);color:var(--c-accent-dark)"><?= icon('users', '', 20) ?></span>
    <div><div class="ms-val"><?= number_format_tr($stats['users']) ?></div><div class="ms-lab">Toplam Kullanıcı</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico"><?= icon('briefcase', '', 20) ?></span>
    <div><div class="ms-val"><?= number_format_tr($stats['careers']) ?></div><div class="ms-lab">Toplam Meslek</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-success-soft);color:var(--c-success)"><?= icon('clipboard-check', '', 20) ?></span>
    <div><div class="ms-val"><?= number_format_tr($stats['tests']) ?></div><div class="ms-lab">Tamamlanan Test</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-warning-soft);color:var(--c-warning)"><?= icon('clipboard-list', '', 20) ?></span>
    <div><div class="ms-val"><?= number_format_tr($stats['questions']) ?></div><div class="ms-lab">Toplam Soru</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico"><?= icon('layers', '', 20) ?></span>
    <div><div class="ms-val"><?= number_format_tr($stats['categories']) ?></div><div class="ms-lab">Meslek Kategorisi</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-info-soft);color:var(--c-info)"><?= icon('newspaper', '', 20) ?></span>
    <div><div class="ms-val"><?= number_format_tr($stats['posts']) ?></div><div class="ms-lab">Blog Yazısı</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-danger-soft);color:var(--c-danger)"><?= icon('heart', '', 20) ?></span>
    <div><div class="ms-val"><?= number_format_tr($stats['favorites']) ?></div><div class="ms-lab">Favori Kaydı</div></div>
  </div>
  <div class="mini-stat">
    <span class="ms-ico" style="background:var(--c-warning-soft);color:var(--c-warning)"><?= icon('mail', '', 20) ?></span>
    <div><div class="ms-val"><?= number_format_tr($stats['messages']) ?></div><div class="ms-lab">İletişim Mesajı</div></div>
  </div>
</div>

<!-- Grafikler -->
<div class="chart-grid-2" style="margin-bottom:22px">
  <section class="panel-card">
    <div class="pc-head"><h3><?= icon('user-plus', '', 17) ?> Günlük Kullanıcı Kayıtları</h3></div>
    <div class="pc-body">
      <div class="chart-box" style="height:250px">
        <canvas data-chart='<?= e(json_encode([
          'type' => 'line',
          'data' => ['labels' => $regLabels, 'datasets' => [[
            'label' => 'Yeni Kullanıcı', 'data' => $regData,
            'borderColor' => '#16B8B0', 'backgroundColor' => 'rgba(22,184,176,.14)',
            'fill' => true, 'tension' => 0.35, 'pointRadius' => 3, 'pointBackgroundColor' => '#16B8B0',
          ]]],
        ])) ?>'></canvas>
      </div>
    </div>
  </section>

  <section class="panel-card">
    <div class="pc-head"><h3><?= icon('clipboard-check', '', 17) ?> Haftalık Test Sayısı</h3></div>
    <div class="pc-body">
      <div class="chart-box" style="height:250px">
        <canvas data-chart='<?= e(json_encode([
          'type' => 'bar',
          'data' => ['labels' => $weekLabels, 'datasets' => [[
            'label' => 'Tamamlanan Test', 'data' => $weekData,
            'backgroundColor' => 'rgba(28,70,109,.88)', 'borderRadius' => 6, 'maxBarThickness' => 34,
          ]]],
        ])) ?>'></canvas>
      </div>
    </div>
  </section>

  <section class="panel-card">
    <div class="pc-head"><h3><?= icon('layers', '', 17) ?> Meslek Kategorisi Dağılımı</h3></div>
    <div class="pc-body">
      <div class="chart-box" style="height:250px">
        <canvas data-chart='<?= e(json_encode([
          'type' => 'doughnut',
          'data' => [
            'labels' => array_column($categoryDist, 'name'),
            'datasets' => [[
              'label' => 'Meslek', 'data' => array_column($categoryDist, 'adet'),
              'backgroundColor' => array_column($categoryDist, 'color'),
            ]],
          ],
        ])) ?>'></canvas>
      </div>
    </div>
  </section>

  <section class="panel-card">
    <div class="pc-head"><h3><?= icon('clipboard-list', '', 17) ?> Soru Kategorisi Dağılımı</h3></div>
    <div class="pc-body">
      <div class="chart-box" style="height:250px">
        <canvas data-chart='<?= e(json_encode([
          'type' => 'polarArea',
          'data' => [
            'labels' => array_column($questionDist, 'name'),
            'datasets' => [['label' => 'Soru', 'data' => array_column($questionDist, 'adet')]],
          ],
        ])) ?>'></canvas>
      </div>
    </div>
  </section>
</div>

<div class="grid g-2" style="margin-bottom:22px">
  <!-- En çok önerilen -->
  <section class="panel-card">
    <div class="pc-head">
      <h3><?= icon('sparkles', '', 17) ?> En Çok Önerilen Meslekler</h3>
      <a href="<?= url('admin/careers.php') ?>" class="btn btn-ghost btn-sm">Tümü</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Meslek</th><th>Önerim</th><th>Ort. Uyum</th></tr></thead>
        <tbody>
          <?php if (!$topRecommended): ?>
            <tr><td colspan="3" class="text-muted-c" style="padding:22px">Henüz öneri verisi yok.</td></tr>
          <?php endif; ?>
          <?php foreach ($topRecommended as $tr): ?>
            <tr>
              <td><a href="<?= url('career-detail.php?slug=' . urlencode($tr['slug'])) ?>"><?= e($tr['name']) ?></a></td>
              <td><span class="badge badge-accent"><?= number_format_tr((int) $tr['adet']) ?></span></td>
              <td><strong style="color:var(--c-accent-dark)">%<?= number_format_tr((float) $tr['ort']) ?></strong></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- En çok incelenen -->
  <section class="panel-card">
    <div class="pc-head">
      <h3><?= icon('eye', '', 17) ?> En Çok İncelenen Meslekler</h3>
      <a href="<?= url('admin/careers.php') ?>" class="btn btn-ghost btn-sm">Tümü</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Meslek</th><th>Görüntülenme</th></tr></thead>
        <tbody>
          <?php foreach ($topViewed as $tv): ?>
            <tr>
              <td><a href="<?= url('career-detail.php?slug=' . urlencode($tv['slug'])) ?>"><?= e($tv['name']) ?></a></td>
              <td><span class="badge"><?= number_format_tr((int) $tv['view_count']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<div class="grid g-2">
  <!-- Son kullanıcılar -->
  <section class="panel-card">
    <div class="pc-head">
      <h3><?= icon('users', '', 17) ?> Son Kaydolan Kullanıcılar</h3>
      <a href="<?= url('admin/users.php') ?>" class="btn btn-ghost btn-sm">Tümü</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Kullanıcı</th><th>Kayıt</th><th>Test</th></tr></thead>
        <tbody>
          <?php foreach ($recentUsers as $ru): ?>
            <tr>
              <td>
                <div class="user-cell">
                  <?= svg_avatar(mb_strtoupper(mb_substr($ru['first_name'], 0, 1) . mb_substr($ru['last_name'], 0, 1)), '#1C466D', 34) ?>
                  <div>
                    <div class="uc-name"><?= e($ru['first_name'] . ' ' . $ru['last_name']) ?></div>
                    <div class="uc-mail"><?= e($ru['email']) ?></div>
                  </div>
                </div>
              </td>
              <td class="td-nowrap"><?= format_date($ru['created_at'], 'd M Y') ?></td>
              <td><span class="badge badge-muted"><?= (int) $ru['test_sayi'] ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Son mesajlar -->
  <section class="panel-card">
    <div class="pc-head">
      <h3><?= icon('mail', '', 17) ?> Son İletişim Mesajları</h3>
      <a href="<?= url('admin/messages.php') ?>" class="btn btn-ghost btn-sm">Tümü</a>
    </div>
    <div class="pc-body">
      <?php if (!$recentMessages): ?>
        <p style="font-size:.89rem;margin:0">Henüz mesaj yok.</p>
      <?php else: ?>
        <?php foreach ($recentMessages as $m): ?>
          <div class="activity-item">
            <span class="ai-ico <?= $m['status'] === 'new' ? 'danger' : 'info' ?>"><?= icon('mail', '', 17) ?></span>
            <div class="flex-1">
              <div class="ai-txt"><strong><?= e($m['name']) ?></strong> — <?= e(str_limit($m['subject'] ?: $m['message'], 60)) ?></div>
              <div class="ai-time"><?= time_ago($m['created_at']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
