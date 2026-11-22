<?php
/**
 * Gelistirme araci: PDF iceriginden metin cikarir (basit akis tabanli).
 * Kullanim: php tools/pdf_text.php storage/cache/ref/onet-short.pdf
 */

$file = $argv[1] ?? '';
if (!is_file($file)) {
    fwrite(STDERR, "Dosya bulunamadi: $file\n");
    exit(1);
}

$data = file_get_contents($file);
$text = '';

// Tum stream nesnelerini bul ve inflate et
$offset = 0;
while (($pos = strpos($data, 'stream', $offset)) !== false) {
    // "stream" anahtar kelimesinden hemen sonra gelen EOL'i atla
    $start = $pos + 6;
    if (substr($data, $start, 2) === "\r\n") {
        $start += 2;
    } elseif (substr($data, $start, 1) === "\n") {
        $start += 1;
    }

    $end = strpos($data, 'endstream', $start);
    if ($end === false) {
        break;
    }

    $raw = substr($data, $start, $end - $start);

    // FlateDecode denenir
    $decoded = @gzuncompress($raw);
    if ($decoded === false) {
        $decoded = @gzinflate($raw);
    }
    if ($decoded === false) {
        $decoded = $raw;
    }

    // Metin gosterim operatorlerini cikar
    if (strpos($decoded, 'Tj') !== false || strpos($decoded, 'TJ') !== false) {
        $text .= extractText($decoded) . "\n";
    }

    $offset = $end + 9;
}

/**
 * PDF icerik akisindan metni cikarir.
 */
function extractText(string $content): string
{
    $out = '';

    // (...) Tj  ve  [(..) -3 (..)] TJ  desenleri
    if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)/s', $content, $m)) {
        foreach ($m[0] as $chunk) {
            $inner = substr($chunk, 1, -1);
            $inner = preg_replace('/\\\\([()\\\\])/', '$1', $inner);
            $inner = preg_replace('/\\\\[rn]/', ' ', $inner);
            $inner = preg_replace('/\\\\t/', ' ', $inner);
            $inner = preg_replace('/\\\\[0-7]{1,3}/', '', $inner);
            $out .= $inner . ' ';
        }
    }

    // Heks stringleri <ABCD> (CID yazi tipleri icin)
    if (preg_match_all('/<([0-9A-Fa-f]{4,})>\s*Tj/', $content, $m2)) {
        foreach ($m2[1] as $hex) {
            $out .= '[HEX:' . substr($hex, 0, 12) . '] ';
        }
    }

    // Satir sonlarini degistir
    $out = preg_replace('/\s{2,}/', ' ', $out);

    return trim($out);
}

echo $text;
