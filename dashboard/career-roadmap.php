<?php
/**
 * Akilli Kariyer - Kariyer Yolu Haritası
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Models\Career;
use App\Models\Result;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$result = Result::latestForUser($userId);
$recommendations = $result ? Result::recommendations((int) $result['id']) : [];

$pageTitle = 'Kariyer Yolu Haritası — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Kariyer yol haritanızı oluşturun ve hedeflerinize doğru adım atın.';
$activeNav = 'recommendations';

require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content">
<section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
<div class="container container-narrow">
<div class="flex between wrap gap-16" style="align-items:flex-end">
<div>
<span class="eyebrow"><?= icon('map', '', 15) ?> Yol Haritası</span>
<h1 style="margin-bottom:6px">Kariyer Yol Haritanızı Oluşturun</h1>
<p style="margin:0">Hedeflediğiniz mesleğe ulaşmak için gerekli adımları planlayın ve ilerlemenizi takip edin.</p>
</div>
<a href="<?= url('dashboard/recommendations.php') ?>" class="btn btn-accent"><?= icon('arrow-right', '', 16) ?> Önerilerim</a>
</div>
</div>
</section>

<section class="section-sm">
<div class="container container-narrow">
<div class="card">
<div class="card-body">
<h2 style="margin-bottom:8px">Kariyer Yol Haritanız</h2>
<p style="color:var(--c-muted);margin-bottom:24px">Aşağıda size özel bir kariyer yol haritası hazırlanmıştır. Bu yol haritası, test sonuçlarınıza ve seçtiğiniz mesleklere göre düzenlenmiştir.</p>

<?php if (!$recommendations): ?>
<div class="empty-state" style="border:0;background:var(--c-bg)">
<div class="es-ico"><?= icon('compass', '', 28) ?></div>
<h3>Henüz test sonucunuz yok</h3>
<p>Kariyer testini tamamlayarak kişiselleştirilmiş bir yol haritası oluşturabilirsiniz.</p>
<a href="<?= url('career-test.php') ?>" class="btn btn-accent"><?= icon('clipboard-check', '', 17) ?> Testi Başlat</a>
</div>
<?php else: ?>
<div class="roadmap-container" style="position:relative;padding:40px 0">
<?php
$topCareers = array_slice($recommendations, 0, 3);
$total = count($topCareers);
foreach ($topCareers as $index => $rec):
$isLast = ($index === count($topCareers) - 1);
$isFirst = ($index === 0);
?>
<div class="roadmap-step" style="position:relative;margin-bottom:60px">
<div class="roadmap-dot" style="position:absolute;left:0;top:60px;width:16px;height:16px;border-radius:50%;background:var(--c-primary);border:3px solid var(--c-bg);z-index:2"></div>
<div class="roadmap-line" style="position:absolute;left:8px;top:22px;width:2px;height:calc(100% - 80px);background:var(--c-border);z-index:1"></div>
<div class="roadmap-content" style="padding-left:40px">
<div class="roadmap-header" style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
<h4 style="font-size:1.15rem;color:var(--c-primary);margin:0"><?= e($rec['name']) ?></h4>
<span class="badge badge-success"><?= icon('target', '', 15) ?> Adım <?= $index + 1 ?></span>
</div>
<div class="roadmap-meta" style="display:flex;gap:16px;margin-bottom:12px">
<span class="badge badge-muted"><?= e($c['category_name']) ?></span>
<span class="badge badge-muted"><?= e(education_label($rec['education_level'])) ?></span>
<span class="badge badge-accent">%<?= number_format_tr((float) $rec['match_score']) ?> Uyum</span>
</div>
<p style="color:var(--c-muted);font-size:.93rem;line-height:1.6;margin-bottom:16px"><?= e(str_limit($rec['short_description'], 120)) ?></p>

<div class="roadmap-steps" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px">
<?php
$steps = [
'1' => 'Temel becerileri geliştirin',
'2' => 'Sertifika alabilirsiniz',
'3' => 'Staj yapabilirsiniz',
'4' => 'İşe başvurabilirsiniz',
];
$stepIndex = 0;
foreach ($steps as $stepKey => $stepLabel): 
$isDone = ($stepIndex === 0);
?>
<div class="step-item" style="display:flex;align-items:center;gap:10px;padding:12px;border:1px solid var(--c-border);border-radius:10px;background:#fff">
<div class="step-dot" style="width:24px;height:24px;border-radius:50%;background:<?= $isDone ? 'var(--c-accent)' : 'var(--c-border)' ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0"><?= $stepIndex + 1 ?></div>
<span style="font-size:.9rem;color:<?= $isDone ? 'var(--c-text)' : 'var(--c-muted)' ?>"><?= e($stepLabel) ?></span>
</div>
<?php $stepIndex++; endforeach; ?>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</section>

<section class="section section-alt">
<div class="container container-narrow">
<div class="section-head">
<span class="eyebrow"><?= icon('trending-up', '', 15) ?> Hedefleriniz</span>
<h2>Kariyer Hedeflerinizi Belirleyin</h2>
<p style="margin-bottom:24px">Kendi hedeflerinizi belirleyin ve ilerlemenizi takip edin.</p>
</div>
<div class="grid g-3">
<div class="card">
<div class="card-body">
<h3 style="margin-bottom:16px"><?= icon('briefcase', '', 19) ?> Meslek Hedefi</h3>
<form method="post" class="grid gap-12">
<?= csrf_field() ?>
<label class="field">
<span>Hedef Meslek</span>
<select name="target_career_id" class="select">
<option value="">Seçiniz</option>
<?php foreach ($recommendations as $rec): ?>
<option value="<?= (int) $rec['career_id'] ?>"><?= e($rec['name']) ?> (%<?= number_format_tr((float) $rec['match_score']) ?>)</option>
<?php endforeach; ?>
</select>
</label>
<label class="field">
<span>Hedef Tarih</span>
<input type="date" name="target_date" class="input">
</label>
<label class="field">
<span>Notlar</span>
<textarea name="target_note" class="textarea" style="min-height:80px" placeholder="Notlarınızı yazabilirsiniz..."></textarea>
</label>
<button type="submit" class="btn btn-accent btn-block"><?= icon('flag', '', 17) ?> Hedefi Kaydet</button>
</form>
</div>
</div>

<div class="card">
<div class="card-body">
<h3 style="margin-bottom:16px"><?= icon('trending-up', '', 19) ?> İlerleme Durumu</h3>
<div class="grid g-2" style="gap:16px">
<div class="stat-card">
<div class="sc-value">0%</div>
<div class="sc-label">Tamamlanma</div>
</div>
<div class="stat-card">
<div class="sc-value">0</div>
<div class="sc-label">Gün Kaldı</div>
</div>
</div>
<div class="progress-bar" style="margin-top:20px"><span style="width:0%"></span></div>
</div>
</div>

<div class="card">
<div class="card-body">
<h3 style="margin-bottom:16px"><?= icon('activity', '', 19) ?> Son Aktiviteler</h3>
<div class="grid g-3" style="gap:12px">
<div class="activity-item">
<span class="ai-ico success"><?= icon('check-circle-2', '', 17) ?></span>
<div class="flex-1">
<div class="ai-txt">Kariyer testi tamamlandı</div>
<div class="ai-time">Bugün</div>
</div>
</div>
<div class="activity-item">
<span class="ai-ico info"><?= icon('heart', '', 17) ?></span>
<div class="flex-1">
<div class="ai-txt">Favorilere eklendi</div>
<div class="ai-time">Bugün</div>
</div>
</div>
<div class="activity-item">
<span class="ai-ico danger"><?= icon('alert-circle', '', 17) ?></span>
<div class="flex-1">
<div class="ai-txt">Test sonucu alındı</div>
<div class="ai-time">Bugün</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>