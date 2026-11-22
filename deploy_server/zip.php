<?php
// Netlify'e yüklemek için ZIP dosyasını JS içinde base64 olarak sunar.
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store');
$file = dirname(__DIR__) . '/netlify-ai.zip';
if (!is_file($file)) {
    echo 'window.__zipError = "zip yok";';
    return;
}
echo 'window.__zip = "' . base64_encode(file_get_contents($file)) . '";';
