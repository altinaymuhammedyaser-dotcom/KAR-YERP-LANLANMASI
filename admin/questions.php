<?php
/**
 * Akilli Kariyer - Yonetim: Kariyer testi sorulari
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $action = $_POST['action'] ?? '';
    $questionId = (int) ($_POST['question_id'] ?? 0);

    if ($action === 'delete' && $questionId > 0) {
        $row = Database::fetch('SELECT `question_text` FROM `test_questions` WHERE `id` = ?', [$questionId]);
        Database::query('DELETE FROM `test_questions` WHERE `id` = ?', [$questionId]);
        Database::query(
            'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
            [(int) Auth::id(), 'question_delete', 'question', $questionId, str_limit($row['question_text'] ?? '', 120), Auth::ip()]
        );
        set_flash('success', 'Soru silindi.');
        redirect(url('admin/questions.php'));
    }

    if ($action === 'toggle' && $questionId > 0) {
        Database::query('UPDATE `test_questions` SET `is_active` = 1 - `is_active` WHERE `id` = ?', [$questionId]);
        set_flash('success', 'Soru durumu güncellendi.');
        redirect(url('admin/questions.php'));
    }

    if ($action === 'sort') {
        // Siralamayi kaydet
        Database::transaction(function () {
            foreach ((array) ($_POST['order'] ?? []) as $id => $sort) {
                Database::query('UPDATE `test_questions` SET `sort_order` = ? WHERE `id` = ?', [(int) $sort, (int) $id]);
            }
        });
        set_flash('success', 'Soru sıralaması güncellendi.');
        redirect(url('admin/questions.php'));
    }
}

$categoryFilter = trim((string) ($_GET['category'] ?? ''));
$typeFilter     = trim((string) ($_GET['type'] ?? ''));
$search         = trim((string) ($_GET['q'] ?? ''));

$where  = ['1=1'];
$params = [];
if ($categoryFilter !== '') {
    $where[] = 'tc.slug = ?';
    $params[] = $categoryFilter;
}
if ($typeFilter !== '') {
    $where[] = 'q.question_type = ?';
    $params[] = $typeFilter;
}
if ($search !== '') {
    $where[] = 'q.question_text LIKE ?';
    $params[] = '%' . $search . '%';
}
$whereSql = implode(' AND ', $where);

$questions = Database::fetchAll(
    "SELECT q.*, tc.name AS category_name, tc.slug AS category_slug, tc.dimension_type,
            (SELECT COUNT(*) FROM test_options o WHERE o.question_id = q.id) AS option_sayi,
            (SELECT COUNT(*) FROM test_answers a WHERE a.question_id = q.id) AS cevap_sayi
     FROM `test_questions` q
     INNER JOIN `test_categories` tc ON tc.id = q.category_id
     WHERE $whereSql
     ORDER BY tc.sort_order ASC, q.sort_order ASC, q.id ASC",
    $params
);

$categories = Database::fetchAll(
    'SELECT tc.*, (SELECT COUNT(*) FROM test_questions q WHERE q.category_id = tc.id) AS question_count
     FROM `test_categories` tc ORDER BY tc.sort_order ASC'
);

$typeLabels = [
    'single'   => 'Tek Seçenek',
    'multiple' => 'Çok Seçenek',
    'scale'    => '1-5 Değerlendirme',
    'card'     => 'Kart Seçimi',
    'scenario' => 'Senaryo',
];

$panelType     = 'admin';
$panelActive   = 'questions';
$panelTitle    = 'Kariyer Testi Soruları';
$panelSubtitle = 'Test sorularını, seçeneklerini ve puanlarını yönetin';
$panelActions  = '<a href="' . url('admin/question-edit.php') . '" class="btn btn-accent btn-sm">' . icon('plus', '', 16) . ' Yeni Soru</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card" style="margin-bottom:20px">
  <div class="pc-body">
    <form method="get" class="filter-row" style="grid-template-columns:2fr 1fr 1fr auto auto">
      <div class="field">
        <label for="qq">Soru Ara</label>
        <span class="search-box">
          <?= icon('search', '', 18) ?>
          <input type="search" id="qq" name="q" class="input" value="<?= e($search) ?>" placeholder="Soru metni">
        </span>
      </div>
      <div class="field">
        <label for="qcat">Kategori</label>
        <select id="qcat" name="category" class="select">
          <option value="">Tümü</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat['slug']) ?>" <?= $categoryFilter === $cat['slug'] ? 'selected' : '' ?>>
              <?= e($cat['name']) ?> (<?= (int) $cat['question_count'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="qtype">Soru Tipi</label>
        <select id="qtype" name="type" class="select">
          <option value="">Tümü</option>
          <?php foreach ($typeLabels as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $typeFilter === $k ? 'selected' : '' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary"><?= icon('filter', '', 16) ?> Filtrele</button>
      </div>
      <div class="field filter-actions">
        <label>&nbsp;</label>
        <a href="<?= url('admin/questions.php') ?>" class="btn btn-outline"><?= icon('refresh-cw', '', 16) ?> Temizle</a>
      </div>
    </form>
  </div>
</section>

<div class="grid g-4" style="margin-bottom:20px">
  <?php foreach ($categories as $cat): ?>
    <a href="<?= url('admin/questions.php?category=' . urlencode($cat['slug'])) ?>" class="mini-stat" style="text-decoration:none">
      <span class="ms-ico"><?= icon($cat['icon'] ?? 'clipboard-list', '', 19) ?></span>
      <div>
        <div class="ms-val"><?= (int) $cat['question_count'] ?></div>
        <div class="ms-lab"><?= e($cat['name']) ?></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<section class="panel-card">
  <div class="pc-head">
    <div><h2>Sorular</h2><p class="pc-sub"><?= count($questions) ?> soru listeleniyor</p></div>
  </div>

  <?php if (!$questions): ?>
    <div class="pc-body">
      <div class="empty-state" style="border:0;background:var(--c-bg)">
        <div class="es-ico"><?= icon('clipboard-x', '', 28) ?></div>
        <h3>Soru bulunamadı</h3>
        <p>Arama kriterlerinize uygun soru yok.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>#</th><th>Soru</th><th>Kategori</th><th>Tip</th><th>Seçenek</th><th>Cevap</th><th>Durum</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($questions as $q): ?>
            <tr>
              <td class="td-nowrap"><span class="badge badge-muted"><?= (int) $q['sort_order'] ?></span></td>
              <td style="max-width:420px">
                <div style="font-weight:600;color:var(--c-primary);line-height:1.45"><?= e(str_limit($q['question_text'], 90)) ?></div>
                <?php if (!empty($q['helper_text'])): ?>
                  <div class="uc-mail"><?= e(str_limit($q['helper_text'], 60)) ?></div>
                <?php endif; ?>
              </td>
              <td><span class="badge"><?= e($q['category_name']) ?></span></td>
              <td class="td-nowrap"><span class="badge badge-info"><?= e($typeLabels[$q['question_type']] ?? $q['question_type']) ?></span></td>
              <td><span class="badge badge-muted"><?= (int) $q['option_sayi'] ?></span></td>
              <td><span class="badge badge-muted"><?= (int) $q['cevap_sayi'] ?></span></td>
              <td>
                <?php if ($q['is_active']): ?>
                  <span class="badge badge-success">Aktif</span>
                <?php else: ?>
                  <span class="badge badge-muted">Pasif</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="td-actions">
                  <a href="<?= url('admin/question-edit.php?id=' . (int) $q['id']) ?>" class="btn btn-outline btn-sm" title="Düzenle"><?= icon('pencil', '', 14) ?></a>
                  <form method="post" style="display:inline" data-guard>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="question_id" value="<?= (int) $q['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm" title="Aktif/Pasif"><?= icon($q['is_active'] ? 'eye' : 'eye-off', '', 14) ?></button>
                  </form>
                  <form method="post" style="display:inline" data-guard onsubmit="return confirm('Bu soru ve seçenekleri silinecek. Devam edilsin mi?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="question_id" value="<?= (int) $q['id'] ?>">
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
