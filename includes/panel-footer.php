<?php
/**
 * Akilli Kariyer - Panel alt cerceve
 */

if (!defined('APP_ROOT')) {
    require_once __DIR__ . '/../app/bootstrap.php';
}

$extraScripts = $extraScripts ?? '';
?>
    </main>

    <footer style="padding:20px 28px;border-top:1px solid var(--c-border);font-size:.8rem;color:var(--c-muted);display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
      <span>&copy; <?= date('Y') ?> <?= e(setting('site_name', 'Akıllı Kariyer')) ?></span>
      <span><?= e(setting('site_slogan', 'Geleceğini Keşfet')) ?></span>
    </footer>
  </div>
</div>

<script src="<?= asset('assets/js/vendor/chart.umd.min.js') ?>"></script>
<script src="<?= asset('assets/js/vendor/lucide.min.js') ?>"></script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
<script>
window.AK = window.AK || {};
window.AK.base = <?= json_encode(url('')) ?>;
window.AK.coachData = <?= json_encode(asset('assets/data/careers.json')) ?>;
</script>
<script src="<?= asset('assets/js/career-coach.js') ?>"></script>
<script>
(function () {
  var layout = document.getElementById('panel-layout');
  var burger = document.getElementById('panel-burger');
  var overlay = document.getElementById('nav-overlay');
  if (layout && burger) {
    burger.addEventListener('click', function () { layout.classList.toggle('nav-open'); });
    if (overlay) overlay.addEventListener('click', function () { layout.classList.remove('nav-open'); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') layout.classList.remove('nav-open'); });
  }
})();
</script>
<?= $extraScripts ?>
</body>
</html>
