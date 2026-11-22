<?php
/**
 * Akilli Kariyer - Yeni sifre belirleme
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\Validator;
use App\Models\User;

$token  = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$email  = mb_strtolower(trim((string) ($_GET['email'] ?? $_POST['email'] ?? '')));
$errors = [];
$valid  = false;

$row = null;
if ($token !== '' && $email !== '') {
    $row = Database::fetch(
        'SELECT * FROM `password_resets` WHERE `email` = ? AND `token` = ? AND `used_at` IS NULL AND `expires_at` > NOW() LIMIT 1',
        [$email, hash('sha256', $token)]
    );
    $valid = (bool) $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    CSRF::check();
    keep_old_input($_POST);

    $validator = Validator::make($_POST)->rules([
        'password'              => 'required|password:8',
        'password_confirmation' => 'required|same:password',
    ]);

    if (!$validator->validate()) {
        $errors = $validator->errors();
    } else {
        $newPassword = (string) $_POST['password'];
        Database::transaction(function () use ($row, $newPassword) {
            User::update((int) $row['user_id'], [
                'password'             => Auth::hash($newPassword),
                'must_change_password' => 0,
            ]);
            Database::query('UPDATE `password_resets` SET `used_at` = NOW() WHERE `id` = ?', [$row['id']]);
            Database::query('DELETE FROM `password_resets` WHERE `user_id` = ? AND `used_at` IS NULL', [$row['user_id']]);
        });

        clear_old_input();
        set_flash('success', 'Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
        redirect(url('login.php'));
    }
}

$pageTitle       = 'Yeni Şifre Belirle — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Hesabınız için yeni bir şifre belirleyin.';
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
      <h2 style="margin-top:36px">Yeni bir şifre belirleyin</h2>
      <p>Güçlü ve benzersiz bir şifre seçerek hesabınızın güvenliğini artırın.</p>
    </div>
    <div style="position:relative;z-index:2;font-size:.82rem;color:rgba(255,255,255,.55)">
      &copy; <?= date('Y') ?> <?= e(setting('site_name', 'Akıllı Kariyer')) ?>
    </div>
  </aside>

  <section class="auth-main">
    <div class="auth-card">
      <h1>Yeni Şifre</h1>

      <?php if (!$valid): ?>
        <div class="alert alert-danger">
          <?= icon('alert-circle', '', 18) ?>
          <span>Sıfırlama bağlantısı geçersiz veya süresi dolmuş. Lütfen yeni bir talep oluşturun.</span>
        </div>
        <a href="<?= url('forgot-password.php') ?>" class="btn btn-primary btn-block">
          <?= icon('refresh-cw', '', 18) ?> Yeni Sıfırlama Talebi
        </a>
      <?php else: ?>
        <p class="ac-sub">Hesabınız: <strong style="color:var(--c-primary)"><?= e($email) ?></strong></p>

        <?php if ($errors): ?>
          <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
        <?php endif; ?>

        <form method="post" action="<?= url('reset-password.php') ?>" class="auth-form" data-guard>
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <input type="hidden" name="email" value="<?= e($email) ?>">

          <label class="field">
            <span>Yeni Şifre</span>
            <span class="pw-field">
              <input type="password" name="password" class="input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                     required minlength="8" autocomplete="new-password" data-pw-strength autofocus>
              <button type="button" class="pw-toggle" aria-label="Şifreyi göster"><?= icon('eye', '', 18) ?></button>
            </span>
            <span class="pw-meter"><span data-pw-meter></span></span>
            <span class="hint" data-pw-label></span>
            <?php if (isset($errors['password'])): ?><span class="error-text"><?= e($errors['password']) ?></span><?php endif; ?>
          </label>

          <label class="field">
            <span>Yeni Şifre (Tekrar)</span>
            <span class="pw-field">
              <input type="password" name="password_confirmation" class="input <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>"
                     required minlength="8" autocomplete="new-password">
              <button type="button" class="pw-toggle" aria-label="Şifreyi göster"><?= icon('eye', '', 18) ?></button>
            </span>
            <?php if (isset($errors['password_confirmation'])): ?><span class="error-text"><?= e($errors['password_confirmation']) ?></span><?php endif; ?>
          </label>

          <button type="submit" class="btn btn-accent btn-lg btn-block">
            <?= icon('shield-check', '', 19) ?> Şifreyi Güncelle
          </button>
        </form>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
