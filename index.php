<?php
/**
 * Akilli Kariyer - Ana sayfa (landing page)
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Blog;
use App\Models\Career;
use App\Models\Test;

$pageTitle       = setting('site_name', 'Akıllı Kariyer') . ' — ' . setting('site_slogan', 'Geleceğini Keşfet');
$pageDescription = 'İlgi alanlarını ve becerilerini analiz ederek sana uygun meslekleri keşfet. 46 soruluk akıllı kariyer testi ile kişiselleştirilmiş meslek önerileri.';
$activeNav       = 'home';

$categories   = Career::categories();
$popularJobs  = Career::mostViewed(6);
if (count($popularJobs) < 6) {
    $popularJobs = Career::featured(6);
}
$questionCount = Test::questionCount();
$careerCount   = Career::totalCount();
$interestCount = count(Career::availableInterests());
$guidePosts    = Blog::posts(['limit' => 3]);

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">

<!-- ============================ HERO ============================ -->
<section class="hero">
  <div class="container">
    <div class="hero-grid">
      <div>
        <span class="eyebrow light"><?= icon('sparkles', '', 15) ?> Akıllı kariyer rehberi</span>
        <h1>Yeteneklerini Keşfet, <span class="hl">Geleceğini Şekillendir!</span></h1>
        <p class="hero-lead">
          İlgi alanlarını ve becerilerini analiz ederek sana uygun meslekleri keşfet.
          Geleceğin için ilk adımı bugün at.
        </p>
        <div class="hero-actions">
          <a href="<?= url('career-test.php') ?>" class="btn btn-accent btn-lg">
            <?= icon('clipboard-check', '', 20) ?> Kariyer Testine Başla
          </a>
          <a href="<?= url('careers.php') ?>" class="btn btn-light btn-lg">
            <?= icon('briefcase', '', 20) ?> Meslekleri Keşfet
          </a>
        </div>
        <div class="hero-trust">
          <span><?= icon('check-circle-2', '', 17) ?> Ücretsiz ve kayıt dâhil</span>
          <span><?= icon('check-circle-2', '', 17) ?> <?= $questionCount ?> soruluk kapsamlı analiz</span>
          <span><?= icon('check-circle-2', '', 17) ?> Kişiselleştirilmiş öneriler</span>
        </div>
      </div>

      <div class="hero-visual">
        <div class="hv-card">
          <h3 class="hv-title">Kariyer Profilin</h3>
          <p class="hv-sub">Test sonuçlarınızdan üretilen örnek analiz</p>
          <div class="hv-bars">
            <div class="hv-bar-row">
              <div class="hb-top"><span>Teknoloji İlgisi</span><span>92%</span></div>
              <div class="hv-bar-track"><span class="hv-bar-fill" data-bar="92"></span></div>
            </div>
            <div class="hv-bar-row">
              <div class="hb-top"><span>Analitik Düşünme</span><span>88%</span></div>
              <div class="hv-bar-track"><span class="hv-bar-fill" data-bar="88"></span></div>
            </div>
            <div class="hv-bar-row">
              <div class="hb-top"><span>Problem Çözme</span><span>84%</span></div>
              <div class="hv-bar-track"><span class="hv-bar-fill" data-bar="84"></span></div>
            </div>
            <div class="hv-bar-row">
              <div class="hb-top"><span>İletişim Becerisi</span><span>71%</span></div>
              <div class="hv-bar-track"><span class="hv-bar-fill" data-bar="71"></span></div>
            </div>
            <div class="hv-bar-row">
              <div class="hb-top"><span>Görsel Düşünme</span><span>63%</span></div>
              <div class="hv-bar-track"><span class="hv-bar-fill" data-bar="63"></span></div>
            </div>
          </div>
        </div>
        <div class="hv-float f1">
          <span class="hvfi"><?= icon('sparkles', '', 18) ?></span>
          <span><strong>En uyumlu alan</strong><span>Yazılım Mühendisliği</span></span>
        </div>
        <div class="hv-float f2">
          <span class="hvfi"><?= icon('target', '', 18) ?></span>
          <span><strong>Uyum puanı</strong><span>%94 eşleşme</span></span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ İSTATİSTİK ============================ -->
<section class="section-sm">
  <div class="container">
    <div class="grid g-4">
      <div class="stat-card">
        <span class="sc-icon"><?= icon('layers', '', 24) ?></span>
        <div>
          <div class="sc-value"><?= number_format_tr(count($categories)) ?></div>
          <div class="sc-label">Meslek Kategorisi</div>
        </div>
      </div>
      <div class="stat-card">
        <span class="sc-icon accent"><?= icon('briefcase', '', 24) ?></span>
        <div>
          <div class="sc-value"><?= number_format_tr($careerCount) ?></div>
          <div class="sc-label">Tanıtılan Meslek</div>
        </div>
      </div>
      <div class="stat-card">
        <span class="sc-icon success"><?= icon('clipboard-list', '', 24) ?></span>
        <div>
          <div class="sc-value"><?= number_format_tr($questionCount) ?></div>
          <div class="sc-label">Kariyer Testi Sorusu</div>
        </div>
      </div>
      <div class="stat-card">
        <span class="sc-icon warning"><?= icon('compass', '', 24) ?></span>
        <div>
          <div class="sc-value"><?= number_format_tr($interestCount) ?></div>
          <div class="sc-label">İlgi Alanı</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ NASIL ÇALIŞIR ============================ -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('route', '', 15) ?> Süreç</span>
      <h2>Nasıl Çalışır?</h2>
      <p>Dört basit adımda kendinizi tanıyın ve size uygun mesleklerle buluşun.</p>
    </div>

    <div class="grid g-4">
      <div class="step-card">
        <div class="step-num"><?= icon('user-search', '', 24) ?></div>
        <h3>Kendini Tanı</h3>
        <p>İlgi alanlarını, yeteneklerini ve çalışma alışkanlıklarını gözden geçir; profilini oluştur.</p>
      </div>
      <div class="step-card">
        <div class="step-num"><?= icon('clipboard-check', '', 24) ?></div>
        <h3>Kariyer Testini Tamamla</h3>
        <p><?= $questionCount ?> soruluk kapsamlı test, dört farklı boyutta seni değerlendirir.</p>
      </div>
      <div class="step-card">
        <div class="step-num"><?= icon('bar-chart-3', '', 24) ?></div>
        <h3>Sonuçlarını İncele</h3>
        <p>İlgi dağılımın, yetenek puanların ve güçlü yönlerin grafiklerle gözünün önünde.</p>
      </div>
      <div class="step-card">
        <div class="step-num"><?= icon('sparkles', '', 24) ?></div>
        <h3>Mesleklerini Keşfet</h3>
        <p>Uyum puanına göre sıralanan meslek önerilerini incele, favorilerine ekle.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================ POPÜLER MESHLEKLER ============================ -->
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('trending-up', '', 15) ?> Popüler</span>
      <h2>En Çok İncelenen Meslekler</h2>
      <p>Mezunların en çok araştırdığı ve platformumuzda öne çıkan kariyerler.</p>
    </div>

    <div class="grid g-3">
      <?php foreach ($popularJobs as $job): ?>
        <article class="career-card card card-hover">
          <div class="cc-media">
            <?= career_art($job) ?>
            <span class="cc-cat"><?= e($job['category_name']) ?></span>
            <span class="cc-icon"><?= icon($job['icon'] ?? 'briefcase', '', 21) ?></span>
          </div>
          <div class="cc-body">
            <h3><a href="<?= url('career-detail.php?slug=' . urlencode($job['slug'])) ?>" style="color:inherit"><?= e($job['name']) ?></a></h3>
            <p class="cc-desc"><?= e(str_limit($job['short_description'], 118)) ?></p>
            <div class="cc-meta">
              <span><?= icon('graduation-cap', '', 15) ?> <?= e(education_label($job['education_level'])) ?></span>
              <span><?= icon('eye', '', 15) ?> <?= number_format_tr((int) $job['view_count']) ?> görüntülenme</span>
            </div>
            <div class="cc-foot">
              <a href="<?= url('career-detail.php?slug=' . urlencode($job['slug'])) ?>" class="btn btn-primary btn-sm btn-block">
                <?= icon('arrow-right', '', 16) ?> Detayları İncele
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top:38px">
      <a href="<?= url('careers.php') ?>" class="btn btn-outline btn-lg">
        Tüm Meslekleri Gör <?= icon('arrow-right', '', 18) ?>
      </a>
    </div>
  </div>
</section>

<!-- ============================ KATEGORİLER ============================ -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('grid-2x2', '', 15) ?> Kategoriler</span>
      <h2>Meslek Kategorileri</h2>
      <p>İlgi alanına göre meslek kataloğunda gezinin ve doğru alanı bulun.</p>
    </div>

    <div class="cat-grid">
      <?php foreach ($categories as $cat): ?>
        <a href="<?= url('careers.php?category=' . urlencode($cat['slug'])) ?>" class="cat-card">
          <span class="cc-ico" style="background:<?= e($cat['color']) ?>"><?= icon($cat['icon'] ?? 'briefcase', '', 24) ?></span>
          <h4><?= e($cat['name']) ?></h4>
          <span><?= (int) $cat['career_count'] ?> meslek</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================ TEST TANITIMI ============================ -->
<section class="section">
  <div class="container">
    <div class="grid g-2" style="align-items:center;gap:52px">
      <div>
        <span class="eyebrow"><?= icon('sparkles', '', 15) ?> Akıllı eşleştirme</span>
        <h2>Kariyer Testi ile Kendine Doğru Mesleği Bul</h2>
        <p style="font-size:1.02rem">
          Test, ilgi alanları, yetenekler, çalışma tercihleri ve çalışma ortamı olmak üzere
          dört boyutta <?= $questionCount ?> soru içerir. Cevapların, veritabanındaki meslek
          kriterleriyle karşılaştırılır ve sana özel bir uyum puanı hesaplanır.
        </p>

        <div class="grid" style="gap:14px;margin:26px 0">
          <div class="flex gap-12 center" style="align-items:flex-start">
            <span class="sc-icon success" style="width:42px;height:42px;border-radius:12px"><?= icon('scale', '', 19) ?></span>
            <div>
              <strong style="display:block;color:var(--c-primary);font-size:.96rem">Ağırlıklı puanlama</strong>
              <span style="font-size:.9rem">İlgi alanı %35, yetenek %30, çalışma tercihi %20, beceri %15</span>
            </div>
          </div>
          <div class="flex gap-12 center" style="align-items:flex-start">
            <span class="sc-icon accent" style="width:42px;height:42px;border-radius:12px"><?= icon('save', '', 19) ?></span>
            <div>
              <strong style="display:block;color:var(--c-primary);font-size:.96rem">Kaldığın yerden devam</strong>
              <span style="font-size:.9rem">Cevapların otomatik kaydedilir, sayfayı yenilesen bile ilerlemezsin</span>
            </div>
          </div>
          <div class="flex gap-12 center" style="align-items:flex-start">
            <span class="sc-icon" style="width:42px;height:42px;border-radius:12px"><?= icon('file-down', '', 19) ?></span>
            <div>
              <strong style="display:block;color:var(--c-primary);font-size:.96rem">PDF sonuç raporu</strong>
              <span style="font-size:.9rem">Sonuçlarını ve grafiklerini indirerek arşivleyebilirsin</span>
            </div>
          </div>
        </div>

        <a href="<?= url('career-test.php') ?>" class="btn btn-accent btn-lg">
          <?= icon('clipboard-check', '', 20) ?> Hemen Başla
        </a>
      </div>

      <div>
        <div class="card" style="padding:26px">
          <div class="flex between center" style="margin-bottom:18px">
            <h4 style="margin:0">Örnek Sonuç Profili</h4>
            <span class="badge badge-accent"><?= icon('sparkles', '', 13) ?> Örnek</span>
          </div>
          <div class="chart-box" style="height:250px">
            <canvas data-chart='<?= e(json_encode([
                'type' => 'radar',
                'data' => [
                    'labels' => ['İlgi', 'Yetenek', 'Tercih', 'Ortam', 'Beceri'],
                    'datasets' => [[
                        'label' => 'Uyum',
                        'data' => [92, 88, 79, 85, 81],
                        'borderColor' => '#16B8B0',
                        'backgroundColor' => 'rgba(22,184,176,.18)',
                        'pointBackgroundColor' => '#16B8B0',
                        'pointRadius' => 3,
                    ]],
                ],
                'options' => [
                    'scales' => [
                        'r' => [
                            'min' => 0, 'max' => 100,
                            'grid' => ['color' => 'rgba(20,43,74,.10)'],
                            'angleLines' => ['color' => 'rgba(20,43,74,.10)'],
                            'pointLabels' => ['color' => '#748399', 'font' => ['size' => 12, 'weight' => '600']],
                            'ticks' => ['display' => false, 'stepSize' => 25],
                        ],
                    ],
                ],
            ])) ?>'></canvas>
          </div>
          <div class="divider"></div>
          <div class="flex between center">
            <div>
              <strong style="display:block;color:var(--c-primary)">Yazılım Mühendisi</strong>
              <span style="font-size:.85rem;color:var(--c-muted)">Teknoloji kategorisi</span>
            </div>
            <div class="match-score">
              <div class="ms-val">94%</div>
              <div class="ms-lab">Uyum</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================ ÖĞRENCİ REHBERİ ============================ -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('book-open', '', 15) ?> Kariyer Rehberi</span>
      <h2>Öğrenciler İçin Rehber</h2>
      <p>Meslek seçimi, üniversite ve kariyer planlaması üzerine pratik yazılar.</p>
    </div>

    <div class="grid g-3">
      <?php foreach ($guidePosts as $post): ?>
        <article class="blog-card">
          <div class="bc-media"><?= blog_art($post) ?></div>
          <div class="bc-body">
            <span class="badge" style="background:<?= e($post['category_color']) ?>22;color:<?= e($post['category_color']) ?>">
              <?= e($post['category_name']) ?>
            </span>
            <h3><a href="<?= url('blog-detail.php?slug=' . urlencode($post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
            <p><?= e(str_limit($post['excerpt'], 130)) ?></p>
            <div class="blog-meta">
              <span><?= icon('calendar', '', 14) ?> <?= format_date($post['published_at']) ?></span>
              <span><?= icon('clock', '', 14) ?> <?= (int) $post['reading_time'] ?> dk</span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top:38px">
      <a href="<?= url('career-guide.php') ?>" class="btn btn-outline btn-lg">
        Rehberi Oku <?= icon('arrow-right', '', 18) ?>
      </a>
    </div>
  </div>
</section>

<!-- ============================ SSS ============================ -->
<section class="section">
  <div class="container container-narrow">
    <div class="section-head">
      <span class="eyebrow"><?= icon('help-circle', '', 15) ?> Yardım</span>
      <h2>Sıkça Sorulan Sorular</h2>
      <p>Merak edilenler hakkında hızlı cevaplar.</p>
    </div>

    <div class="faq-item open">
      <button type="button" class="faq-q">
        Kariyer testi tam olarak ne ölçüyor?
        <?= icon('chevron-down', '', 20) ?>
      </button>
      <div class="faq-a"><div class="faq-inner">
        Test dört farklı boyutta değerlendirir: ilgi alanları, yetenekler, çalışma tercihleri ve
        çalışma ortamı. Her soru bu boyutlardan birine puan katkısı sağlar; cevapların
        veritabanındaki meslek kriterleriyle karşılaştırılarak uyum puanı hesaplanır.
      </div></div>
    </div>

    <div class="faq-item">
      <button type="button" class="faq-q">
        Testi tamamlamadan öneri alabilir miyim?
        <?= icon('chevron-down', '', 20) ?>
      </button>
      <div class="faq-a"><div class="faq-inner">
        Hayır. Öneriler yalnızca tüm sorular cevaplandıktan sonra hesaplanır. Böylece sonuç
        eksik veriye dayanmaz. Testi yarıda bırakırsan kaldığın yerden devam edebilirsin.
      </div></div>
    </div>

    <div class="faq-item">
      <button type="button" class="faq-q">
        Sonuçlar benim için kesin bir meslek belirliyor mu?
        <?= icon('chevron-down', '', 20) ?>
      </button>
      <div class="faq-a"><div class="faq-inner">
        Hayır. Sonuçlar bir rehber niteliğindedir; kesin meslek belirleme ya da kişilik testi
        değildir. Sana uygun olabilecek alanları gösterir, kararı sen verirsin.
      </div></div>
    </div>

    <div class="faq-item">
      <button type="button" class="faq-q">
        Verilerim nasıl saklanıyor?
        <?= icon('chevron-down', '', 20) ?>
      </button>
      <div class="faq-a"><div class="faq-inner">
        Şifreniz tek yönlü (bcrypt) hash olarak saklanır, asla düz metin tutulmaz.
        Kişisel verilerin yalnızca size ait profiliniz, test cevaplarınız ve aktivite kayıtlarınız için
        işlenir. Ayrıntılar için <a href="<?= url('kvkk.php') ?>">KVKK Aydınlatma Metni</a>'ni inceleyebilirsiniz.
      </div></div>
    </div>

    <div class="faq-item">
      <button type="button" class="faq-q">
        Testi tekrar yapabilir miyim?
        <?= icon('chevron-down', '', 20) ?>
      </button>
      <div class="faq-a"><div class="faq-inner">
        Evet. Hesabınızdaki "Test Sonuçlarım" sayfasından istediğiniz zaman yeni bir test
        başlatabilirsiniz. Eski sonuçlarınız saklanır ve karşılaştırabilirsiniz.
      </div></div>
    </div>
  </div>
</section>

<!-- ============================ İLETİŞİM ============================ -->
<section class="section section-alt" id="iletisim">
  <div class="container">
    <div class="grid g-2" style="gap:52px;align-items:center">
      <div>
        <span class="eyebrow"><?= icon('mail', '', 15) ?> İletişim</span>
        <h2>Soruların mı Var?</h2>
        <p>Meslekler, test, üniversite seçimi veya platform hakkındaki sorularını bize yaz.
          Ekibimiz en kısa sürede dönüş yapacak.</p>
        <ul class="contact-info" style="margin-top:26px">
          <li>
            <span class="ci-ico"><?= icon('mail', '', 19) ?></span>
            <span><strong>E-posta</strong><span><?= e(setting('site_email', 'info@akillikariyer.com')) ?></span></span>
          </li>
          <li>
            <span class="ci-ico"><?= icon('phone', '', 19) ?></span>
            <span><strong>Telefon</strong><span><?= e(setting('site_phone', '')) ?></span></span>
          </li>
          <li>
            <span class="ci-ico"><?= icon('map-pin', '', 19) ?></span>
            <span><strong>Adres</strong><span><?= e(setting('site_address', '')) ?></span></span>
          </li>
        </ul>
        <a href="<?= url('contact.php') ?>" class="btn btn-primary" style="margin-top:24px">
          <?= icon('send', '', 18) ?> İletişim Formu
        </a>
      </div>

      <div class="contact-card">
        <h3 style="margin-bottom:6px">Hızlı Mesaj</h3>
        <p style="margin-bottom:20px;font-size:.93rem">Formu doldurun, doğrudan bize ulaşın.</p>
        <form method="post" action="<?= url('contact.php') ?>" class="grid gap-16" data-guard>
          <?= csrf_field() ?>
          <div class="form-grid">
            <label class="field">
              <span>Ad Soyad <span style="color:var(--c-danger)">*</span></span>
              <input type="text" name="name" class="input" required maxlength="120" value="<?= e(old('name')) ?>">
            </label>
            <label class="field">
              <span>E-posta <span style="color:var(--c-danger)">*</span></span>
              <input type="email" name="email" class="input" required maxlength="190" value="<?= e(old('email')) ?>">
            </label>
          </div>
          <label class="field">
            <span>Konu</span>
            <input type="text" name="subject" class="input" maxlength="200" value="<?= e(old('subject')) ?>">
          </label>
          <label class="field">
            <span>Mesajınız <span style="color:var(--c-danger)">*</span></span>
            <textarea name="message" class="textarea" required maxlength="3000" rows="5"><?= e(old('message')) ?></textarea>
          </label>
          <button type="submit" class="btn btn-accent btn-block"><?= icon('send', '', 18) ?> Gönder</button>
        </form>
      </div>
    </div>
  </div>
</section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
