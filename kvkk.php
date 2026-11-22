<?php
/**
 * Akilli Kariyer - KVKK Aydinlatma Metni
 */

require_once __DIR__ . '/app/bootstrap.php';

$pageTitle       = 'KVKK Aydınlatma Metni — ' . setting('site_name', 'Akıllı Kariyer');
$pageDescription = '6698 sayılı KVKK kapsamında kişisel verilerinizin işlenmesi hakkında bilgilendirme metni.';
$activeNav       = '';

require_once __DIR__ . '/includes/header.php';
?>

<main id="main-content">
  <section class="section-sm" style="background:var(--c-white);border-bottom:1px solid var(--c-border)">
    <div class="container container-narrow">
      <span class="eyebrow"><?= icon('shield-check', '', 15) ?> Yasal</span>
      <h1 style="margin-bottom:6px">KVKK Aydınlatma Metni</h1>
      <p style="margin:0">6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında hazırlanmıştır.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="container container-narrow">
      <article class="article">
        <p>
          <?= e(setting('site_name', 'Akıllı Kariyer')) ?> platformu olarak, 6698 sayılı
          Kişisel Verilerin Korunması Kanunu ("KVKK") ve ilgili mevzuat uyarınca veri sorumlusu
          sıfatıyla aşağıdaki bilgilendirmeyi yapmaktayız.
        </p>

        <h2>1. Veri Sorumlusu</h2>
        <p>
          Veri sorumlusu, <?= e(setting('site_name', 'Akıllı Kariyer')) ?> platformunun işletmecisidir.
          İletişim bilgileri: <?= e(setting('site_email', '')) ?>, <?= e(setting('site_phone', '')) ?>,
          <?= e(setting('site_address', '')) ?>.
        </p>

        <h2>2. İşlenen Kişisel Veriler</h2>
        <p>Platform üzerinde aşağıdaki kişisel veriler işlenmektedir:</p>
        <ul>
          <li><strong>Hesap bilgileri:</strong> Ad, soyad, e-posta adresi, şifre (şifrelenmiş biçimde), eğitim seviyesi.</li>
          <li><strong>Profil bilgileri:</strong> Telefon numarası, şehir, okul adı, bölüm, sınıf seviyesi, kullanıcı tanıtımı ve tercih edilen çalışma biçimi.</li>
          <li><strong>Test verileri:</strong> Kariyer testi sırasında verilen yanıtlar, hesaplanan boyut puanları, oluşturulan meslek önerileri.</li>
          <li><strong>Etkileşim verileri:</strong> Favoriye eklenen meslekler, incelenen meslekler, sistem içi aktivite geçmişi.</li>
          <li><strong>Teknik veriler:</strong> IP adresi, oturum kayıtları, giriş deneme kayıtları ve güvenlik denetim kayıtları.</li>
        </ul>

        <h2>3. Kişisel Verilerin İşlenme Amaçları</h2>
        <ul>
          <li>Hesap oluşturma, oturum açma ve kullanıcı yönetimi işlemlerinin yürütülmesi,</li>
          <li>Kariyer testinin uygulanması, kişiselleştirilmiş meslek önerilerinin üretilmesi ve saklanması,</li>
          <li>Favori mesleklerin takibi ve panelde gösterimi,</li>
          <li>Platformun güvenliğinin sağlanması, brute force saldırılarının engellenmesi ve kötüye kullanımın tespiti,</li>
          <li>Hizmet kalitesinin ölçülmesi ve iyileştirilmesi amacıyla istatistiksel analizlerin yapılması,</li>
          <li>İletişim formu aracılığıyla ulaşan taleplerin yanıtlanması.</li>
        </ul>

        <h2>4. Hukuki Sebep</h2>
        <p>
          Kişisel verileriniz; KVKK 5. maddesinde yer alan "bir sözleşmenin kurulması veya
          ifasıyla doğrudan doğruya ilgili olması", "hukuki yükümlülüğün yerine getirilmesi",
          "meşru menfaat" ve "açık rıza" hukuki sebeplerine dayanılarak işlenmektedir.
        </p>

        <h2>5. Aktarım</h2>
        <p>
          Kişisel verileriniz; yasal zorunluluk hâlleri dışında üçüncü kişilerle paylaşılmaz,
          satılmaz veya reklam amacıyla kullanılmaz. Verileriniz, platformun barındığı sunucu
          ve veritabanı altyapısında saklanır. Yasal zorunluluk hâllerinde yetkili kamu
          kurumlarıyla paylaşım yapılabilir.
        </p>

        <h2>6. Saklama Süresi</h2>
        <p>
          Hesabınız aktif olduğu sürece verileriniz saklanır. Hesabınızı silmeniz hâlinde;
          test sonuçları, favoriler ve aktivite kayıtları dahil olmak üzere hesabınıza ait
          tüm veriler kalıcı olarak silinir. Güvenlik kayıtları (giriş denemeleri, denetim
          kayıtları) yasal saklama süreleri boyunca tutulur.
        </p>

        <h2>7. Güvenlik</h2>
        <p>
          Şifreler <strong>bcrypt</strong> algoritması ile tek yönlü biçimde şifrelenerek saklanır;
          hiçbir yerde düz metin olarak tutulmaz. Tüm veritabanı sorguları hazırlanmış ifadelerle
          (prepared statements) çalıştırılır. Form gönderimleri CSRF token koruması ile
          doğrulanır. HTTPS kullanımı önerilir.
        </p>

        <h2>8. Çerezler</h2>
        <p>
          Platform; oturum yönetimi için zorunlu bir çerez, "Oturum açık kalsın" seçeneği
          tercih edildiğinde ise kalıcı bir hatırlama çerezi kullanır. Reklam veya takip
          çerezi kullanılmaz.
        </p>

        <h2>9. Haklarınız</h2>
        <p>KVKK 11. madde kapsamında; verilerinizin işlenip işlenmediğini öğrenme, bilgi talep etme, düzeltilmesini veya silinmesini isteme ve işlemeye itiraz etme haklarına sahipsiniz. Bu haklarınızı kullanmak için info@akillikariyer.com adresine yazabilirsiniz; talepler en geç 30 gün içinde sonuçlandırılır.</p>

        <h2>10. Açık Rıza ve Geri Alma</h2>
        <p>
          Kanuni yükümlülük veya sözleşmenin ifası için zorunlu olmayan durumlarda açık rıza
          dayalı işleme yapılır. Rızanızı dilediğiniz zaman geri çekebilirsiniz; geri çekme
          işlemi, rıza verildiği tarihten itibaren geçerlidir.
        </p>

        <h2>11. Başvuru ve İletişim</h2>
        <p>
          KVKK 13. madde uyarınca, kanunun belirlediği usullerle başvuruda bulunmak ve
          taleplerinizle ilgili bilgi talep etmek hakkına sahipsiniz. Başvurularınızı
          <?= e(setting('site_email', '')) ?> adresine iletebilirsiniz.
        </p>
      </article>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
