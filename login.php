<?php
/**
 * Akilli Kariyer - Giris ekrani
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Validator;
use App\Models\User;

if (Auth::check()) {
    redirect(url('dashboard/index.php'));
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    keep_old_input($_POST);

    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    $validator = Validator::make($_POST)->rules([
        'email'    => 'required|email|max:190',
        'password' => 'required|min:1|max:200',
    ]);

    if (!$validator->validate()) {
        $errors = $validator->errors();
    } elseif (Auth::isLockedOut($email)) {
        $errors['email'] = 'Çok fazla başarısız giriş denemesi yapıldı. Lütfen bir süre sonra tekrar deneyin.';
    } elseif (!Auth::attempt($email, $password, $remember)) {
        $errors['password'] = 'E-posta adresi veya şifre hatalı. Bilgilerinizi kontrol edin.';
    } else {
        clear_old_input();
        clear_errors();
        CSRF::regenerate();

        $target = $_SESSION['redirect_after_login'] ?? url('dashboard/index.php');
        unset($_SESSION['redirect_after_login']);
        redirect($target);
    }
}

$pageTitle       = 'Giriş Yap — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Akıllı Kariyer hesabınıza giriş yaparak kariyer testinize devam edin.';
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
      <h2 style="margin-top:36px">Tekrar hoş geldiniz!</h2>
      <p>Kariyer keşif yolculuğunuza kaldığınız yerden devam edin.</p>
      <ul class="auth-points" style="list-style:none;padding:0">
        <li>
          <span class="ap-ico"><?= icon('clipboard-check', '', 17) ?></span>
          <span>Yarım kalan testinizi kaldığınız yerden tamamlayın</span>
        </li>
        <li>
          <span class="ap-ico"><?= icon('sparkles', '', 17) ?></span>
          <span>Kişiselleştirilmiş meslek önerilerinizi görün</span>
        </li>
        <li>
          <span class="ap-ico"><?= icon('heart', '', 17) ?></span>
          <span>Favori mesleklerinizi takip edin</span>
        </li>
      </ul>
    </div>
    <div style="position:relative;z-index:2;font-size:.82rem;color:rgba(255,255,255,.55)">
      &copy; <?= date('Y') ?> <?= e(setting('site_name', 'Akıllı Kariyer'))
      ?> &middot; <a href="<?= url('kvkk.php') ?>" style="color:rgba(255,255,255,.75)">KVKK</a>
    </div>
  </aside>

  <section class="auth-main">
    <div class="auth-card">
      <h1>Giriş Yap</h1>
      <p class="ac-sub">Hesabınıza giriş yaparak kariyer yolculuğunuza devam edin.</p>

      <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e($error) ?></span></div>
      <?php endif; ?>
      <?php if ($error = flash('success')): ?>
        <div class="alert alert-success"><?= icon('check-circle-2', '', 18) ?><span><?= e($error) ?></span></div>
      <?php endif; ?>
      <?php if ($errors): ?>
        <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
      <?php endif; ?>

      <form method="post" action="<?= url('login.php') ?>" class="auth-form" data-guard>
        <?= csrf_field() ?>

        <label class="field">
          <span>E-posta Adresi</span>
          <input type="email" name="email" class="input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                 value="<?= e($email) ?>" required autocomplete="email" placeholder="ornek@eposta.com" autofocus>
          <?php if (isset($errors['email'])): ?>
            <span class="error-text"><?= e($errors['email']) ?></span>
          <?php endif; ?>
        </label>

        <label class="field">
          <span>Şifre</span>
          <span class="pw-field">
            <input type="password" name="password" class="input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                   required autocomplete="current-password" placeholder="••••••••">
            <button type="button" class="pw-toggle" aria-label="Şifreyi göster"><?= icon('eye', '', 18) ?></button>
          </span>
          <?php if (isset($errors['password'])): ?>
            <span class="error-text"><?= e($errors['password']) ?></span>
          <?php endif; ?>
        </label>

        <div class="flex between center" style="gap:12px;flex-wrap:wrap">
          <label class="checkbox">
            <input type="checkbox" name="remember" value="1" checked>
            <span>Oturum açık kalsın</span>
          </label>
          <a href="<?= url('forgot-password.php') ?>" style="font-size:.87rem;font-weight:600">Şifremi unuttum</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block">
          <?= icon('log-in', '', 19) ?> Giriş Yap
        </button>
      </form>

      <div class="auth-alt">
        Hesabınız yok mu? <a href="<?= url('register.php') ?>" style="font-weight:700">Ücretsiz kayıt olun</a>
      </div>

      <div class="divider"></div>

      <div style="background:var(--c-light-blue);border-radius:14px;padding:16px 18px">
        <strong style="display:block;color:var(--c-primary);font-size:.9rem;margin-bottom:6px">
          <?= icon('info', '', 15) ?> Demo hesabı
        </strong>
        <span style="font-size:.84rem;color:var(--c-muted)">
          <code>demo@akillikariyer.com</code> &middot; <code>Demo!2345</code>
        </span>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
