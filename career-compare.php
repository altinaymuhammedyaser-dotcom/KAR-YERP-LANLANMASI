<?php
/**
 * Akilli Kariyer - Meslek karsilastirma sayfasi
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Models\Career;

$ids = array_map('intval', array_filter(array_map('intval', $_GET['id'] ?? [])));

if (count($ids) < 2) {
    redirect(url('careers.php'));
}

$careers = [];
foreach ($ids as $id) {
    $c = Career::find($id);
    if ($c) $careers[] = $c;
}

if (count($careers) < 2) {
    redirect(url('careers.php'));
}

$pageTitle = 'Meslek Karsilastirma — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Secilen meslekleri yan yana karsilastirin ve hangisinin size daha uygun olduguna karar verin.';
$activeNav = 'careers';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
<section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
<div class="container">
<div class="flex between wrap gap-16" style="align-items:flex-end">
<div>
<span class="eyebrow"><?= icon('git-compare', '', 15) ?> Karşılaştırma</span>
<h1 style="margin-bottom:6px">Meslek Karşılaştırma</h1>
<p style="margin:0">Seçtiğiniz meslekleri yan yana karşılaştırın ve hangisinin size daha uygun olduğu hakkında daha bilinçli bir karar alın.</p>
</div>
<a href="<?= url('careers.php') ?>" class="btn btn-outline"><?= icon('arrow-left', '', 16) ?> Kataloğe Geri Dön</a>
</div>
</div>
</section>

<section class="section">
<div class="container container-narrow">
<div class="card">
<div class="card-body" style="padding:0">
<table class="data-table" style="width:100%;table-layout:fixed">
<thead>
<tr>
<th style="width:200px">Özellik</th>
<?php foreach ($careers as $c): ?>
<th style="min-width:180px"><?= e($c['name']) ?></th>
<?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php
$fields = [
    ['key' => 'category_name', 'label' => 'Kategori'],
    ['key' => 'education_level', 'label' => 'Eğitim Seviyesi', 'fn' => 'education_label'],
    ['key' => 'education_detail', 'label' => 'Eğitim Detayı'],
    ['key' => 'salary_range', 'label' => 'Tahmini Maaş'],
    ['key' => 'working_areas', 'label' => 'Çalışma Alanları'],
    ['key' => 'short_description', 'label' => 'Kısa Tanıtım'],
];
foreach ($fields as $f):
?>
<tr>
<td style="font-weight:700;color:var(--c-primary);width:200px"><?= e($f['label']) ?></td>
<?php foreach ($careers as $c): ?>
<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
<?php if ($f['fn'] === 'education_label'): ?>
<?= e(education_label($c[$f['key']])) ?>
<?php else: ?>
<?= e($c[$f['key']]) ?>
<?php endif; ?>
</td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<div class="section-head" style="margin-top:40px">
<h2>Karşılaştırma Özeti</h2>
</div>
<div class="grid g-3" style="margin-top:20px">
<?php foreach ($careers as $c): ?>
<div class="card" style="text-align:center">
<div class="card-body">
<span class="badge badge-accent" style="margin-bottom:12px"><?= e($c['category_name']) ?></span>
<h3><?= e($c['name']) ?></h3>
<div class="match-score" style="margin-top:10px">
<div class="ms-val" style="color:var(--c-accent-dark)">%</div>
<div class="ms-lab">Skor</div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>