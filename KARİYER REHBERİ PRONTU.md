# PROJE ADI: AKILLI MESLEK VE KARİYER REHBERİ
## PROFESSIONAL CAREER DISCOVERY & GUIDANCE PLATFORM

### 1. PROJENİN AMACI VE VİZYONU

Sen profesyonel bir Full Stack yazılım geliştiricisi, PHP uzmanı, MySQL veritabanı mimarı, algoritma geliştiricisi ve üst düzey UI/UX tasarımcısısın.

Görevin, öğrencilerin kendi ilgi alanlarını, yeteneklerini, becerilerini, çalışma alışkanlıklarını ve kişisel tercihlerini keşfetmelerini sağlayan kapsamlı bir Akıllı Meslek ve Kariyer Rehberi platformu geliştirmektir.

Bu uygulama, öğrencilerin kariyer tercihlerini daha bilinçli yapmalarına yardımcı olmalıdır.

Platform şu temel özelliklere sahip olmalıdır:

- Kullanıcı kayıt ve giriş sistemi.
- Kapsamlı kariyer testi.
- İlgi alanı ve yetenek analizi.
- Akıllı meslek eşleştirme algoritması.
- Kişiye özel meslek önerileri.
- Ayrıntılı meslek tanıtım sayfaları.
- Eğitim ve kariyer rehberi.
- Kullanıcıya özel kontrol paneli.
- Kariyer testi geçmişi.
- Favori meslekler.
- Gelişmiş yönetici paneli.
- Gerçek MySQL veritabanı bağlantısı.

ÖNEMLİ:

Bu proje sadece görsel bir HTML şablonu olmayacak. Bütün sayfalar birbirine bağlı, veritabanıyla çalışan, işlevsel ve gerçek bir web uygulaması olacak.

Hiçbir buton göstermelik olmayacak. Hiçbir sayfa boş bırakılmayacak. Her özellik gerçekten çalışacak.

---

# 2. TEKNOLOJİ MİMARİSİ

Backend:
- PHP 8.2+
- MySQL 8.0 veya MariaDB
- PDO
- PHP Session
- PHP password_hash()
- PHP password_verify()

Frontend:
- HTML5
- CSS3
- JavaScript ES6+
- Bootstrap 5.3
- AJAX / Fetch API
- Chart.js
- Lucide Icons

Diğer:
- Google Fonts
- Responsive Grid ve Flexbox
- JSON veri alışverişi
- Sunucu taraflı form doğrulama

Mimari kurallar:
- MVC mantığına uygun katmanlı yapı kullan.
- Veritabanı bağlantısını merkezi yönet.
- Tekrarlanan kodları ayrı bileşenlere ayır.
- Header, footer, sidebar ve navbar bileşenlerini ortak kullan.
- PHP dosyalarında HTML, SQL ve iş mantığını mümkün olduğunca ayır.
- Veritabanı sorgularında PDO prepared statements kullan.
- Kodlar okunabilir ve geliştirilebilir olsun.

---

# 3. PROFESYONEL TASARIM SİSTEMİ

Platformun görsel kalitesi çok önemli.

Sıradan, eski görünümlü, basit Bootstrap şablonu kullanma.

Gerçek bir profesyonel eğitim ve kariyer platformunun tasarım kalitesini hedefle.

## Renk paleti

Ana renk:
#142B4A

İkincil renk:
#1C466D

Turkuaz:
#16B8B0

Açık mavi:
#EAF4FA

Arka plan:
#F6F8FC

Beyaz:
#FFFFFF

Ana metin:
#172B42

İkincil metin:
#748399

Başarı:
#20A779

Uyarı:
#F0A43A

## Görsel tasarım özellikleri

- Premium SaaS platform görünümü.
- Büyük ve dengeli tipografi.
- Modern kartlar.
- Yumuşak gölgeler.
- İnce kenarlıklar.
- Dengeli boşluklar.
- Profesyonel ikonlar.
- Görsel hiyerarşi.
- Akıcı hover efektleri.
- Modern geçiş animasyonları.
- Gradient renk geçişleri yalnızca gerekli yerlerde.
- Kaliteli illüstrasyon ve görseller.

Bütün sayfalarda aynı tasarım sistemi kullanılmalı.

Mobil tasarım masaüstünün küçültülmüş hâli olmamalı; mobil için özel düzenlenmeli.

## Tasarım kalitesi

- 360px mobil ekranlardan büyük masaüstü ekranlara kadar uyum.
- Mobil navigasyon menüsü.
- Mobilde düzgün çalışan formlar.
- Dokunmatik cihazlara uygun butonlar.
- Kartların responsive yerleşimi.
- Yatay taşma olmaması.
- Okunaklı yazı boyutları.

---

# 4. ANA SAYFA – LANDING PAGE

Ana sayfa ilk açıldığında kullanıcıyı etkileyen profesyonel bir görünüm sunmalı.

## A. Navigasyon

Sol tarafta platform logosu:

AKILLI KARİYER

Alt slogan:
Geleceğini Keşfet

Menü:
- Ana Sayfa
- Kariyer Testi
- Meslekleri Keşfet
- İlgi Alanları
- Kariyer Rehberi
- Hakkımızda

Sağ tarafta:
- Giriş Yap
- Ücretsiz Kayıt Ol

Giriş yapan kullanıcılarda giriş/kayıt butonları yerine profil menüsü göster.

## B. Hero alanı

Büyük başlık:

"Yeteneklerini Keşfet, Geleceğini Şekillendir!"

Açıklama:

"İlgi alanlarını ve becerilerini analiz ederek sana uygun meslekleri keşfet. Geleceğin için ilk adımı bugün at."

Butonlar:
- Kariyer Testine Başla
- Meslekleri Keşfet

Hero alanının sağında profesyonel bir kariyer ve eğitim illüstrasyonu kullan.

Arka planda hafif geometrik şekiller ve yumuşak gradient geçişler olsun.

## C. İstatistik bölümü

Örnek platform istatistikleri:
- Meslek kategorileri.
- Tanıtılan meslekler.
- Kariyer testi soruları.
- Keşfedilebilecek ilgi alanları.

Gerçek veriler veritabanından gelsin. Gerçek olmayan kullanıcı sayıları veya başarı oranları gösterme.

## D. Nasıl çalışır?

Dört adımlı bir bölüm oluştur:

1. Kendini Tanı
2. Kariyer Testini Tamamla
3. Sonuçlarını İncele
4. Sana Uygun Meslekleri Keşfet

Her adımda ayrı ikon ve kısa açıklama bulunsun.

## E. Popüler meslekler

Kartlarla meslekleri tanıt.

Örnek:
- Yazılım Mühendisi
- Mimar
- Psikolog
- Doktor
- Öğretmen
- Grafik Tasarımcı

Her kartta:
- Görsel.
- Meslek ismi.
- Kategori.
- Kısa açıklama.
- Detayları İncele butonu.

## F. Meslek kategorileri

- Teknoloji
- Sağlık
- Eğitim
- Mühendislik
- Sanat ve Tasarım
- Hukuk
- İşletme
- Bilim
- İletişim
- Spor

## G. Diğer bölümler

- Kariyer testi tanıtımı.
- Öğrenciler için rehber.
- Sıkça sorulan sorular.
- İletişim alanı.
- Profesyonel footer.

---

# 5. KAYIT, GİRİŞ VE KULLANICI YÖNETİMİ

Gerçek ve güvenli üyelik sistemi oluştur.

## Kayıt ekranı

Alanlar:
- Ad
- Soyad
- E-posta
- Şifre
- Şifre tekrarı
- Eğitim seviyesi
- KVKK ve gizlilik bilgilendirmesi onayı

Eğitim seviyesi seçenekleri:
- Ortaokul
- Lise
- Üniversite
- Diğer

Özellikler:
- E-posta kontrolü.
- Şifre doğrulama.
- Güvenli şifre saklama.
- Form doğrulaması.
- Hata ve başarı mesajları.
- Kayıttan sonra giriş ekranına yönlendirme.

## Giriş ekranı

- E-posta.
- Şifre.
- Oturum açık kalsın.
- Şifremi unuttum.
- Giriş yap.
- Kayıt ol.

## Kullanıcı güvenliği

- password_hash.
- password_verify.
- Session fixation koruması.
- Session ID yenileme.
- Güvenli cookie ayarları.
- Giriş denemelerine sınır koyma.
- Yetki kontrolü.

---

# 6. KARİYER TESTİ – PROJENİN ANA ÖZELLİĞİ

Platformun en gelişmiş bölümü kariyer testi olacak.

Sıradan ve birkaç sorudan oluşan bir test oluşturma.

Kullanıcıyı farklı yönlerden değerlendiren kapsamlı bir sistem geliştir.

## Test kategorileri

### A. İlgi alanları

- Teknoloji.
- Bilim.
- Sağlık.
- Eğitim.
- Sanat.
- Tasarım.
- Spor.
- İşletme.
- Ekonomi.
- Hukuk.
- İletişim.
- Sosyal hizmetler.
- Mühendislik.
- Araştırma.

### B. Yetenek alanları

- Analitik düşünme.
- Matematiksel düşünme.
- Problem çözme.
- İletişim.
- Yaratıcılık.
- Liderlik.
- Organizasyon.
- El becerisi.
- Araştırma.
- Teknolojik beceriler.
- Görsel düşünme.
- Sözel ifade.

### C. Kişisel çalışma tercihleri

- Takım çalışması.
- Bireysel çalışma.
- İnsanlarla iletişim.
- Bilgisayar başında çalışma.
- Saha çalışması.
- Araştırma yapma.
- Üretim ve tasarım.
- Planlama ve yönetim.

### D. Çalışma ortamı

- Ofis.
- Laboratuvar.
- Hastane.
- Okul.
- Atölye.
- Açık alan.
- Uzaktan çalışma.
- Karma çalışma.

## Test tasarımı

Test ekranı modern ve dikkat dağıtmayan bir yapıda olmalı.

Sol veya üst alanda:
- Test başlığı.
- Test kategorisi.
- Tamamlanma yüzdesi.
- İlerleme çubuğu.

Orta alanda:
- Soru numarası.
- Büyük soru kartı.
- Cevap seçenekleri.
- Önceki soru.
- Sonraki soru.

Alt bölüm:
- Testten çık.
- Cevapları kaydet.
- Testi tamamla.

## Soru tipleri

1. Tek seçenekli sorular.
2. Çok seçenekli sorular.
3. 1–5 arası değerlendirme soruları.
4. İlgi alanı seçme kartları.
5. Senaryo tabanlı tercih soruları.

Örnek sorular:

- Yeni teknolojileri öğrenmekten hoşlanır mısın?
- İnsanlara yardım etmek senin için ne kadar önemlidir?
- Matematiksel problemleri çözmekten hoşlanır mısın?
- Yeni tasarımlar üretmek ilgini çeker mi?
- Bir ekip içerisinde çalışmayı tercih eder misin?
- Araştırma yaparak yeni bilgiler öğrenmek hoşuna gider mi?
- Bilgisayar ve yazılımla ilgilenir misin?
- Bir problemi çözmek için farklı yöntemler denemeyi sever misin?

En az 40 kaliteli soru hazırla.

Sorular farklı kategorilere dengeli dağıtılsın.

Test tamamlanmadan sonuç gösterme.

Kullanıcının test cevaplarını gerçek zamanlı olarak veya güvenli kayıt adımlarıyla veritabanında sakla.

Kullanıcı sayfayı yenilediğinde kaldığı yerden devam edebilsin.

---

# 7. AKILLI MESLEK EŞLEŞTİRME ALGORİTMASI

Basit rastgele öneriler oluşturma.

Gerçek bir puanlama sistemi geliştir.

Mesleklerin kendi ilgi alanı, yetenek, çalışma ortamı ve beceri kriterleri bulunsun.

Örnek:

Yazılım Mühendisliği:
- Teknoloji ilgisi: yüksek.
- Analitik düşünme: yüksek.
- Problem çözme: yüksek.
- Matematik: orta/yüksek.
- Araştırma: yüksek.

Psikoloji:
- İnsan ilişkileri: yüksek.
- İletişim: yüksek.
- Araştırma: orta/yüksek.
- Sosyal ilgi: yüksek.

Grafik Tasarım:
- Yaratıcılık: yüksek.
- Görsel düşünme: yüksek.
- Tasarım ilgisi: yüksek.

## Eşleştirme mantığı

Her kullanıcı cevabını ilgili kriterlere göre puanlandır.

Mesleklerin kriter ağırlıklarını veritabanından al.

Öneri puanı:
- İlgi alanı uyumu: %35
- Yetenek uyumu: %30
- Çalışma tercihi uyumu: %20
- Beceri uyumu: %15

Bu oranlar yönetim panelinden düzenlenebilir olsun.

Her meslek için 0–100 arası uygunluk puanı hesapla.

Sonuçları en yüksek puandan en düşük puana doğru sırala.

Öneri açıklamalarını kullanıcının cevaplarından hareketle oluştur.

Örneğin:

"Teknolojiye olan ilgin, analitik düşünme tercihin ve problem çözme becerilerine verdiğin yüksek puanlar nedeniyle yazılım mühendisliği senin için araştırmaya değer alanlardan biri."

Önemli:
Bu sonuçlar kesin meslek belirleme veya bilimsel kişilik tanısı değildir. Kullanıcıya alternatifler ve kendini keşfetme fırsatı sunan rehberlik sonuçlarıdır.

---

# 8. TEST SONUÇ EKRANI

Test tamamlandıktan sonra özel bir sonuç sayfası açılsın.

Sayfa çok profesyonel ve görsel açıdan etkileyici olsun.

## Üst bölüm

"Senin Kariyer Profilin Hazır!"

Altında kullanıcının adı ve test tarihi gösterilsin.

## Sonuç kartları

En uygun 5 mesleği sırala.

Her kartta:
- Meslek görseli.
- Meslek adı.
- Kategori.
- Uygunluk yüzdesi.
- Neden önerildiği.
- Detayları görüntüle.
- Favorilere ekle.

## Grafikler

Chart.js kullanarak:

1. İlgi alanı dağılımı.
2. Yetenek puanları.
3. Çalışma tercihi analizi.
4. Meslek uygunluk grafiği.

## Kişisel değerlendirme

- Güçlü yönlerin.
- İlgini çeken alanlar.
- Geliştirilebilecek beceriler.
- Önerilen eğitim alanları.
- Keşfedebileceğin alternatif meslekler.

## Ek özellikler

- Sonuçları PDF olarak indir.
- Sonuçları hesabımda sakla.
- Testi yeniden yap.
- Meslek detaylarını görüntüle.

PDF oluşturma işlemi sunucu tarafında çalışsın.

---

# 9. MESLEK KÜTÜPHANESİ

Geniş kapsamlı meslek kataloğu oluştur.

Başlangıçta en az 50 farklı meslek kaydı ekle.

Meslekler kategorilere ayrılmış olsun.

Her meslek detay sayfasında şu alanlar bulunsun:

1. Meslek adı.
2. Meslek görseli.
3. Genel tanıtım.
4. Mesleğin görevleri.
5. Gerekli beceriler.
6. Gerekli eğitim.
7. Üniversite bölüm örnekleri.
8. Çalışma alanları.
9. Kariyer gelişim yolları.
10. Avantajlar ve çalışma koşulları.
11. İlgili meslekler.
12. Benzer kariyer seçenekleri.

## Arama ve filtreleme

- Meslek adına göre arama.
- Kategoriye göre filtreleme.
- Eğitim alanına göre filtreleme.
- İlgi alanına göre filtreleme.
- Alfabetik sıralama.
- En çok incelenenler.
- Favori meslekler.

Filtreler AJAX ile çalışabilsin.

Arama sonuçlarında sayfalama kullan.

---

# 10. KULLANICI DASHBOARD

Giriş yapan kullanıcıya özel bir kontrol paneli tasarla.

## Sidebar menüsü

- Genel Bakış
- Kariyer Testim
- Test Sonuçlarım
- Meslek Önerilerim
- Meslekleri Keşfet
- Favorilerim
- Profilim
- Ayarlar
- Çıkış Yap

## Dashboard ana ekranı

Üstte:
"Merhaba, [Kullanıcı Adı]!"

Altında:
"Kariyer yolculuğuna devam etmeye hazır mısın?"

İstatistik kartları:
- Tamamlanan test.
- Favori meslek sayısı.
- Son test tarihi.
- Keşfedilen meslek sayısı.

Diğer alanlar:
- Son test sonuçları.
- Sana önerilen meslekler.
- İlgi alanı grafiği.
- Yetenek grafiği.
- Kariyer gelişim önerileri.
- Son aktiviteler.

Her bilgi veritabanından gelsin.

---

# 11. FAVORİ MESLEK SİSTEMİ

Kullanıcı meslekleri favorilerine ekleyebilsin.

Özellikler:
- Favori butonu.
- Favoriden kaldırma.
- Favori listesi.
- Favori sayısı.
- Meslek detayına geçiş.

AJAX kullanarak sayfa yenilenmeden favori işlemi gerçekleştir.

Aynı mesleğin iki defa eklenmesini engelle.

---

# 12. KARİYER REHBERİ VE BLOG

Kullanıcıların meslek seçimi ve eğitim hakkında bilgi edinebileceği ayrı bir bölüm oluştur.

Kategori örnekleri:

- Doğru meslek seçimi.
- Üniversite ve bölüm seçimi.
- Geleceğin meslekleri.
- Teknoloji kariyerleri.
- Yetenek geliştirme.
- Öğrenciler için öneriler.
- Kariyer planlaması.

Her içerikte:
- Başlık.
- Kapak görseli.
- Kategori.
- İçerik.
- Yayın tarihi.
- Okuma süresi.

İçerikleri admin panelinden yönet.

---

# 13. ADMIN PANELİ

Kullanıcı panelinden tamamen ayrı profesyonel bir yönetim paneli oluştur.

Admin tasarımı modern bir SaaS yönetim sistemi gibi olsun.

## Yönetim ana ekranı

İstatistik kartları:
- Toplam kullanıcı.
- Toplam meslek.
- Toplam kariyer testi.
- Tamamlanan test.
- Toplam soru.
- Toplam blog yazısı.

Grafikler:
- Günlük kullanıcı kayıtları.
- Haftalık test sayısı.
- En çok önerilen meslekler.
- En çok incelenen meslekler.
- Meslek kategorisi dağılımı.

## Kullanıcı yönetimi

- Kullanıcı listele.
- Kullanıcı ara.
- Kullanıcı detaylarını görüntüle.
- Kullanıcı rolünü yönet.
- Kullanıcı hesabını etkinleştir veya devre dışı bırak.

## Meslek yönetimi

- Meslek ekle.
- Meslek düzenle.
- Meslek sil.
- Görsel yükle.
- Kategori seç.
- Meslek kriterlerini tanımla.
- Meslek açıklamalarını düzenle.
- Meslekleri yayına al veya gizle.

## Test yönetimi

- Soru ekle.
- Soru düzenle.
- Soru sil.
- Soru kategorisi belirle.
- Cevap seçenekleri ekle.
- Cevap puanlarını düzenle.
- Soruları aktif/pasif yap.
- Soru sırasını değiştir.

## Blog yönetimi

- Yazı oluştur.
- Yazı düzenle.
- Yazı sil.
- Görsel yükle.
- Kategori belirle.
- Yayın durumunu değiştir.

## Sistem ayarları

- Site adı.
- Logo.
- Açıklama.
- İletişim bilgileri.
- Test puanlama ağırlıkları.
- Kayıt ayarları.

Admin yetkileri sunucu tarafında kontrol edilsin.

---

# 14. MYSQL VERİTABANI MİMARİSİ

Veritabanı adı:

akilli_kariyer

Aşağıdaki tabloları oluştur:

### Kullanıcı tabloları

1. users
2. user_profiles
3. user_roles

### Meslek tabloları

4. careers
5. career_categories
6. career_skills
7. career_interests
8. career_environments
9. career_requirements

### Kariyer testi tabloları

10. test_categories
11. test_questions
12. test_options
13. test_sessions
14. test_answers
15. test_results
16. career_recommendations
17. career_match_scores

### Kullanıcı etkileşimleri

18. favorite_careers
19. viewed_careers
20. user_activities

### İçerik tabloları

21. blog_posts
22. blog_categories

### Sistem tabloları

23. site_settings
24. contact_messages
25. password_resets
26. audit_logs

## Veritabanı kuralları

- Her tabloda uygun Primary Key kullan.
- Foreign Key ilişkilerini kur.
- E-posta UNIQUE olmalı.
- Gerekli alanlarda indeks oluştur.
- created_at ve updated_at alanları kullan.
- İlişkili kayıtları güvenli şekilde yönet.
- Uygun veri tiplerini seç.
- Test cevaplarını kullanıcı ve oturumla ilişkilendir.
- Bir kullanıcının bir test oturumunda bir soruya birden fazla çelişkili cevabı olmasını engelle.
- Meslek kriterlerini ayrı tablolarda tut.

Bütün veritabanını oluşturacak eksiksiz database.sql dosyası hazırla.

Ayrıca başlangıç verilerini ekle:
- 50 meslek.
- 10 meslek kategorisi.
- 40 veya daha fazla kariyer testi sorusu.
- Test cevap seçenekleri.
- Meslek kriterleri.

---

# 15. GÜVENLİK VE VERİ KORUMA

Aşağıdaki güvenlik standartlarını uygula:

- PDO prepared statements.
- SQL Injection koruması.
- XSS koruması.
- CSRF token.
- Session güvenliği.
- Şifre hash sistemi.
- Admin rol kontrolü.
- Dosya uzantısı ve MIME kontrolü.
- Dosya boyutu sınırı.
- Güvenli dosya isimleri.
- Yetkisiz erişim engeli.
- Form doğrulaması.
- Güvenli hata mesajları.
- Hassas bilgileri loglara yazmama.

KVKK kapsamında gerekli aydınlatma ve gizlilik metinleri için ayrı sayfalar oluştur.

Gereksiz kişisel veri toplama.

---

# 16. DOSYA MİMARİSİ

Düzenli ve profesyonel klasör yapısı oluştur:

akilli-kariyer/
│
├── index.php
├── careers.php
├── career-detail.php
├── career-test.php
├── test-result.php
├── career-guide.php
├── blog.php
├── blog-detail.php
├── about.php
├── contact.php
├── login.php
├── register.php
├── logout.php
│
├── app/
│   ├── Core/
│   │   ├── Database.php
│   │   ├── Router.php
│   │   ├── Auth.php
│   │   ├── Validator.php
│   │   └── CSRF.php
│   │
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   │   ├── CareerMatchingService.php
│   │   ├── TestService.php
│   │   └── RecommendationService.php
│   │
│   └── Helpers/
│
├── config/
│   ├── app.php
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   └── functions.php
│
├── dashboard/
│   ├── index.php
│   ├── tests.php
│   ├── results.php
│   ├── recommendations.php
│   ├── favorites.php
│   ├── profile.php
│   └── settings.php
│
├── admin/
│   ├── index.php
│   ├── users.php
│   ├── careers.php
│   ├── categories.php
│   ├── questions.php
│   ├── tests.php
│   ├── blog.php
│   └── settings.php
│
├── api/
│   ├── test.php
│   ├── favorites.php
│   ├── careers.php
│   └── recommendations.php
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── icons/
│   └── fonts/
│
├── database/
│   └── database.sql
│
├── storage/
│   ├── uploads/
│   └── logs/
│
├── README.md
└── .htaccess

Dosya yapısını gerçek işlevlere göre genişletebilirsin.

---

# 17. PERFORMANS VE KULLANICI DENEYİMİ

- Veritabanı sorgularını optimize et.
- Gereksiz sorgu tekrarlarını önle.
- Sayfalama kullan.
- Görselleri optimize et.
- Lazy loading uygula.
- JavaScript hatalarını gider.
- Formlarda yükleniyor durumu göster.
- İşlem başarılı olduğunda bildirim göster.
- Hata durumunda kullanıcıya anlaşılır mesaj sun.
- AJAX işlemlerinde çift tıklama sorunlarını önle.
- Butonların gerçekten işlem yapmasını sağla.

Sayfalar hızlı açılmalı ve donma yapmamalı.

---

# 18. TEST VE KALİTE KONTROLÜ

Projeyi tamamladığında aşağıdaki testleri gerçekleştir:

1. Kullanıcı kayıt olabiliyor mu?
2. Kullanıcı giriş yapabiliyor mu?
3. Şifre güvenli şekilde saklanıyor mu?
4. Kullanıcı çıkış yapabiliyor mu?
5. Kariyer testi başlatılabiliyor mu?
6. Test cevapları kaydediliyor mu?
7. Test kaldığı yerden devam ediyor mu?
8. Test sonuçları hesaplanıyor mu?
9. Meslek önerileri doğru sıralanıyor mu?
10. Sonuçlar kullanıcı panelinde saklanıyor mu?
11. Meslek arama çalışıyor mu?
12. Filtreler çalışıyor mu?
13. Favorilere ekleme ve çıkarma çalışıyor mu?
14. Admin meslek ekleyebiliyor mu?
15. Admin soru ekleyebiliyor mu?
16. Blog yönetimi çalışıyor mu?
17. Yetkisiz kullanıcı admin paneline erişebiliyor mu? (Erişememeli.)
18. Mobil tasarım düzgün mü?
19. Bütün butonlar çalışıyor mu?
20. Veritabanı bağlantısı doğru mu?

Tüm hataları gider.

---

# 19. TESLİMAT

Projeyi yarım bırakma.

Teslim edilecekler:

1. Bütün PHP dosyaları.
2. Bütün CSS dosyaları.
3. Bütün JavaScript dosyaları.
4. MySQL database.sql dosyası.
5. Başlangıç meslek ve soru verileri.
6. Admin paneli.
7. Kullanıcı paneli.
8. README kurulum rehberi.
9. Gerekli görseller ve ikonlar.
10. Çalıştırılabilir tam proje ZIP dosyası.

Kurulum:
- XAMPP veya benzeri PHP sunucusu.
- MySQL veritabanı oluşturma.
- database.sql içe aktarma.
- Veritabanı bağlantısını ayarlama.
- Uygulamayı başlatma.

Kurulum adımlarını Türkçe açıkla.

# SON TALİMAT

Projeyi tek seferde bütün sayfalarıyla geliştirmeye çalışırken hiçbir bölümü eksik bırakma.

Önce veritabanı mimarisini ve dosya yapısını oluştur. Ardından backend işlemlerini, frontend tasarımlarını, kullanıcı panelini, kariyer testini, eşleştirme algoritmasını ve admin panelini tamamla.

Bir sayfanın butonunu başka bir sayfaya bağlamayı unutma.

Boş bağlantılar, göstermelik istatistikler, çalışmayan formlar, sahte giriş sistemi ve sadece tasarımdan ibaret ekranlar kesinlikle istemiyorum.

**NİHAİ HEDEF:**

Türkiye'deki öğrencilerin kullanabileceği, modern görünüşlü, kapsamlı, güvenli, gerçek MySQL veritabanına bağlı, PHP ile geliştirilmiş, akıllı kariyer testi ve meslek önerileri sunan eksiksiz bir web platformu oluştur.
