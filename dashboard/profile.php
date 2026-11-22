<?php
/**
 * Akilli Kariyer - Panel: Profilim
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    keep_old_input($_POST);

    $validator = Validator::make($_POST)->rules([
        'first_name' => 'required|min:2|max:60',
        'last_name'  => 'required|min:2|max:60',
        'email'      => 'required|email|max:190',
        'city'       => 'max:80',
        'school_name' => 'max:150',
        'study_field' => 'max:150',
        'grade_level' => 'max:60',
        'phone'      => 'phone',
        'bio'        => 'max:600',
    ]);

    if (!$validator->validate()) {
        $errors = $validator->errors();
    } else {
        $email = mb_strtolower(trim($_POST['email']));

        if (User::emailExists($email, $userId)) {
            $errors['email'] = 'Bu e-posta adresi başka bir kullanıcıya ait.';
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
            clear_old_input();
            set_flash('success', 'Profil bilgileriniz başarıyla güncellendi.');
            redirect(url('dashboard/profile.php'));
        }
    }
}

$profile = $user->profile();

$panelType     = 'user';
$panelActive   = 'profile';
$panelTitle    = 'Profilim';
$panelSubtitle = 'Kişisel bilgilerinizi ve profil ayrıntılarınızı yönetin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<div class="layout-aside ratio" style="gap:22px">

  <section class="panel-card">
    <div class="pc-head"><div><h2>Kişisel Bilgiler</h2><p class="pc-sub">Profil bilgilerinizi güncelleyin</p></div></div>
    <div class="pc-body">
      <?php if ($errors): ?>
        <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
      <?php endif; ?>

      <form method="post" class="panel-form" data-guard>
        <?= csrf_field() ?>

        <div class="pf-section">
          <h4><?= icon('user', '', 18) ?> Kimlik Bilgileri</h4>
          <p class="pf-sub">Hesabınıza ait temel bilgiler</p>
          <div class="pf-grid">
            <label class="field">
              <span>Ad</span>
              <input type="text" name="first_name" class="input <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                     value="<?= e(old('first_name', $user->first_name)) ?>" required maxlength="60">
            </label>
            <label class="field">
              <span>Soyad</span>
              <input type="text" name="last_name" class="input <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                     value="<?= e(old('last_name', $user->last_name)) ?>" required maxlength="60">
            </label>
            <label class="field">
              <span>E-posta</span>
              <input type="email" name="email" class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                     value="<?= e(old('email', $user->email)) ?>" required maxlength="190">
            </label>
            <label class="field">
              <span>Telefon</span>
              <input type="tel" name="phone" class="input" value="<?= e(old('phone', $profile['phone'] ?? '')) ?>" placeholder="05XX XXX XX XX">
            </label>
            <label class="field full">
              <span>Eğitim Seviyesi</span>
              <select name="education_level" class="select">
                <?php foreach (['ortaokul' => 'Ortaokul', 'lise' => 'Lise', 'universite' => 'Üniversite', 'diger' => 'Diğer'] as $k => $v): ?>
                  <option value="<?= e($k) ?>" <?= $user->education_level === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
          </div>
        </div>

        <div class="pf-section">
          <h4><?= icon('graduation-cap', '', 18) ?> Eğitim Bilgileri</h4>
          <p class="pf-sub">Test önerilerinizi zenginleştirmek için isteğe bağlı bilgiler</p>
          <div class="pf-grid">
            <label class="field">
              <span>Okul / Kurum</span>
              <input type="text" name="school_name" class="input" value="<?= e(old('school_name', $profile['school_name'] ?? '')) ?>" maxlength="150" placeholder="Örn. İstanbul Üniversitesi">
            </label>
            <label class="field">
              <span>Bölüm / Alan</span>
              <input type="text" name="study_field" class="input" value="<?= e(old('study_field', $profile['study_field'] ?? '')) ?>" maxlength="150" placeholder="Örn. Bilgisayar Mühendisliği">
            </label>
            <label class="field">
              <span>Sınıf / Seviye</span>
              <input type="text" name="grade_level" class="input" value="<?= e(old('grade_level', $profile['grade_level'] ?? '')) ?>" maxlength="60" placeholder="Örn. 3. sınıf">
            </label>
            <label class="field">
              <span>Şehir</span>
              <input type="text" name="city" class="input" value="<?= e(old('city', $profile['city'] ?? '')) ?>" maxlength="80" placeholder="Örn. İstanbul">
            </label>
            <label class="field full">
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
              <span>Hakkımda</span>
              <textarea name="bio" class="textarea" maxlength="600" placeholder="Kendinizi kısaca tanıtın…"><?= e(old('bio', $profile['bio'] ?? '')) ?></textarea>
              <span class="hint">En fazla 600 karakter</span>
            </label>
          </div>
        </div>

        <div class="pf-actions">
          <button type="submit" class="btn btn-primary"><?= icon('save', '', 17) ?> Değişiklikleri Kaydet</button>
          <a href="<?= url('dashboard/index.php') ?>" class="btn btn-outline">Vazgeç</a>
        </div>
      </form>
    </div>
  </section>

  <div class="grid" style="gap:22px">
    <section class="panel-card">
      <div class="pc-body text-center" style="padding:30px 22px">
        <?= svg_avatar($user->initials(), '#1C466D', 84) ?>
        <h3 style="margin:14px 0 2px"><?= e($user->fullName()) ?></h3>
        <p style="font-size:.86rem;margin:0"><?= e($user->email) ?></p>
        <div class="flex gap-8" style="justify-content:center;margin-top:14px">
          <span class="badge badge-accent"><?= e($user->educationLabel()) ?></span>
          <span class="badge badge-muted"><?= icon('calendar', '', 12) ?> <?= format_date($user->created_at, 'F Y') ?> üye</span>
        </div>
      </div>
    </section>

    <section class="panel-card">
      <div class="pc-head"><h3><?= icon('shield-check', '', 17) ?> Hesap Güvenliği</h3></div>
      <div class="pc-body">
        <div class="activity-item">
          <span class="ai-ico success"><?= icon('lock', '', 17) ?></span>
          <div>
            <div class="ai-txt">Şifreniz tek yönlü (bcrypt) olarak şifrelenerek saklanır.</div>
            <div class="ai-time">Son giriş: <?= $user->last_login_at ? format_date($user->last_login_at, 'd F Y H:i') : '—' ?></div>
          </div>
        </div>
        <a href="<?= url('dashboard/settings.php') ?>" class="btn btn-outline btn-block btn-sm" style="margin-top:14px">
          <?= icon('key-round', '', 15) ?> Şifreyi Değiştir
        </a>
      </div>
    </section>

    <section class="panel-card">
      <div class="pc-head"><h3><?= icon('info', '', 17) ?> Veri Mahiyeti</h3></div>
      <div class="pc-body">
        <p style="font-size:.87rem;margin:0 0 12px">
          Profilinizdeki bilgiler yalnızca size özel öneriler üretmek için kullanılır ve
          üçüncü taraflarla paylaşılmaz.
        </p>
        <a href="<?= url('kvkk.php') ?>" class="btn btn-ghost btn-sm btn-block"><?= icon('file-text', '', 15) ?> KVKK Aydınlatma Metni</a>
        <a href="<?= url('privacy.php') ?>" class="btn btn-ghost btn-sm btn-block"><?= icon('lock', '', 15) ?> Gizlilik Politikası</a>
      </div>
    </section>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
