<?php
/**
 * Akilli Kariyer - Yonetim: Meslekler
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $action = $_POST['action'] ?? '';
    $careerId = (int) ($_POST['career_id'] ?? 0);

    if ($action === 'delete' && $careerId > 0) {
        $row = Database::fetch('SELECT `name`,`image` FROM `careers` WHERE `id` = ?', [$careerId]);
        if ($row) {
            Database::query('DELETE FROM `careers` WHERE `id` = ?', [$careerId]);
            \App\Helpers\Upload::delete($row['image']);
            Database::query(
                'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
                [(int) Auth::id(), 'career_delete', 'career', $careerId, $row['name'] . ' silindi', Auth::ip()]
            );
            set_flash('success', 'Meslek silindi.');
        }
        redirect(url('admin/careers.php'));
    }

    if ($action === 'toggle' && $careerId > 0) {
        Database::query('UPDATE `careers` SET `is_active` = 1 - `is_active` WHERE `id` = ?', [$careerId]);
        set_flash('success', 'Meslek görünürlüğü güncellendi.');
        redirect(url('admin/careers.php'));
    }

    if ($action === 'feature' && $careerId > 0) {
        Database::query('UPDATE `careers` SET `is_featured` = 1 - `is_featured` WHERE `id` = ?', [$careerId]);
        set_flash('success', 'Öne çıkanlar durumu güncellendi.');
        redirect(url('admin/careers.php'));
    }
}

$search   = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$page     = max(1, (int) ($_GET['sayfa'] ?? 1));
$per      = ADMIN_PER_PAGE;

$where  = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = '(c.name LIKE ? OR c.short_description LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term);
}
if ($category !== '') {
    $where[] = 'cc.slug = ?';
    $params[] = $category;
}
$whereSql = implode(' AND ', $where);

$total = (int) Database::fetchColumn(
    "SELECT COUNT(*) FROM `careers` c INNER JOIN `career_categories` cc ON cc.id = c.category_id WHERE $whereSql",
    $params
);

$careers = Database::fetchAll(
    "SELECT c.*, cc.name AS category_name,
            (SELECT COUNT(*) FROM `career_interests` ci WHERE ci.career_id = c.id) AS kriter_sayi
     FROM `careers` c INNER JOIN `career_categories` cc ON cc.id = c.category_id
     WHERE $whereSql ORDER BY c.sort_order ASC, c.name ASC
     LIMIT $per OFFSET " . (($page - 1) * $per),
    $params
);

$categories = Database::fetchAll('SELECT * FROM `career_categories` ORDER BY sort_order ASC');

$panelType     = 'admin';
$panelActive   = 'careers';
$panelTitle    = 'Meslek Yönetimi';
$panelSubtitle = 'Meslek kataloğunu, kriterlerini ve görünürlüğünü yönetin';
$panelActions  = '<a href="' . url('admin/career-edit.php') . '" class="btn btn-accent btn-sm">' . icon('plus', '', 16) . ' Yeni Meslek</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card" style="margin-bottom:20px">
  <div class="pc-body">
    <form method="get" class="filter-row" style="grid-template-columns:2fr 1fr auto auto">
      <div class="field">
        <label for="cq">Meslek Ara</label>
        <span class="search-box">
          <?= icon('search', '', 18) ?>
          <input type="search" id="cq" name="q" class="input" value="<?= e($search) ?>" placeholder="Meslek adı veya açıklama">
        </span>
      </div>
      <div class="field">
        <label for="ccat">Kategori</label>
        <select id="ccat" name="category" class="select">
          <option value="">Tümü</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary"><?= icon('filter', '', 16) ?> Filtrele</button>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <a href="<?= url('admin/careers.php') ?>" class="btn btn-outline"><?= icon('refresh-cw', '', 16) ?> Temizle</a>
      </div>
    </form>
  </div>
</section>

<section class="panel-card">
  <div class="pc-head">
    <div><h2>Meslekler</h2><p class="pc-sub"><strong><?= number_format_tr($total) ?></strong> kayıt</p></div>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Meslek</th><th>Kategori</th><th>Eğitim</th><th>Kriter</th><th>Görüntülenme</th><th>Durum</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($careers as $c): ?>
          <tr>
            <td>
              <div class="flex gap-10 center">
                <span style="width:38px;height:38px;border-radius:11px;background:var(--c-light-blue);color:var(--c-secondary);display:grid;place-items:center;flex-shrink:0">
                  <?= icon($c['icon'] ?? 'briefcase', '', 18) ?>
                </span>
                <div>
                  <div class="uc-name"><?= e($c['name']) ?></div>
                  <div class="uc-mail"><?= e(str_limit($c['short_description'], 48)) ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge"><?= e($c['category_name']) ?></span></td>
            <td class="td-nowrap"><?= e(education_label($c['education_level'])) ?></td>
            <td><span class="badge badge-muted"><?= (int) $c['kriter_sayi'] ?></span></td>
            <td><span class="badge badge-muted"><?= number_format_tr((int) $c['view_count']) ?></span></td>
            <td>
              <?php if ($c['is_active']): ?>
                <span class="badge badge-success">Yayında</span>
              <?php else: ?>
                <span class="badge badge-muted">Gizli</span>
              <?php endif; ?>
              <?php if ($c['is_featured']): ?>
                <span class="badge badge-accent"><?= icon('star', '', 11) ?> Öne Çıkan</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="td-actions">
                <a href="<?= url('admin/career-edit.php?id=' . (int) $c['id']) ?>" class="btn btn-outline btn-sm" title="Düzenle">
                  <?= icon('pencil', '', 14) ?>
                </a>
                <form method="post" style="display:inline" data-guard>
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="feature">
                  <input type="hidden" name="career_id" value="<?= (int) $c['id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm" title="Öne çıkar">
                    <?= icon('star', '', 14) ?>
                  </button>
                </form>
                <form method="post" style="display:inline" data-guard>
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="career_id" value="<?= (int) $c['id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm" title="Görünürlük">
                    <?= icon($c['is_active'] ? 'eye' : 'eye-off', '', 14) ?>
                  </button>
                </form>
                <form method="post" style="display:inline" data-guard onsubmit="return confirm('Bu meslek kalıcı olarak silinecek. Devam edilsin mi?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="career_id" value="<?= (int) $c['id'] ?>">
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

  <?php echo pagination($page, $total, url('admin/careers.php'), array_filter(['q' => $search, 'category' => $category])); ?>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
