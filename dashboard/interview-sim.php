<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Core\Auth;
use App\Core\CSRF;
use App\Models\Test;

Auth::requireLogin();
$user = Auth::user();
$userId = (int) $user->id;

$panelTitle = 'Mülakat Simülatörü';
$panelSubtitle = 'Mülakat sorularına cevap verin ve puanlamayı görün.';
$panelActive = 'interview';

// Sorular
$sources = [
    [
        'soru' => 'Mülakatı nasıl hazırladığınızı anlatın.',
        'cevap' => 'Mülakatı hazırlamak için, şirketin değerlerini ve kültürünü araştırdım. Ayrıca, kendi deneyimlerimden örnekler hazırladım ve bu örnekleri sorulara bağladım.',
        'puan' => 85,
    ],
    [
        'soru' => 'Bir takım çalışması sırasında ortaya çıkan bir çatışmayı nasıl çözdünüz?',
        'cevap' => 'Takım çalışmasında çatışma yaşadığım zaman, önce her iki tarafın görüşlerini dinledim. Daha sonra, ortak bir çözüm aradım ve bu çözümü tüm takıma açıkladım.',
        'puan' => 90,
    ],
    [
        'soru' => 'Bir proje süresince en çok zorlandığınız şey nedir?',
        'cevap' => 'En çok zorlandığım şey, projede belirli bir zamanda tamamlanması gereken bir görevin olmasıydı. Bu görevi zamanında tamamlamak için, diğer görevleri geçersiz kıldım.',
        'puan' => 70,
    ],
    [
        'soru' => 'Bir müşterinin memnuniyetini nasıl artırırsınız?',
        'cevap' => 'Müşterinin memnuniyetini artırmak için, önce onun ihtiyaçlarını anladım. Daha sonra, bu ihtiyaçlara uygun çözümler sundum.',
        'puan' => 80,
    ],
    [
        'soru' => 'Bir hata ile karşılaştığınızda nasıl davranırsınız?',
        'cevap' => 'Bir hata ile karşılaştığımda, önce hatanın nedenini belirledim. Daha sonra, bu hatanın nasıl düzeltileceğini düşündüm.',
        'puan' => 85,
    ],
    [
        'soru' => 'Bir projeyi nasıl yönetirsiniz?',
        'cevap' => 'Bir projeyi yönetmek için, önce projenin hedeflerini belirledim. Daha sonra, bu hedeflere ulaşmak için bir plan hazırladım.',
        'puan' => 90,
    ],
    [
        'soru' => 'Bir takım arkadaşınızın performansı düşük olduğunda ne yaparsınız?',
        'cevap' => 'Bir takım arkadaşının performansı düşük olduğunda, önce onun neden düşük performans gösterdiğini sordum. Daha sonra, bu sorunu çözmek için bir plan hazırladım.',
        'puan' => 80,
    ],
    [
        'soru' => 'Bir müşteriye karşı etik kurallar nelerdir?',
        'cevap' => 'Bir müşteriye karşı etik kurallar, dürüstlük, saygılık ve güvenilirlik kurallarıdır.',
        'puan' => 95,
    ],
];

// Sırayla soruları göster
$questions = $sources;
$questionCount = count($questions);
$currentIndex = isset($_SESSION['interview_sim']) ? $_SESSION['interview_sim']['current_index'] : 0;
$answers = isset($_SESSION['interview_sim']) ? $_SESSION['interview_sim']['answers'] : [];
$score = 0;

// POST işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::check();
    $index = (int) $_POST['index'];
    $answer = trim($_POST['answer']);
    $answers[$index] = $answer;
    $score += $questions[$index]['puan'];
    $currentIndex = $index + 1;
    
    // Son soru
    if ($currentIndex >= $questionCount) {
        $score = round($score / $questionCount * 100);
        $result = $score >= 85 ? 'Harika' : ($score >= 65 ? 'İyi' : 'Geliştirilmeli');
        
        // Sonuçları kaydet
        $session = Test::createSession($userId, 'interview-sim', $questionCount, $score, $result);
        
        // Sonuç sayfasına yönlendir
        redirect(url('dashboard/interview-sim.php?result=1'));
    } else {
        // Sıradaki soruya geç
        $_SESSION['interview_sim'] = [
            'current_index' => $currentIndex,
            'answers' => $answers,
        ];
        redirect(url('dashboard/interview-sim.php?index=' . $currentIndex));
    }
}

// Sonuç sayfası
if (isset($_GET['result'])) {
    $result = $score >= 85 ? 'Harika' : ($score >= 65 ? 'İyi' : 'Geliştirilmeli');
    $score = round($score / $questionCount * 100);
    
    require_once __DIR__ . '/../includes/panel-header.php';
    ?>
    <section class="section-sm">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <h2 style="margin-bottom:16px">Mülakat Sonuçlarınız</h2>
                    <div class="grid g-2" style="gap:16px">
                        <div class="stat-card">
                            <div class="sc-value" style="font-size:1.5rem"><?= $score ?>%</div>
                            <div class="sc-label">Toplam Puan</div>
                        </div>
                        <div class="stat-card">
                            <div class="sc-value" style="font-size:1.5rem"><?= $result ?></div>
                            <div class="sc-label">Seviye</div>
                        </div>
                    </div>
                    <div class="progress-bar" style="margin-top:20px"><span style="width:<?= $score ?>%"></span></div>
                    <div class="mt-16" style="font-size:0.9rem;color:var(--c-muted)">
                        <p><strong>Yorum:</strong> Mülakat sonuçlarınız <?= $result ?>. Bu seviyede olmanız, şirketin ihtiyaçlarına uygun bir aday olduğunu gösterir.</p>
                    </div>
                    <div class="mt-16">
                        <a href="<?= url('dashboard/interview-sim.php') ?>" class="btn btn-accent">Yeniden Başlat</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
    <?php exit; }
}

// Soru sayfası
require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="section-sm">
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h2 style="margin-bottom:16px">Mülakat Simülatörü</h2>
                <p style="margin-bottom:24px">Mülakat sorularına cevap verin. Her cevap puanlanacak ve sonuç sayfasında değerlendirilecek.</p>
                <form method="post" class="grid g-2">
                    <?php if ($currentIndex < $questionCount): ?>
                    <div class="field">
                        <label>Soru</label>
                        <div class="text-muted" style="margin-bottom:10px"><?= e($questions[$currentIndex]['soru']) ?></div>
                    </div>
                    <div class="field">
                        <label>Cevap</label>
                        <textarea name="answer" class="textarea" style="min-height:100px" placeholder="Cevabınızı yazın..." required></textarea>
                    </div>
                    <input type="hidden" name="index" value="<?= $currentIndex ?>">
                    <input type="hidden" name="_token" value="<?= CSRF::token() ?>">
                    <button type="submit" class="btn btn-accent">Cevapla</button>
                    <?php else: ?>
                    <div class="text-center" style="padding:32px;">
                        <h3 style="margin-bottom:16px">Mülakat Simülatörü Tamamlandı</h3>
                        <p style="margin-bottom:24px">Sonuçlarınızı görmek için lütfen sonuç sayfasına gidin.</p>
                        <a href="<?= url('dashboard/interview-sim.php?result=1') ?>" class="btn btn-accent">Sonuçlarım</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>