# 🎯 AKILLI KARİYER REHBERİ

**Profesyonel Meslek Keşif ve Kariyer Yönlendirme Platformu**

Öğrencilerin ilgi alanlarını, yeteneklerini ve çalışma tercihlerini analiz ederek
kişiselleştirilmiş meslek önerileri sunan, PHP 8.2 + MySQL tabanlı tam kapsamlı web uygulaması.

---

## 📋 İÇİNDEKİLER

1. [Öne Çıkan Özellikler](#-öne-çıkan-özellikler)
2. [Teknoloji Altyapısı](#-teknoloji-altyapısı)
3. [Kurulum Rehberi (XAMPP)](#-kurulum-rehberi-xampp)
4. [Varsayılan Hesaplar](#-varsayılan-hesaplar)
5. [Proje Dosya Yapısı](#-proje-dosya-yapısı)
6. [Veritabanı Mimarisi](#-veritabanı-mimarisi)
7. [Eşleştirme Algoritması](#-eşleştirme-algoritması)
8. [Güvenlik Önlemleri](#-güvenlik-önlemleri)
9. [Yönetim Paneli Kullanımı](#-yönetim-paneli-kullanımı)
10. [Sık Sorulan Sorular](#-sık-sorulan-sorular)
11. [Sorun Giderme](#-sorun-giderme)

---

## ✨ ÖNE ÇIKAN ÖZELLİKLER

### 🎯 Akıllı Kariyer Testi
- **48 gerçekçi soru**, 5 farklı soru tipi (tek seçenek, çok seçenek, 1-5 değerlendirme, kart seçimi, senaryo)
- **4 değerlendirme boyutu**: İlgi Alanları · Yetenekler · Çalışma Tercihleri · Çalışma Ortamı
- Cevaplar **otomatik kaydedilir**; sayfa yenilense bile kaldığınız yerden devam edersiniz
- 5 soru tipinin tamamı özel arayüzlerle sunulur

### 🧮 Akıllı Meslek Eşleştirme Algoritması
- Her meslek için **4 ayrı kriter ağırlığı** (ilgi, yetenek, çalışma tercihi, beceri)
- Ağırlıklar **yönetim panelinden** değiştirilebilir; değişiklik geçmiş sonuçlara uygulanabilir
- Her öneri, **kullanıcının cevaplarından üretilen doğal dil açıklamasıyla** gelir
- Sonuçlar 0-100 arası uyum puanı ile sıralanır

### 📊 Görsel Raporlama
- İlgi alanı dağılımı, yetenek puanları, çalışma tercihi ve ortam analizi (Chart.js)
- **Sunucu tarafında üretilen PDF raporu** (gömülü Türkçe yazı tipi ile, harici kütüphane gerektirmez)
- Kişisel değerlendirme: güçlü yönler, gelişim alanları, önerilen eğitim alanları

### 🗂️ Kapsamlı Meslek Kataloğu
- **54 meslek**, **10 kategori**, her meslek için 12 ayrıntılı alan
- **Her meslek için gerçek fotoğraf** (Unsplash) — kartlarda, detay sayfasında ve öneri listelerinde görünür
- Görevler, beceriler, eğitim, bölüm örnekleri, çalışma alanları, kariyer yolları, avantajlar
- Ad, kategori, eğitim seviyesi ve ilgi alanına göre **AJAX ile anlık filtreleme**
- Sayfalama, alfabetik sıralama, "en çok incelenenler"

### 👤 Kullanıcı Paneli
- Genel bakış, kariyer testim, test sonuçlarım, meslek önerilerim, keşfet, favorilerim
- Profil, ayarlar (şifre değiştirme) ve aktivite geçmişi
- Favori meslekler **sayfa yenilenmeden** AJAX ile eklenip çıkarılır

### 🛡️ Yönetim Paneli
- Kullanıcı yönetimi (rol, etkin/pasif, silme, denetim kaydı)
- Meslek CRUD + **eşleştirme kriteri tanımlama arayüzü**
- Test sorusu CRUD + seçenek puan eşleştirme, soru sıralama
- Kategori, blog yazısı ve iletişim mesajı yönetimi
- Site ayarları, **eşleştirme ağırlıkları** ve toplu yeniden hesaplama
- Dashboard: 8 istatistik kartı + 5 canlı grafik

### 📱 Duyarlı Tasarım
- 360px'ten 4K genişliğe kadar test edilmiş, **yatay taşma yok**
- Mobil için ayrı düzenlenmiş navigasyon, formlar ve kart yerleşimleri
- Dokunmatik cihazlara uygun 44px+ buton hedefleri

---

## 🛠 TEKNOLOJİ ALTYAPISI

| Katman | Teknoloji |
|---|---|
| **Backend** | PHP 8.2+, PDO (prepared statements), PHP Session |
| **Veritabanı** | MySQL 8.0+ / MariaDB 10.4+ (InnoDB, utf8mb4) |
| **Frontend** | HTML5, CSS3, JavaScript ES6+ |
| **Kütüphaneler** | Bootstrap 5.3, Chart.js 4.4, Lucide Icons, Plus Jakarta Sans |
| **PDF** | Dahili PDF motoru (CIDFontType2 / Identity-H, gömülü TTF) |
| **Mimari** | MVC katmanlı: Core / Models / Services / Controllers / Helpers |

> Tüm kütüphaneler (Bootstrap, Chart.js, Lucide) **yerel olarak** `assets/` klasöründe bulunur.
> İnternet bağlantısı olmadan da uygulama eksiksiz çalışır.

---

## 🚀 KURULUM REHBERİ (XAMPP)

### Adım 1 — XAMPP Kurulumu

1. [xampp.org](https://www.apachefriends.org/tr/index.html) adresinden **XAMPP** (PHP 8.x + MySQL/MariaDB) indirin.
2. Kurulumu tamamlayın ve **XAMPP Control Panel**'i açın.
3. Panelde **Apache** ve **MySQL** satırlarındaki **Start** butonlarına tıklayın.
   - Apache yeşil "Running" ✓
   - MySQL yeşil "Running" ✓

### Adım 2 — Projeyi Kopyalama

Proje klasörünü XAMPP'ın `htdocs` dizinine kopyalayın:

```
C:\xampp\htdocs\akilli-kariyer\
```

> **Alternatif:** `C:\xampp\htdocs` yerine herhangi bir klasöre kopyalayıp Apache'nin
> `DocumentRoot` ayarını değiştirebilirsiniz.

### Adım 3 — Veritabanı Oluşturma

**Yöntem A — phpMyAdmin (kolay):**

1. Tarayıcıdan `http://localhost/phpmyadmin` adresini açın.
2. Sol üstten **"Veritabanı"** sekmesine tıklayın.
3. **Veritabanı adı** alanına `akilli_kariyer` yazın, karakter seti `utf8mb4_general_ci` seçin.
4. **"Oluştur"** butonuna tıklayın.
5. Oluşturduğunuz veritabanını seçin, üstten **"İçe Aktar"** sekmesine geçin.
6. **"Dosya seç"** ile `akilli-kariyer/database/database.sql` dosyasını gösterin.
7. Karakter kümesi `utf8mb4` olsun, **"Çalıştır"** / **"Git"** butonuna tıklayın.
8. İşlem tamamlandığında sol tarafta `akilli_kariyer` veritabanını göreceksiniz.

**Yöntem B — Komut satırı:**

```bash
cd C:\xampp\mysql\bin
mysql -u root -p < C:\xampp\htdocs\akilli-kariyer\database\database.sql
```

> İçe aktarma sonrası veritabanında **29 tablo**, **54 meslek**, **48 soru** ve
> **241 cevap seçeneği** bulunacaktır.

### Adım 4 — Veritabanı Bağlantı Ayarları

`config/database.php` dosyasını açın ve kendi ortamınıza göre düzenleyin:

```php
return [
    'host'     => '127.0.0.1',   // XAMPP için 'localhost' da olabilir
    'port'     => '3306',
    'database' => 'akilli_kariyer',
    'username' => 'root',        // XAMPP'da varsayılan: root
    'password' => '',            // XAMPP'da varsayılan: boş
    'charset'  => 'utf8mb4',
    'options'  => [ /* ... */ ],
];
```

> Alternatif olarak MySQL kullanıcı adı/şifresini işletim sistemi ortam değişkenleriyle
> (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) tanımlayabilirsiniz.

### Adım 5 — (Opsiyonel) Demo Verisini Yükleyin

Demo hesabının panelde örnek verilerle görünmesi için (test sonucu, favoriler, aktiviteler):

```bash
cd C:\xampp\htdocs\akilli-kariyer
php tools/seed_demo.php
```

Bu komut `demo@akillikariyer.com` hesabına 48 soruluk tamamlanmış bir test, 12 meslek önerisi
ve 7 favori meslek ekler. **Zorunlu değildir** — yeni kurulumlarda boş bir panel de
tamamen çalışır durumda olur.

### Adım 6 — Uygulamayı Çalıştırma

Tarayıcıda şu adresi açın:

```
http://localhost/akilli-kariyer/
```

**Tebrikler! Platform hazır.** 🎉

### Adım 7 — (Opsiyonel) Apache'yi Otomatik Başlatma

Windows + XAMPP kullanıyorsanız `http://localhost` adresine gittiğinizde uygulamayı görmek
için `htdocs` içine küçük bir yönlendirme ekleyebilirsiniz:

```apache
# C:\xampp\htdocs\index.php
<?php
header('Location: /akilli-kariyer/');
exit;
```

---

## 🔑 VARSAYILAN HESAPLAR

| Rol | E-posta | Şifre | Açıklama |
|---|---|---|---|
| **Yönetici** | `admin@akillikariyer.com` | `Admin!2345` | Tam yetki: yönetim paneli, meslek/soru/blog yönetimi, ayarlar |
| **Demo Kullanıcı** | `demo@akillikariyer.com` | `Demo!2345` | Örnek test sonucu ve favoriler ile hazır panel |

> ⚠️ **Canlıya almadan önce** yönetici şifresini mutlaka değiştirin:
> *Yönetim Paneli → Kullanıcılar → Kullanıcıyı düzenle → Şifre değiştir*

---

## 📁 PROJE DOSYA YAPISI

```
akilli-kariyer/
│
├── index.php                  # Ana sayfa (landing page)
├── careers.php                # Meslek kataloğu + filtreler
├── career-detail.php          # Meslek detay sayfası
├── career-test.php            # Kariyer testi ekranı
├── test-result.php            # Test sonuç ekranı
├── interests.php              # İlgi alanları keşif sayfası
├── career-guide.php           # Kariyer rehberi (blog listesi)
├── blog.php                   # Blog listesi
├── blog-detail.php            # Blog yazı detayı
├── about.php                  # Hakkımızda
├── contact.php                # İletişim formu
├── login.php / register.php   # Üyelik
├── logout.php                 # Çıkış
├── forgot-password.php        # Şifre sıfırlama talebi
├── reset-password.php         # Yeni şifre belirleme
├── kvkk.php / privacy.php     # Yasal metinler
├── terms.php                  # Kullanım koşulları
├── 404.php                    # Özel hata sayfası
│
├── app/
│   ├── bootstrap.php          # Uygulama önyükleyici (otoloader, oturum, hata yönetimi)
│   ├── Core/
│   │   ├── Database.php       # PDO merkezi yönetimi
│   │   ├── Auth.php           # Kimlik doğrulama, yetki, brute force koruması
│   │   ├── CSRF.php           # CSRF token üretimi ve doğrulama
│   │   ├── Validator.php      # Sunucu tarafı form doğrulama
│   │   ├── Router.php         # Basit yönlendirici
│   │   ├── PdfBuilder.php     # PDF motoru (TrueType gömme)
│   │   └── TrueTypeFont.php   # TTF ayrıştırıcı (cmap/hmtx)
│   ├── Models/                # User, Career, Test, Result, Blog, Setting, Activity
│   ├── Services/
│   │   ├── CareerMatchingService.php  # Eşleştirme algoritması
│   │   ├── TestService.php            # Test oturumu yönetimi
│   │   ├── RecommendationService.php  # Öneri saklama ve sunum
│   │   └── ReportService.php          # PDF rapor üretimi
│   ├── Controllers/           # (genişletilebilir controller katmanı)
│   └── Helpers/
│       ├── functions.php      # Global yardımcı fonksiyonlar
│       └── Upload.php         # Güvenli dosya yükleme
│
├── config/
│   ├── app.php                # Uygulama ayarları (BASE_PATH otomatik hesaplanır)
│   └── database.php           # Veritabanı bağlantı bilgileri
│
├── includes/
│   ├── header.php             # Ortak <head> + header
│   ├── navbar.php             # Mobil navigasyon
│   ├── footer.php             # Ortak footer + scriptler
│   ├── panel-header.php       # Kullanıcı/yönetici paneli üst çerçeve
│   ├── panel-footer.php       # Panel alt çerçeve
│   ├── career-card.php        # Yeniden kullanılabilir meslek kartı
│   └── error-page.php         # Hata sayfası
│
├── dashboard/                 # Kullanıcı hesap paneli
│   ├── index.php  tests.php  results.php  recommendations.php
│   ├── explore.php  favorites.php  activities.php  profile.php  settings.php
│
├── admin/                     # Yönetim paneli
│   ├── index.php  users.php  careers.php  career-edit.php
│   ├── categories.php  questions.php  question-edit.php
│   ├── blog.php  blog-edit.php  messages.php  settings.php
│
├── api/                       # JSON API uç noktaları
│   ├── test.php               # Cevap kaydetme / test tamamlama
│   ├── favorites.php          # Favori ekleme/çıkarma
│   ├── careers.php            # Filtrelenmiş meslek listesi
│   ├── recommendations.php    # Meslek önerileri
│   └── result-pdf.php         # PDF rapor indirme
│
├── assets/
│   ├── css/  style.css, dashboard.css, vendor-bootstrap.min.css
│   ├── js/   main.js, test.js, careers.js + vendor/
│   ├── images/
│   │   ├── careers/      54 meslek fotoğrafı (JPG, ~4,6 MB)
│   │   └── favicon.svg
│   └── fonts/ Roboto (PDF gömme için)
│
├── database/
│   └── database.sql           # Şema + başlangıç verileri (tek dosya)
│
├── storage/
│   ├── uploads/               # Yüklenen görseller
│   ├── cache/                 # Üretilen PDF'ler
│   └── logs/                  # Uygulama logları
│
├── tools/                     # Geliştirme araçları (dağıtımda opsiyonel)
├── README.md
└── .htaccess
```

---

## 🗄 VERİTABANI MİMARİSİ

`akilli_kariyer` veritabanında **29 tablo** bulunur:

### Kullanıcı
| Tablo | Açıklama |
|---|---|
| `users` | Hesap bilgileri (şifre bcrypt hash'li) |
| `user_profiles` | Profil ayrıntıları (şehir, okul, bölüm, bio) |
| `user_roles` | Roller (user / editor / admin) |

### Meslek
| Tablo | Açıklama |
|---|---|
| `career_categories` | 10 meslek kategorisi |
| `careers` | 54 meslek kaydı |
| `career_interests` | Mesleğin ilgi alanı kriterleri + ağırlık |
| `career_skills` | Mesleğin yetenek kriterleri + ağırlık |
| `career_preferences` | Mesleğin çalışma tercihi kriterleri |
| `career_environments` | Mesleğin çalışma ortamı kriterleri |
| `career_competencies` | Mesleğin gerektirdiği somut beceriler |
| `career_requirements` | Görevler, eğitim, bölüm, sertifika, avantajlar |

### Kariyer Testi
| Tablo | Açıklama |
|---|---|
| `test_categories` | 4 test kategorisi (boyut tipi) |
| `test_questions` | 48 soru (5 farklı tip) |
| `test_options` | 241 seçenek (puan + boyut eşleştirmesi) |
| `test_sessions` | Test oturumları (kaldığı yerden devam) |
| `test_answers` | Cevaplar — `UNIQUE(session_id, question_id)` ile çift cevap engellendi |
| `test_results` | Sonuç kayıtları (JSON boyut puanları) |
| `career_recommendations` | Meslek önerileri (uyum puanı + gerekçe) |
| `career_match_scores` | Puanlama kırılımları (5 bileşen) |

### Etkileşim & İçerik
`favorite_careers` · `viewed_careers` · `user_activities` · `blog_posts` · `blog_categories`

### Sistem
`site_settings` · `contact_messages` · `password_resets` · `login_attempts` · `audit_logs`

> **Foreign key ilişkileri** `ON DELETE CASCADE` ile tanımlıdır; silme işlemlerinde
> ilişkili kayıtlar otomatik temizlenir.

---

## 🧮 EŞLEŞTİRME ALGORİTMASI

### Adım 1 — Boyut Puanları
Her soru bir boyuta (ilgi / yetenek / tercih / ortam) 1-5 arası puan katkısı sağlar.
Bu puanlar `0..1` aralığına normalize edilir (**3 = nötr = 0.5**):

```
boyutPuanı = Σ (seçenekPuanı_normalize × kategoriAğırlığı) / Σ kategoriAğırlığı
```

### Adım 2 — Bileşen Uyumları
Her meslek için dört ayrı uyum değeri hesaplanır:

```
uyum = Σ (kullanıcıPuanı[d] × meslekAğırlığı[d]) / Σ meslekAğırlığı[d]
```

| Bileşen | Kaynak Tablo |
|---|---|
| İlgi alanı uyumu | `career_interests` |
| Yetenek uyumu | `career_skills` |
| Çalışma tercihi uyumu | `career_preferences` + `career_environments` |
| Beceri uyumu | `career_competencies` (yetenek eşleştirmesiyle) |

### Adım 3 — Nihai Uyum Puanı

```
toplam = (35 × ilgi + 30 × yetenek + 20 × tercih + 15 × beceri) / 100
```

Ağırlıklar **Sistem Ayarları → Eşleştirme Ağırlıkları** ekranından değiştirilebilir.
Değişiklikten sonra **"Mevcut sonuçları yeniden hesapla"** seçeneği ile tüm geçmiş
sonuçlar yeni ağırlıklarla güncellenebilir.

### Adım 4 — Gerekçe Üretimi
Kullanıcının en yüksek puan verdiği boyutlar, mesleğin kendi kriterleriyle eşleştirilir
ve doğal dil bir cümle oluşturulur:

> *"Teknolojik becerilerin, teknolojiye olan ilgin ve yaratıcılık gücün nedeniyle
> Front-End Geliştirici senin için araştırmaya değer alanlardan biri olarak öne çıkıyor."*

---

## 🔒 GÜVENLİK ÖNLEMLERİ

| Önlem | Uygulama |
|---|---|
| **SQL Injection** | Tüm sorgularda PDO prepared statements, `ATTR_EMULATE_PREPARES = false` |
| **XSS** | Tüm çıktılarda `e()` kaçış fonksiyonu; `htmlspecialchars(ENT_QUOTES)` |
| **CSRF** | Tüm formlarda gizli token; AJAX'te `X-CSRF-Token` başlığı |
| **Şifreleme** | `password_hash()` (bcrypt) + `password_verify()` + otomatik rehash |
| **Session fixation** | Girişte `session_regenerate_id(true)` |
| **Çerez güvenliği** | `HttpOnly`, `SameSite=Lax`, HTTPS'te `Secure` |
| **Brute force** | IP + e-posta bazlı başarısız giriş sayacı, otomatik hesap kilidi |
| **Yetki kontrolü** | Admin sayfaları sunucu tarafında **her istekte** doğrulanır |
| **Dosya yükleme** | Uzantı + MIME + `getimagesize()` doğrulaması, 2 MB limit, rastgele dosya adı |
| **Hata gizliliği** | Üretimde hata mesajları gizlenir, detaylar log'a yazılır |
| **KVKK** | Aydınlatma metni, gizlilik politikası, açık rıza onayı, gereksiz veri toplanmaz |
| **Denetim** | Admin işlemleri `audit_logs` tablosuna kaydedilir |

---

## 🛠 YÖNETİM PANELİ KULLANIMI

**Yönetim Paneli → Kullanıcılar**
- E-posta ile arama · Durum filtresi · Rol filtresi
- Rol değiştirme (Kullanıcı / Editör / Yönetici) — açılır listeden tek tıkla
- Hesabı etkinleştirme / devre dışı bırakma (kendi hesabınız hariç)
- Kullanıcı silme (onaylı)

**Yönetim Paneli → Meslekler**
- Meslek ekleme / düzenleme / silme / yayına alma / gizleme / öne çıkarma
- **Eşleştirme Kriterleri** bölümünde her boyut (ilgi, yetenek, tercih, ortam) için
  onay kutusu + 1-5 ağırlık kaydırıcısı
- Beceri editörü: beceri adı + karşılık gelen yetenek + ağırlık
- Görsel yükleme (JPG/PNG/WEBP/GIF, 2 MB)

**Yönetim Paneli → Test Soruları**
- Soru ekleme / düzenleme / silme / aktif-pasif
- 5 farklı soru tipi, seçenek bazında **1-5 puan** ve **boyut eşleştirmesi**
- Çoklu seçimli sorularda min/max seçim sınırı
- Soru sırasını belirleme

**Yönetim Paneli → Sistem Ayarları**
- Site adı, slogan, açıklama, iletişim bilgileri
- **Eşleştirme ağırlıkları** (kaydırıcılarla) + toplu yeniden hesaplama
- Kayıt açık/kapalı, KVKK zorunluluğu, minimum şifre uzunluğu
- Maksimum giriş denemesi, hesap kilit süresi, şifre sıfırlama token ömrü

---

## ❓ SIK SORULAN SORULAR

**S: Testi yarıda bırakırsam ne olur?**
Cevaplarınız her soruda anında veritabanına kaydedilir. Sayfayı kapatsanız veya bilgisayarı
kapatırsanız bile kaldığınız yerden devam edersiniz. Panelden **Kariyer Testim** bölümünden
oturumu görebilir ve silebilirsiniz.

**S: Testi tekrar yapabilir miyim?**
Evet. **Testi Yeniden Yap** seçeneğiyle yeni bir oturum başlatırsınız. Eski sonuçlarınız
silinmez, **Test Sonuçlarım** sayfasında karşılaştırılabilir.

**S: Sonuçlar benim mesleğimi kesin olarak belirler mi?**
Hayır. Sonuçlar bir **rehberdir**; kesin meslek belirleme veya bilimsel kişilik tanısı
değildir. Size ilgi alanlarınız ve yetenekleriniz üzerinden keşif fırsatı sunar.

**S: PDF raporu nasıl üretiliyor?**
Tamamen **sunucu tarafında**, harici kütüphane veya sunucu gerektirmeden çalışır.
`assets/fonts/` içindeki Roboto yazı tipi PDF'e gömülür (Identity-H kodlaması), böylece
Türkçe karakterler (ğ, ş, İ, ı, ç, ö, ü) sorunsuz görünür.

**S: Uygulamayı alt klasöre kurabilir miyim?**
Evet. `htdocs/akilli-kariyer/` şeklinde kurulum otomatik olarak algılanır. `BASE_PATH`
değeri `config/app.php` içinde otomatik hesaplanır. Gerekirse
`define('BASE_PATH_OVERRIDE', '/akilli-kariyer');` ile elle de belirleyebilirsiniz.

**S: Farklı bir MySQL sunucusu kullanabilir miyim?**
Evet. `config/database.php` dosyasındaki sunucu, port, veritabanı adı ve kullanıcı bilgilerini
güncellemeniz yeterlidir. Uzak sunucu için `config/app.php` içinde `APP_DEBUG` değerini
`false` yapmayı unutmayın.

**S: Verilerimi silebilir miyim?**
**Ayarlar → Hesap İşlemleri** bölümünden hesabınızı ve tüm verilerinizi kalıcı olarak
silebilirsiniz.

---

## 🛠 SORUN GİDERME

| Sorun | Çözüm |
|---|---|
| *"Veritabanına bağlanılamadı"* | MySQL servisinin çalıştığını ve `config/database.php` bilgilerini kontrol edin |
| *"mysqli_real_connect: Access denied"* | MySQL kullanıcı adı/şifresi hatalı; phpMyAdmin ile giriş yapıp kullanıcı oluşturun |
| *"Unknown database 'akilli_kariyer'"** | `database.sql` dosyasını içe aktarmayı unutmuşsunuz |
| Sayfalar boş / CSS yok | `assets/` klasörünün tamamen kopyalandığından emin olun |
| Türkçe karakterler bozuk | Veritabanını `utf8mb4_unicode_ci` ile oluşturduğunuzdan emin olun |
| "Oturum açık kalsın" çalışmıyor | `storage/` klasörü yazılabilir olmalı; tarayıcı çerezlerini temizleyin |
| 500 hatası | `storage/logs/` klasörünü oluşturun ve yazılabilir yapın; `APP_DEBUG = true` ile ayrıntıyı görün |
| Port 8080/80 hatası | XAMPP Apache'nin durduğundan ve 80 portunun boş olduğundan emin olun |

---

## 📸 Görsel Kaynakları

- **Meslek fotoğrafları:** [Unsplash](https://unsplash.com) — Unsplash Lisansı ile kullanılmaktadır.
  Görseller `assets/images/careers/` klasöründe, mesleğin slug adıyla saklanır.
  Fotoğrafı olmayan bir meslek eklenirse uygulama otomatik olarak **prosedürel SVG illüstrasyonu** üretir.
- **İkonlar:** [Lucide](https://lucide.dev) (ISC Lisansı)
- **Grafikler:** [Chart.js](https://www.chartjs.org) (MIT Lisansı)
- **Arayüz:** [Bootstrap](https://getbootstrap.com) (MIT Lisansı)
- **Yazı tipi:** [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans) · [Roboto](https://fonts.google.com/specimen/Roboto) (Apache 2.0)

---

## 📄 LİSANS

Bu proje eğitim ve demo amaçlıdır. Serbestçe kullanılabilir, düzenlenebilir ve
dağıtılabilir.

---

<div align="center">

**AKILLI KARİYER — Geleceğini Keşfet**

PHP 8.2 · MySQL · Bootstrap 5 · Chart.js · Lucide

</div>
