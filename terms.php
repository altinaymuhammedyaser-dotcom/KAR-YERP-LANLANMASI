<?php
/**
 * Akilli Kariyer - Kullanim kosullari
 */

require_once __DIR__ . '/app/bootstrap.php';

$pageTitle       = 'Kullanım Koşulları — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Akıllı Kariyer platformunu kullanmaya ilişkin şartlar ve koşullar.';
$activeNav       = '';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container container-narrow">
      <span class="eyebrow"><?= icon('file-text', '', 15) ?> Yasal</span>
      <h1 style="margin-bottom:6px">Kullanım Koşulları</h1>
      <p style="margin:0">Platformu kullanmadan önce lütfen bu koşulları okuyun.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="container container-narrow">
      <article class="article">
        <h2>1. Genel Hükümler</h2>
        <p>
          <?= e(setting('site_name', 'Akıllı Kariyer')) ?> platformuna ("Platform") erişerek
          bu koşulları kabul etmiş sayılırsınız. Platform, öğrencilerin kariyer keşif
          süreçlerini yapılandırmalarına yardımcı olmak amacıyla işletilmektedir.
        </p>

        <h2>2. Hesap Oluşturma</h2>
        <ul>
          <li>Platforma erişmek için doğru ve güncel bilgilerle bir hesap oluşturmanız gerekir.</li>
          <li>Şifrenizi gizli tutmak ve düzenli olarak değiştirmek sizin sorumluluğunuzdadır.</li>
          <li>Bir hesabı birden fazla kişi tarafından kullanmak Platform kurallarına aykırıdır.</li>
          <li>18 yaşından küçükler ebeveyn/veli onayıyla kayıt olabilir.</li>
        </ul>

        <h2>3. Kariyer Testi ve Sonuçlar Hakkında</h2>
        <div class="alert alert-info" style="margin:18px 0">
          <?= icon('info', '', 18) ?>
          <span>
            Platformdaki kariyer testi bir <strong>rehber</strong>dir; kesin meslek belirleme,
            bilimsel kişilik tanısı veya mesleksel yeterlilik belgesi değildir. Sonuçlar,
            verdiğiniz yanıtlara ve veritabanındaki meslek kriterlerine dayanan rehber niteliğindedir.
            Karar öncesi meslek detaylarını, eğitim seçeneklerini ve çalışma koşullarını mutlaka inceleyin.
          </span>
        </div>
        <ul>
          <li>Test sonuçlarına ilişkin hiçbir öneri bağlayıcı değildir.</li>
          <li>Platform üzerindeki maaş aralıkları bilgilendirme amaçlıdır ve değişkenlik gösterir.</li>
          <li>Test sonuçlarına dayanarak kariyer değişikliği yapmadan önce mesleğin güncel koşullarını araştırın.</li>
        </ul>

        <h2>4. Kullanıcı Yükümlülükleri</h2>
        <ul>
          <li>Platformu yasalara aykırı amaçlarla kullanmamak,</li>
          <li>Diğer kullanıcıların haklarını ihlal eden veya platforma zarar veren davranışlardan kaçınmak,</li>
          <li>Yanlış veya eksik bilgiyle kayıt yapmamak,</li>
          <li>Test cevaplarını kasıtlı olarak yanlış işaretlemek surecini bozmamak.</li>
        </ul>

        <h2>5. Fikri Mülkiyet Hakları</h2>
        <p>
          Platform üzerindeki metinler, görseller, arayüz tasarımı, veritabanı içeriği ve
          eşleştirme algoritması Platform'un mülkiyetindedir. Bu içeriklerin kopyalanması,
          dağıtılması veya ticari amaçla kullanılması yazılı izin olmadan yasaktır.
        </p>

        <h2>6. Üçüncü Taraf Bağlantıları</h2>
        <p>
          Platform, üçüncü taraf web sitelerine bağlantılar içerebilir. Bu bağlantılar
          üzerindeki içerikler ilgili sahiplerine aittir ve Platform bu içeriklerden sorumlu tutulamaz.
        </p>

        <h2>7. Platformun İçeriğinde Değişiklik Yapma Hakkı</h2>
        <p>
          Platform, meslek bilgilerini, test sorularını, eşleştirme kriterlerini ve içeriklerini
          güncelleme, düzeltme veya kaldırma hakkını saklı tutar. Eşleştirme algoritmasının
          ağırlıkları yönetim panelinden değiştirilebilir.
        </p>

        <h2>8. Sorumluluğun Sınırlandırılması</h2>
        <p>
          Platform, meslek önerilerinin doğruluğunu veya işe yerleştirme garantisi vermez.
          Kariyer kararlarının sonuçlarından kullanıcı sorumludur. Platform, yasal zorunluluk
          hâlleri dışında oluşan zararlardan dolaylı olarak sorumlu tutulamaz.
        </p>

        <h2>9. Hesap Askıya Alma ve Silme</h2>
        <p>
          Platform, kuralları ihlal eden hesapları askıya alabilir veya sonlandırabilir.
          Kullanıcılar, <strong>Ayarlar → Hesap İşlemleri</strong> bölümünden hesaplarını
          kalıcı olarak silebilir.
        </p>

        <h2>10. Değişiklikler</h2>
        <p>
          Bu koşullar önceden bildirilmeksizin güncellenebilir. Güncel sürüm bu sayfada
          yayımlanır. Platformu kullanmaya devam etmeniz, güncel koşulları kabul ettiğiniz
          anlamına gelir.
        </p>

        <h2>11. İletişim</h2>
        <p>
          Bu koşullarla ilgili sorularınız için
          <strong><?= e(setting('site_email', 'info@akillikariyer.com')) ?></strong> adresine
          yazabilirsiniz.
        </p>
      </article>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
