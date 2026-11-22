<?php
/**
 * Akilli Kariyer - Sayfa alti (footer)
 */

if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/../app/bootstrap.php';
}

$extraScripts = $extraScripts ?? '';
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <span class="brand-mark"><?= icon('compass', '', 21) ?></span>
          <span class="brand-text">
            <span class="brand-name">AKILLI KARİYER</span>
            <span class="brand-slogan"><?= e(setting('site_slogan', 'Geleceğini Keşfet')) ?></span>
          </span>
        </div>
        <p>
          Öğrencilerin ilgi alanlarını, yeteneklerini ve çalışma tercihlerini analiz ederek
          kişiselleştirilmiş meslek önerileri sunan akıllı kariyer rehberi platformu.
        </p>
        <div class="social-links">
          <a href="<?= url('contact.php') ?>" aria-label="İletişim"><?= icon('mail', '', 18) ?></a>
          <a href="<?= url('career-guide.php') ?>" aria-label="Kariyer Rehberi"><?= icon('book-open', '', 18) ?></a>
          <a href="<?= url('about.php') ?>" aria-label="Hakkımızda"><?= icon('info', '', 18) ?></a>
          <a href="<?= url('kvkk.php') ?>" aria-label="KVKK Aydınlatma Metni"><?= icon('shield-check', '', 18) ?></a>
        </div>
      </div>

      <div>
        <h5>Keşfet</h5>
        <ul>
          <li><a href="<?= url('career-test.php') ?>"><?= icon('clipboard-check', '', 15) ?> Kariyer Testi</a></li>
          <li><a href="<?= url('careers.php') ?>"><?= icon('briefcase', '', 15) ?> Meslekler</a></li>
          <li><a href="<?= url('interests.php') ?>"><?= icon('compass', '', 15) ?> İlgi Alanları</a></li>
          <li><a href="<?= url('careers.php?sort=popular') ?>"><?= icon('trending-up', '', 15) ?> Popüler Meslekler</a></li>
        </ul>
      </div>

      <div>
        <h5>Kurumsal</h5>
        <ul>
          <li><a href="<?= url('about.php') ?>"><?= icon('info', '', 15) ?> Hakkımızda</a></li>
          <li><a href="<?= url('contact.php') ?>"><?= icon('mail', '', 15) ?> İletişim</a></li>
          <li><a href="<?= url('kvkk.php') ?>"><?= icon('shield-check', '', 15) ?> KVKK Aydınlatma Metni</a></li>
          <li><a href="<?= url('privacy.php') ?>"><?= icon('lock', '', 15) ?> Gizlilik Politikası</a></li>
          <li><a href="<?= url('terms.php') ?>"><?= icon('file-text', '', 15) ?> Kullanım Koşulları</a></li>
        </ul>
      </div>

      <div>
        <h5>İletişim</h5>
        <ul>
          <li><a href="mailto:<?= e(setting('site_email', 'info@akillikariyer.com')) ?>">
            <?= icon('mail', '', 15) ?> <?= e(setting('site_email', 'info@akillikariyer.com')) ?></a></li>
          <li><a href="tel:<?= e(preg_replace('/\s+/', '', setting('site_phone', ''))) ?>">
            <?= icon('phone', '', 15) ?> <?= e(setting('site_phone', '')) ?></a></li>
          <li style="display:flex;gap:9px;color:rgba(255,255,255,.66);font-size:.9rem;line-height:1.6">
            <?= icon('map-pin', '', 15) ?> <span><?= e(setting('site_address', '')) ?></span>
          </li>
          <li style="display:flex;gap:9px;color:rgba(255,255,255,.66);font-size:.9rem">
            <?= icon('clock', '', 15) ?> <span><?= e(setting('site_working_hours', '')) ?></span>
          </li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= e(setting('site_name', 'Akıllı Kariyer')) ?>. Tüm hakları saklıdır.</span>
      <span style="display:flex;gap:20px;flex-wrap:wrap">
        <a href="<?= url('kvkk.php') ?>">KVKK</a>
        <a href="<?= url('privacy.php') ?>">Gizlilik</a>
        <a href="<?= url('terms.php') ?>">Kullanım Koşulları</a>
      </span>
    </div>
  </div>
</footer>

<button type="button" class="scroll-top" aria-label="Yukarı çık"><?= icon('arrow-up', '', 20) ?></button>

<script src="<?= asset('assets/js/vendor/chart.umd.min.js') ?>"></script>
<script src="<?= asset('assets/js/vendor/lucide.min.js') ?>"></script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
window.AK = window.AK || {};
window.AK.base = <?= json_encode(url('')) ?>;
window.AK.coachData = <?= json_encode(asset('assets/data/careers.json')) ?>;
</script>
<script src="<?= asset('assets/js/career-coach.js') ?>"></script>
<?= $extraScripts ?>
</body>
</html>
