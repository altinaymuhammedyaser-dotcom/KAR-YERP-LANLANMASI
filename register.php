<?php
/**
 * Akilli Kariyer - Kayit ekrani
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Validator;
use App\Models\Activity;
use App\Models\User;

if (Auth::check()) {
    redirect(url('dashboard/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    keep_old_input($_POST);

    $validator = Validator::make($_POST)->rules([
        'first_name'            => 'required|min:2|max:60',
        'last_name'             => 'required|min:2|max:60',
        'email'                 => 'required|email|max:190',
        'password'              => 'required|password:8',
        'password_confirmation' => 'required|same:password',
        'education_level'       => 'required|in:ortaokul,lise,universite,diger',
        'kvkk'                  => 'required',
    ]);

    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));

    if ($validator->validate()) {
        if (User::emailExists($email)) {
            $errors['email'] = 'Bu e-posta adresi zaten kayıtlı. Giriş yapmayı deneyin.';
        } elseif (!setting('registration_open', true)) {
            $errors['email'] = 'Şu anda yeni kayıtlar kapalıdır.';
        } else {
            $userId = User::create([
                'first_name'      => trim($_POST['first_name']),
                'last_name'       => trim($_POST['last_name']),
                'email'           => $email,
                'password'        => Auth::hash($_POST['password']),
                'education_level' => $_POST['education_level'],
            ]);

            Activity::log($userId, 'registration', 'Akıllı Kariyer platformuna ücretsiz üye oldunuz.', 'user', $userId);

            $user = User::find($userId);
            Auth::login($user);
            CSRF::regenerate();
            clear_old_input();

            set_flash('success', 'Aramıza hoş geldiniz! Kariyer testinizi hemen başlatabilirsiniz.');
            redirect(url('career-test.php'));
        }
    } else {
        $errors = $validator->errors();
    }
}

$pageTitle       = 'Ücretsiz Kayıt Ol — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Akıllı Kariyer platformuna ücretsiz kayıt olun, kariyer testini tamamlayın ve size uygun meslekleri keşfedin.';
$activeNav       = '';
$bodyClass       = 'auth-page';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content" class="auth-wrap">
  <aside class="auth-side">
    <div class="as-content">
      <a href="<?= url('index.php') ?>" class="footer-brand" style="color:#fff">
        <span class="brand-mark"><?= icon('compass', '', 21) ?></span>
        <span class="brand-text">
          <span class="brand-name" style="color:#fff">AKILLI KARİYER</span>
          <span class="brand-slogan"><?= e(setting('site_slogan', 'Geleceğini Keşfet')) ?></span>
        </span>
      </a>
      <h2 style="margin-top:36px">Kariyerini keşfetmeye başla</h2>
      <p>Ücretsiz üye ol, testini tamamla, kişiselleştirilmiş meslek önerilerini hemen gör.</p>
      <ul class="auth-points" style="list-style:none;padding:0">
        <li>
          <span class="ap-ico"><?= icon('sparkles', '', 17) ?></span>
          <span>Kişiselleştirilmiş meslek eşleştirme algoritması</span>
        </li>
        <li>
          <span class="ap-ico"><?= icon('save', '', 17) ?></span>
          <span>Tüm test sonuçlarını hesabınızda saklayın</span>
        </li>
        <li>
          <span class="ap-ico"><?= icon('file-down', '', 17) ?></span>
          <span>Sonuç raporunuzu PDF olarak indirin</span>
        </li>
        <li>
          <span class="ap-ico"><?= icon('lock', '', 17) ?></span>
          <span>Şifreniz güvenli biçimde şifrelenerek saklanır</span>
        </li>
      </ul>
    </div>
    <div style="position:relative;z-index:2;font-size:.82rem;color:rgba(255,255,255,.55)">
      Kayıt olarak <a href="<?= url('kvkk.php') ?>" style="color:rgba(255,255,255,.8)">KVKK Aydınlatma Metni</a>'ni kabul etmiş olursunuz.
    </div>
  </aside>

  <section class="auth-main">
    <div class="auth-card">
      <h1>Ücretsiz Kayıt Ol</h1>
      <p class="ac-sub">Birkaç saniyede hesabınızı oluşturun, keşfe başlayın.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
      <?php endif; ?>

      <form method="post" action="<?= url('register.php') ?>" class="auth-form" data-guard>
        <?= csrf_field() ?>

        <div class="form-grid" style="gap:14px">
          <label class="field">
            <span>Ad</span>
            <input type="text" name="first_name" class="input <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                   value="<?= e(old('first_name')) ?>" required maxlength="60" placeholder="Adınız" autocomplete="given-name">
            <?php if (isset($errors['first_name'])): ?><span class="error-text"><?= e($errors['first_name']) ?></span><?php endif; ?>
          </label>

          <label class="field">
            <span>Soyad</span>
            <input type="text" name="last_name" class="input <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                   value="<?= e(old('last_name')) ?>" required maxlength="60" placeholder="Soyadınız" autocomplete="family-name">
            <?php if (isset($errors['last_name'])): ?><span class="error-text"><?= e($errors['last_name']) ?></span><?php endif; ?>
          </label>
        </div>

        <label class="field">
          <span>E-posta Adresi</span>
          <input type="email" name="email" class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                 value="<?= e(old('email')) ?>" required maxlength="190" placeholder="ornek@eposta.com" autocomplete="email">
          <?php if (isset($errors['email'])): ?><span class="error-text"><?= e($errors['email']) ?></span><?php endif; ?>
        </label>

        <div class="form-grid" style="gap:14px">
          <label class="field">
            <span>Şifre</span>
            <span class="pw-field">
              <input type="password" name="password" class="input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                     required minlength="8" placeholder="En az 8 karakter" autocomplete="new-password" data-pw-strength>
              <button type="button" class="pw-toggle" aria-label="Şifreyi göster"><?= icon('eye', '', 18) ?></button>
            </span>
            <span class="pw-meter"><span data-pw-meter></span></span>
            <span class="hint" data-pw-label></span>
            <?php if (isset($errors['password'])): ?><span class="error-text"><?= e($errors['password']) ?></span><?php endif; ?>
          </label>

          <label class="field">
            <span>Şifre Tekrarı</span>
            <span class="pw-field">
              <input type="password" name="password_confirmation" class="input <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>"
                     required minlength="8" placeholder="Şifreyi tekrar girin" autocomplete="new-password">
              <button type="button" class="pw-toggle" aria-label="Şifreyi göster"><?= icon('eye', '', 18) ?></button>
            </span>
            <?php if (isset($errors['password_confirmation'])): ?><span class="error-text"><?= e($errors['password_confirmation']) ?></span><?php endif; ?>
          </label>
        </div>

        <label class="field">
          <span>Eğitim Seviyesi</span>
          <select name="education_level" class="select <?= isset($errors['education_level']) ? 'is-invalid' : '' ?>" required>
            <option value="">Seçiniz</option>
            <?php foreach (['ortaokul' => 'Ortaokul', 'lise' => 'Lise', 'universite' => 'Üniversite', 'diger' => 'Diğer'] as $k => $v): ?>
              <option value="<?= e($k) ?>" <?= old('education_level') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['education_level'])): ?><span class="error-text"><?= e($errors['education_level']) ?></span><?php endif; ?>
        </label>

        <label class="checkbox" style="align-items:flex-start">
          <input type="checkbox" name="kvkk" value="1" <?= old('kvkk') ? 'checked' : '' ?> required>
          <span style="font-size:.87rem;line-height:1.6">
            <a href="<?= url('kvkk.php') ?>" target="_blank" style="font-weight:700">KVKK Aydınlatma Metni</a>'ni
            ve <a href="<?= url('privacy.php') ?>" target="_blank" style="font-weight:700">Gizlilik Politikası</a>'nı
            okudum, kabul ediyorum.
          </span>
        </label>
        <?php if (isset($errors['kvkk'])): ?><span class="error-text"><?= e($errors['kvkk']) ?></span><?php endif; ?>

        <button type="submit" class="btn btn-accent btn-lg btn-block">
          <?= icon('user-plus', '', 19) ?> Ücretsiz Kayıt Ol
        </button>
      </form>

      <div class="auth-alt">
        Zaten hesabınız var mı? <a href="<?= url('login.php') ?>" style="font-weight:700">Giriş yapın</a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
