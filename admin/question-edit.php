<?php
/**
 * Akilli Kariyer - Yonetim: Soru ekle / duzenle
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\Validator;
use App\Services\CareerMatchingService;

Auth::requireAdmin();

$questionId = (int) ($_GET['id'] ?? 0);
$isEdit     = $questionId > 0;
$errors     = [];

$question = $isEdit ? Database::fetch(
    'SELECT q.*, tc.dimension_type FROM `test_questions` q
     INNER JOIN `test_categories` tc ON tc.id = q.category_id WHERE q.id = ?',
    [$questionId]
) : null;

if ($isEdit && !$question) {
    abort_page(404, 'Soru Bulunamadı', 'Düzenlenecek soru kaydı bulunamadı.', url('admin/questions.php'));
}

$options = $isEdit
    ? Database::fetchAll('SELECT * FROM `test_options` WHERE `question_id` = ? ORDER BY `sort_order`', [$questionId])
    : [];

$categories = Database::fetchAll('SELECT * FROM `test_categories` ORDER BY `sort_order` ASC');

$dimensionTypes = [
    'interest'    => 'İlgi alanı',
    'ability'     => 'Yetenek',
    'preference'  => 'Çalışma tercihi',
    'environment' => 'Çalışma ortamı',
    'none'        => 'Puanlama yapma',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $validator = Validator::make($_POST)->rules([
        'question_text' => 'required|min:10',
        'category_id'   => 'required|integer',
        'question_type' => 'required|in:single,multiple,scale,card,scenario',
        'helper_text'   => 'max:255',
    ]);

    $optionsInput = array_values(array_filter((array) ($_POST['options'] ?? []), static fn($o) => trim((string) ($o['text'] ?? '')) !== ''));

    if (!$validator->validate()) {
        $errors = $validator->errors();
    } elseif (count($optionsInput) < 2) {
        $errors['options'] = 'En az 2 cevap seçeneği eklemelisiniz.';
    } else {
        $type = $_POST['question_type'];
        $max = (int) ($_POST['max_select'] ?? 1);
        $min = (int) ($_POST['min_select'] ?? 1);
        $post = $_POST;

        if ($type === 'multiple') {
            $min = max(1, min($min, count($optionsInput)));
            $max = max($min, min($max, count($optionsInput)));
        } else {
            $min = 1;
            $max = 1;
        }

        $newId = Database::transaction(function () use ($questionId, $isEdit, $post, $optionsInput, $type, $min, $max) {
            $data = [
                'category_id'   => (int) $post['category_id'],
                'question_text' => trim($post['question_text']),
                'helper_text'   => trim($post['helper_text'] ?? '') ?: null,
                'question_type' => $type,
                'min_select'    => $min,
                'max_select'    => $max,
                'sort_order'    => (int) ($post['sort_order'] ?? 0),
                'is_active'     => isset($post['is_active']) ? 1 : 0,
            ];

            if ($isEdit) {
                $sets = [];
                $params = [];
                foreach ($data as $k => $v) {
                    $sets[] = "`$k` = ?";
                    $params[] = $v;
                }
                $params[] = $questionId;
                Database::update('UPDATE `test_questions` SET ' . implode(', ', $sets) . ' WHERE `id` = ?', $params);
                $id = $questionId;
                Database::query('DELETE FROM `test_options` WHERE `question_id` = ?', [$id]);
            } else {
                $cols = array_keys($data);
                $ph = implode(',', array_fill(0, count($cols), '?'));
                $id = Database::insert(
                    'INSERT INTO `test_questions` (`' . implode('`,`', $cols) . '`) VALUES (' . $ph . ')',
                    array_values($data)
                );
            }

            $order = 0;
            foreach ($optionsInput as $opt) {
                Database::query(
                    'INSERT INTO `test_options` (`question_id`,`option_text`,`option_key`,`score_value`,`mapping_type`,`mapping_key`,`sort_order`)
                     VALUES (?,?,?,?,?,?,?)',
                    [
                        $id,
                        trim((string) $opt['text']),
                        trim((string) ($opt['key'] ?? '')) ?: null,
                        (int) ($opt['score'] ?? 3),
                        (string) ($opt['mapping_type'] ?? 'none'),
                        trim((string) ($opt['mapping_key'] ?? '')) ?: null,
                        ++$order,
                    ]
                );
            }

            return $id;
        });

        Database::query(
            'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
            [(int) Auth::id(), $isEdit ? 'question_update' : 'question_create', 'question', $newId, str_limit($_POST['question_text'], 120), Auth::ip()]
        );

        set_flash('success', $isEdit ? 'Soru güncellendi.' : 'Soru eklendi.');
        redirect(url('admin/question-edit.php?id=' . $newId));
    }
}

$optionCount = count($options) ?: 4;

$panelType     = 'admin';
$panelActive   = 'questions';
$panelTitle    = $isEdit ? 'Soruyu Düzenle' : 'Yeni Soru Ekle';
$panelSubtitle = $isEdit ? str_limit($question['question_text'] ?? '', 80) : 'Test sorusunu ve cevap seçeneklerini tanımlayın';
$panelActions  = '<a href="' . url('admin/questions.php') . '" class="btn btn-outline btn-sm">' . icon('arrow-left', '', 15) . ' Listeye Dön</a>';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
<?php endif; ?>

<form method="post" class="panel-form" data-guard>
  <?= csrf_field() ?>

  <div class="layout-aside ratio" style="gap:22px">
    <div class="grid" style="gap:22px">

      <section class="panel-card">
        <div class="pc-head"><h2>Soru Metni</h2></div>
        <div class="pc-body">
          <div class="pf-grid">
            <label class="field full">
              <span>Soru *</span>
              <textarea name="question_text" class="textarea" required style="min-height:100px"><?= e(old('question_text', $question['question_text'] ?? '')) ?></textarea>
            </label>
            <label class="field full">
              <span>Yardımcı Metin</span>
              <input type="text" name="helper_text" class="input" maxlength="255" value="<?= e(old('helper_text', $question['helper_text'] ?? '')) ?>" placeholder="Örn. 1 = Hiç katılmam • 5 = Çok katılırım">
            </label>
            <label class="field">
              <span>Kategori *</span>
              <select name="category_id" class="select" required>
                <option value="">Seçiniz</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int) $cat['id'] ?>" <?= (int) ($question['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?> (<?= e($dimensionTypes[$cat['dimension_type']] ?? '') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="field">
              <span>Soru Tipi *</span>
              <select name="question_type" class="select" required>
                <?php foreach ([
                  'single'   => 'Tek Seçenek',
                  'multiple' => 'Çok Seçenek',
                  'scale'    => '1-5 Değerlendirme',
                  'card'     => 'Kart Seçimi',
                  'scenario' => 'Senaryo',
                ] as $k => $v): ?>
                  <option value="<?= e($k) ?>" <?= ($question['question_type'] ?? 'single') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="field">
              <span>En Az Seçim</span>
              <input type="number" name="min_select" class="input" min="1" max="10" value="<?= (int) ($question['min_select'] ?? 1) ?>">
            </label>
            <label class="field">
              <span>En Çok Seçim</span>
              <input type="number" name="max_select" class="input" min="1" max="10" value="<?= (int) ($question['max_select'] ?? 1) ?>">
            </label>
          </div>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-head">
          <div>
            <h2>Cevap Seçenekleri</h2>
            <p class="pc-sub">Her seçeneğin puanı (1-5) ve hangi boyuta karşılık geldiği eşleştirme hesabında kullanılır</p>
          </div>
          <button type="button" class="btn btn-outline btn-sm" id="add-option"><?= icon('plus', '', 15) ?> Seçenek Ekle</button>
        </div>
        <div class="pc-body">
          <div id="option-list" class="grid" style="gap:12px">
            <?php
            $optRows = $options ?: array_fill(0, 4, null);
            foreach ($optRows as $idx => $opt):
                $mType = $opt['mapping_type'] ?? 'none';
                $mKey  = $opt['mapping_key'] ?? '';
                $labels = CareerMatchingService::labelsFor($mType);
            ?>
              <div class="option-row">
                <div class="option-main">
                  <input type="text" name="options[<?= $idx ?>][text]" class="input" placeholder="Seçenek metni" value="<?= e($opt['option_text'] ?? '') ?>">
                  <input type="text" name="options[<?= $idx ?>][key]" class="input" placeholder="Anahtar" maxlength="10" value="<?= e($opt['option_key'] ?? '') ?>">
                  <select name="options[<?= $idx ?>][score]" class="select">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                      <option value="<?= $s ?>" <?= (int) ($opt['score_value'] ?? 3) === $s ? 'selected' : '' ?>>Puan <?= $s ?></option>
                    <?php endfor; ?>
                  </select>
                  <button type="button" class="btn btn-outline btn-sm remove-option" style="color:var(--c-danger)"><?= icon('trash-2', '', 15) ?></button>
                </div>
                <div class="option-map">
                  <select name="options[<?= $idx ?>][mapping_type]" class="select option-mapping-type">
                    <?php foreach ($dimensionTypes as $mt => $ml): ?>
                      <option value="<?= e($mt) ?>" <?= $mType === $mt ? 'selected' : '' ?>><?= e($ml) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="options[<?= $idx ?>][mapping_key]" class="select option-mapping-key">
                    <option value="">Seçim yok</option>
                    <?php foreach ($labels as $lk => $ll): ?>
                      <option value="<?= e($lk) ?>" <?= $mKey === $lk ? 'selected' : '' ?>><?= e($ll) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    </div>

    <div class="grid" style="gap:22px">
      <section class="panel-card">
        <div class="pc-head"><h3>Yayın Ayarları</h3></div>
        <div class="pc-body grid" style="gap:16px">
          <label class="switch">
            <input type="checkbox" name="is_active" value="1" <?= ($question['is_active'] ?? 1) ? 'checked' : '' ?>>
            <span class="sw-track"></span>
            <span>Aktif</span>
          </label>
          <label class="field">
            <span>Sıra</span>
            <input type="number" name="sort_order" class="input" value="<?= (int) ($question['sort_order'] ?? 0) ?>">
          </label>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-body">
          <button type="submit" class="btn btn-accent btn-block btn-lg">
            <?= icon('save', '', 19) ?> <?= $isEdit ? 'Değişiklikleri Kaydet' : 'Soruyu Oluştur' ?>
          </button>
          <a href="<?= url('admin/questions.php') ?>" class="btn btn-ghost btn-block" style="margin-top:9px">Vazgeç</a>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-head"><h3>Puanlama İpucu</h3></div>
        <div class="pc-body">
          <p style="font-size:.86rem;margin:0 0 12px">
            Seçenek puanları 1-5 arasında olmalıdır. 3 puan "nötr", 5 puan "çok güçlü" anlamına gelir.
            Eşleştirme algoritması bu puanları boyut ortalamasına dönüştürür.
          </p>
          <div class="alert alert-info" style="margin:0;font-size:.84rem">
            <?= icon('info', '', 16) ?>
            <span>Bir seçeneğin boyut eşleştirmesi "Puanlama yapma" olarak bırakılırsa, o seçenek hiçbir boyuta katkı sağlamaz.</span>
          </div>
        </div>
      </section>
    </div>
  </div>
</form>

<script>
(function () {
  var dimensions = <?= json_encode([
      'interest'    => CareerMatchingService::INTEREST_LABELS,
      'ability'     => CareerMatchingService::ABILITY_LABELS,
      'preference'  => CareerMatchingService::PREFERENCE_LABELS,
      'environment' => CareerMatchingService::ENVIRONMENT_LABELS,
  ]) ?>;

  function refreshKeySelects(row) {
    var typeSelect = row.querySelector('.option-mapping-type');
    var keySelect = row.querySelector('.option-mapping-key');
    if (!typeSelect || !keySelect) return;
    var current = keySelect.value;
    var labels = dimensions[typeSelect.value] || {};
    var html = '<option value="">Secim yok</option>';
    Object.keys(labels).forEach(function (k) {
      html += '<option value="' + k + '">' + labels[k] + '</option>';
    });
    keySelect.innerHTML = html;
    if (current && labels[current]) keySelect.value = current;
  }

  document.querySelectorAll('.option-row').forEach(function (row) {
    var typeSelect = row.querySelector('.option-mapping-type');
    if (typeSelect) typeSelect.addEventListener('change', function () { refreshKeySelects(row); });
  });

  var list = document.getElementById('option-list');
  var idx = list.querySelectorAll('.option-row').length;

  document.getElementById('add-option').addEventListener('click', function () {
    var row = document.createElement('div');
    row.className = 'option-row';
    var typeOptions = '';
    var labels = <?= json_encode($dimensionTypes) ?>;
    Object.keys(labels).forEach(function (k) { typeOptions += '<option value="' + k + '">' + labels[k] + '</option>'; });

    row.innerHTML =
      '<div class="option-main">' +
        '<input type="text" name="options[' + idx + '][text]" class="input" placeholder="Secenek metni">' +
        '<input type="text" name="options[' + idx + '][key]" class="input" placeholder="Anahtar" maxlength="10">' +
        '<select name="options[' + idx + '][score]" class="select">' +
          '<option value="1">Puan 1</option><option value="2">Puan 2</option><option value="3" selected>Puan 3</option>' +
          '<option value="4">Puan 4</option><option value="5">Puan 5</option></select>' +
        '<button type="button" class="btn btn-outline btn-sm remove-option" style="color:var(--c-danger)"><i data-lucide="trash-2" style="width:15px;height:15px"></i></button>' +
      '</div>' +
      '<div class="option-map">' +
        '<select name="options[' + idx + '][mapping_type]" class="select option-mapping-type">' + typeOptions + '</select>' +
        '<select name="options[' + idx + '][mapping_key]" class="select option-mapping-key"><option value="">Secim yok</option></select>' +
      '</div>';

    list.appendChild(row);
    idx++;
    refreshKeySelects(row);
    if (window.lucide) window.lucide.createIcons();
  });

  list.addEventListener('click', function (e) {
    if (e.target.closest('.remove-option')) {
      e.target.closest('.option-row').remove();
    }
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
