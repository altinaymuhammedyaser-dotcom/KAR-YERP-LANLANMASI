<?php
/**
 * Geliştirme aracı: Meslek fotoğraflarini indirir.
 * Kullanim: php tools/fetch_career_photos.php
 *
 * Unsplash (Unsplash License) kaynakli gercek meslek fotografi kullanilir.
 * Gorseller assets/images/careers/{slug}.jpg olarak kaydedilir.
 */

$map = [
    // ---------------------------- TEKNOLOJI ----------------------------
    'yazilim-muhendisi'           => '1531482615713-2afd69097998',
    'front-end-gelistirici'       => '1550439062-609e1531270e',
    'veri-bilimci'                => '1460925895917-afdab827c52f',
    'siber-guvenlik-uzmani'       => '1555949963-ff9fe0c870eb',
    'bilgisayar-muhendisi'        => '1498050108023-c5249f4df085',
    'yapay-zeka-muhendisi'         => '1515879218367-8466d910aaa4',
    'bilgi-teknolojileri-uzmani'  => '1522071820081-009f0129c71c',
    // ---------------------------- SAGLIK ----------------------------
    'doktor'                      => '1582213782179-e0d53f98f2ca',
    'hemsire'                     => '1584515933487-779824d29309',
    'eczaci'                      => '1516841273335-e39b37888115',
    'dis-hekimi'                  => '1559839734-2b71ea197ec2',
    'fizyoterapist'               => '1631217868264-e5b90bb7e133',
    'psikolog'                    => '1494790108377-be9c29b29330',
    // ---------------------------- EGITIM ----------------------------
    'ogretmen'                    => '1509062522246-3755977927d7',
    'matematik-ogretmeni'         => '1503676260728-1c00da094a0b',
    'universite-ogretim-gorevlisi' => '1524178232363-1fb2b075b655',
    'psikolojik-danisman'        => '1523240795612-9a054b0db644',
    'egitim-uzmani'               => '1541339907198-e08756dedf3f',
    // ---------------------------- MUHENDISLIK ----------------------------
    'makine-muhendisi'            => '1581094794329-c8112a89af12',
    'elektrik-elektronik-muhendisi' => '1581091226825-a6a2a5aee158',
    'insaat-muhendisi'            => '1541888946425-d81bb19240f5',
    'gida-muhendisi'              => '1504917595217-d4dc5ebe6122',
    'cevre-muhendisi'             => '1581092160562-40aa08e78837',
    'havacilik-muhendisi'         => '1504328345606-18bbc8c9d7d1',
    // ---------------------------- SANAT VE TASARIM ----------------------------
    'grafik-tasarimci'            => '1626785774573-4b799315345d',
    'endustri-tasarimcisi'        => '1561214115-f2f134cc4912',
    'mimar'                       => '1503387762-592deb58ef4e',
    'ic-mimarlik-tasarimcisi'     => '1600607687939-ce8a6c25118c',
    'fotografci'                  => '1492691527719-9d1e07e534b4',
    'animasyon-tasarimcisi'       => '1618005198919-d3d4b5a92ead',
    // ---------------------------- HUKUK ----------------------------
    'avukat'                      => '1589829545856-d10d557cf95f',
    'isletme-hukuku-danismani'    => '1589578527966-fdac0f44566c',
    'savci'                       => '1436450412740-6b988f486c6b',
    'noter'                       => '1450101499163-c8848c66ca85',
    // ---------------------------- ISLETME ----------------------------
    'girisimci'                   => '1521791136064-7986c2920216',
    'finans-analisti'             => '1551288049-bebda4e38f71',
    'pazarlama-uzmani'            => '1542744173-8e7e53415bb0',
    'insan-kaynaklari-uzmani'     => '1522071820081-009f0129c71c',
    'muhasebeci'                  => '1454165804606-c3d57bc86b40',
    'lojistik-uzmani'             => '1531058020387-3be344556be6',
    // ---------------------------- BILIM ----------------------------
    'laboratuvar-teknisyeni'      => '1576086213369-97a306d36557',
    'biyokimyager'                => '1582719478250-c89cae4dc85b',
    'fizik-arastirmaci'           => '1532187863486-abf9dbad1b69',
    'meteoroloji-muhendisi'       => '1547347298-4074fc3086f0',
    'bilim-insani'                => '1507003211169-0a1dd7228f2d',
    // ---------------------------- ILETISIM ----------------------------
    'gazeteci'                    => '1504711434969-e33886168f5c',
    'icerik-editoru'              => '1495020689067-958852a7765e',
    'halkla-iliskiler-uzmani'      => '1492724441997-5dc865305da7',
    'kriz-iletisim-uzmani'         => '1590602847861-f357a9332bbc',
    'sosyal-medya-yoneticisi'     => '1611162617474-5b21e879e113',
    // ---------------------------- SPOR ----------------------------
    'antrenor'                    => '1517649763962-0c623066013b',
    'spor-bilimci'                => '1534438327276-14e5300c3a48',
    'profesyonel-futbolcu'        => '1579952363873-27f3bade9f55',
    'spor-menajeri'               => '1540747913346-19e32dc3e97e',
];

$destDir = __DIR__ . '/../assets/images/careers';
if (!is_dir($destDir)) {
    mkdir($destDir, 0775, true);
}

// Mevcut meslek slug'larini veritabanindan al (yoksa seed dosyasindan)
$slugs = array_keys($map);

$mh = curl_multi_init();
$handles = [];
$targets = [];

foreach ($slugs as $slug) {
    $photoId = $map[$slug];
    $url = "https://images.unsplash.com/photo-{$photoId}?w=900&h=600&q=80&fm=jpg&fit=crop&crop=entropy";
    $target = $destDir . '/' . $slug . '.jpg';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING       => '',
    ]);
    curl_multi_add_handle($mh, $ch);
    $handles[] = $ch;
    $targets[] = $target;
}

$running = null;
do {
    curl_multi_exec($mh, $running);
    curl_multi_select($mh, 0.15);
} while ($running > 0);

$ok = 0; $fail = [];
foreach ($handles as $i => $ch) {
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $data = curl_multi_getcontent($ch);
    if ($code === 200 && $data !== false && strlen($data) > 8000) {
        file_put_contents($targets[$i], $data);
        $ok++;
    } else {
        $fail[] = basename($targets[$i]) . " ($code)";
    }
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);
}
curl_multi_close($mh);

echo "Indirilen: $ok / " . count($slugs) . "\n";
if ($fail) {
    echo "Basarisiz:\n";
    foreach ($fail as $f) { echo "  $f\n"; }
}

// Toplam boyut
$total = 0;
foreach (glob($destDir . '/*.jpg') as $file) { $total += filesize($file); }
echo 'Toplam boyut: ' . round($total / 1024 / 1024, 2) . " MB\n";
