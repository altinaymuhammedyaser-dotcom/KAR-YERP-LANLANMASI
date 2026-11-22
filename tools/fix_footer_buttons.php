<?php
/** Footer altındaki 4 boş butonu işlevli, ikonlu ve açıklamalı hale getirir. */
$root = dirname(__DIR__) . '/netlify-src';

$home = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-7 9 7v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 21v-7h6v7"/></svg>';
$brief = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M2 12h20"/></svg>';
$book = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>';
$mail = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>';

$block = '<div class="social-links">' .
  '<a href="/" title="Ana sayfaya git" aria-label="Ana sayfa" data-tip="Ana Sayfa">' . $home . '</a>' .
  '<a href="/careers" title="Meslek kataloğunu aç" aria-label="Meslekler" data-tip="Meslekler">' . $brief . '</a>' .
  '<a href="/career-guide" title="Kariyer rehberini oku" aria-label="Kariyer Rehberi" data-tip="Rehber">' . $book . '</a>' .
  '<a href="mailto:info@akillikariyer.com" title="E-posta gönder" aria-label="E-posta gönder" data-tip="İletişim">' . $mail . '</a>' .
  '</div>';

$css = <<<'CSS'
<style>
.social-links{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.social-links a{position:relative;width:42px;height:42px;border-radius:13px;display:grid;place-items:center;text-decoration:none;
  background:linear-gradient(145deg,rgba(255,255,255,.12),rgba(255,255,255,.04));border:1px solid rgba(255,255,255,.14);
  color:#fff;transition:transform .22s ease,background .22s ease,box-shadow .22s ease,color .22s ease}
.social-links a svg{width:19px;height:19px;display:block}
.social-links a:hover,.social-links a:focus-visible{transform:translateY(-3px);background:linear-gradient(135deg,#16B8B0,#0E918B);color:#fff;box-shadow:0 12px 24px -12px rgba(22,184,176,.9);outline:none}
.social-links a:after{content:attr(data-tip);position:absolute;bottom:calc(100% + 9px);left:50%;transform:translateX(-50%) translateY(4px);
  background:#142B4A;color:#fff;font-size:.68rem;font-weight:700;white-space:nowrap;padding:6px 9px;border-radius:8px;
  opacity:0;pointer-events:none;transition:.2s ease;box-shadow:0 8px 20px rgba(0,0,0,.25);z-index:5}
.social-links a:hover:after,.social-links a:focus-visible:after{opacity:1;transform:translateX(-50%) translateY(0)}
[data-theme="dark"] .social-links a{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.15);color:#D9E6F5}
</style>
CSS;

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$n = 0;
foreach ($it as $file) {
    if ($file->getExtension() !== 'html') continue;
    $path = $file->getPathname();
    $c = file_get_contents($path);
    if (preg_match('#<div class="social-links">.*?</div>#s', $c)) {
        $c = preg_replace('#<div class="social-links">.*?</div>#s', $block, $c, 1);
    } elseif (strpos($c, 'site-footer') !== false && strpos($c, 'data-tip="Ana Sayfa"') === false) {
        // Hakkımızda gibi yeni tasarımlı footer'lara ekle
        $c = str_replace('<h5>Platform</h5>', $block . '<h5>Platform</h5>', $c);
        if (strpos($c, 'data-tip="Ana Sayfa"') === false) {
            $c = str_replace('</footer>', $block . '</footer>', $c);
        }
    } else {
        continue;
    }
    if (strpos($c, 'data-tip="Ana Sayfa"') !== false && strpos($c, '.social-links a:after') === false) {
        $c = str_replace('</head>', $css . "\n</head>", $c);
    }
    file_put_contents($path, $c);
    $n++;
}
echo "$n sayfa güncellendi.\n";
