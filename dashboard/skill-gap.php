<?php
/**
 * Akilli Kariyer - Beceri Boşluk Analizi
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Models\Career;
use App\Models\Result;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$result = Result::latestForUser($userId);
$recommendations = $result ? Result::recommendations((int) $result['id']) : [];

$pageTitle = 'Beceri Boşluk Analizi — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Kendi becerilerinizi hedef mesleklerinizin gerektirdiği becerilerle karşılaştırın ve geliştirmeniz gereken alanları belirleyin.';
$activeNav = 'recommendations';

require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content">
<section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
<div class="container container-narrow">
<div class="flex between wrap gap-16" style="align-items:flex-end">
<div>
<span class="eyebrow"><?= icon('zap', '', 15) ?> Beceriler</span>
<h1 style="margin-bottom:6px">Beceri Boşluk Analizi</h1>
<p style="margin:0">Kendi becerilerinizi hedef mesleklerinizin gerektirdiği becerilerle karşılaştırın ve geliştirmeniz gereken alanları belirleyin.</p>
</div>
<a href="<?= url('dashboard/recommendations.php') ?>" class="btn btn-accent"><?= icon('arrow-right', '', 16) ?> Önerilerim</a>
</div>
</div>
</section>

<section class="section-sm">
<div class="container container-narrow">
<?php if (!$recommendations): ?>
<div class="empty-state" style="border:0;background:var(--c-bg)">
<div class="es-ico"><?= icon('compass', '', 28) ?></div>
<h3>Henüz test sonucunuz yok</h3>
<p>Kariyer testini tamamlayarak kişiselleştirilmiş bir beceri boşluk analizi oluşturabilirsiniz.</p>
<a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('clipboard-check', '', 17) ?> Testi Başlat</a>
</div>
<?php else: ?>
<form method="post" class="grid gap-24">
<div class="filter-row" style="grid-template-columns:260px 1fr">
<div class="field">
<label for="target_career_id">Hedef Meslek Seçin</span>
<select name="target_career_id" id="target_career_id" class="select">
<option value="">Seçiniz</option>
<?php foreach ($recommendations as $rec): ?>
<option value="<?= (int) $rec['career_id'] ?>"><?= e($rec['name']) ?> (%<?= number_format_tr((float) $rec['match_score']) ?>)</option>
<?php endforeach; ?>
</select>
</label>
<div class="field filter-actions">
<label>&nbsp;</label>
<button type="submit" class="btn btn-primary"><?= icon('search', '', 16) ?> Analiz Et</button>
</div>
</div>
</form>

<?php if (!empty($_GET['career_id'])): ?>
<?php
$targetCareerId = (int) $_GET['career_id'];
$targetCareer = Career::find($targetCareerId);
if ($targetCareer):
$competencies = Career::competencies($targetCareerId);
$userAbilities = [];
if ($result) {
$detail = Result::recommendations((int) $result['id']);
foreach ($detail as $rec) {
if ((int) $rec['career_id'] === $targetCareerId) {
foreach ($rec['components'] as $key => $val) {
$userAbilities[$key] = (float) $val;
}
break;
}
}
}
?>
<div class="skill-gap-section" style="margin-top:32px">
<div class="section-head left">
<h2 style="margin-bottom:8px"><?= e($targetCareer['name']) ?> Beceri Boşluğu</h2>
<p style="color:var(--c-muted);margin-bottom:0"><?= e(str_limit($targetCareer['short_description'], 100)) ?></p>
</div>
<div class="skill-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
<?php
$total = count($competencies);
$matched = 0;
$totalGap = 0;
foreach ($competencies as $comp):
$userScore = isset($userAbilities[$comp['ability_key']]) ? (float) $userAbilities[$comp['ability_key']] : 0;
$targetScore = (int) $comp['weight'] / 5;
$gap = $targetScore - $userScore;
if ($gap < 0) $gap = 0;
if ($userScore >= $targetScore) $matched++;
$totalGap += $gap;
$progress = min(100, max(0, round(($userScore / max($targetScore, 0.1)) * 100)));
$gapColor = $progress >= 80 ? 'var(--c-success)' : ($progress >= 60 ? 'var(--c-warning)' : 'var(--c-danger)');
?>
<div class="skill-card" style="border:1px solid var(--c-border);border-radius:14px;padding:18px;background:#fff">
<div class="skill-header" style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
<div style="flex:1">
<h4 style="font-size:1rem;font-weight:700;color:var(--c-primary);margin:0"><?= e($comp['competency_name']) ?></h4>
<div class="skill-cat-badge" style="font-size:.72rem;color:var(--c-muted);margin-top:4px"><?= e($comp['ability_key'] ?? '-') ?></div>
</div>
<div class="skill-score" style="text-align:right">
<div style="font-size:1.25rem;font-weight:800;color:<?= $progress >= 80 ? 'var(--c-success)' : ($progress >= 60 ? 'var(--c-warning)' : 'var(--c-danger)') ?>"><?= number_format_tr($progress) ?>%</div>
</div>
</div>
<div class="skill-bar-container" style="height:8px;background:#E6ECF5;border-radius:4px;overflow:hidden;margin-top:10px">
<div class="skill-bar-fill" style="height:100%;background:<?= $gapColor ?>;width:<?= $progress ?>%;transition:width 1s ease"></div>
</div>
<div class="skill-stats" style="display:flex;justify-content:space-between;margin-top:8px">
<span class="text-sm text-muted-c"><?= number_format_tr($userScore * 100) ?>% Senin</span>
<span class="text-sm text-muted-c"><?= number_format_tr($targetScore * 100) ?>% Hedef</span>
<span class="text-sm text-muted-c" style="color:<?= $gapColor ?>"><?= number_format_tr($gap * 100) ?>% Boşluk</span>
</div>
<div class="skill-suggestion" style="margin-top:10px;font-size:.84rem;line-height:1.5;color:var(--c-muted)">
<?php if ($progress >= 80): ?>
Bu beceride yeterli seviyedesiniz.
<?php elseif ($progress >= 60): ?>
Bu beceriyi biraz daha geliştirebilirsiniz.
<?php else: ?>
Bu beceride önemli bir geliştirme ihtiyacı vardır.
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
<div class="summary-card" style="margin-top:24px;background:var(--c-light-blue);border-radius:14px;padding:20px">
<h4 style="margin-bottom:12px"><?= icon('target', '', 18) ?> Genel Değerlendirme</h4>
<div class="grid g-3" style="gap:16px">
<div class="stat-card" style="padding:16px">
<div class="sc-value" style="font-size:1.5rem"><?= $matched ?></div>
<div class="sc-label">Beceride Uygun</div>
</div>
<div class="stat-card" style="padding:16px">
<div class="sc-value" style="font-size:1.5rem"><?= ($total - $matched) ?></div>
<div class="sc-label">Geliştirilecek</div>
</div>
<div class="stat-card" style="padding:16px">
<div class="sc-value" style="font-size:1.5rem"><?= number_format_tr(round(($matched / max(1, count($competencies))) * 100)) ?>%</div>
<div class="sc-label">Ortalama Uyum</div>
</div>
</div>
<div class="progress-bar" style="margin-top:16px"><span style="width:<?= round(($matched / max(1, count($competencies))) * 100) ?>%"></span></div>
</div>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
</div>
</section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>