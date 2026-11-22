<?php
/**
 * Akilli Kariyer - Hakkimizda
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Models\Career;
use App\Models\Test;

$careerCount  = Career::totalCount();
$categoryCount = count(Career::categories());
$questionCount = Test::questionCount();
$postCount     = \App\Models\Blog::totalCount();

$pageTitle       = 'Hakkımızda — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Akıllı Kariyer, öğrencilerin ilgi alanlarını ve yeteneklerini analiz ederek kariyer yolculuklarını planlamalarına yardımcı olan bir rehber platformudur.';
$activeNav       = 'about';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="hero" style="padding:56px 0 64px">
    <div class="container">
      <div style="max-width:760px">
        <span class="eyebrow light"><?= icon('info', '', 15) ?> Hakkımızda</span>
        <h1>Kariyer Seçimini Bilimsel ve Şeffaf Bir Sürece Dönüştürüyoruz</h1>
        <p class="hero-lead">
          Akıllı Kariyer, öğrencilerin "Bu iş bana uygun mu?" sorusuna verilen cevapları
          sezgiye değil, veriye dayandırmak için geliştirildi.
        </p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container container-narrow">
      <article class="article">
        <h2>Amacımız</h2>
        <p>
          Öğrencilerin kariyer kararları çoğu zaman çevre baskısı, popüler meslekler ve yanlış
          bilgilerle şekillenir. Oysa doğru bir kariyer kararı; ilgi alanlarının, yeteneklerin ve
          çalışma alışkanlıklarının birlikte değerlendirilmesiyle mümkündür.
        </p>
        <p>
          Akıllı Kariyer, bu değerlendirmeyi sistematik bir sürece dönüştürür. Kullanıcılar
          dört farklı boyutta (ilgi alanları, yetenekler, çalışma tercihleri ve çalışma ortamı)
          sorulara yanıt verir; cevaplar, her mesleğin veritabanında tanımlı kriterleriyle
          karşılaştırılarak kişiselleştirilmiş uyum puanları üretilir.
        </p>

        <h2>Nasıl Çalışıyor?</h2>
        <h3>1. Veri tabanı</h3>
        <p>
          <?= $careerCount ?> meslek; <?= $categoryCount ?> kategori altında, her biri için görevler,
          beceriler, eğitim bilgisi, çalışma alanları, kariyer gelişim yolları ve eşleştirme
          kriterleriyle birlikte tanımlanmıştır.
        </p>
        <h3>2. Test</h3>
        <p>
          <?= $questionCount ?> soruluk test; tek seçenekli, çok seçenekli, 1-5 değerlendirme,
          kart seçimi ve senaryo tabanlı olmak üzere beş farklı soru tipi kullanır. Her soru
          belirli bir boyuta puan katkısı sağlar.
        </p>
        <h3>3. Eşleştirme algoritması</h3>
        <p>
          Her meslek için dört bileşen ayrı ayrı hesaplanır ve ağırlıklı olarak birleştirilir:
          ilgi alanı uyumu, yetenek uyumu, çalışma tercihi uyumu ve beceri uyumu. Bu ağırlıklar
          yönetim panelinden değiştirilebilir; değişiklikler tüm geçmiş sonuçlara uygulanabilir.
        </p>
        <h3>4. Sonuçlar</h3>
        <p>
          Kullanıcılar; sıralı meslek önerileri, boyut bazlı grafikler, güçlü yönler ve gelişim
          alanları ile kişisel bir değerlendirme raporu alır. Raporu PDF olarak indirebilirler.
        </p>

        <h2>Ne Değildir?</h2>
        <p>
          Bu platform bir <strong>psikolojik test</strong> veya <strong>bilimsel kişilik analizi</strong>
          değildir. Kesin bir meslek belirlemez, meslek seçme zorunluluğu oluşturmaz. Amacı,
          kariyer keşfini kolaylaştıracak bir <strong>rehber</strong> olmaktır. Kararı siz verirsiniz.
        </p>
        <p>
          Platform, öğrencilerin kariyer süreçlerini yapılandırmalarına, kendilerine dair farkındalık
          oluşturmalarına ve bilinçli kararlar vermelerine yardımcı olmak için var olmaktadır.
        </p>

        <h2>Gizlilik ve Güvenlik</h2>
        <p>
          Şifreler <strong>bcrypt</strong> algoritmasıyla tek yönlü biçimde şifrelenir ve asla
          düz metin olarak saklanmaz. Form gönderimlerinde CSRF koruması, tüm veritabanı
          sorgularında hazırlanmış ifadeler (prepared statements) kullanılır. Kişisel veriler
          yalnızca hesabınıza ait bilgilerle sınırlıdır ve üçüncü taraflarla paylaşılmaz.
          Ayrıntılar için <a href="<?= url('kvkk.php') ?>">KVKK Aydınlatma Metni</a> ve
          <a href="<?= url('privacy.php') ?>">Gizlilik Politikası</a> sayfalarımıza göz atabilirsiniz.
        </p>
      </article>
    </div>
  </section>

  <section class="section section-alt">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><?= icon('bar-chart-3', '', 15) ?> Rakamlarla</span>
        <h2>Platformda Neler Var?</h2>
      </div>
      <div class="grid g-4">
        <div class="stat-card">
          <span class="sc-icon accent"><?= icon('briefcase', '', 24) ?></span>
          <div><div class="sc-value"><?= $careerCount ?></div><div class="sc-label">Meslek</div></div>
        </div>
        <div class="stat-card">
          <span class="sc-icon"><?= icon('layers', '', 24) ?></span>
          <div><div class="sc-value"><?= $categoryCount ?></div><div class="sc-label">Kategori</div></div>
        </div>
        <div class="stat-card">
          <span class="sc-icon success"><?= icon('clipboard-list', '', 24) ?></span>
          <div><div class="sc-value"><?= $questionCount ?></div><div class="sc-label">Test Sorusu</div></div>
        </div>
        <div class="stat-card">
          <span class="sc-icon warning"><?= icon('book-open', '', 24) ?></span>
          <div><div class="sc-value"><?= $postCount ?></div><div class="sc-label">Rehber Yazısı</div></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container container-narrow text-center">
      <span class="eyebrow"><?= icon('sparkles', '', 15) ?> Başlayın</span>
      <h2 style="margin-bottom:10px">Kendi Kariyer Profilinizi Oluşturun</h2>
      <p style="max-width:560px;margin:0 auto 26px">
        Ücretsiz hesap oluşturun, kapsamlı kariyer testini tamamlayın ve size uygun meslekleri
        keşfedin. Tüm sonuçlarınız hesabınızda saklanır.
      </p>
      <div class="flex gap-12" style="justify-content:center;flex-wrap:wrap">
        <a href="<?= url('register.php') ?>" class="btn btn-accent btn-lg"><?= icon('user-plus', '', 19) ?> Ücretsiz Kayıt Ol</a>
        <a href="<?= url('career-test.php') ?>" class="btn btn-outline btn-lg"><?= icon('clipboard-check', '', 19) ?> Teste Başla</a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
