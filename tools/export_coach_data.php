<?php
/** AI koç için meslek verisini JSON olarak dışa aktarır. */
$p = new PDO('mysql:host=127.0.0.1;dbname=akilli_kariyer;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$rows = $p->query('
    SELECT c.name, c.slug, c.short_description, c.salary_range, c.education_level,
           c.working_areas, c.image, cat.name AS category, cat.slug AS category_slug
    FROM careers c
    LEFT JOIN career_categories cat ON cat.id = c.category_id
    WHERE c.is_active = 1
    ORDER BY cat.name, c.name
')->fetchAll();

$photoMap = [];
$photoFile = dirname(__DIR__) . '/career_photos.json';
if (is_file($photoFile)) {
    $decoded = json_decode(file_get_contents($photoFile), true);
    if (is_array($decoded)) $photoMap = $decoded;
}

$out = [];
foreach ($rows as $r) {
    $out[] = [
        'name'       => $r['name'],
        'slug'       => $r['slug'],
        'category'   => $r['category'] ?: 'Diğer',
        'categorySlug' => $r['category_slug'] ?: '',
        'summary'    => $r['short_description'] ?: '',
        'salary'     => $r['salary_range'] ?: '',
        'education'  => $r['education_level'] ?: '',
        'work'       => $r['working_areas'] ?: '',
        'photo'      => $photoMap[$r['slug']] ?? '',
    ];
}

$dir = dirname(__DIR__) . '/assets/data';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$json = json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
file_put_contents($dir . '/careers.json', $json);
echo count($out), " meslek yazildi: ", $dir . '/careers.json', PHP_EOL;
