<?php
/**
 * Akilli Kariyer - Gizlilik Politikasi
 */

require_once __DIR__ . '/app/bootstrap.php';

$pageTitle       = 'Gizlilik Politikası — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = 'Akıllı Kariyer platformunda verilerinizin nasıl toplandığı, işlendiği ve korunduğuna dair bilgilendirme.';
$activeNav       = '';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container container-narrow">
      <span class="eyebrow"><?= icon('lock', '', 15) ?> Yasal</span>
      <h1 style="margin-bottom:6px">Gizlilik Politikası</h1>
      <p style="margin:0">Platformda verilerinizin nasıl işlendiğine dair taahhüdümüz.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="container container-narrow">
      <article class="article">
        <h2>1. Kapsam</h2>
        <p>
          Bu politika, <?= e(setting('site_name', 'Akıllı Kariyer')) ?> platformunda
          (<strong>Akıllı Kariyer</strong>) ziyaretçilerin ve üyelerin verilerinin nasıl
          toplandığı, kullanıldığı, saklandığı ve korunduğunu açıklar.
        </p>

        <h2>2. Toplanan Veriler</h2>
        <p>Yalnızca platformun hizmetini sunabilmesi için gerekli veriler toplanır:</p>
        <ul>
          <li><strong>Zorunlu veriler:</strong> Ad, soyad, e-posta adresi, şifre. Hesabınızın oluşturulması ve güvenliği için gereklidir.</li>
          <li><strong>İsteğe bağlı veriler:</strong> Profil ayrıntıları, test cevapları, favoriler. Yalnızca siz girdiğinizde işlenir.</li>
          <li><strong>Otomatik veriler:</strong> IP adresi, tarayıcı bilgisi, oturum kayıtları. Güvenlik ve hata ayıklama amacıyla toplanır.</li>
        </ul>

        <h2>3. Verilerin Kullanımı</h2>
        <ul>
          <li>Kariyer testini uygulamak ve sonuçlarını hesaplamak,</li>
          <li>Hesabınızı oluşturmak, oturum açmanızı sağlamak ve şifrenizi sıfırlamak,</li>
          <li>Favori mesleklerinizi ve paneldeki ilerlemenizi saklamak,</li>
          <li>Hizmetlerimizi güvenli kılmak, hata ayıklamak ve iyileştirmek,</li>
          <li>Yasal yükümlülüklerimizi yerine getirmek.</li>
        </ul>

        <h2>4. Verilerin Paylaşımı</h2>
        <p>
          Kişisel verileriniz satılmaz, kiralanmaz veya reklam amaçlı üçüncü taraflarla
          paylaşılmaz. Verileriniz yalnızca hizmetin işletilmesi için gerekli tedarikçilerle
          (hosting, e-posta hizmetleri) hizmet sağlama amacıyla paylaşılabilir ve bu
          tedarikçilerin de gizlilik yükümlülüğü vardır. Yasal zorunluluk hâlleri saklıdır.
        </p>

        <h2>5. Veri Güvenliği</h2>
        <ul>
          <li>Şifreler bcrypt algoritmasıyla tek yönlü şifrelenir; düz metin saklanmaz.</li>
          <li>Veritabanı erişimi PDO prepared statements ile sorgulanır; SQL enjeksiyonuna karşı korunur.</li>
          <li>Tüm form gönderimleri CSRF tokeni ile doğrulanır.</li>
          <li>Çıktılar XSS'e karşı kaçış fonksiyonları ile işlenir.</li>
          <li>Yönetici yetkileri sunucu tarafında her istekte yeniden doğrulanır.</li>
          <li>Başarısız giriş denemeleri sınırlandırılır ve IP bazlı hesap kilidi uygulanır.</li>
        </ul>

        <h2>6. Çerezler</h2>
        <p>
          Platform üç tür çerez kullanır: oturum çerezi (zorunlu), "Oturum açık kalsın" seçeneği
          kullananlar için kalıcı hatırlama çerezi ve tercih çerezi. Reklam ağı, profilleme
          veya üçüncü taraf takip çerezleri kullanılmaz.
        </p>

        <h2>7. Haklarınız</h2>
        <p>
          Verilerinize erişme, düzeltilmesini, silinmesini veya işlemeye itiraz etme haklarına
          sahipsiniz. Bu haklarınızı <strong>Ayarlar → Hesap İşlemleri</strong> bölümünden
          kullanabilir veya info@akillikariyer.com adresine yazarak talep edebilirsiniz.
        </p>

        <h2>8. Veri Güvenliği İhlali</h2>
        <p>
          Kişisel verilerinizin kanuni yollara başvurulmasını gerektirecek şekilde ihlal
          edilmesi hâlinde, 5651 sayılı Kanun'un 12. maddesi uyarınca kişisel veri sorumlusu
          olarak yasal bildirim yükümlülüğümüzü yerine getiririz.
        </p>

        <h2>9. Politikada Değişiklik</h2>
        <p>
          Bu politika zaman zaman güncellenebilir. Değişiklikler bu sayfada yayımlanır ve
          yürürlüğe girme tarihi belirtilir. Önemli değişikliklerde kullanıcılar bilgilendirilir.
        </p>

        <h2>10. İletişim</h2>
        <p>
          Gizlilik uygulamalarımızla ilgili sorularınız için
          <strong><?= e(setting('site_email', 'info@akillikariyer.com')) ?></strong> adresine
          yazabilirsiniz.
        </p>
      </article>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
