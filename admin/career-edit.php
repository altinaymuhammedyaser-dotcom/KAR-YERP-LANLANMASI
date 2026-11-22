<?php
/**
 * Akilli Kariyer - Yonetim: Meslek ekle / duzenle
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\Validator;
use App\Helpers\Upload;
use App\Models\Career;
use App\Services\CareerMatchingService;

Auth::requireAdmin();

$careerId = (int) ($_GET['id'] ?? 0);
$isEdit   = $careerId > 0;
$errors   = [];

$career = $isEdit ? Database::fetch('SELECT * FROM `careers` WHERE `id` = ?', [$careerId]) : null;

if ($isEdit && !$career) {
    abort_page(404, 'Meslek Bulunamadı', 'Düzenlenecek meslek kaydı bulunamadı.', url('admin/careers.php'));
}

// Mevcut kriterler
$existing = [
    'interest'    => [],
    'ability'     => [],
    'preference'  => [],
    'environment' => [],
    'competency'  => [],
];
if ($isEdit) {
    foreach (['interest' => 'career_interests', 'ability' => 'career_skills', 'preference' => 'career_preferences', 'environment' => 'career_environments'] as $type => $table) {
        $keyField = $type === 'interest' ? 'interest_key' : ($type === 'ability' ? 'ability_key' : ($type === 'preference' ? 'preference_key' : 'environment_key'));
        $rows = Database::fetchAll("SELECT `$keyField` AS k, `weight` FROM `$table` WHERE `career_id` = ?", [$careerId]);
        foreach ($rows as $r) {
            $existing[$type][$r['k']] = (int) $r['weight'];
        }
    }
    $existing['competency'] = Database::fetchAll('SELECT * FROM `career_competencies` WHERE `career_id` = ? ORDER BY `sort_order`', [$careerId]);
}

// Mevcut gereksinimler
$requirements = $isEdit ? Career::requirements($careerId) : [];
$reqGroups = [];
foreach ($requirements as $r) {
    $reqGroups[$r['requirement_type']][] = $r['content'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();

    $validator = Validator::make($_POST)->rules([
        'name'          => 'required|min:2|max:160',
        'category_id'   => 'required|integer',
        'short_description' => 'required|min:10|max:320',
        'description'   => 'required|min:20',
        'education_level' => 'required|in:ortaokul,lise,universite,diger',
        'education_detail' => 'max:255',
        'working_areas' => 'max:255',
        'salary_range'  => 'max:120',
        'icon'          => 'max:60',
    ]);

    if (!$validator->validate()) {
        $errors = $validator->errors();
    } else {
        // Gorsel yukleme
        $image = $career['image'] ?? null;
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
            $slug = slugify((string) $_POST['name'], 'meslek-' . time());

            // Slug benzersizligi
            $base = $slug;
            $n = 1;
            while (Database::fetchColumn('SELECT id FROM `careers` WHERE `slug` = ? AND `id` <> ?', [$slug, $careerId])) {
                $slug = $base . '-' . (++$n);
            }

            $data = [
                'category_id'      => (int) $_POST['category_id'],
                'name'             => trim($_POST['name']),
                'slug'             => $slug,
                'short_description'=> trim($_POST['short_description']),
                'description'      => trim($_POST['description']),
                'image'            => $image,
                'icon'             => trim($_POST['icon'] ?? 'briefcase'),
                'education_level'  => $_POST['education_level'],
                'education_detail' => trim($_POST['education_detail'] ?? ''),
                'working_areas'    => trim($_POST['working_areas'] ?? ''),
                'career_path'      => implode(' → ', array_filter(array_map('trim', explode("\n", (string) ($_POST['career_path'] ?? ''))))),
                'advantages'       => implode(' • ', array_filter(array_map('trim', explode("\n", (string) ($_POST['advantages'] ?? ''))))),
                'salary_range'     => trim($_POST['salary_range'] ?? ''),
                'is_featured'      => isset($_POST['is_featured']) ? 1 : 0,
                'is_active'        => isset($_POST['is_active']) ? 1 : 0,
                'sort_order'       => (int) ($_POST['sort_order'] ?? 0),
            ];

            Database::transaction(function () use ($data, $careerId, $isEdit) {
                if ($isEdit) {
                    $sets = [];
                    $params = [];
                    foreach ($data as $k => $v) {
                        $sets[] = "`$k` = ?";
                        $params[] = $v;
                    }
                    $params[] = $careerId;
                    Database::update('UPDATE `careers` SET ' . implode(', ', $sets) . ' WHERE `id` = ?', $params);
                    $newId = $careerId;
                } else {
                    $cols = array_keys($data);
                    $ph = implode(',', array_fill(0, count($cols), '?'));
                    $newId = Database::insert(
                        'INSERT INTO `careers` (`' . implode('`,`', $cols) . '`) VALUES (' . $ph . ')',
                        array_values($data)
                    );
                }

                // Kriterleri guncelle
                $tables = [
                    'interest'    => ['career_interests', 'interest_key'],
                    'ability'     => ['career_skills', 'ability_key'],
                    'preference'  => ['career_preferences', 'preference_key'],
                    'environment' => ['career_environments', 'environment_key'],
                ];
                foreach ($tables as $type => [$table, $field]) {
                    Database::query("DELETE FROM `$table` WHERE `career_id` = ?", [$newId]);
                    foreach ((array) ($_POST['criteria'][$type] ?? []) as $key => $weight) {
                        $weight = max(1, min(5, (int) $weight));
                        if ($weight > 0) {
                            Database::query(
                                "INSERT INTO `$table` (`career_id`,`$field`,`weight`) VALUES (?,?,?)",
                                [$newId, $key, $weight]
                            );
                        }
                    }
                }

                // Beceriler
                Database::query('DELETE FROM `career_competencies` WHERE `career_id` = ?', [$newId]);
                $compRows = [];
                $order = 0;
                foreach ((array) ($_POST['competencies'] ?? []) as $comp) {
                    $name = trim((string) ($comp['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $abilityKey = trim((string) ($comp['ability'] ?? ''));
                    $weight = max(1, min(5, (int) ($comp['weight'] ?? 3)));
                    $compRows[] = [$newId, $name, $abilityKey !== '' ? $abilityKey : null, $weight, ++$order];
                }
                if ($compRows) {
                    Database::query(
                        'INSERT INTO `career_competencies` (`career_id`,`competency_name`,`ability_key`,`weight`,`sort_order`) VALUES ' .
                        implode(',', array_fill(0, count($compRows), '(?,?,?,?,?)')),
                        array_merge(...$compRows)
                    );
                }

                // Metinsel gereksinimler
                Database::query('DELETE FROM `career_requirements` WHERE `career_id` = ?', [$newId]);
                $reqRows = [];
                $order = 0;
                $textGroups = [
                    'task'        => 'tasks',
                    'department'  => 'departments',
                    'advantage'   => 'advantages_list',
                    'certificate' => 'certificates',
                ];
                foreach ($textGroups as $type => $field) {
                    foreach ((array) ($_POST[$field] ?? []) as $line) {
                        $line = trim((string) $line);
                        if ($line === '') {
                            continue;
                        }
                        $reqRows[] = [$newId, $type, null, $line, ++$order];
                    }
                }
                // Egitim ve calisma alani
                if (!empty($data['education_detail'])) {
                    $reqRows[] = [$newId, 'education', 'Gerekli Eğitim', $data['education_detail'], ++$order];
                }
                if (!empty($data['working_areas'])) {
                    $reqRows[] = [$newId, 'work_area', 'Çalışma Alanları', $data['working_areas'], ++$order];
                }
                if ($compRows) {
                    foreach ($compRows as $cr) {
                        $reqRows[] = [$newId, 'skill', null, $cr[1], ++$order];
                    }
                }
                if ($reqRows) {
                    Database::query(
                        'INSERT INTO `career_requirements` (`career_id`,`requirement_type`,`title`,`content`,`sort_order`) VALUES ' .
                        implode(',', array_fill(0, count($reqRows), '(?,?,?,?,?)')),
                        array_merge(...$reqRows)
                    );
                }
            });

            Database::query(
                'INSERT INTO `audit_logs` (`user_id`,`action`,`entity_type`,`entity_id`,`details`,`ip_address`) VALUES (?,?,?,?,?,?)',
                [(int) Auth::id(), $isEdit ? 'career_update' : 'career_create', 'career', $careerId, trim($_POST['name']), Auth::ip()]
            );

            set_flash('success', $isEdit ? 'Meslek güncellendi.' : 'Meslek oluşturuldu.');
            redirect(url('admin/career-edit.php?id=' . (int) Database::fetchColumn('SELECT LAST_INSERT_ID()') ?: 0));
        }
    }
}

$categories = Career::categories(false);
$icons = ['briefcase','code-2','layout-template','bar-chart-3','shield-check','server','brain-circuit','headset',
          'stethoscope','syringe','pill','smile','accessibility','brain','graduation-cap','sigma','book-open',
          'messages-square','presentation','cog','building-2','wheat','leaf','plane','palette','box','landmark',
          'armchair','camera','clapperboard','scale','gavel','file-signature','rocket','line-chart','megaphone',
          'users','calculator','truck','flask-conical','dna','atom','cloud-rain','microscope','newspaper',
          'edit-3','share-2','siren','dumbbell','activity','circle-dot','handshake','compass'];

$panelType     = 'admin';
$panelActive   = 'careers';
$panelTitle    = $isEdit ? 'Mesleği Düzenle' : 'Yeni Meslek Ekle';
$panelSubtitle = $isEdit ? $career['name'] : 'Meslek kataloğuna yeni bir meslek ekleyin';
$panelActions  = '<a href="' . url('admin/careers.php') . '" class="btn btn-outline btn-sm">' . icon('arrow-left', '', 15) . ' Listeye Dön</a>';

require_once __DIR__ . '/../includes/panel-header.php';

$v = static function (string $key, $default = '') {
    return e(old($key, $default));
};
?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel-form" data-guard>
  <?= csrf_field() ?>

  <div class="layout-aside wide" style="gap:22px">
    <div class="grid" style="gap:22px">

      <!-- Temel bilgiler -->
      <section class="panel-card">
        <div class="pc-head"><h2>Temel Bilgiler</h2></div>
        <div class="pc-body">
          <div class="pf-grid">
            <label class="field">
              <span>Meslek Adı *</span>
              <input type="text" name="name" class="input" required maxlength="160" value="<?= $v('name', $career['name'] ?? '') ?>">
            </label>
            <label class="field">
              <span>Kategori *</span>
              <select name="category_id" class="select" required>
                <option value="">Seçiniz</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int) $cat['id'] ?>" <?= (int) ($career['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="field full">
              <span>Kısa Tanıtım * <small class="text-muted-c">(kartlarda görünür, maks. 320 karakter)</small></span>
              <textarea name="short_description" class="textarea" required maxlength="320" style="min-height:80px"><?= $v('short_description', $career['short_description'] ?? '') ?></textarea>
            </label>
            <label class="field full">
              <span>Genel Tanıtım *</span>
              <textarea name="description" class="textarea" required style="min-height:170px"><?= $v('description', $career['description'] ?? '') ?></textarea>
            </label>
            <label class="field">
              <span>İkon</span>
              <select name="icon" class="select">
                <?php foreach ($icons as $ic): ?>
                  <option value="<?= e($ic) ?>" <?= ($career['icon'] ?? 'briefcase') === $ic ? 'selected' : '' ?>><?= e($ic) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="field">
              <span>Görsel</span>
              <input type="file" name="image" class="input" accept="image/*">
              <span class="hint">JPG, PNG, WEBP veya GIF • maks. 2 MB</span>
              <?php if (!empty($career['image'])): ?>
                <span class="hint"><img src="<?= e(Upload::url($career['image'])) ?>" alt="" style="max-width:90px;border-radius:8px;margin-top:8px"></span>
              <?php endif; ?>
            </label>
          </div>
        </div>
      </section>

      <!-- Eslestirme kriterleri -->
      <section class="panel-card">
        <div class="pc-head">
          <div>
            <h2>Eşleştirme Kriterleri</h2>
            <p class="pc-sub">Bu değerler, kullanıcı test sonuçlarıyla mesleğin uyum puanını hesaplar</p>
          </div>
        </div>
        <div class="pc-body">
          <?php
          $criteriaGroups = [
              ['interest', 'İlgi Alanı Kriterleri', 'Kullanıcının hangi ilgi alanları bu mesleğe uygun?', CareerMatchingService::INTEREST_LABELS],
              ['ability', 'Yetenek Kriterleri', 'Bu meslek hangi yetenekleri önceliklendirir?', CareerMatchingService::ABILITY_LABELS],
              ['preference', 'Çalışma Tercihi Kriterleri', 'Hangi çalışma biçimleri bu mesleğe uygun?', CareerMatchingService::PREFERENCE_LABELS],
              ['environment', 'Çalışma Ortamı Kriterleri', 'Hangi ortamlar bu mesleğe uygun?', CareerMatchingService::ENVIRONMENT_LABELS],
          ];
          foreach ($criteriaGroups as [$type, $title, $desc, $labels]): ?>
            <div class="pf-section" style="margin-bottom:16px">
              <h4><?= icon('target', '', 17) ?> <?= e($title) ?></h4>
              <p class="pf-sub"><?= e($desc) ?> <strong>1</strong> = düşük öncelik, <strong>5</strong> = yüksek öncelik</p>
              <div class="grid" style="gap:9px">
                <?php foreach ($labels as $key => $label): ?>
                  <?php $w = (int) ($existing[$type][$key] ?? 0); ?>
                  <label class="weight-row" style="padding:8px 0">
                    <span class="wr-label" style="font-size:.86rem"><?= e($label) ?></span>
                    <span style="display:flex;align-items:center;gap:12px">
                      <input type="checkbox" class="w-toggle" style="width:17px;height:17px;accent-color:var(--c-accent)" data-target="w-<?= e($type) ?>-<?= e($key) ?>">
                      <input type="range" name="criteria[<?= e($type) ?>][<?= e($key) ?>]" id="w-<?= e($type) ?>-<?= e($key) ?>"
                             min="0" max="5" step="1" value="<?= $w ?>" style="flex:1">
                    </span>
                    <span class="wr-val" id="lbl-<?= e($type) ?>-<?= e($key) ?>"><?= $w ?: '—' ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Beceriler -->
      <section class="panel-card">
        <div class="pc-head">
          <div>
            <h2>Meslek Becerileri</h2>
            <p class="pc-sub">Beceri uyumu, bu becerilerin hangi yeteneğe karşılık geldiğine göre hesaplanır</p>
          </div>
        </div>
        <div class="pc-body">
          <div id="competency-list" class="grid" style="gap:12px">
            <?php
            $compList = $existing['competency'] ?: [];
            $compList[] = ['competency_name' => '', 'ability_key' => '', 'weight' => 3];
            foreach ($compList as $idx => $comp): ?>
              <div class="competency-row">
                <input type="text" name="competencies[<?= $idx ?>][name]" class="input" placeholder="Beceri adı (örn. Kod yazma)"
                       value="<?= e($comp['competency_name'] ?? '') ?>">
                <select name="competencies[<?= $idx ?>][ability]" class="select">
                  <option value="">Yetenek seçin</option>
                  <?php foreach (CareerMatchingService::ABILITY_LABELS as $ak => $al): ?>
                    <option value="<?= e($ak) ?>" <?= ($comp['ability_key'] ?? '') === $ak ? 'selected' : '' ?>><?= e($al) ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="competencies[<?= $idx ?>][weight]" class="select">
                  <?php for ($w = 1; $w <= 5; $w++): ?>
                    <option value="<?= $w ?>" <?= (int) ($comp['weight'] ?? 3) === $w ? 'selected' : '' ?>><?= $w ?></option>
                  <?php endfor; ?>
                </select>
                <button type="button" class="btn btn-outline btn-sm remove-comp" style="color:var(--c-danger)">
                  <?= icon('trash-2', '', 15) ?>
                </button>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn btn-outline btn-sm" id="add-competency" style="margin-top:14px">
            <?= icon('plus', '', 15) ?> Beceri Ekle
          </button>
        </div>
      </section>

      <!-- Görevler ve diger listeler -->
      <section class="panel-card">
        <div class="pc-head"><h2>Görevler, Bölümler ve Diğerleri</h2><p class="pc-sub">Her satır ayrı bir kayıt olarak eklenir</p></div>
        <div class="pc-body">
          <div class="pf-grid">
            <label class="field">
              <span>Mesleğin Görevleri (her satıra bir görev)</span>
              <textarea name="tasks[]" class="textarea" style="min-height:150px" placeholder="Görev 1&#10;Görev 2"><?php foreach ($reqGroups['task'] ?? [] as $t): ?><?= e($t) ?>&#10;<?php endforeach; ?></textarea>
            </label>
            <label class="field">
              <span>Üniversite Bölüm Örnekleri</span>
              <textarea name="departments[]" class="textarea" style="min-height:150px" placeholder="Bilgisayar Muhendisligi&#10;Yazilim Muhendisligi"><?php foreach ($reqGroups['department'] ?? [] as $d): ?><?= e($d) ?>&#10;<?php endforeach; ?></textarea>
            </label>
            <label class="field">
              <span>Avantajlar ve Çalışma Koşulları</span>
              <textarea name="advantages_list[]" class="textarea" style="min-height:150px" placeholder="Avantaj 1&#10;Avantaj 2"><?php foreach ($reqGroups['advantage'] ?? [] as $a): ?><?= e($a) ?>&#10;<?php endforeach; ?></textarea>
            </label>
            <label class="field">
              <span>Faydalı Sertifikalar</span>
              <textarea name="certificates[]" class="textarea" style="min-height:150px" placeholder="Sertifika 1&#10;Sertifika 2"><?php foreach ($reqGroups['certificate'] ?? [] as $c): ?><?= e($c) ?>&#10;<?php endforeach; ?></textarea>
            </label>
            <label class="field full">
              <span>Kariyer Gelişim Yolu (her satıra bir aşama)</span>
              <textarea name="career_path" class="textarea" style="min-height:110px" placeholder="Staj&#10;Kidemli Muhendis&#10;Teknik Direktor"><?= $v('career_path', $career['career_path'] ?? '') ?></textarea>
            </label>
          </div>
        </div>
      </section>
    </div>

    <!-- Yan panel -->
    <div class="grid" style="gap:22px">
      <section class="panel-card">
        <div class="pc-head"><h3>Yayın Ayarları</h3></div>
        <div class="pc-body grid" style="gap:16px">
          <label class="switch">
            <input type="checkbox" name="is_active" value="1" <?= ($career['is_active'] ?? 1) ? 'checked' : '' ?>>
            <span class="sw-track"></span>
            <span>Yayında</span>
          </label>
          <label class="switch">
            <input type="checkbox" name="is_featured" value="1" <?= ($career['is_featured'] ?? 0) ? 'checked' : '' ?>>
            <span class="sw-track"></span>
            <span>Öne çıkan meslek</span>
          </label>
          <label class="field">
            <span>Sıralama</span>
            <input type="number" name="sort_order" class="input" value="<?= (int) ($career['sort_order'] ?? 0) ?>">
          </label>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-head"><h3>Eğitim & Maaş</h3></div>
        <div class="pc-body grid" style="gap:16px">
          <label class="field">
            <span>Eğitim Seviyesi *</span>
            <select name="education_level" class="select" required>
              <?php foreach (Career::educationLevels() as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= ($career['education_level'] ?? 'universite') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="field">
            <span>Eğitim Detayı</span>
            <input type="text" name="education_detail" class="input" maxlength="255" value="<?= $v('education_detail', $career['education_detail'] ?? '') ?>" placeholder="Örn. Bilgisayar Mühendisligi (4 yil)">
          </label>
          <label class="field">
            <span>Çalışma Alanları</span>
            <input type="text" name="working_areas" class="input" maxlength="255" value="<?= $v('working_areas', $career['working_areas'] ?? '') ?>">
          </label>
          <label class="field">
            <span>Maaş Aralığı</span>
            <input type="text" name="salary_range" class="input" maxlength="120" value="<?= $v('salary_range', $career['salary_range'] ?? '') ?>" placeholder="Örn. 45.000 - 120.000 TL">
          </label>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-body">
          <button type="submit" class="btn btn-accent btn-block btn-lg">
            <?= icon('save', '', 19) ?> <?= $isEdit ? 'Değişiklikleri Kaydet' : 'Mesleği Oluştur' ?>
          </button>
          <a href="<?= url('admin/careers.php') ?>" class="btn btn-ghost btn-block" style="margin-top:9px">Vazgeç</a>
        </div>
      </section>
    </div>
  </div>
</form>

<script>
(function () {
  // Kriter aralikleri
  document.querySelectorAll('input[type=range]').forEach(function (r) {
    var label = document.getElementById('lbl-' + r.id.replace('w-', ''));
    var toggle = document.querySelector('.w-toggle[data-target="' + r.id + '"]');
    function sync() {
      if (label) label.textContent = r.value > 0 ? r.value : '—';
    }
    r.addEventListener('input', sync);
    sync();
    if (toggle) {
      toggle.addEventListener('change', function () {
        if (toggle.checked && r.value === '0') { r.value = '3'; }
        if (!toggle.checked) { r.value = '0'; }
        sync();
      });
    }
  });

  // Beceri ekleme
  var list = document.getElementById('competency-list');
  var addBtn = document.getElementById('add-competency');
  var index = list.querySelectorAll('.competency-row').length;

  addBtn.addEventListener('click', function () {
    var abilities = <?= json_encode(array_map(static fn($k) => [$k, CareerMatchingService::ABILITY_LABELS[$k]], array_keys(CareerMatchingService::ABILITY_LABELS))) ?>;
    var options = '<option value="">Yetenek seçin</option>';
    abilities.forEach(function (a) { options += '<option value="' + a[0] + '">' + a[1] + '</option>'; });
    var row = document.createElement('div');
    row.className = 'competency-row';
    row.innerHTML =
      '<input type="text" name="competencies[' + index + '][name]" class="input" placeholder="Beceri adı (örn. Kod yazma)">' +
      '<select name="competencies[' + index + '][ability]" class="select">' + options + '</select>' +
      '<select name="competencies[' + index + '][weight]" class="select">' +
        '<option value="1">1</option><option value="2">2</option><option value="3" selected>3</option>' +
        '<option value="4">4</option><option value="5">5</option></select>' +
      '<button type="button" class="btn btn-outline btn-sm remove-comp" style="color:var(--c-danger)"><i data-lucide="trash-2" style="width:15px;height:15px"></i></button>';
    list.appendChild(row);
    index++;
    if (window.lucide) window.lucide.createIcons();
  });

  list.addEventListener('click', function (e) {
    if (e.target.closest('.remove-comp')) {
      e.target.closest('.competency-row').remove();
    }
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
