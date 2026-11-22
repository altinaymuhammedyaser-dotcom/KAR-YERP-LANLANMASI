<?php
/** Premium CSS'i tüm sayfalara, meslek verisini panel sayfalarına ekler. */
$root = dirname(__DIR__) . '/netlify-src';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$n1 = $n2 = 0;
foreach ($it as $file) {
    if ($file->getExtension() !== 'html') continue;
    $path = $file->getPathname();
    $c = file_get_contents($path);
    if (strpos($c, 'premium.css') === false) {
        $c = str_replace('</head>', '  <link rel="stylesheet" href="/assets/css/premium.css">' . "\n</head>", $c);
        $n1++;
    }
    if (strpos($path, DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR) !== false && strpos($c, 'careers-data.js') === false) {
        $c = str_replace('<script src="/assets/js/panel.js"></script>', '<script src="/assets/data/careers-data.js"></script>' . "\n" . '<script src="/assets/js/panel.js"></script>', $c);
        $n2++;
    }
    file_put_contents($path, $c);
}
echo "$n1 sayfaya premium.css, $n2 panele veri eklendi.\n";
