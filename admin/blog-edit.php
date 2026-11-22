<?php
/**
 * Akilli Kariyer - Yonetim: Blog yazisi ekle / duzenle
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\Validator;
use App\Helpers\Upload;

Auth::requireAdmin();

$postId = (int) ($_GET['id'] ?? 0);
$isEdit = $postId > 0;
$errors = [];

$post = $isEdit ? Database::fetch('SELECT * FROM `blog_posts` WHERE `id` = ?', [$postId]) : null;

if ($isEdit && !$post) {
    abort_page(404, 'Yazı Bulunamadı', 'Düzenlenecek yazı kaydı bulunamadı.', url('admin/blog.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $validator = Validator::make($_POST)->rules([
        'title'       => 'required|min:5|max:220',
        'category_id' => 'required|integer',
        'excerpt'     => 'required|min:20|max:400',
        'content'     => 'required|min:50',
    ]);

    if (!$validator->validate()) {
        $errors = $validator->errors();
    } else {
        $image = $post['image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $upload = Upload::image($_FILES['image']);
            if ($upload['success']) {
                Upload::delete($image);
                $image = $upload['path'];
            } else {
                $errors['image'] = $upload['error'];
            }
        }

        if (!$errors) {
            $slug = slugify((string) $_POST['title'], 'yazi-' . time());
            $base = $slug;
            $n = 1;
            while (Database::fetchColumn('SELECT id FROM `blog_posts` WHERE `slug` = ? AND `id` <> ?', [$slug, $postId])) {
                $slug = $base . '-' . (++$n);
            }

            $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
            $publishedAt = $status === 'published'
                ? ($post['published_at'] ?? date('Y-m-d H:i:s'))
                : null;

            // Okuma suresi (dakika)
            $wordCount = str_word_count(strip_tags((string) $_POST['content']));
            $readingTime = max(1, (int) ceil($wordCount / 200));

            $data = [
                'category_id' => (int) $_POST['category_id'],
                'title'       => trim($_POST['title']),
                'slug'        => $slug,
                'excerpt'     => trim($_POST['excerpt']),
                'content'     => trim($_POST['content']),
                'image'       => $image,
                'status'      => $status,
                'reading_time'=> $readingTime,
                'published_at'=> $publishedAt,
                'author_id'   => $post['author_id'] ?? Auth::id(),
            ];

            if ($isEdit) {
                $sets = [];
                $params = [];
                foreach ($data as $k => $v) {
                    $sets[] = "`$k` = ?";
                    $params[] = $v;
                }
                $params[] = $postId;
                Database::update('UPDATE `blog_posts` SET ' . implode(', ', $sets) . ' WHERE `id` = ?', $params);
                $newId = $postId;
            } else {
                $cols = array_keys($data);
                $ph = implode(',', array_fill(0, count($cols), '?'));
                $newId = Database::insert(
                    'INSERT INTO `blog_posts` (`' . implode('`,`', $cols) . '`) VALUES (' . $ph . ')',
                    array_values($data)
                );
            }

            Database::query(
                'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
                [(int) Auth::id(), $isEdit ? 'post_update' : 'post_create', 'blog_post', $newId, trim($_POST['title']), Auth::ip()]
            );

            set_flash('success', $isEdit ? 'Yazı güncellendi.' : 'Yazı oluşturuldu.');
            redirect(url('admin/blog-edit.php?id=' . $newId));
        }
    }
}

$categories = Database::fetchAll('SELECT * FROM `blog_categories` ORDER BY sort_order ASC');

$panelType     = 'admin';
$panelActive   = 'blog';
$panelTitle    = $isEdit ? 'Yazıyı Düzenle' : 'Yeni Yazı Oluştur';
$panelSubtitle = $isEdit ? str_limit($post['title'] ?? '', 70) : 'Kariyer rehberi içeriği yazın';
$panelActions  = '<a href="' . url('admin/blog.php') . '" class="btn btn-outline btn-sm">' . icon('arrow-left', '', 15) . ' Listeye Dön</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel-form" data-guard>
  <?= csrf_field() ?>

  <div class="layout-aside wide" style="gap:22px">
    <div class="grid" style="gap:22px">
      <section class="panel-card">
        <div class="pc-head"><h2>Yazı İçeriği</h2></div>
        <div class="pc-body">
          <div class="panel-form">
            <label class="field">
              <span>Başlık *</span>
              <input type="text" name="title" class="input" required maxlength="220" value="<?= e(old('title', $post['title'] ?? '')) ?>">
            </label>

            <label class="field">
              <span>Özet * <small class="text-muted-c">(kartlarda görünür, maks. 400 karakter)</small></span>
              <textarea name="excerpt" class="textarea" required maxlength="400" style="min-height:80px"><?= e(old('excerpt', $post['excerpt'] ?? '')) ?></textarea>
            </label>

            <label class="field">
              <span>İçerik * <small class="text-muted-c">(HTML desteklenir)</small></span>
              <div class="flex gap-8" style="margin-bottom:8px;flex-wrap:wrap">
                <?php
                $tools = [
                    ['h2', 'Başlık 2', '<h2>', '</h2>'],
                    ['h3', 'Başlık 3', '<h3>', '</h3>'],
                    ['p', 'Paragraf', '<p>', '</p>'],
                    ['b', 'Kalın', '<strong>', '</strong>'],
                    ['i', 'İtalik', '<em>', '</em>'],
                    ['ul', 'Liste', '<ul>', '</ul>'],
                    ['quote', 'Alıntı', '<blockquote>', '</blockquote>'],
                ];
                foreach ($tools as $t): ?>
                  <button type="button" class="btn btn-outline btn-sm" data-tag="<?= e($t[0]) ?>"><?= e($t[1]) ?></button>
                <?php endforeach; ?>
              </div>
              <textarea name="content" id="content-editor" class="textarea" required style="min-height:380px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.9rem"><?= e(old('content', $post['content'] ?? '')) ?></textarea>
            </label>
          </div>
        </div>
      </section>
    </div>

    <div class="grid" style="gap:22px">
      <section class="panel-card">
        <div class="pc-head"><h3>Yayın Ayarları</h3></div>
        <div class="pc-body grid" style="gap:16px">
          <label class="field">
            <span>Kategori *</span>
            <select name="category_id" class="select" required>
              <option value="">Seçiniz</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= (int) ($post['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label class="field">
            <span>Durum</span>
            <select name="status" class="select">
              <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Taslak</option>
              <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>Yayında</option>
            </select>
          </label>

          <label class="field">
            <span>Kapak Görseli</span>
            <input type="file" name="image" class="input" accept="image/*">
            <span class="hint">JPG, PNG, WEBP veya GIF • maks. 2 MB</span>
            <?php if (!empty($post['image'])): ?>
              <span class="hint"><img src="<?= e(Upload::url($post['image'])) ?>" alt="" style="max-width:100%;border-radius:8px;margin-top:8px"></span>
            <?php endif; ?>
          </label>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-body">
          <button type="submit" class="btn btn-accent btn-block btn-lg">
            <?= icon('save', '', 19) ?> <?= $isEdit ? 'Değişiklikleri Kaydet' : 'Yazıyı Oluştur' ?>
          </button>
          <a href="<?= url('admin/blog.php') ?>" class="btn btn-ghost btn-block" style="margin-top:9px">Vazgeç</a>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-head"><h3>Önizleme</h3></div>
        <div class="pc-body">
          <div id="preview" style="font-size:.88rem;line-height:1.7;color:var(--c-text);max-height:320px;overflow:auto">
            <p style="color:var(--c-muted)">Yazdığınız içerik burada önizlenir.</p>
          </div>
        </div>
      </section>
    </div>
  </div>
</form>

<script>
(function () {
  var editor = document.getElementById('content-editor');
  var preview = document.getElementById('preview');
  var tags = {
    h2: ['<h2>', '</h2>'], h3: ['<h3>', '</h3>'], p: ['<p>', '</p>'],
    b: ['<strong>', '</strong>'], i: ['<em>', '</em>'],
    ul: ['<ul>\n  <li>', '</li>\n</ul>'], quote: ['<blockquote>', '</blockquote>']
  };

  document.querySelectorAll('[data-tag]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var t = tags[btn.dataset.tag];
      var start = editor.selectionStart;
      var end = editor.selectionEnd;
      var selected = editor.value.substring(start, end) || 'Metin';
      editor.value = editor.value.substring(0, start) + t[0] + selected + t[1] + editor.value.substring(end);
      editor.focus();
      updatePreview();
    });
  });

  function updatePreview() {
    preview.innerHTML = editor.value || '<p style="color:var(--c-muted)">Yazdığınız içerik burada önizlenir.</p>';
  }

  editor.addEventListener('input', updatePreview);
  updatePreview();
})();
</script>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
