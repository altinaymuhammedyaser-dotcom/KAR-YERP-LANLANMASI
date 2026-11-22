<?php
/**
 * Akilli Kariyer - Sifre sifirlama talebi
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\CSRF;
use App\Core\Database;
use App\Core\Validator;
use App\Models\User;

$errors = [];
$sent   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    keep_old_input($_POST);

    $validator = Validator::make($_POST)->rules(['email' => 'required|email|max:190']);

    if (!$validator->validate()) {
        $errors = $validator->errors();
    } else {
        $email = mb_strtolower(trim($_POST['email']));
        $user  = User::findByEmail($email);

        // Kullanici var/yok bilgisi sizdirmamak icin her durumda ayni mesaji gosteriyoruz.
        if ($user && $user->is_active) {
            $lifetime = (int) setting('password_reset_token_lifetime', 60);
            $token    = bin2hex(random_bytes(32));

            Database::query(
                'INSERT INTO `password_resets` (`user_id`,`email`,`token`,`expires_at`,`ip_address`) VALUES (?,?,?,?,?)',
                [$user->id, $email, hash('sha256', $token), date('Y-m-d H:i:s', time() + $lifetime * 60), \App\Core\Auth::ip()]
            );

            // Geliştirme ortaminda sifre sifirlama linki ekrana gosterilir.
            if (APP_DEBUG) {
                $_SESSION['_dev_reset_link'] = url('reset-password.php?token=' . $token . '&email=' . urlencode($email));
            }
        }

        $sent = true;
        clear_old_input();
    }
}

$pageTitle       = 'Şifremi Unuttum — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Hesabınız için şifre sıfırlama bağlantısı talep edin.';
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
      <h2 style="margin-top:36px">Şifrenizi mi unuttunuz?</h2>
      <p>Endişelenmeyin. Kayıtlı e-posta adresinize güvenli bir sıfırlama bağlantısı göndereceğiz.</p>
    </div>
    <div style="position:relative;z-index:2;font-size:.82rem;color:rgba(255,255,255,.55)">
      &copy; <?= date('Y') ?> <?= e(setting('site_name', 'Akıllı Kariyer')) ?>
    </div>
  </aside>

  <section class="auth-main">
    <div class="auth-card">
      <h1>Şifre Sıfırlama</h1>
      <p class="ac-sub">Hesabınıza ait e-posta adresini girin, size sıfırlama bağlantısı gönderelim.</p>

      <?php if ($sent): ?>
        <div class="alert alert-success">
          <?= icon('check-circle-2', '', 18) ?>
          <span>
            Eğer bu adres kayıtlıysa şifre sıfırlama bağlantısı gönderildi. Lütfen gelen kutunuzu
            (ve spam klasörünüzü) kontrol edin.
          </span>
        </div>
        <?php if (!empty($_SESSION['_dev_reset_link'])): ?>
          <div class="alert alert-info">
            <?= icon('info', '', 18) ?>
            <span>
              <strong>Geliştirme modu sıfırlama bağlantısı:</strong><br>
              <a href="<?= e($_SESSION['_dev_reset_link']) ?>"><?= e($_SESSION['_dev_reset_link']) ?></a>
            </span>
          </div>
          <?php unset($_SESSION['_dev_reset_link']); ?>
        <?php endif; ?>
        <a href="<?= url('login.php') ?>" class="btn btn-outline btn-block" style="margin-top:10px">
          <?= icon('arrow-left', '', 18) ?> Giriş Ekranına Dön
        </a>
      <?php else: ?>
        <?php if ($errors): ?>
          <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
        <?php endif; ?>

        <form method="post" action="<?= url('forgot-password.php') ?>" class="auth-form" data-guard>
          <?= csrf_field() ?>
          <label class="field">
            <span>E-posta Adresi</span>
            <input type="email" name="email" class="input" value="<?= e(old('email')) ?>" required
                   autocomplete="email" placeholder="ornek@eposta.com" autofocus>
          </label>
          <button type="submit" class="btn btn-primary btn-lg btn-block">
            <?= icon('mail', '', 19) ?> Sıfırlama Bağlantısı Gönder
          </button>
        </form>

        <div class="auth-alt">
          <a href="<?= url('login.php') ?>" style="font-weight:700">Giriş ekranına dön</a>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
