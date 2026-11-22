<?php
/** Panel sayfalarına kalıcı tema başlatıcı ekler. */
$root = dirname(__DIR__) . '/netlify-src/dashboard';
$snippet = <<<'HTML'
<script>
(function(){var t=localStorage.getItem('akk-theme');if(!t){var m=document.cookie.match(/(?:^|; )akk_theme=([^;]+)/);t=m?decodeURIComponent(m[1]):null;}if(t==='dark')document.documentElement.setAttribute('data-theme','dark');else document.documentElement.removeAttribute('data-theme');})();
</script>
HTML;
$n=0;
foreach (glob($root.'/*.html') as $f) {
    $c=file_get_contents($f);
    if (strpos($c,'akk_theme=([^;]+)')!==false) continue;
    $c=str_replace('</head>', $snippet."\n</head>", $c);
    file_put_contents($f,$c); $n++;
}
echo "$n panele tema baslatıcı eklendi.\n";
