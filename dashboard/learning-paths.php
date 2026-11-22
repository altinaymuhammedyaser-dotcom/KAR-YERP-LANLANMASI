<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Core\Auth;

Auth::requireLogin();
$user = Auth::user();
$userId = (int) $user->id;

$panelTitle = 'Öğrenme Yolları';
$panelSubtitle = 'Kendi öğrenme yolunu seçin ve ilerleyin.';
$panelActive = 'learning';

// Öğrenme yolları (sabit veri)
$learningPaths = [
    [
        'title' => 'Yazılım Geliştirici Yolu',
        'description' => 'Yazılım geliştirme konusunda uzmanlaşmak için gerekli becerileri öğrenin.',
        'steps' => [
            ['title' => 'HTML & CSS', 'duration' => '2 hafta', 'description' => 'Temel web geliştirme dillerini öğrenin.'],
            ['title' => 'JavaScript', 'duration' => '4 hafta', 'description' => 'İnteraktif web uygulamaları geliştirmek için JavaScript öğrenin.'],
            ['title' => 'React', 'duration' => '6 hafta', 'description' => 'Modern bir JavaScript kütüphanesiyle uygulamalar geliştirin.'],
            ['title' => 'Node.js', 'duration' => '4 hafta', 'description' => 'Sunucu tarafında JavaScript kullanarak uygulamalar geliştirin.'],
            ['title' => 'Docker', 'duration' => '2 hafta', 'description' => 'Uygulamaları containerize edin.'],
            ['title' => 'Veritabanı', 'duration' => '4 hafta', 'description' => 'Veritabanı yönetimi öğrenin.'],
        ],
        'color' => '#142B4A',
        'icon' => 'code',
    ],
    [
        'title' => 'Veri Bilimci Yolu',
        'description' => 'Veri analizi ve makine öğrenmesi konularında uzmanlaşmak için gerekli becerileri öğrenin.',
        'steps' => [
            ['title' => 'Python', 'duration' => '4 hafta', 'description' => 'Veri bilimi için popüler bir dil öğrenin.'],
            ['title' => 'Pandas', 'duration' => '2 hafta', 'description' => 'Veri analizi için Python kütüphanesi öğrenin.'],
            ['title' => 'NumPy', 'duration' => '2 hafta', 'description' => 'Matematiksel işlemler için Python kütüphanesi öğrenin.'],
            ['title' => 'Scikit-learn', 'duration' => '4 hafta', 'description' => 'Makine öğrenmesi için Python kütüphanesi öğrenin.'],
            ['title' => 'SQL', 'duration' => '2 hafta', 'description' => 'Veritabanı sorgulama öğrenin.'],
            ['title' => 'Tableau', 'duration' => '2 hafta', 'description' => 'Veri görselleştirme için araç öğrenin.'],
        ],
        'color' => '#1C466D',
        'icon' => 'database',
    ],
    [
        'title' => 'Tasarımcı Yolu',
        'description' => 'Görsel tasarım ve kullanıcı deneyimi konularında uzmanlaşmak için gerekli becerileri öğrenin.',
        'steps' => [
            ['title' => 'Figma', 'duration' => '3 hafta', 'description' => 'Görsel tasarım için araç öğrenin.'],
            ['title' => 'Adobe XD', 'duration' => '2 hafta', 'description' => 'Görsel tasarım için araç öğrenin.'],
            ['title' => 'Photoshop', 'duration' => '2 hafta', 'description' => 'Görsel düzenleme için araç öğrenin.'],
            ['title' => 'UI/UX', 'duration' => '4 hafta', 'description' => 'Kullanıcı deneyimi için tasarım öğrenin.'],
            ['title' => 'Prototyping', 'duration' => '2 hafta', 'description' => 'Uygulama prototipleri geliştirin.'],
        ],
        'color' => '#16B8B0',
        'icon' => 'paint-brush',
    ],
    [
        'title' => 'İşletme Yöneticisi Yolu',
        'description' => 'İşletme yönetimi ve stratejik planlama konularında uzmanlaşmak için gerekli becerileri öğrenin.',
        'steps' => [
            ['title' => 'İşletme Yönetimi', 'duration' => '4 hafta', 'description' => 'İşletme yönetimi konularını öğrenin.'],
            ['title' => 'Stratejik Planlama', 'duration' => '2 hafta', 'description' => 'İşletme stratejileri öğrenin.'],
            ['title' => 'Finans', 'duration' => '2 hafta', 'description' => 'İşletme finansmanını öğrenin.'],
            ['title' => 'İnsan Kaynakları', 'duration' => '2 hafta', 'description' => 'İnsan kaynakları yönetimi öğrenin.'],
            ['title' => 'İletişim Becerileri', 'duration' => '4 hafta', 'description' => 'İşletme iletişim becerilerini geliştirin.'],
            ['title' => 'Proje Yönetimi', 'duration' => '2 hafta', 'description' => 'Proje yönetimini öğrenin.'],
        ],
        'color' => '#142B4A',
        'icon' => 'briefcase',
    ],
];

// Öğrenme yollarını session'da tut
$session = isset($_SESSION['learning_paths']) ? $_SESSION['learning_paths'] : [];

// POST işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pathId = (int) $_POST['path_id'];
    if (isset($learningPaths[$pathId])) {
        $session[$pathId] = $learningPaths[$pathId];
        $_SESSION['learning_paths'] = $session;
        // Yol başlat
        $path = $learningPaths[$pathId];
        $session['current_path'] = $pathId;
        $session['current_step'] = 0;
        $_SESSION['learning_paths'] = $session;
        
        // Yönlendirme
        redirect(url('dashboard/learning-paths.php?path=' . $pathId));
    }
}

// Yol seçimi
$selectedPathId = isset($_GET['path']) ? (int) $_GET['path'] : null;
$selectedPath = $selectedPathId !== null ? $learningPaths[$selectedPathId] : null;

// Sıradaki adım
$nextStep = null;
if ($selectedPath && isset($session[$selectedPathId])) {
    $currentStep = $session[$selectedPathId]['current_step'] ?? 0;
    if ($currentStep < count($selectedPath['steps'])) {
        $nextStep = $selectedPath['steps'][$currentStep];
    }
}

// Sıradaki adım
$nextStep = null;
if ($selectedPath && isset($session[$selectedPathId])) {
    $currentStep = $session[$selectedPathId]['current_step'] ?? 0;
    if ($currentStep < count($selectedPath['steps'])) {
        $nextStep = $selectedPath['steps'][$currentStep];
    }
}

// Sıradaki adım
$nextStep = null;
if ($selectedPath && isset($session[$selectedPathId])) {
    $currentStep = $session[$selectedPathId]['current_step'] ?? 0;
    if ($currentStep < count($selectedPath['steps'])) {
        $nextStep = $selectedPath['steps'][$currentStep];
    }
}

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="section-sm">
    <div class="container">
        <div class="flex between wrap">
            <div>
                <h1 style="margin-bottom:6px">Öğrenme Yolları</h1>
                <p style="margin:0">Kendi öğrenme yolunu seçin ve ilerleyin.</p>
            </div>
            <div class="flex">
                <a href="<?= url('dashboard/learning-paths.php') ?>" class="btn btn-accent">Yeni Yol Seç</a>
            </div>
        </div>
    </div>
</section>

<section class="section-sm">
    <div class="container">
        <?php if ($selectedPath): ?>
        <div class="card">
            <div class="card-body">
                <h2 style="margin-bottom:16px"><?= e($selectedPath['title']) ?></h2>
                <p style="margin-bottom:16px"><?= e($selectedPath['description']) ?></p>
                <div class="grid g-2" style="gap:16px">
                    <?php foreach ($selectedPath['steps'] as $step): ?>
                    <div class="step-card">
                        <div class="sc-value" style="font-size:1.5rem"><?= $step['duration'] ?></div>
                        <div class="sc-label"><?= e($step['title']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($nextStep): ?>
                <div class="mt-16">
                    <div class="text-center">
                        <h3 style="margin-bottom:16px">Sıradaki Adım</h3>
                        <p style="margin-bottom:16px"><?= e($nextStep['title']) ?></p>
                        <p style="margin-bottom:16px"><?= e($nextStep['description']) ?></p>
                        <button type="button" class="btn btn-accent">Devam Et</button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center">
            <h2 style="margin-bottom:16px">Öğrenme Yolları</h2>
            <p style="margin-bottom:24px">Kendi öğrenme yolunu seçin.</p>
            <div class="grid g-3">
                <?php foreach ($learningPaths as $path): ?>
                <div class="learning-path">
                    <div class="lp-header" style="background:<?= $path['color'] ?>">
                        <h3 style="margin:0"><?= e($path['title']) ?></h3>
                        <p style="margin:0"><?= e($path['description']) ?></p>
                    </div>
                    <div class="lp-body">
                        <div class="lp-steps">
                            <?php foreach ($path['steps'] as $step): ?>
                            <div class="lp-step">
                                <div class="lp-step-icon" style="background:<?= $path['color'] ?>;color:#fff"><?= e($step['title']) ?></div>
                                <div class="lp-step-text">
                                    <h4 style="margin:0"><?= e($step['title']) ?></h4>
                                    <p style="margin:0"><?= e($step['description']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="lp-actions">
                            <button type="button" class="btn btn-accent" onclick="window.location.href='<?= url('dashboard/learning-paths.php?path=' . $path['id']) ?>'">Başlat</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>