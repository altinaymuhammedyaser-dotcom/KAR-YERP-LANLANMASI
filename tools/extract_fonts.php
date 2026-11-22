<?php
/**
 * Gelistirme araci: pdfmake vfs_fonts.js icinden Roboto TrueType dosyalarini cikarir.
 * Kullanim: php tools/extract_fonts.php
 */

$src = __DIR__ . '/../assets/fonts/dl_test.bin';
$out = __DIR__ . '/../assets/fonts/';

if (!is_file($src)) {
    fwrite(STDERR, "Kaynak dosya bulunamadi: $src\n");
    exit(1);
}

$js = file_get_contents($src);

// "Roboto-Regular.ttf": "<base64>" kaliplarini bul
preg_match_all('/"([^"]+\.ttf)"\s*:\s*"([A-Za-z0-9+\/=\s]+)"/', $js, $matches, PREG_SET_ORDER);

$written = [];
foreach ($matches as $m) {
    $name = $m[1];
    $data = base64_decode(preg_replace('/\s+/', '', $m[2]));
    if ($data === false || strlen($data) < 1000) {
        continue;
    }
    // TTF imzasi dogrulamasi (0x00010000 ya da 'true' ya da 'OTTO')
    $sig = substr($data, 0, 4);
    if (!in_array($sig, ["\x00\x01\x00\x00", 'true', 'OTTO'], true)) {
        continue;
    }
    $target = $out . $name;
    file_put_contents($target, $data);
    $written[$name] = strlen($data);
}

foreach ($written as $name => $size) {
    echo "Yazildi: assets/fonts/{$name} (" . number_format($size) . " byte)\n";
}

if (!$written) {
    fwrite(STDERR, "Hiçbir font çıkarılamadı.\n");
    exit(1);
}
