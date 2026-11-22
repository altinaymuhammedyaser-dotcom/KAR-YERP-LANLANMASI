<?php
/** Netlify statik sürümü için üye paneli sayfalarını ve ortak script enjeksiyonunu üretir. */
$root = dirname(__DIR__) . '/netlify-src';

$pages = [
    'index'            => 'Genel Bakış',
    'tests'            => 'Kariyer Testim',
    'results'          => 'Test Sonuçlarım',
    'recommendations'  => 'Meslek Önerilerim',
    'explore'          => 'Meslekleri Keşfet',
    'favorites'        => 'Favorilerim',
    'activities'       => 'Aktivitelerim',
    'career-roadmap'   => 'Kariyer Yol Haritam',
    'skill-gap'        => 'Beceri Analizim',
    'cv-builder'       => 'CV Oluşturucu',
    'interview-sim'    => 'Mülakat Simülatörü',
    'personality-test' => 'Kişilik Testi',
    'profile'          => 'Profilim',
    'settings'         => 'Ayarlar',
];

$head = <<<'HTML'
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>__TITLE__ — Akıllı Kariyer</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/panel.css">
<link rel="stylesheet" href="/assets/css/career-coach.css">
</head>
HTML;

$scripts = <<<'HTML'
<script>
window.AK = window.AK || {};
window.AK.base = '/';
window.AK.coachData = '/assets/data/careers.json';
window.AK.careerTpl = 'meslek/{slug}';
window.AK.urls = {
  test: 'career-test',
  personality: 'dashboard/personality-test.html',
  interview: 'dashboard/interview-sim.html',
  cv: 'dashboard/cv-builder.html',
  results: 'dashboard/results.html',
  profile: 'dashboard/profile.html'
};
</script>
<script src="/assets/js/netlify-app.js"></script>
<script src="/assets/js/career-coach.js"></script>
<script src="/assets/js/panel.js"></script>
<script>
(function(){
  var l=document.getElementById('panel-layout'),b=document.getElementById('panel-burger'),o=document.getElementById('nav-overlay');
  if(b&&l)b.addEventListener('click',function(){l.classList.toggle('nav-open');});
  if(o&&l)o.addEventListener('click',function(){l.classList.remove('nav-open');});
  if(window.lucide)window.lucide.createIcons();
})();
</script>
</body>
</html>
HTML;

foreach ($pages as $key => $title) {
    $dataKey = $key === 'career-roadmap' ? 'roadmap' : $key;
    $html = str_replace('__TITLE__', $title, $head);
    $html .= <<<HTML
<body data-dashboard="{$dataKey}">
<div class="panel-layout" id="panel-layout">
  <aside class="panel-side" id="panel-side">
    <a class="panel-brand" href="/"><span class="mark"><i data-lucide="compass" style="width:20px;height:20px"></i></span><span>Akıllı Kariyer<small>GELECEĞİNİ KEŞFET</small></span></a>
    <div class="panel-user"><span class="panel-avatar" data-user-initials>AK</span><div><b data-user-name>Üye</b><small data-user-email>hesap</small></div></div>
    <nav class="panel-nav" id="panelNav"></nav>
    <a href="/" style="color:rgba(255,255,255,.7);font-size:.8rem;text-decoration:none;padding:10px 12px">← Siteye dön</a>
  </aside>
  <div id="nav-overlay" style="display:none"></div>
  <main class="panel-main">
    <div class="panel-top">
      <div><h1 id="panelTitle">{$title}</h1><p id="panelSubtitle">Üye paneli</p></div>
      <button type="button" class="panel-burger" id="panel-burger" aria-label="Menü"><i data-lucide="menu" style="width:22px;height:22px"></i></button>
    </div>
    <div id="panelContent"><div class="panel-card"><p class="muted">Yükleniyor…</p></div></div>
  </main>
</div>
<div class="panel-gate" id="authGate" style="display:none">
  <div class="box"><h2>Üye alanı</h2><p>Bu sayfayı görüntülemek için giriş yapmalısın.</p>
    <div class="panel-actions" style="justify-content:center">
      <button type="button" class="primary" data-demo-login>Demo hesapla giriş yap</button>
      <a href="/login">Giriş yap</a>
    </div>
  </div>
</div>
HTML;
    $html .= "\n" . $scripts;
    file_put_contents($root . '/dashboard/' . $key . '.html', $html);
}

/* Panel navigasyonunu panel.js içinden değil, her sayfada sunucu tarafında da verelim (JS yüklemeden önce görünsün). */
$navJs = file_get_contents($root . '/assets/js/panel.js');
$navHtml = '';
foreach (['index|Genel Bakış|layout-dashboard|index.html','tests|Kariyer Testim|clipboard-check|tests.html','results|Test Sonuçlarım|file-bar-chart|results.html','recommendations|Meslek Önerilerim|sparkles|recommendations.html','explore|Meslekleri Keşfet|search|explore.html','favorites|Favorilerim|heart|favorites.html','activities|Aktivitelerim|activity|activities.html','sep|| |','roadmap|Kariyer Yol Haritam|map|career-roadmap.html','skill-gap|Beceri Analizim|target|skill-gap.html','cv-builder|CV Oluşturucu|file-text|cv-builder.html','interview-sim|Mülakat Simülatörü|mic|interview-sim.html','personality-test|Kişilik Testi|brain|personality-test.html','sep|| |','profile|Profilim|user-cog|profile.html','settings|Ayarlar|settings|settings.html'] as $line) {
    $p = explode('|', $line);
    if ($p[0] === 'sep') { $navHtml .= '<div class="nav-sep"></div>'; continue; }
    $navHtml .= '<a href="/dashboard/' . $p[3] . '" data-nav="' . $p[0] . '"><i data-lucide="' . $p[2] . '" style="width:17px;height:17px"></i><span>' . $p[1] . '</span></a>';
}
foreach (glob($root . '/dashboard/*.html') as $f) {
    $c = file_get_contents($f);
    $c = str_replace('<nav class="panel-nav" id="panelNav"></nav>', '<nav class="panel-nav" id="panelNav">' . $navHtml . '</nav>', $c);
    file_put_contents($f, $c);
}

/* Tüm herkese açık HTML sayfalarına AI koç + oturum desteği ekle */
$injectHead = '<link rel="stylesheet" href="/assets/css/career-coach.css">' . "\n";
$injectBody = <<<'HTML'
<script>
window.AK = window.AK || {};
window.AK.base = '/';
window.AK.coachData = '/assets/data/careers.json';
window.AK.careerTpl = 'meslek/{slug}';
window.AK.urls = {test:'career-test',personality:'dashboard/personality-test.html',interview:'dashboard/interview-sim.html',cv:'dashboard/cv-builder.html',results:'dashboard/results.html',profile:'dashboard/profile.html'};
</script>
<script src="/assets/js/netlify-app.js"></script>
<script src="/assets/js/career-coach.js"></script>
HTML;

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$count = 0;
foreach ($it as $file) {
    if ($file->getExtension() !== 'html') continue;
    // dashboard sayfaları zaten kendi scriptlerini içeriyor
    if (strpos(str_replace('\\', '/', $file->getPathname()), '/dashboard/') !== false) continue;
    $c = file_get_contents($file->getPathname());
    if (strpos($c, 'career-coach.js') !== false) continue;
    $c = str_replace('</head>', $injectHead . '</head>', $c);
    $c = str_replace('</body>', $injectBody . "\n</body>", $c);
    file_put_contents($file->getPathname(), $c);
    $count++;
}

echo count($pages), " panel sayfasi, ", $count, " genel sayfa guncellendi.\n";
