<?php
/**
 * Akilli Kariyer - Yonetim: Kullanicilar
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Models\User;

Auth::requireAdmin();
$adminId = (int) Auth::id();

$search = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$role   = trim((string) ($_GET['role'] ?? ''));
$page   = max(1, (int) ($_GET['sayfa'] ?? 1));
$per    = ADMIN_PER_PAGE;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($action === 'toggle' && $userId > 0 && $userId !== $adminId) {
        $target = User::find($userId);
        if ($target) {
            User::update($userId, ['is_active' => $target->is_active ? 0 : 1]);
            Database::query(
                'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
                [$adminId, 'user_toggle', 'user', $userId, $target->email . ' hesabi ' . ($target->is_active ? 'devre disi' : 'aktif') . ' yapildi', Auth::ip()]
            );
            set_flash('success', $target->fullName() . ' hesabı ' . ($target->is_active ? 'devre dışı bırakıldı' : 'aktifleştirildi') . '.');
        }
        redirect(url('admin/users.php?' . http_build_query(array_filter(['q' => $search, 'status' => $status, 'role' => $role]))));
    }

    if ($action === 'role' && $userId > 0) {
        $newRole = $_POST['role'] ?? 'user';
        if (!in_array($newRole, ['user', 'editor', 'admin'], true)) {
            $newRole = 'user';
        }
        if ($userId === $adminId && $newRole !== 'admin') {
            set_flash('error', 'Kendi yönetici yetkinizi kaldıramazsınız.');
        } else {
            $target = User::find($userId);
            Database::query('DELETE FROM `user_roles` WHERE `user_id` = ?', [$userId]);
            Database::query('INSERT INTO `user_roles` (`user_id`,`role`) VALUES (?,?)', [$userId, $newRole]);
            Database::query(
                'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
                [$adminId, 'user_role', 'user', $userId, ($target ? $target->email : '') . ' -> ' . $newRole, Auth::ip()]
            );
            set_flash('success', 'Kullanıcı rolü güncellendi.');
        }
        redirect(url('admin/users.php?' . http_build_query(array_filter(['q' => $search, 'status' => $status, 'role' => $role]))));
    }

    if ($action === 'delete' && $userId > 0 && $userId !== $adminId) {
        $target = User::find($userId);
        User::delete($userId);
        Database::query(
            'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
            [$adminId, 'user_delete', 'user', $userId, ($target ? $target->email : '') . ' silindi', Auth::ip()]
        );
        set_flash('success', 'Kullanıcı silindi.');
        redirect(url('admin/users.php'));
    }
}

$where  = ['1=1'];
$params = [];

if ($search !== '') {
    $where[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}
if ($status === 'active') {
    $where[] = 'u.is_active = 1';
} elseif ($status === 'passive') {
    $where[] = 'u.is_active = 0';
}
if ($role !== '') {
    $where[] = 'EXISTS (SELECT 1 FROM user_roles ur WHERE ur.user_id = u.id AND ur.role = ?)';
    $params[] = $role;
}

$whereSql = implode(' AND ', $where);
$total = (int) Database::fetchColumn("SELECT COUNT(*) FROM `users` u WHERE $whereSql", $params);

$users = Database::fetchAll(
    "SELECT u.*,
            (SELECT GROUP_CONCAT(r.role) FROM user_roles r WHERE r.user_id = u.id) AS roller,
            (SELECT COUNT(*) FROM test_results t WHERE t.user_id = u.id) AS test_sayi,
            (SELECT COUNT(*) FROM favorite_careers f WHERE f.user_id = u.id) AS favori_sayi
     FROM `users` u
     WHERE $whereSql
     ORDER BY u.created_at DESC
     LIMIT $per OFFSET " . (($page - 1) * $per),
    $params
);

$panelType     = 'admin';
$panelActive   = 'users';
$panelTitle    = 'Kullanıcı Yönetimi';
$panelSubtitle = 'Kayıtlı kullanıcıları görüntüleyin, rolleri ve erişimlerini yönetin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card" style="margin-bottom:20px">
  <div class="pc-body">
    <form method="get" class="filter-row">
      <div class="field">
        <label for="uq">Kullanıcı Ara</label>
        <span class="search-box">
          <?= icon('search', '', 18) ?>
          <input type="search" id="uq" name="q" class="input" value="<?= e($search) ?>" placeholder="Ad, soyad veya e-posta">
        </span>
      </div>
      <div class="field">
        <label for="ustatus">Durum</label>
        <select id="ustatus" name="status" class="select">
          <option value="">Tümü</option>
          <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
          <option value="passive" <?= $status === 'passive' ? 'selected' : '' ?>>Devre dışı</option>
        </select>
      </div>
      <div class="field">
        <label for="urole">Rol</label>
        <select id="urole" name="role" class="select">
          <option value="">Tümü</option>
          <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>Kullanıcı</option>
          <option value="editor" <?= $role === 'editor' ? 'selected' : '' ?>>Editör</option>
          <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Yönetici</option>
        </select>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary"><?= icon('filter', '', 16) ?> Filtrele</button>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <a href="<?= url('admin/users.php') ?>" class="btn btn-outline"><?= icon('refresh-cw', '', 16) ?> Temizle</a>
      </div>
    </form>
  </div>
</section>

<section class="panel-card">
  <div class="pc-head">
    <div><h2>Kullanıcılar</h2><p class="pc-sub"><strong><?= number_format_tr($total) ?></strong> kullanıcı bulundu</p></div>
  </div>

  <?php if (!$users): ?>
    <div class="pc-body">
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('user-x', '', 28) ?></div>
        <h3>Kullanıcı bulunamadı</h3>
        <p>Arama kriterlerinize uygun kayıt yok.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>Kullanıcı</th><th>Eğitim</th><th>Rol</th><th>Test</th><th>Favori</th><th>Kayıt</th><th>Durum</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <?php $roles = array_filter(explode(',', (string) $u['roller'])); ?>
            <tr>
              <td>
                <div class="user-cell">
                  <?= svg_avatar(mb_strtoupper(mb_substr($u['first_name'], 0, 1) . mb_substr($u['last_name'], 0, 1)), '#1C466D', 36) ?>
                  <div>
                    <div class="uc-name"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></div>
                    <div class="uc-mail"><?= e($u['email']) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge badge-muted"><?= e(education_label($u['education_level'])) ?></span></td>
              <td>
                <?php foreach ($roles as $r): ?>
                  <span class="badge <?= $r === 'admin' ? 'badge-danger' : ($r === 'editor' ? 'badge-warning' : 'badge-info') ?>"><?= e(ucfirst($r)) ?></span>
                <?php endforeach; ?>
              </td>
              <td><span class="badge badge-muted"><?= (int) $u['test_sayi'] ?></span></td>
              <td><span class="badge badge-muted"><?= (int) $u['favori_sayi'] ?></span></td>
              <td class="td-nowrap"><?= format_date($u['created_at'], 'd M Y') ?></td>
              <td>
                <?php if ($u['is_active']): ?>
                  <span class="badge badge-success"><?= icon('check', '', 12) ?> Aktif</span>
                <?php else: ?>
                  <span class="badge badge-danger"><?= icon('x', '', 12) ?> Pasif</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="td-actions">
                  <form method="post" style="display:inline" data-guard>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" title="<?= $u['is_active'] ? 'Devre dışı bırak' : 'Aktifleştir' ?>">
                      <?= icon($u['is_active'] ? 'lock' : 'unlock', '', 14) ?>
                    </button>
                  </form>

                  <form method="post" style="display:inline" data-guard>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="role">
                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                    <select name="role" class="select" style="padding:6px 28px 6px 10px;font-size:.78rem;width:auto" onchange="this.form.submit()">
                      <?php foreach (['user' => 'Kullanıcı', 'editor' => 'Editör', 'admin' => 'Yönetici'] as $rk => $rv): ?>
                        <option value="<?= e($rk) ?>" <?= in_array($rk, $roles, true) ? 'selected' : '' ?>><?= e($rv) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>

                  <?php if ((int) $u['id'] !== $adminId): ?>
                    <form method="post" style="display:inline" data-guard onsubmit="return confirm('Bu kullanıcı ve tüm verileri kalıcı olarak silinecek. Devam edilsin mi?')">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                      <button type="submit" class="btn btn-outline btn-sm" style="color:var(--c-danger)" title="Sil">
                        <?= icon('trash-2', '', 14) ?>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php echo pagination($page, $total, url('admin/users.php'), array_filter(['q' => $search, 'status' => $status, 'role' => $role])); ?>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
