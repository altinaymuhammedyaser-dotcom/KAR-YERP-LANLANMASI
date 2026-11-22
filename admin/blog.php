<?php
/**
 * Akilli Kariyer - Yonetim: Blog yazilari
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $action = $_POST['action'] ?? '';
    $postId = (int) ($_POST['post_id'] ?? 0);

    if ($action === 'delete' && $postId > 0) {
        $row = Database::fetch('SELECT `title` FROM `blog_posts` WHERE `id` = ?', [$postId]);
        Database::query('DELETE FROM `blog_posts` WHERE `id` = ?', [$postId]);
        Database::query(
            'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
            [(int) Auth::id(), 'post_delete', 'blog_post', $postId, $row['title'] ?? '', Auth::ip()]
        );
        set_flash('success', 'Yazı silindi.');
        redirect(url('admin/blog.php'));
    }

    if ($action === 'toggle' && $postId > 0) {
        $row = Database::fetch('SELECT `status` FROM `blog_posts` WHERE `id` = ?', [$postId]);
        $newStatus = ($row['status'] ?? 'draft') === 'published' ? 'draft' : 'published';
        $publishedAt = $newStatus === 'published' ? date('Y-m-d H:i:s') : null;
        Database::query('UPDATE `blog_posts` SET `status` = ?, `published_at` = ? WHERE `id` = ?', [$newStatus, $publishedAt, $postId]);
        set_flash('success', 'Yayın durumu güncellendi.');
        redirect(url('admin/blog.php'));
    }
}

$statusFilter = trim((string) ($_GET['status'] ?? ''));
$categoryFilter = trim((string) ($_GET['category'] ?? ''));
$search = trim((string) ($_GET['q'] ?? ''));

$where  = ['1=1'];
$params = [];
if ($statusFilter !== '') {
    $where[] = 'p.status = ?';
    $params[] = $statusFilter;
}
if ($categoryFilter !== '') {
    $where[] = 'bc.slug = ?';
    $params[] = $categoryFilter;
}
if ($search !== '') {
    $where[] = '(p.title LIKE ? OR p.excerpt LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term);
}
$whereSql = implode(' AND ', $where);

$posts = Database::fetchAll(
    "SELECT p.*, bc.name AS category_name, bc.color AS category_color,
            u.first_name AS author_name
     FROM `blog_posts` p
     INNER JOIN `blog_categories` bc ON bc.id = p.category_id
     LEFT JOIN `users` u ON u.id = p.author_id
     WHERE $whereSql
     ORDER BY COALESCE(p.published_at, p.id) DESC",
    $params
);

$categories = Database::fetchAll('SELECT * FROM `blog_categories` ORDER BY sort_order ASC');

$panelType     = 'admin';
$panelActive   = 'blog';
$panelTitle    = 'Blog Yazıları';
$panelSubtitle = 'Kariyer rehberi içeriklerini oluşturun ve yayınlayın';
$panelActions  = '<a href="' . url('admin/blog-edit.php') . '" class="btn btn-accent btn-sm">' . icon('plus', '', 16) . ' Yeni Yazı</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card" style="margin-bottom:20px">
  <div class="pc-body">
    <form method="get" class="filter-row" style="grid-template-columns:2fr 1fr 1fr auto auto">
      <div class="field">
        <label for="bq">Yazı Ara</label>
        <span class="search-box">
          <?= icon('search', '', 18) ?>
          <input type="search" id="bq" name="q" class="input" value="<?= e($search) ?>" placeholder="Başlık veya özet">
        </span>
      </div>
      <div class="field">
        <label for="bcat">Kategori</label>
        <select id="bcat" name="category" class="select">
          <option value="">Tümü</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat['slug']) ?>" <?= $categoryFilter === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="bstatus">Durum</label>
        <select id="bstatus" name="status" class="select">
          <option value="">Tümü</option>
          <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Yayında</option>
          <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Taslak</option>
        </select>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary"><?= icon('filter', '', 16) ?> Filtrele</button>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <a href="<?= url('admin/blog.php') ?>" class="btn btn-outline"><?= icon('refresh-cw', '', 16) ?> Temizle</a>
      </div>
    </form>
  </div>
</section>

<section class="panel-card">
  <div class="pc-head"><div><h2>Yazılar</h2><p class="pc-sub"><?= count($posts) ?> kayıt</p></div></div>

  <?php if (!$posts): ?>
    <div class="pc-body">
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('newspaper', '', 28) ?></div>
        <h3>Yazı bulunamadı</h3>
        <p>Kriterlere uygun blog yazısı yok.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Başlık</th><th>Kategori</th><th>Yazar</th><th>Okuma</th><th>Görüntülenme</th><th>Durum</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($posts as $p): ?>
            <tr>
              <td style="max-width:380px">
                <div style="font-weight:600;color:var(--c-primary);line-height:1.4"><?= e(str_limit($p['title'], 70)) ?></div>
                <div class="uc-mail"><?= e(str_limit($p['excerpt'] ?? '', 62)) ?></div>
              </td>
              <td><span class="badge" style="background:<?= e($p['category_color']) ?>22;color:<?= e($p['category_color']) ?>"><?= e($p['category_name']) ?></span></td>
              <td class="td-nowrap"><?= e($p['author_name'] ?? '—') ?></td>
              <td><span class="badge badge-muted"><?= (int) $p['reading_time'] ?> dk</span></td>
              <td><span class="badge badge-muted"><?= number_format_tr((int) $p['view_count']) ?></span></td>
              <td>
                <?php if ($p['status'] === 'published'): ?>
                  <span class="badge badge-success">Yayında</span>
                <?php else: ?>
                  <span class="badge badge-warning">Taslak</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="td-actions">
                  <a href="<?= url('blog-detail.php?slug=' . urlencode($p['slug'])) ?>" target="_blank" class="btn btn-outline btn-sm" title="Görüntüle"><?= icon('external-link', '', 14) ?></a>
                  <a href="<?= url('admin/blog-edit.php?id=' . (int) $p['id']) ?>" class="btn btn-outline btn-sm" title="Düzenle"><?= icon('pencil', '', 14) ?></a>
                  <form method="post" style="display:inline" data-guard>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="post_id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" title="Yayın durumu"><?= icon($p['status'] === 'published' ? 'eye-off' : 'send', '', 14) ?></button>
                  </form>
                  <form method="post" style="display:inline" data-guard onsubmit="return confirm('Bu yazı silinecek. Devam edilsin mi?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="post_id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" style="color:var(--c-danger)" title="Sil"><?= icon('trash-2', '', 14) ?></button>
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

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
