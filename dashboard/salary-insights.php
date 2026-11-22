<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Core\Auth;
use App\Models\Career;

Auth::requireLogin();
$user = Auth::user();
$userId = (int) $user->id;

$panelTitle = 'Maaş Öngörüleri';
$panelSubtitle = 'Mesleklerin maaş bilgilerini karşılaştırın.';
$panelActive = 'salary';

// Sadece maaş bilgisi olan meslekleri al
$careers = Career::all();
$filteredCareers = [];
foreach ($careers as $c) {
    if (isset($c['salary_min']) && $c['salary_min'] !== null) {
        $filteredCareers[] = $c;
    }
}

// Kategori bazlı gruplama
$categories = [];
foreach ($filteredCareers as $c) {
    $cat = $c['category'] ?? 'Bilinmeyen';
    if (!isset($categories[$cat])) {
        $categories[$cat] = [];
    }
    $categories[$cat][] = $c;
}

// Kategorileri sırala
$categories = array_map(function($cat) {
    usort($cat, function($a, $b) {
        return $a['salary_min'] <=> $b['salary_min'];
    });
    return $cat;
}, $categories);

// Kategori seçimi
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : key($categories);

// Kategori bazlı filtreleme
$filteredCareers = $categories[$selectedCategory] ?? [];

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="section-sm">
    <div class="container">
        <div class="flex between wrap">
            <div>
                <h1 style="margin-bottom:6px">Maaş Öngörüleri</h1>
                <p style="margin:0">Mesleklerin ortalama maaş bilgilerini karşılaştırın.</p>
            </div>
            <div class="flex">
                <select id="category-filter" class="select" style="margin-right:10px;">
                    <?php foreach ($categories as $cat => $c): ?>
                    <option value="<?= $cat ?>" <?= $cat === $selectedCategory ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline btn-sm" style="margin-left:10px;" onclick="location.reload()">Yenile</button>
            </div>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <div class="grid g-3">
            <?php foreach ($filteredCareers as $c): ?>
            <div class="career-card">
                <div class="cc-header">
                    <div class="cc-title">
                        <h3 style="margin:0"><?= e($c['name']) ?></h3>
                        <div class="cc-badge" style="background:<?= $c['color'] ?>;color:#fff"><?= e($c['category']) ?></div>
                    </div>
                </div>
                <div class="cc-body">
                    <div class="cc-salary">
                        <span class="cc-salary-label">Min</span>
                        <span class="cc-salary-value"><?= number_format_tr($c['salary_min']) ?> ₺</span>
                    </div>
                    <div class="cc-salary">
                        <span class="cc-salary-label">Max</span>
                        <span class="cc-salary-value"><?= number_format_tr($c['salary_max']) ?> ₺</span>
                    </div>
                    <div class="cc-salary">
                        <span class="cc-salary-label">Ortalama</span>
                        <span class="cc-salary-value"><?= number_format_tr($c['salary_avg']) ?> ₺</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
    document.getElementById('category-filter').addEventListener('change', function() {
        const cat = this.value;
        window.location.href = window.location.pathname + '?category=' + cat;
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>