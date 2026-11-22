<?php
/**
 * Akilli Kariyer - Panel: Ayarlar
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Validator;
use App\Models\Activity;
use App\Models\User;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;
$errors = [];
$tab    = $_GET['tab'] ?? 'profil';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $validator = Validator::make($_POST)->rules([
            'first_name' => 'required|min:2|max:60',
            'last_name'  => 'required|min:2|max:60',
            'email'      => 'required|email|max:190',
            'phone'      => 'phone',
            'city'       => 'max:80',
            'school_name'  => 'max:150',
            'study_field'  => 'max:150',
            'grade_level'  => 'max:60',
            'bio'        => 'max:600',
        ]);

        if (!$validator->validate()) {
            $errors = $validator->errors();
        } else {
            $email = mb_strtolower(trim($_POST['email']));
            if (User::emailExists($email, $userId)) {
                $errors['email'] = 'Bu e-posta adresi başkası tarafından kullanılıyor.';
            } else {
                User::update($userId, [
                    'first_name'      => trim($_POST['first_name']),
                    'last_name'       => trim($_POST['last_name']),
                    'email'           => $email,
                    'education_level' => $_POST['education_level'] ?? $user->education_level,
                ]);

                User::updateProfile($userId, [
                    'phone'                => trim($_POST['phone'] ?? ''),
                    'city'                 => trim($_POST['city'] ?? ''),
                    'school_name'          => trim($_POST['school_name'] ?? ''),
                    'study_field'          => trim($_POST['study_field'] ?? ''),
                    'grade_level'          => trim($_POST['grade_level'] ?? ''),
                    'bio'                  => trim($_POST['bio'] ?? ''),
                    'preferred_work_style' => $_POST['preferred_work_style'] ?? 'karma',
                ]);

                Activity::log($userId, 'profile_update', 'Profil bilgilerinizi güncellediniz.', 'user', $userId);
                set_flash('success', 'Profil bilgileriniz başarıyla güncellendi.');
                redirect(url('dashboard/settings.php?tab=profil'));
            }
        }
    }

    if ($action === 'password') {
        $validator = Validator::make($_POST)->rules([
            'current_password' => 'required|max:200',
            'new_password'     => 'required|password:8',
            'password_confirmation' => 'required|same:new_password',
        ]);

        if (!$validator->validate()) {
            $errors = $validator->errors();
        } elseif (!password_verify($_POST['current_password'], $user->password)) {
            $errors['current_password'] = 'Mevcut şifreniz hatalı.';
        } else {
            User::update($userId, ['password' => Auth::hash($_POST['new_password'])]);
            Activity::log($userId, 'profile_update', 'Hesap şifrenizi güncellediniz.', 'user', $userId);
            set_flash('success', 'Şifreniz başarıyla güncellendi.');
            redirect(url('dashboard/settings.php?tab=password'));
        }
    }

    if ($action === 'delete_account') {
        if ($_POST['confirm'] !== 'SİL') {
            set_flash('error', 'Onay metni hatalı. Hesabınız silinmedi.');
        } else {
            Activity::log($userId, 'account_delete', 'Hesabınızı silme talebi gönderildi.', 'user', $userId);
            User::delete($userId);
            Auth::logout();
            session_start();
            set_flash('success', 'Hesabınız silindi. Tekrar bekleriz.');
            redirect(url('index.php'));
        }
    }
}

$profile = $user->profile();
$panelType     = 'user';
$panelActive   = 'settings';
$panelTitle    = 'Ayarlar';
$panelSubtitle = 'Hesap güvenliği ve tercihlerinizi yönetin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<div class="tabs" style="margin-bottom:22px">
  <button type="button" class="tab-btn <?= $tab === 'profil' ? 'active' : '' ?>" data-tab="profil"><?= icon('user', '', 15) ?> Profil</button>
  <button type="button" class="tab-btn <?= $tab === 'sifre' ? 'active' : '' ?>" data-tab="sifre"><?= icon('key-round', '', 15) ?> Şifre Değiştir</button>
  <button type="button" class="tab-btn <?= $tab === 'bildirim' ? 'active' : '' ?>" data-tab="bildirim"><?= icon('bell', '', 15) ?> Bildirimler</button>
  <button type="button" class="tab-btn <?= $tab === 'gizlilik' ? 'active' : '' ?>" data-tab="gizlilik"><?= icon('lock', '', 15) ?> Gizlilik</button>
  <button type="button" class="tab-btn <?= $tab === 'tema' ? 'active' : '' ?>" data-tab="tema"><?= icon('sun', '', 15) ?> Tema</button>
  <button type="button" class="tab-btn <?= $tab === 'tehlike' ? 'active' : '' ?>" data-tab="tehlike"><?= icon('alert-triangle', '', 15) ?> Hesap İşlemleri</button>
</div>

<!-- Profil -->
<div class="tab-panel <?= $tab === 'profil' ? 'active' : '' ?>" data-panel="profil">
  <section class="panel-card" style="max-width:640px">
    <div class="pc-head"><div><h2>Kişisel Bilgiler</h2><p class="pc-sub">Profili güncel tutun</p></div></div>
    <div class="pc-body">
      <?php if ($errors): ?>
        <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
      <?php endif; ?>

      <form method="post" class="panel-form" data-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="profile">

        <div class="pf-grid">
          <label class="field">
            <span>Ad</span>
            <input type="text" name="first_name" class="input" required maxlength="60" value="<?= e(old('first_name', $user->first_name)) ?>">
          </label>
          <label class="field">
            <span>Soyad</span>
            <input type="text" name="last_name" class="input" required maxlength="60" value="<?= e(old('last_name', $user->last_name)) ?>">
          </label>
          <label class="field">
            <span>E-posta</span>
            <input type="email" name="email" class="input" required maxlength="190" value="<?= e(old('email', $user->email)) ?>">
          </label>
          <label class="field">
            <span>Eğitim Seviyesi</span>
            <select name="education_level" class="select">
              <?php foreach (['ortaokul' => 'Ortaokul', 'lise' => 'Lise', 'universite' => 'Üniversite', 'diger' => 'Diğer'] as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $user->education_level === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="field">
            <span>Telefon</span>
            <input type="tel" name="phone" class="input" value="<?= e(old('phone', $profile['phone'] ?? '')) ?>" placeholder="05xx xxx xx xx">
          </label>
          <label class="field">
            <span>Şehir</span>
            <input type="text" name="city" class="input" value="<?= e(old('city', $profile['city'] ?? '')) ?>" maxlength="80" placeholder="Örn. İstanbul">
          </label>
          <label class="field">
            <span>Okula/Kuruma</span>
            <input type="text" name="school_name" class="input" value="<?= e(old('school_name', $profile['school_name'] ?? '')) ?>" maxlength="150" placeholder="Örn. İstanbul Üniversitesi">
          </label>
          <label class="field">
            <span>Bölüm/Alan</span>
            <input type="text" name="study_field" class="input" value="<?= e(old('study_field', $profile['study_field'] ?? '')) ?>" maxlength="150" placeholder="Örn. Bilgisayar Mühendisliği">
          </label>
          <label class="field">
            <span>Sınıf/Seviye</span>
            <input type="text" name="grade_level" class="input" value="<?= e(old('grade_level', $profile['grade_level'] ?? '')) ?>" maxlength="60" placeholder="Örn. 3. sınıf">
          </label>
          <label class="field">
            <span>Çalışma Tercihi</span>
            <select name="preferred_work_style" class="select">
              <?php foreach ([
                'bireysel' => 'Bireysel çalışma',
                'takim'    => 'Takım çalışması',
                'saha'     => 'Saha çalışması',
                'dijital'  => 'Dijital ortam',
                'karma'    => 'Karma çalışma',
              ] as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= ($profile['preferred_work_style'] ?? 'karma') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="field full">
            <span>Hakkında</span>
            <textarea name="bio" class="textarea" maxlength="600" placeholder="Kendinizi kısaca tanıtın..."><?= e(old('bio', $profile['bio'] ?? '')) ?></textarea>
          </label>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><?= icon('save', '', 17) ?> Profili Kaydet</button>
      </form>
    </div>
  </section>
</div>

<!-- Şifre -->
<div class="tab-panel <?= $tab === 'sifre' ? 'active' : '' ?>" data-panel="sifre">
  <section class="panel-card" style="max-width:640px">
    <div class="pc-head"><div><h2>Şifre Değiştir</h2><p class="pc-sub">Güçlü bir şifre hesabınızı korur</p></div></div>
    <div class="pc-body">
      <?php if ($errors): ?>
        <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
      <?php endif; ?>

      <form method="post" class="panel-form" data-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">

        <label class="field">
          <span>Mevcut Şifre</span>
          <span class="pw-field">
            <input type="password" name="current_password" class="input" required autocomplete="current-password">
            <button type="button" class="pw-toggle" aria-label="Göster"><?= icon('eye', '', 18) ?></button>
          </span>
        </label>

        <label class="field">
          <span>Yeni Şifre</span>
          <span class="pw-field">
            <input type="password" name="new_password" class="input" required minlength="8" autocomplete="new-password" data-pw-strength>
            <button type="button" class="pw-toggle" aria-label="Göster"><?= icon('eye', '', 18) ?></button>
          </span>
          <span class="pw-meter"><span data-pw-meter></span></span>
          <span class="hint" data-pw-label></span>
        </label>

        <label class="field">
          <span>Yeni Şifre (Tekrar)</span>
          <span class="pw-field">
            <input type="password" name="password_confirmation" class="input" required minlength="8" autocomplete="new-password">
            <button type="button" class="pw-toggle" aria-label="Göster"><?= icon('eye', '', 18) ?></button>
          </span>
        </label>

        <div class="pf-actions">
          <button type="submit" class="btn btn-primary"><?= icon('save', '', 17) ?> Şifreyi Güncelle</button>
        </div>
      </form>
    </div>
  </section>
</div>

<!-- Bildirimler -->
<div class="tab-panel <?= $tab === 'bildirim' ? 'active' : '' ?>" data-panel="bildirim">
  <section class="panel-card" style="max-width:640px">
    <div class="pc-head"><div><h2>Bildirim Tercihleri</h2><p class="pc-sub">Hangi bildirimleri almak istiyorsunuz?</p></div></div>
    <div class="pc-body">
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('bell', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Kariyer testini tamamladığında bildirim al</div>
          <div class="ai-time">E-posta</div>
        </div>
        <label class="switch">
          <input type="checkbox" name="notify_test" value="1" checked>
          <span class="sw-track"></span>
          <span>Aktif</span>
        </label>
      </div>
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('bell', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Yeni meslek önerisi geldiğinde bildirim al</div>
          <div class="ai-time">E-posta</div>
        </div>
        <label class="switch">
          <input type="checkbox" name="notify_recommendation" value="1" checked>
          <span class="sw-track"></span>
          <span>Aktif</span>
        </label>
      </div>
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('bell', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Kariyer rehberi yazılarında yeni içerikler</div>
          <div class="ai-time">E-posta</div>
        </div>
        <label class="switch">
          <input type="checkbox" name="notify_blog" value="1">
          <span class="sw-track"></span>
          <span>Pasif</span>
        </label>
      </div>
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('bell', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Haftalık kariyer özetini al</div>
          <div class="ai-time">Pazartesi sabahları</div>
        </div>
        <label class="switch">
          <input type="checkbox" name="notify_weekly" value="1">
          <span class="sw-track"></span>
          <span>Pasif</span>
        </label>
      </div>
    </div>
  </section>
</div>

<!-- Gizlilik -->
<div class="tab-panel <?= $tab === 'gizlilik' ? 'active' : '' ?>" data-panel="gizlilik">
  <section class="panel-card" style="max-width:640px">
    <div class="pc-head"><div><h2>Gizlilik Tercihleri</h2><p class="pc-sub">Verilerinizin nasıl paylaşıldığını kontrol edin</p></div></div>
    <div class="pc-body">
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('eye-off', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Profilimi arama sonuçlarında görünür kıl</div>
          <div class="ai-time">Diğer kullanıcılar profilinizi bulabilir</div>
        </div>
        <label class="switch">
          <input type="checkbox" name="profile_visible" value="1" checked>
          <span class="sw-track"></span>
          <span>Açık</span>
        </label>
      </div>
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('eye-off', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">E-posta adresimi gizle</div>
          <div class="ai-time">Diğer kullanıcılar e-postanızı göremez</div>
        </div>
        <label class="switch">
          <input type="checkbox" name="email_private" value="1" checked>
          <span class="sw-track"></span>
          <span>Açık</span>
        </label>
      </div>
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('eye-off', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Analitik veriler için kullanım verilerini topla</div>
          <div class="ai-time">Kullanım deneyimini geliştirmek için</div>
        </div>
        <label class="switch">
          <input type="checkbox" name="analytics" value="1" checked>
          <span class="sw-track"></span>
          <span>Açık</span>
        </label>
      </div>
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('trash-2', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Tüm veri ayarlarını sıfırla</div>
          <div class="ai-time">Kullanım tercihlerinizi sıfırlar</div>
        </div>
        <button type="button" class="btn btn-outline btn-sm" style="color:var(--c-danger)"><?= icon('trash-2', '', 15) ?> Sıfırla</button>
      </div>
    </div>
  </section>
</div>

<!-- Tema -->
<div class="tab-panel <?= $tab === 'tema' ? 'active' : '' ?>" data-panel="tema">
  <section class="panel-card" style="max-width:640px">
    <div class="pc-head"><div><h2>Görünüm Ayarları</h2><p class="pc-sub">Platform görünümünü kişiselleştirin</p></div></div>
    <div class="pc-body">
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('sun', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Görünüm Modu</div>
          <div class="ai-time">Tema tercihinizi seçin</div>
        </div>
        <div class="flex gap-8">
          <button type="button" class="btn btn-outline btn-sm theme-btn <?= (setting('theme_mode','light')==='light')?'btn-accent':'btn-outline' ?>" data-theme="light">
            <?= icon('sun', '', 16) ?> Açık
          </button>
          <button type="button" class="btn btn-outline btn-sm theme-btn <?= (setting('theme_mode','light')==='dark')?'btn-accent':'btn-outline' ?>" data-theme="dark">
            <?= icon('moon', '', 16) ?> Koyu
          </button>
        </div>
      </div>
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('font', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Yazı Tipi Büyüklüğü</div>
          <div class="ai-time">Okunabilirlik ayarı</div>
        </div>
        <div class="flex gap-8">
          <button type="button" class="btn btn-outline btn-sm font-btn" data-size="small">Küçük</button>
          <button type="button" class="btn btn-outline btn-sm font-btn btn-accent" data-size="normal">Normal</button>
          <button type="button" class="btn btn-outline btn-sm font-btn" data-size="large">Büyük</button>
        </div>
      </div>
      <div class="activity-item">
        <span class="ai-ico info"><?= icon('palette', '', 17) ?></span>
        <div class="flex-1">
          <div class="ai-txt">Aksent Rengi</div>
          <div class="ai-time">Arayüz rengini seçin</div>
        </div>
        <div class="flex gap-8">
          <button type="button" class="btn btn-outline btn-sm color-btn" style="background:var(--c-accent);color:#fff" data-color="#16B8B0"></button>
          <button type="button" class="btn btn-outline btn-sm color-btn" style="background:var(--c-primary);color:#fff" data-color="#142B4A"></button>
          <button type="button" class="btn btn-outline btn-sm color-btn" style="background:var(--c-success);color:#fff" data-color="#20A779"></button>
          <button type="button" class="btn btn-outline btn-sm color-btn" style="background:var(--c-warning);color:#fff" data-color="#F0A43A"></button>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Hesap İşlemleri -->
<div class="tab-panel <?= $tab === 'tehlike' ? 'active' : '' ?>" data-panel="tehlike">
  <section class="panel-card" style="max-width:640px;border-color:rgba(224,90,78,.35)">
    <div class="pc-head" style="background:var(--c-danger-soft)">
      <div><h2 style="color:#A63B31"><?= icon('alert-triangle', '', 17) ?> Hesap İşlemleri</h2><p class="pc-sub">Bu işlemler geri alınamaz, dikkatli olun</p></div>
    </div>
    <div class="pc-body">
      <form method="post" class="panel-form" data-guard>
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete_account">
        <div class="alert alert-danger" style="margin:0">
          <?= icon('alert-triangle', '', 18) ?>
          <span>
            Hesabınızı silmek; test sonuçlarınız, favorileriniz ve profil bilgileriniz kalıcı olarak silinir. Bu işlem geri alınamaz.
          </span>
        </div>
        <label class="field">
          <span>Onaylamak için <strong>SİL</strong> yazın</span>
          <input type="text" name="confirm" class="input" placeholder="SİL">
        </label>
        <div class="pf-actions">
          <button type="submit" class="btn btn-danger"><?= icon('trash-2', '', 17) ?> Hesabımı Sil</button>
        </div>
      </form>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>