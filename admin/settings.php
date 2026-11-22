<?php
/**
 * Akilli Kariyer - Yonetim: Sistem ayarlari
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Models\Setting;
use App\Services\RecommendationService;

Auth::requireAdmin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_general') {
        $pairs = [];
        foreach (['site_name', 'site_slogan', 'site_description', 'site_email', 'site_phone', 'site_address', 'site_working_hours'] as $key) {
            if (isset($_POST[$key])) {
                $pairs[$key] = trim((string) $_POST[$key]);
            }
        }
        Setting::setMany($pairs);
        set_flash('success', 'Genel ayarlar kaydedildi.');
        redirect(url('admin/settings.php#genel'));
    }

    if ($action === 'save_weights') {
        $weights = [
            'weight_interest'         => (int) ($_POST['weight_interest'] ?? 35),
            'weight_ability'          => (int) ($_POST['weight_ability'] ?? 30),
            'weight_preference'       => (int) ($_POST['weight_preference'] ?? 20),
            'weight_skill'            => (int) ($_POST['weight_skill'] ?? 15),
            'sub_weight_environment'  => (int) ($_POST['sub_weight_environment'] ?? 8),
            'recommendation_limit'    => (int) ($_POST['recommendation_limit'] ?? 12),
        ];

        $total = $weights['weight_interest'] + $weights['weight_ability'] + $weights['weight_preference'] + $weights['weight_skill'];
        if ($total <= 0) {
            $errors['weights'] = 'Ağırlıkların toplamı sıfırdan büyük olmalıdır.';
        } elseif ($weights['sub_weight_environment'] > $weights['weight_preference']) {
            $errors['weights'] = 'Ortam alt ağırlığı, çalışma tercihi ağırlığından büyük olamaz.';
        } else {
            Setting::setMany($weights);

            if (isset($_POST['recalculate'])) {
                $count = RecommendationService::recalculateAll();
                set_flash('success', 'Ağırlıklar kaydedildi ve ' . $count . ' test sonucu yeniden hesaplandı.');
            } else {
                set_flash('success', 'Eşleştirme ağırlıkları kaydedildi.');
            }
            redirect(url('admin/settings.php#puanlama'));
        }
    }

    if ($action === 'save_registration') {
        Setting::setMany([
            'registration_open'            => isset($_POST['registration_open']) ? '1' : '0',
            'require_kvkk'                => isset($_POST['require_kvkk']) ? '1' : '0',
            'min_password_length'         => (int) ($_POST['min_password_length'] ?? 8),
            'max_login_attempts'          => (int) ($_POST['max_login_attempts'] ?? 5),
            'login_lockout_minutes'       => (int) ($_POST['login_lockout_minutes'] ?? 15),
            'password_reset_token_lifetime' => (int) ($_POST['password_reset_token_lifetime'] ?? 60),
            'test_questions_per_session'  => (int) ($_POST['test_questions_per_session'] ?? 0),
            'show_results_disclaimer'     => isset($_POST['show_results_disclaimer']) ? '1' : '0',
        ]);
        set_flash('success', 'Kayıt ve güvenlik ayarları kaydedildi.');
        redirect(url('admin/settings.php#kayit'));
    }
}

$settings = Setting::all();
$groupLabels = [
    'genel'    => 'Genel Bilgiler',
    'iletisim' => 'İletişim Bilgileri',
    'puanlama' => 'Eşleştirme Ağırlıkları',
    'kayit'    => 'Kayıt ve Test Ayarları',
    'guvenlik' => 'Güvenlik Ayarları',
];

$panelType     = 'admin';
$panelActive   = 'settings';
$panelTitle    = 'Sistem Ayarları';
$panelSubtitle = 'Site bilgileri, eşleştirme ağırlıkları ve güvenlik ayarları';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
<?php endif; ?>

<div class="tabs" style="margin-bottom:22px">
  <button type="button" class="tab-btn active" data-tab="genel"><?= icon('settings', '', 15) ?> Genel</button>
  <button type="button" class="tab-btn" data-tab="puanlama"><?= icon('scale', '', 15) ?> Puanlama</button>
  <button type="button" class="tab-btn" data-tab="kayit"><?= icon('user-cog', '', 15) ?> Kayıt & Güvenlik</button>
</div>

<!-- GENEL -->
<div class="tab-panel active" data-panel="genel">
  <form method="post" class="panel-form" data-guard>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_general">

    <div class="layout-aside ratio" style="gap:22px">
      <section class="panel-card">
        <div class="pc-head"><h2>Site Bilgileri</h2></div>
        <div class="pc-body">
          <div class="panel-form">
            <div class="pf-grid">
              <label class="field">
                <span>Site Adı</span>
                <input type="text" name="site_name" class="input" value="<?= e(setting('site_name', 'Akıllı Kariyer')) ?>">
              </label>
              <label class="field">
                <span>Slogan</span>
                <input type="text" name="site_slogan" class="input" value="<?= e(setting('site_slogan', 'Geleceğini Keşfet')) ?>">
              </label>
              <label class="field full">
                <span>Site Açıklaması</span>
                <textarea name="site_description" class="textarea" style="min-height:100px"><?= e(setting('site_description', '')) ?></textarea>
              </label>
            </div>
          </div>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-head"><h2>İletişim Bilgileri</h2></div>
        <div class="pc-body">
          <div class="panel-form">
            <label class="field">
              <span>E-posta</span>
              <input type="email" name="site_email" class="input" value="<?= e(setting('site_email', '')) ?>">
            </label>
            <label class="field">
              <span>Telefon</span>
              <input type="text" name="site_phone" class="input" value="<?= e(setting('site_phone', '')) ?>">
            </label>
            <label class="field">
              <span>Çalışma Saatleri</span>
              <input type="text" name="site_working_hours" class="input" value="<?= e(setting('site_working_hours', '')) ?>">
            </label>
            <label class="field">
              <span>Adres</span>
              <textarea name="site_address" class="textarea" style="min-height:90px"><?= e(setting('site_address', '')) ?></textarea>
            </label>
            <button type="submit" class="btn btn-accent btn-block"><?= icon('save', '', 17) ?> Kaydet</button>
          </div>
        </div>
      </section>
    </div>
  </form>
</div>

<!-- PUANLAMA -->
<div class="tab-panel" data-panel="puanlama">
  <form method="post" class="panel-form" data-guard>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_weights">

    <div class="layout-aside ratio" style="gap:22px">
      <section class="panel-card">
        <div class="pc-head">
          <div>
            <h2>Eşleştirme Ağırlıkları</h2>
            <p class="pc-sub">Meslek uyum puanının bileşenlere göre hesaplanma oranları</p>
          </div>
        </div>
        <div class="pc-body">
          <div class="alert alert-info" style="margin-bottom:18px">
            <?= icon('info', '', 18) ?>
            <span>Ağırlıklar, kullanıcıların test sonuçları ile meslek kriterleri arasındaki
              eşleşme puanını doğrudan etkiler. Toplamları 100 olmalıdır.</span>
          </div>

          <?php
          $weightFields = [
              ['weight_interest',        'İlgi Alanı Uyumu',   'Kullanıcının ilgi alanlarının mesleğin ilgi kriterleriyle uyumu'],
              ['weight_ability',         'Yetenek Uyumu',      'Kullanıcının yetenek puanlarının meslek yetenekleriyle uyumu'],
              ['weight_preference',      'Çalışma Tercihi',   'Çalışma tercihi ve çalışma ortamı uyumunun toplam ağırlığı'],
              ['weight_skill',           'Beceri Uyumu',      'Mesleğin gerektirdiği becerilerin karşılanma oranı'],
          ];
          foreach ($weightFields as [$key, $label, $desc]): ?>
            <div class="weight-row">
              <span class="wr-label"><?= e($label) ?><small><?= e($desc) ?></small></span>
              <input type="range" name="<?= e($key) ?>" min="0" max="100" step="5"
                     value="<?= (int) setting($key, 0) ?>" data-weight-output="<?= e($key) ?>">
              <span class="wr-val" id="out-<?= e($key) ?>">%<?= (int) setting($key, 0) ?></span>
            </div>
          <?php endforeach; ?>

          <div class="divider"></div>

          <div class="weight-row">
            <span class="wr-label">Ortam Alt Ağırlığı<small>Çalışma tercihi ağırlığı içinde ortamın payı</small></span>
            <input type="range" name="sub_weight_environment" min="0" max="20" step="1"
                   value="<?= (int) setting('sub_weight_environment', 8) ?>" data-weight-output="sub_weight_environment">
            <span class="wr-val" id="out-sub_weight_environment">%<?= (int) setting('sub_weight_environment', 8) ?></span>
          </div>

          <div class="weight-row">
            <span class="wr-label">Hesaplanacak Öneri Sayısı<small>Test sonucunda kaç meslek listelenecek</small></span>
            <input type="range" name="recommendation_limit" min="3" max="30" step="1"
                   value="<?= (int) setting('recommendation_limit', 12) ?>" data-weight-output="recommendation_limit">
            <span class="wr-val" id="out-recommendation_limit"><?= (int) setting('recommendation_limit', 12) ?></span>
          </div>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-head"><h3>Uygula</h3></div>
        <div class="pc-body grid" style="gap:14px">
          <label class="switch">
            <input type="checkbox" name="recalculate" value="1" checked>
            <span class="sw-track"></span>
            <span>Mevcut sonuçları yeniden hesapla</span>
          </label>
          <p style="font-size:.85rem;margin:0">
            Ağırlıklar değiştiğinde daha önce tamamlanmış testlerin önerileri eski ağırlıklarla
            hesaplanmış olur. Bu seçenek işaretliyken tüm sonuçlar yeni ağırlıklarla güncellenir.
          </p>
          <button type="submit" class="btn btn-accent btn-block"><?= icon('save', '', 17) ?> Ağırlıkları Kaydet</button>
        </div>
      </section>
    </div>
  </form>
</div>

<!-- KAYIT & GÜVENLİK -->
<div class="tab-panel" data-panel="kayit">
  <form method="post" class="panel-form" data-guard>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_registration">

    <div class="layout-aside equal" style="gap:22px">
      <section class="panel-card">
        <div class="pc-head"><h2>Kayıt Ayarları</h2></div>
        <div class="pc-body">
          <div class="panel-form">
            <label class="switch">
              <input type="checkbox" name="registration_open" value="1" <?= setting('registration_open', true) ? 'checked' : '' ?>>
              <span class="sw-track"></span>
              <span>Yeni kayıtlara açık</span>
            </label>
            <label class="switch">
              <input type="checkbox" name="require_kvkk" value="1" <?= setting('require_kvkk', true) ? 'checked' : '' ?>>
              <span class="sw-track"></span>
              <span>KVKK onayı zorunlu</span>
            </label>
            <label class="switch">
              <input type="checkbox" name="show_results_disclaimer" value="1" <?= setting('show_results_disclaimer', true) ? 'checked' : '' ?>>
              <span class="sw-track"></span>
              <span>Sonuç sayfasında uyarı göster</span>
            </label>
            <label class="field">
              <span>Minimum Şifre Uzunluğu</span>
              <input type="number" name="min_password_length" class="input" min="6" max="32" value="<?= (int) setting('min_password_length', 8) ?>">
            </label>
            <label class="field">
              <span>Oturum Başına Soru Sayısı <small class="text-muted-c">(0 = tüm sorular)</small></span>
              <input type="number" name="test_questions_per_session" class="input" min="0" max="100" value="<?= (int) setting('test_questions_per_session', 0) ?>">
            </label>
          </div>
        </div>
      </section>

      <section class="panel-card">
        <div class="pc-head"><h2>Güvenlik Ayarları</h2></div>
        <div class="pc-body">
          <div class="panel-form">
            <label class="field">
              <span>Maksimum Başarısız Giriş Denemesi</span>
              <input type="number" name="max_login_attempts" class="input" min="3" max="20" value="<?= (int) setting('max_login_attempts', 5) ?>">
            </label>
            <label class="field">
              <span>Hesap Kilit Süresi (dakika)</span>
              <input type="number" name="login_lockout_minutes" class="input" min="1" max="1440" value="<?= (int) setting('login_lockout_minutes', 15) ?>">
            </label>
            <label class="field">
              <span>Şifre Sıfırlama Token Ömrü (dakika)</span>
              <input type="number" name="password_reset_token_lifetime" class="input" min="10" max="1440" value="<?= (int) setting('password_reset_token_lifetime', 60) ?>">
            </label>
            <button type="submit" class="btn btn-accent btn-block"><?= icon('save', '', 17) ?> Ayarları Kaydet</button>
          </div>
        </div>
      </section>
    </div>
  </form>
</div>

<script>
(function () {
  var btns = document.querySelectorAll('.tab-btn');
  var panels = document.querySelectorAll('.tab-panel');
  function activate(key) {
    btns.forEach(function (b) { b.classList.toggle('active', b.dataset.tab === key); });
    panels.forEach(function (p) { p.classList.toggle('active', p.dataset.panel === key); });
  }
  btns.forEach(function (b) {
    b.addEventListener('click', function () { activate(b.dataset.tab); });
  });

  document.querySelectorAll('[data-weight-output]').forEach(function (r) {
    var out = document.getElementById('out-' + r.dataset.weightOutput);
    function sync() {
      if (!out) return;
      out.textContent = (r.dataset.weightOutput === 'recommendation_limit' ? '' : '%') + r.value;
    }
    r.addEventListener('input', sync);
    sync();
  });

  // Hash ile sekme ac
  if (window.location.hash) {
    var key = window.location.hash.replace('#', '');
    var target = document.querySelector('[data-tab="' + key + '"]');
    if (target) activate(key);
  }
})();
</script>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
