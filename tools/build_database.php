<?php
/**
 * Akilli Kariyer - database.sql dosyasini uretir.
 * Kullanim:  php tools/build_database.php
 *
 * Semasi:  tools/schema.php
 * Veriler:  tools/seed_careers.php , tools/seed_questions.php
 */

if (PHP_SAPI !== 'cli') {
    die('Bu arac sadece komut satirindan calistirilabilir.');
}

$root   = dirname(__DIR__);
$outFile = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sql';

$schema    = require __DIR__ . '/schema.php';
$seedC     = require __DIR__ . '/seed_careers.php';
$seedQ     = require __DIR__ . '/seed_questions.php';

/* ------------------------------------------------------------------ */
/* Yardimcilar                                                        */
/* ------------------------------------------------------------------ */

function sqlStr(?string $v): string {
    if ($v === null) return 'NULL';
    return "'" . str_replace(["\\", "'"], ["\\\\", "''"], $v) . "'";
}

function sqlNum($v): string {
    return is_null($v) ? 'NULL' : (string) (int) $v;
}

function sqlFloat($v): string {
    return is_null($v) ? 'NULL' : (string) round((float) $v, 4);
}

/** "teknoloji:5,bilim:4" -> ['teknoloji'=>5,'bilim'=>4] */
function parseWeights(?string $spec): array {
    $out = [];
    if (!$spec) return $out;
    foreach (explode(',', $spec) as $part) {
        $part = trim($part);
        if ($part === '') continue;
        $bits = explode(':', $part);
        $key = trim($bits[0]);
        $val = isset($bits[1]) ? (int) trim($bits[1]) : 3;
        if ($key === '') continue;
        $out[$key] = max(1, min(5, $val));
    }
    return $out;
}

/** Turkce karakterleri normalize ederek URL uyumlu slug uretir */
function slugify(string $text, string $fallback = 'icerik'): string {
    $tr = [
        'ı'=>'i','İ'=>'i','ş'=>'s','Ş'=>'s','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u',
        'ö'=>'o','Ö'=>'o','ç'=>'c','Ç'=>'c','â'=>'a','Â'=>'a','î'=>'i','Î'=>'i','û'=>'u','Û'=>'u'
    ];
    $text = strtr($text, $tr);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : $fallback;
}

/**
 * Beceri metninden yetenek boyutu eslemesi.
 * Anahtar kelimeler oncelik sirasina gore kontrol edilir.
 */
function guessAbility(string $skill): ?string {
    $s = mb_strtolower($skill, 'UTF-8');
    $map = [
        'yapay zeka'        => 'teknoloji_becerisi',
        'derin ogrenme'     => 'teknoloji_becerisi',
        'programlama'       => 'teknoloji_becerisi',
        'yazilim'           => 'teknoloji_becerisi',
        'kod'               => 'teknoloji_becerisi',
        'teknoloji'         => 'teknoloji_becerisi',
        'bilgisayar'        => 'teknoloji_becerisi',
        'elektronik'        => 'teknoloji_becerisi',
        'algoritma'         => 'analitik',
        'analiz'            => 'analitik',
        'analitik'          => 'analitik',
        'degerlendirme'     => 'analitik',
        'matematik'         => 'matematik',
        'hesap'             => 'matematik',
        'sayisal'           => 'matematik',
        'istatistik'        => 'matematik',
        'modelleme'         => 'matematik',
        'problem cozme'     => 'problem_cozme',
        'hata ayiklama'     => 'problem_cozme',
        'cozum'             => 'problem_cozme',
        'liderlik'          => 'liderlik',
        'yonetim'           => 'liderlik',
        'ekip'              => 'liderlik',
        'iletişim'          => 'iletisim_becerisi',
        'iletisim'          => 'iletisim_becerisi',
        'sunum'             => 'iletisim_becerisi',
        'ikna'              => 'iletisim_becerisi',
        'anlat'             => 'iletisim_becerisi',
        'rapor'             => 'sozsel_ifade',
        'yazili'            => 'sozsel_ifade',
        'dokumantasyon'     => 'sozsel_ifade',
        'sozlesme'          => 'sozsel_ifade',
        'yazim'             => 'sozsel_ifade',
        'sözlü'             => 'sozsel_ifade',
        'senaryo'           => 'sozsel_ifade',
        'makale'            => 'sozsel_ifade',
        'akademik'          => 'sozsel_ifade',
        'yazarlik'          => 'sozsel_ifade',
        'yaratıcı'          => 'yaraticilik',
        'yaraticilik'       => 'yaraticilik',
        'yaratıcılık'       => 'yaraticilik',
        'tasarim'           => 'yaraticilik',
        'estetik'           => 'yaraticilik',
        'ozgun'             => 'yaraticilik',
        'konsept'           => 'yaraticilik',
        'gorsel'            => 'gorsel_dusunme',
        'görsel'            => 'gorsel_dusunme',
        'renk'              => 'gorsel_dusunme',
        'cizim'             => 'gorsel_dusunme',
        'kompozisyon'       => 'gorsel_dusunme',
        'tipografi'        => 'gorsel_dusunme',
        'yerlesim'          => 'gorsel_dusunme',
        'planlama'          => 'organizasyon',
        'organizasyon'      => 'organizasyon',
        'koordinasyon'      => 'organizasyon',
        'düzen'             => 'organizasyon',
        'surec'             => 'organizasyon',
        'sure'              => 'organizasyon',
        'kaynak yonetimi'   => 'organizasyon',
        'envanter'          => 'organizasyon',
        'butce'             => 'organizasyon',
        'bütçe'             => 'organizasyon',
        'el becerisi'       => 'el_becerisi',
        'elle'              => 'el_becerisi',
        'atolye'            => 'el_becerisi',
        'pratik'            => 'el_becerisi',
        'motor'             => 'el_becerisi',
        'deney'             => 'arastirma_yetenegi',
        'arastirma'         => 'arastirma_yetenegi',
        'araştırma'         => 'arastirma_yetenegi',
        'inceleme'          => 'arastirma_yetenegi',
        'literat'           => 'arastirma_yetenegi',
        'ampirik'           => 'arastirma_yetenegi',
        'empati'            => 'iletisim_becerisi',
        'sabir'             => 'iletisim_becerisi',
        'dogruluk'          => 'analitik',
        'etik'              => 'sozsel_ifade',
        'mevzuat'           => 'sozsel_ifade',
        'sosyal'            => 'iletisim_becerisi',
        'hikaye'            => 'sozsel_ifade',
        'hikaye anlat'      => 'sozsel_ifade',
        'senaryo yazma'     => 'sozsel_ifade',
    ];
    foreach ($map as $needle => $ability) {
        if (mb_strpos($s, $needle, 0, 'UTF-8') !== false) {
            return $ability;
        }
    }
    return null;
}

/** Yetenek anahtarlarinin Turkce gorunum adlari */
function abilityLabel(string $key): string {
    $map = [
        'analitik' => 'analitik düşünme', 'matematik' => 'matematiksel düşünme',
        'problem_cozme' => 'problem çözme', 'iletisim_becerisi' => 'iletişim becerisi',
        'yaraticilik' => 'yaratıcılık', 'liderlik' => 'liderlik', 'organizasyon' => 'organizasyon',
        'el_becerisi' => 'el becerisi', 'arastirma_yetenegi' => 'araştırma yeteneği',
        'teknoloji_becerisi' => 'teknolojik beceriler', 'gorsel_dusunme' => 'görsel düşünme',
        'sozsel_ifade' => 'sözel ifade',
    ];
    return $map[$key] ?? $key;
}

/** Ilgi alani anahtarlarinin Turkce gorunum adlari */
function interestLabel(string $key): string {
    $map = [
        'teknoloji' => 'teknoloji', 'bilim' => 'bilim', 'saglik' => 'sağlık', 'egitim' => 'eğitim',
        'sanat' => 'sanat', 'tasarim' => 'tasarım', 'spor' => 'spor', 'isletme' => 'işletme',
        'ekonomi' => 'ekonomi', 'hukuk' => 'hukuk', 'iletisim' => 'iletişim',
        'sosyal_hizmet' => 'sosyal hizmetler', 'muhendislik' => 'mühendislik', 'arastirma' => 'araştırma',
    ];
    return $map[$key] ?? $key;
}

/** Tercih anahtarlarinin Turkce gorunum adlari */
function preferenceLabel(string $key): string {
    $map = [
        'takim_calismasi' => 'takım çalışması', 'bireysel_calisma' => 'bireysel çalışma',
        'insanlarla_iletisim' => 'insanlarla iletişim', 'bilgisayar_basinda' => 'bilgisayar başında çalışma',
        'saha_calismasi' => 'saha çalışması', 'arastirma_yapma' => 'araştırma yapma',
        'uretim_tasarim' => 'üretim ve tasarım', 'planlama_yonetim' => 'planlama ve yönetim',
    ];
    return $map[$key] ?? $key;
}

/** Ortam anahtarlarinin Turkce gorunum adlari */
function environmentLabel(string $key): string {
    $map = [
        'ofis' => 'ofis ortamı', 'laboratuvar' => 'laboratuvar ortamı', 'hastane' => 'hastane ortamı',
        'okul' => 'eğitim ortamı', 'atolye' => 'atölye ortamı', 'acik_alan' => 'açık alan',
        'uzaktan_calisma' => 'uzaktan çalışma', 'karma_calisma' => 'karma çalışma',
    ];
    return $map[$key] ?? $key;
}

/* ------------------------------------------------------------------ */
/* Baslangic verilerini olustur                                        */
/* ------------------------------------------------------------------ */

$sql = [];
$sql[] = "-- ============================================================";
$sql[] = "-- AKILLI KARİYER REHBERİ - VERİTABANI YEDEK DOSYASI";
$sql[] = "-- Otomatik olarak tools/build_database.php aracı ile üretilmiştir.";
$sql[] = "-- MySQL 8.0+ / MariaDB 10.4+ ile uyumludur.";
$sql[] = "-- İçe aktarma:  mysql -u root -p < database.sql";
$sql[] = "-- ============================================================";
$sql[] = "";
$sql[] = $schema;
$sql[] = "";

/* ---------------------- Yonetici / demo kullanici ---------------------- */
$adminPass   = 'Admin!2345';
$demoPass    = 'Demo!2345';
$adminHash   = password_hash($adminPass, PASSWORD_DEFAULT);
$demoHash    = password_hash($demoPass, PASSWORD_DEFAULT);

$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "-- BASLANGIC KULLANICILARI";
$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "INSERT INTO `users` (`id`,`first_name`,`last_name`,`email`,`password`,`education_level`,`is_active`,`email_verified_at`,`created_at`) VALUES";
$sql[] = "(1,'Site','Yoneticisi','admin@akillikariyer.com'," . sqlStr($adminHash) . ",'universite',1,NOW(),NOW()),";
$sql[] = "(2,'Deniz','Yilmaz','demo@akillikariyer.com'," . sqlStr($demoHash) . ",'universite',1,NOW(),NOW());";

$sql[] = "INSERT INTO `user_profiles` (`user_id`,`city`,`study_field`,`bio`,`preferred_work_style`) VALUES";
$sql[] = "(1,'İstanbul','Bilgisayar Mühendisliği','Platform yöneticisi hesabı.','karma'),";
$sql[] = "(2,'İzmir','Psikoloji','Demo kullanıcı hesabı.','takim');";

$sql[] = "INSERT INTO `user_roles` (`user_id`,`role`) VALUES (1,'admin'),(1,'user'),(2,'user');";
$sql[] = "";

/* ---------------------- Kategoriler ---------------------- */
$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "-- MESLEK KATEGORILERI";
$sql[] = "-- ---------------------------------------------------------------------";
$catId = [];
$rows = [];
$i = 1;
foreach ($seedC['categories'] as $c) {
    [$name, $slug, $desc, $icon, $color, $sort] = $c;
    $catId[$slug] = $i;
    $rows[] = "(" . $i . "," . sqlStr($name) . "," . sqlStr($slug) . "," . sqlStr($desc) . "," . sqlStr($icon) . "," . sqlStr($color) . "," . $sort . ",1)";
    $i++;
}
$sql[] = "INSERT INTO `career_categories` (`id`,`name`,`slug`,`description`,`icon`,`color`,`sort_order`,`is_active`) VALUES";
$sql[] = implode(",\n", $rows) . ";";
$sql[] = "";

/* ---------------------- Meslekler ---------------------- */
$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "-- MESLEKLER (" . count($seedC['careers']) . " kayit)";
$sql[] = "-- ---------------------------------------------------------------------";

$careerRows   = [];
$interestRows = [];
$skillRows    = [];
$compRows     = [];
$prefRows     = [];
$envRows      = [];
$reqRows      = [];

$careerId = 0;
$reqId    = 0;
$compId   = 0;

foreach ($seedC['careers'] as $c) {
    $careerId++;
    $slug = slugify($c['name'], 'meslek-' . $careerId);
    $cid  = $catId[$c['cat']] ?? 1;
    $w    = $c['w'];

    // Meslek fotografi varsa kaydet (assets/images/careers/{slug}.jpg)
    $photoFile = __DIR__ . '/../assets/images/careers/' . $slug . '.jpg';
    $imagePath = is_file($photoFile) ? 'careers/' . $slug . '.jpg' : null;

    $careerRows[] = "(" . $careerId . "," . $cid . "," . sqlStr($c['name']) . "," . sqlStr($slug) . ","
        . sqlStr($c['short']) . "," . sqlStr($c['desc']) . "," . sqlStr($imagePath) . "," . sqlStr($c['icon']) . ","
        . sqlStr($c['edu']) . "," . sqlStr($c['edu_detail']) . "," . sqlStr($c['areas']) . ","
        . sqlStr(implode(' → ', $c['path'])) . "," . sqlStr(implode(' • ', $c['adv'])) . ","
        . sqlStr($c['salary']) . ",0," . ($careerId <= 6 ? 1 : 0) . ",1," . $careerId . ")";

    // Ilgi alani kriterleri
    foreach (parseWeights($w['i'] ?? '') as $key => $val) {
        $interestRows[] = "(" . $careerId . "," . sqlStr($key) . "," . $val . ")";
    }
    // Yetenek kriterleri
    foreach (parseWeights($w['a'] ?? '') as $key => $val) {
        $skillRows[] = "(" . $careerId . "," . sqlStr($key) . "," . $val . ")";
    }
    // Calisma tercihi kriterleri
    foreach (parseWeights($w['p'] ?? '') as $key => $val) {
        $prefRows[] = "(" . $careerId . "," . sqlStr($key) . "," . $val . ")";
    }
    // Calisma ortami kriterleri
    foreach (parseWeights($w['e'] ?? '') as $key => $val) {
        $envRows[] = "(" . $careerId . "," . sqlStr($key) . "," . $val . ")";
    }
    // Beceriler (beceri uyumu bileseni)
    foreach ($c['skills'] as $idx => $skill) {
        $ability = guessAbility($skill) ?? 'analitik';
        $compId++;
        $compRows[] = "(" . $compId . "," . $careerId . "," . sqlStr($skill) . "," . sqlStr($ability) . ","
            . max(2, min(5, 5 - $idx)) . "," . ($idx + 1) . ")";
    }
    // Metinsel gereksinimler
    $ord = 0;
    foreach ($c['tasks'] as $t) {
        $reqId++;
        $reqRows[] = "(" . $reqId . "," . $careerId . ",'task',NULL," . sqlStr($t) . "," . (++$ord) . ")";
    }
    foreach ($c['skills'] as $s) {
        $reqId++;
        $reqRows[] = "(" . $reqId . "," . $careerId . ",'skill',NULL," . sqlStr($s) . "," . (++$ord) . ")";
    }
    $reqId++;
    $reqRows[] = "(" . $reqId . "," . $careerId . ",'education'," . sqlStr('Gerekli Eğitim') . "," . sqlStr($c['edu_detail']) . "," . (++$ord) . ")";
    foreach ($c['depts'] as $d) {
        $reqId++;
        $reqRows[] = "(" . $reqId . "," . $careerId . ",'department'," . sqlStr('Örnek Bölümler') . "," . sqlStr($d) . "," . (++$ord) . ")";
    }
    $reqId++;
    $reqRows[] = "(" . $reqId . "," . $careerId . ",'work_area'," . sqlStr('Çalışma Alanları') . "," . sqlStr($c['areas']) . "," . (++$ord) . ")";
    foreach ($c['path'] as $p) {
        $reqId++;
        $reqRows[] = "(" . $reqId . "," . $careerId . ",'path'," . sqlStr('Kariyer Gelişim Yolu') . "," . sqlStr($p) . "," . (++$ord) . ")";
    }
    foreach ($c['adv'] as $a) {
        $reqId++;
        $reqRows[] = "(" . $reqId . "," . $careerId . ",'advantage'," . sqlStr('Avantajlar') . "," . sqlStr($a) . "," . (++$ord) . ")";
    }
    foreach ($c['certs'] as $cc) {
        $reqId++;
        $reqRows[] = "(" . $reqId . "," . $careerId . ",'certificate'," . sqlStr('Faydalı Sertifikalar') . "," . sqlStr($cc) . "," . (++$ord) . ")";
    }
}

$sql[] = "INSERT INTO `careers` (`id`,`category_id`,`name`,`slug`,`short_description`,`description`,`image`,`icon`,`education_level`,`education_detail`,`working_areas`,`career_path`,`advantages`,`salary_range`,`view_count`,`is_featured`,`is_active`,`sort_order`) VALUES";
$sql[] = implode(",\n", $careerRows) . ";";

$sql[] = "INSERT INTO `career_interests` (`career_id`,`interest_key`,`weight`) VALUES";
$sql[] = implode(",\n", $interestRows) . ";";

$sql[] = "INSERT INTO `career_skills` (`career_id`,`ability_key`,`weight`) VALUES";
$sql[] = implode(",\n", $skillRows) . ";";

$sql[] = "INSERT INTO `career_preferences` (`career_id`,`preference_key`,`weight`) VALUES";
$sql[] = implode(",\n", $prefRows) . ";";

$sql[] = "INSERT INTO `career_environments` (`career_id`,`environment_key`,`weight`) VALUES";
$sql[] = implode(",\n", $envRows) . ";";

$sql[] = "INSERT INTO `career_competencies` (`id`,`career_id`,`competency_name`,`ability_key`,`weight`,`sort_order`) VALUES";
$sql[] = implode(",\n", $compRows) . ";";

$sql[] = "INSERT INTO `career_requirements` (`id`,`career_id`,`requirement_type`,`title`,`content`,`sort_order`) VALUES";
$sql[] = implode(",\n", $reqRows) . ";";
$sql[] = "";

/* ---------------------- Test kategorileri ve sorular ---------------------- */
$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "-- TEST KATEGORILERI, SORULAR VE CEVAP SECENEKLERI";
$sql[] = "-- ---------------------------------------------------------------------";

$testCatId = [];
$rows = [];
$i = 1;
foreach ($seedQ['categories'] as $c) {
    [$name, $slug, $desc, $icon, $dimType, $weight, $sort] = $c;
    $testCatId[$slug] = $i;
    $rows[] = "(" . $i . "," . sqlStr($name) . "," . sqlStr($slug) . "," . sqlStr($desc) . "," . sqlStr($icon) . ","
        . sqlStr($dimType) . "," . sqlFloat($weight) . "," . $sort . ",1)";
    $i++;
}
$sql[] = "INSERT INTO `test_categories` (`id`,`name`,`slug`,`description`,`icon`,`dimension_type`,`weight`,`sort_order`,`is_active`) VALUES";
$sql[] = implode(",\n", $rows) . ";";

$qRows   = [];
$oRows   = [];
$qId     = 0;
$oId     = 0;
$sortOrd = 0;

foreach ($seedQ['questions'] as $q) {
    $qId++;
    $sortOrd++;
    $cat = $testCatId[$q['cat']] ?? 1;
    $type = $q['type'];
    $min  = $q['min'] ?? 1;
    $max  = $q['max'] ?? ($type === 'multiple' ? 4 : ($type === 'scale' ? 1 : 1));
    $qRows[] = "(" . $qId . "," . $cat . "," . sqlStr($q['text']) . ","
        . sqlStr($q['helper'] ?? null) . "," . sqlStr($q['src'] ?? null) . "," . sqlStr($type) . "," . $min . "," . $max . ","
        . $sortOrd . ",1)";

    $osort = 0;
    foreach ($q['options'] as $o) {
        $oId++;
        $oRows[] = "(" . $oId . "," . $qId . "," . sqlStr($o[0]) . "," . sqlStr($o[1]) . ","
            . (int) $o[2] . "," . sqlStr($o[3]) . "," . sqlStr($o[4]) . "," . (++$osort) . ")";
    }
}

$sql[] = "INSERT INTO `test_questions` (`id`,`category_id`,`question_text`,`helper_text`,`source`,`question_type`,`min_select`,`max_select`,`sort_order`,`is_active`) VALUES";
$sql[] = implode(",\n", $qRows) . ";";
$sql[] = "INSERT INTO `test_options` (`id`,`question_id`,`option_text`,`option_key`,`score_value`,`mapping_type`,`mapping_key`,`sort_order`) VALUES";
$sql[] = implode(",\n", $oRows) . ";";
$sql[] = "";

/* ---------------------- Blog ---------------------- */
$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "-- BLOG KATEGORILERI VE YAZILARI";
$sql[] = "-- ---------------------------------------------------------------------";

$blogCatId = [];
$rows = [];
$i = 1;
foreach ($seedC['blog_categories'] as $c) {
    [$name, $slug, $desc, $icon, $color, $sort] = $c;
    $blogCatId[$slug] = $i;
    $rows[] = "(" . $i . "," . sqlStr($name) . "," . sqlStr($slug) . "," . sqlStr($desc) . ","
        . sqlStr($icon) . "," . sqlStr($color) . "," . $sort . ")";
    $i++;
}
$sql[] = "INSERT INTO `blog_categories` (`id`,`name`,`slug`,`description`,`icon`,`color`,`sort_order`) VALUES";
$sql[] = implode(",\n", $rows) . ";";

$rows = [];
$i = 1;
foreach ($seedC['posts'] as $p) {
    $slug = slugify($p['title'], 'yazi-' . $i);
    $rows[] = "(" . $i . "," . ($blogCatId[$p['cat']] ?? 1) . ",1," . sqlStr($p['title']) . "," . sqlStr($slug) . ","
        . sqlStr($p['excerpt']) . "," . sqlStr($p['content']) . ",NULL,'published',"
        . (int) $p['read'] . ",0,NOW(),NOW())";
    $i++;
}
$sql[] = "INSERT INTO `blog_posts` (`id`,`category_id`,`author_id`,`title`,`slug`,`excerpt`,`content`,`image`,`status`,`reading_time`,`view_count`,`published_at`,`created_at`) VALUES";
$sql[] = implode(",\n", $rows) . ";";
$sql[] = "";

/* ---------------------- Site ayarlari ---------------------- */
$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "-- SITE AYARLARI";
$sql[] = "-- ---------------------------------------------------------------------";
$rows = [];
$i = 1;
foreach ($seedC['settings'] as $s) {
    [$key, $value, $group, $type, $label] = $s;
    $rows[] = "(" . $i . "," . sqlStr($key) . "," . sqlStr($value) . "," . sqlStr($group) . ","
        . sqlStr($type) . "," . sqlStr($label) . ")";
    $i++;
}
$sql[] = "INSERT INTO `site_settings` (`id`,`setting_key`,`setting_value`,`setting_group`,`setting_type`,`label`) VALUES";
$sql[] = implode(",\n", $rows) . ";";
$sql[] = "";

/* ---------------------- Ornek aktivite ---------------------- */
$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "-- ORNEK KULLANICI AKTIVITELERI";
$sql[] = "-- ---------------------------------------------------------------------";
$sql[] = "INSERT INTO `user_activities` (`user_id`,`activity_type`,`description`,`reference_type`,`reference_id`) VALUES";
$sql[] = "(2,'registration','Akıllı Kariyer platformuna ücretsiz üye oldunuz.','user',2),";
$sql[] = "(2,'view_career','Yazılım Mühendisi mesleğini incelediniz.','career',1),";
$sql[] = "(2,'favorite','Yazılım Mühendisi mesleğini favorilerinize eklediniz.','career',1);";
$sql[] = "";
$sql[] = "-- ============================================================";
$sql[] = "-- BAŞLANGIÇ VERİLERİ TAMAMLANDI";
$sql[] = "-- ============================================================";

/* ------------------------------------------------------------------ */
/* Dosyayi yaz                                                        */
/* ------------------------------------------------------------------ */

$content = implode("\n", $sql) . "\n";
if (!is_dir(dirname($outFile))) {
    mkdir(dirname($outFile), 0775, true);
}
file_put_contents($outFile, $content, LOCK_EX);

echo "database.sql olusturuldu: " . $outFile . PHP_EOL;
echo "Boyut: " . round(strlen($content) / 1024, 1) . " KB" . PHP_EOL;
echo "Meslek sayisi      : " . count($seedC['careers']) . PHP_EOL;
echo "Kategori sayisi    : " . count($seedC['categories']) . PHP_EOL;
echo "Soru sayisi        : " . count($seedQ['questions']) . PHP_EOL;
echo "Secenek sayisi     : " . $oId . PHP_EOL;
echo "Blog yazisi        : " . count($seedC['posts']) . PHP_EOL;
echo "Ayarlar            : " . count($seedC['settings']) . PHP_EOL;
echo PHP_EOL;
echo "Yonetici girisi : admin@akillikariyer.com / {$adminPass}" . PHP_EOL;
echo "Demo girisi     : demo@akillikariyer.com  / {$demoPass}" . PHP_EOL;
