<?php
/**
 * Akilli Kariyer - Yonetim: Meslek kategorileri
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Models\Career;

Auth::requireAdmin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id   = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $desc = trim((string) ($_POST['description'] ?? ''));
        $icon = trim((string) ($_POST['icon'] ?? 'briefcase'));
        $color = trim((string) ($_POST['color'] ?? '#1C466D'));
        $sort = (int) ($_POST['sort_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $errors['name'] = 'Kategori adı zorunludur.';
        } elseif (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $errors['color'] = 'Geçerli bir renk kodu girin (#1C466D gibi).';
        } else {
            $slug = slugify($name, 'kategori');
            $base = $slug;
            $n = 1;
            while (Database::fetchColumn('SELECT id FROM `career_categories` WHERE `slug` = ? AND `id` <> ?', [$slug, $id])) {
                $slug = $base . '-' . (++$n);
            }

            if ($id > 0) {
                Database::update(
                    'UPDATE `career_categories` SET `name`=?,`slug`=?,`description`=?,`icon`=?,`color`=?,`sort_order`=?,`is_active`=? WHERE `id`=?',
                    [$name, $slug, $desc, $icon, $color, $sort, $active, $id]
                );
                set_flash('success', 'Kategori güncellendi.');
            } else {
                Database::insert(
                    'INSERT INTO `career_categories` (`name`,`slug`,`description`,`icon`,`color`,`sort_order`,`is_active`) VALUES (?,?,?,?,?,?,?)',
                    [$name, $slug, $desc, $icon, $color, $sort, $active]
                );
                set_flash('success', 'Kategori eklendi.');
            }
            redirect(url('admin/categories.php'));
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $count = (int) Database::fetchColumn('SELECT COUNT(*) FROM `careers` WHERE `category_id` = ?', [$id]);
        if ($count > 0) {
            set_flash('error', 'Bu kategoride ' . $count . ' meslek var. Önce meslekleri taşıyın veya silin.');
        } else {
            Database::query('DELETE FROM `career_categories` WHERE `id` = ?', [$id]);
            set_flash('success', 'Kategori silindi.');
        }
        redirect(url('admin/categories.php'));
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        Database::query('UPDATE `career_categories` SET `is_active` = 1 - `is_active` WHERE `id` = ?', [$id]);
        set_flash('success', 'Kategori durumu güncellendi.');
        redirect(url('admin/categories.php'));
    }
}

$editId = (int) ($_GET['edit'] ?? 0);
$editing = $editId > 0 ? Database::fetch('SELECT * FROM `career_categories` WHERE `id` = ?', [$editId]) : null;

$categories = Database::fetchAll(
    'SELECT cc.*, (SELECT COUNT(*) FROM `careers` c WHERE c.category_id = cc.id AND c.is_active = 1) AS career_count
     FROM `career_categories` cc ORDER BY cc.sort_order ASC, cc.name ASC'
);

$icons = ['briefcase','cpu','heart-pulse','graduation-cap','cog','palette','scale','briefcase','flask-conical','megaphone','trophy','layers','globe','code-2','newspaper','shield-check'];

$panelType     = 'admin';
$panelActive   = 'categories';
$panelTitle    = 'Meslek Kategorileri';
$panelSubtitle = 'Meslek kataloğunun kategori yapısını yönetin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<div class="layout-aside" style="gap:22px">

  <section class="panel-card">
    <div class="pc-head"><h2>Kategoriler</h2><p class="pc-sub"><?= count($categories) ?> kategori</p></div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Kategori</th><th>Meslek</th><th>Renk</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td>
                <div class="flex gap-10 center">
                  <span style="width:38px;height:38px;border-radius:11px;background:<?= e($cat['color']) ?>;color:#fff;display:grid;place-items:center;flex-shrink:0">
                    <?= icon($cat['icon'] ?? 'briefcase', '', 18) ?>
                  </span>
                  <div>
                    <div class="uc-name"><?= e($cat['name']) ?></div>
                    <div class="uc-mail"><?= e(str_limit($cat['description'] ?? '', 50)) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge badge-muted"><?= (int) $cat['career_count'] ?></span></td>
              <td><span style="display:inline-block;width:26px;height:14px;border-radius:4px;background:<?= e($cat['color']) ?>;border:1px solid var(--c-border)"></span></td>
              <td><?= (int) $cat['sort_order'] ?></td>
              <td>
                <?php if ($cat['is_active']): ?>
                  <span class="badge badge-success">Aktif</span>
                <?php else: ?>
                  <span class="badge badge-muted">Pasif</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="td-actions">
                  <a href="<?= url('admin/categories.php?edit=' . (int) $cat['id']) ?>" class="btn btn-outline btn-sm" title="Düzenle"><?= icon('pencil', '', 14) ?></a>
                  <form method="post" style="display:inline" data-guard>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" title="Durum"><?= icon($cat['is_active'] ? 'eye' : 'eye-off', '', 14) ?></button>
                  </form>
                  <form method="post" style="display:inline" data-guard onsubmit="return confirm('Bu kategori silinecek. Devam edilsin mi?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="color:var(--c-danger)" title="Sil"><?= icon('trash-2', '', 14) ?></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="panel-card">
    <div class="pc-head">
      <h3><?= $editing ? 'Kategoriyi Düzenle' : 'Yeni Kategori' ?></h3>
      <?php if ($editing): ?>
        <a href="<?= url('admin/categories.php') ?>" class="btn btn-ghost btn-sm">Vazgeç</a>
      <?php endif; ?>
    </div>
    <div class="pc-body">
      <?php if ($errors): ?>
        <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
      <?php endif; ?>

      <form method="post" class="panel-form" data-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">

        <label class="field">
          <span>Kategori Adı *</span>
          <input type="text" name="name" class="input" required maxlength="100" value="<?= e($editing['name'] ?? '') ?>">
        </label>

        <label class="field">
          <span>Açıklama</span>
          <textarea name="description" class="textarea" style="min-height:80px" maxlength="255"><?= e($editing['description'] ?? '') ?></textarea>
        </label>

        <div class="pf-grid">
          <label class="field">
            <span>İkon</span>
            <select name="icon" class="select">
              <?php foreach ($icons as $ic): ?>
                <option value="<?= e($ic) ?>" <?= ($editing['icon'] ?? 'briefcase') === $ic ? 'selected' : '' ?>><?= e($ic) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="field">
            <span>Renk</span>
            <input type="color" name="color" class="input" style="height:46px;padding:4px" value="<?= e($editing['color'] ?? '#1C466D') ?>">
          </label>
          <label class="field">
            <span>Sıra</span>
            <input type="number" name="sort_order" class="input" value="<?= (int) ($editing['sort_order'] ?? 0) ?>">
          </label>
          <label class="field" style="display:flex;align-items:flex-end">
            <label class="switch">
              <input type="checkbox" name="is_active" value="1" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>>
              <span class="sw-track"></span>
              <span>Aktif</span>
            </label>
          </label>
        </div>

        <button type="submit" class="btn btn-accent btn-block">
          <?= icon('save', '', 17) ?> <?= $editing ? 'Güncelle' : 'Kategori Ekle' ?>
        </button>
      </form>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
