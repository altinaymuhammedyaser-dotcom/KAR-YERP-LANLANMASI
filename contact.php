<?php
/**
 * Akilli Kariyer - Iletisim sayfasi
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\CSRF;
use App\Core\Database;
use App\Core\Validator;

$errors = [];
$sent   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    keep_old_input($_POST);

    $validator = Validator::make($_POST)->rules([
        'name'    => 'required|min:2|max:120',
        'email'   => 'required|email|max:190',
        'subject' => 'max:200',
        'message' => 'required|min:10|max:3000',
    ]);

    if (!$validator->validate()) {
        $errors = $validator->errors();
    } else {
        Database::insert(
            'INSERT INTO `contact_messages` (`name`,`email`,`subject`,`message`,`status`,`ip_address`,`created_at`)
             VALUES (?,?,?,?,?,?,NOW())',
            [
                trim($_POST['name']),
                mb_strtolower(trim($_POST['email'])),
                trim($_POST['subject'] ?? '') ?: null,
                trim($_POST['message']),
                'new',
                \App\Core\Auth::ip(),
            ]
        );
        clear_old_input();
        $sent = true;
    }
}

$pageTitle       = 'İletişim — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Akıllı Kariyer platformu hakkında sorularınızı bize iletin.';
$activeNav       = 'contact';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container">
      <span class="eyebrow"><?= icon('mail', '', 15) ?> İletişim</span>
      <h1 style="margin-bottom:6px">Bize Ulaşın</h1>
      <p style="margin:0">Meslekler, test, üniversite seçimi veya platform hakkındaki tüm sorularınız için buradayız.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="container">
      <div class="layout-aside ratio" style="gap:34px">

        <div class="contact-card">
          <?php if ($sent): ?>
            <div class="text-center" style="padding:30px 10px">
              <div class="es-ico" style="width:76px;height:76px;border-radius:22px;background:var(--c-success-soft);color:var(--c-success);display:grid;place-items:center;margin:0 auto 22px">
                <?= icon('check-circle-2', '', 32) ?>
              </div>
              <h2>Mesajınız Alındı!</h2>
              <p style="max-width:420px;margin:0 auto 24px">
                En kısa sürede size dönüş yapacağız. Gelen kutunuzu (ve spam klasörünüzü) kontrol edin.
              </p>
              <a href="<?= url('index.php') ?>" class="btn btn-primary"><?= icon('arrow-left', '', 17) ?> Ana Sayfaya Dön</a>
            </div>
          <?php else: ?>
            <h2 style="margin-bottom:6px">Mesaj Gönder</h2>
            <p style="margin-bottom:24px;font-size:.94rem">Formu doldurun, en kısa sürede dönüş yapalım.</p>

            <?php if ($errors): ?>
              <div class="alert alert-danger"><?= icon('alert-circle', '', 18) ?><span><?= e(reset($errors)) ?></span></div>
            <?php endif; ?>

            <form method="post" class="grid gap-16" data-guard>
              <?= csrf_field() ?>
              <div class="form-grid">
                <label class="field">
                  <span>Ad Soyad *</span>
                  <input type="text" name="name" class="input" required maxlength="120" value="<?= e(old('name')) ?>" placeholder="Adınız Soyadınız">
                </label>
                <label class="field">
                  <span>E-posta *</span>
                  <input type="email" name="email" class="input" required maxlength="190" value="<?= e(old('email')) ?>" placeholder="ornek@eposta.com">
                </label>
              </div>
              <label class="field">
                <span>Konu</span>
                <select name="subject" class="select">
                  <option value="">Konu seçin</option>
                  <option value="Kariyer Testi" <?= old('subject') === 'Kariyer Testi' ? 'selected' : '' ?>>Kariyer Testi</option>
                  <option value="Meslekler Hakkında" <?= old('subject') === 'Meslekler Hakkında' ? 'selected' : '' ?>>Meslekler Hakkında</option>
                  <option value="Üniversite ve Bölüm Seçimi" <?= old('subject') === 'Üniversite ve Bölüm Seçimi' ? 'selected' : '' ?>>Üniversite ve Bölüm Seçimi</option>
                  <option value="Hesap ve Üyelik" <?= old('subject') === 'Hesap ve Üyelik' ? 'selected' : '' ?>>Hesap ve Üyelik</option>
                  <option value="Teknik Destek" <?= old('subject') === 'Teknik Destek' ? 'selected' : '' ?>>Teknik Destek</option>
                  <option value="Diğer" <?= old('subject') === 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                </select>
              </label>
              <label class="field">
                <span>Mesajınız *</span>
                <textarea name="message" class="textarea" required maxlength="3000" rows="6" placeholder="Sorunuzu buraya yazabilirsiniz…"><?= e(old('message')) ?></textarea>
              </label>
              <button type="submit" class="btn btn-accent btn-lg btn-block"><?= icon('send', '', 19) ?> Mesajı Gönder</button>
            </form>
          <?php endif; ?>
        </div>

        <div class="grid" style="gap:22px">
          <div class="card">
            <div class="card-body">
              <h3 style="margin-bottom:16px"><?= icon('info', '', 19) ?> İletişim Bilgileri</h3>
              <ul class="contact-info">
                <li>
                  <span class="ci-ico"><?= icon('mail', '', 19) ?></span>
                  <span><strong>E-posta</strong><span><?= e(setting('site_email', 'info@akillikariyer.com')) ?></span></span>
                </li>
                <li>
                  <span class="ci-ico"><?= icon('phone', '', 19) ?></span>
                  <span><strong>Telefon</strong><span><?= e(setting('site_phone', '')) ?></span></span>
                </li>
                <li>
                  <span class="ci-ico"><?= icon('map-pin', '', 19) ?></span>
                  <span><strong>Adres</strong><span><?= e(setting('site_address', '')) ?></span></span>
                </li>
                <li>
                  <span class="ci-ico"><?= icon('clock', '', 19) ?></span>
                  <span><strong>Çalışma Saatleri</strong><span><?= e(setting('site_working_hours', '')) ?></span></span>
                </li>
              </ul>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <h3 style="margin-bottom:14px"><?= icon('help-circle', '', 19) ?> Sıkça Sorulanlar</h3>
              <div class="grid" style="gap:10px">
                <a href="<?= url('career-guide.php?category=dogru-meslek-secimi') ?>" class="flex between center gap-10" style="font-size:.89rem;font-weight:600;color:var(--c-text)">
                  Meslek seçiminde neler dikkat edilmeli? <?= icon('arrow-right', '', 15) ?>
                </a>
                <a href="<?= url('career-test.php') ?>" class="flex between center gap-10" style="font-size:.89rem;font-weight:600;color:var(--c-text)">
                  Kariyer testi nasıl çalışır? <?= icon('arrow-right', '', 15) ?>
                </a>
                <a href="<?= url('kvkk.php') ?>" class="flex between center gap-10" style="font-size:.89rem;font-weight:600;color:var(--c-text)">
                  Verilerim nasıl korunuyor? <?= icon('arrow-right', '', 15) ?>
                </a>
              </div>
            </div>
          </div>

          <div class="card" style="background:var(--grad-primary);border:0;color:#fff">
            <div class="card-body">
              <h3 style="color:#fff"><?= icon('sparkles', '', 19) ?> Hemen Başlayın</h3>
              <p style="color:rgba(255,255,255,.75);font-size:.9rem">
                Kariyer testini ücretsiz tamamlayın ve size uygun meslekleri keşfedin.
              </p>
              <a href="<?= url('career-test.php') ?>" class="btn btn-accent btn-block btn-sm">Testi Başlat</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
