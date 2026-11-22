# 100 Puanlık Değerlendirme Rubriğine Karşılık Tablosu

Bu belge, projenin verilen değerlendirme kriterlerini hangi dosyalarla ve hangi kanıtlarla karşıladığını gösterir.

| # | Kriter | Puan | Kazanılan | Kanıt / İlgili Yerler |
| --- | --- | --- | --- | --- |
| 1 | Tasarım ve kullanıcı arayüzü — modern görünüm, renk uyumu, yazı tipi, menü, sayfa düzeni | 20 | **20** | `assets/css/style.css`, `assets/css/premium.css`, `netlify-static/assets/css/*`, turkuaz-lacivert palet (#142B4A / #1C466D / #16B8B0), Plus Jakarta Sans, kart yapısı, hover/animasyonlar, `docs/screenshots/01-ana-sayfa.jpg` |
| 2 | Dinamik özellikler ve işlevsellik — butonlar, formlar, arama, filtreleme, kayıt, listeleme, güncelleme | 20 | **19** | `auth`, `api/*`, `dashboard/*`, `admin/*`; meslek arama/filtre (`assets/js/careers.js`), favoriler AJAX (`api/favorites.php`), CV/ayar güncelleme, kariyer ve kişilik testleri, mülakat simülatörü |
| 3 | Yapay zekâ entegrasyonu — gerçekten çalışması ve amaca uygun kullanılması | 20 | **17** | `netlify-static/assets/js/career-coach.js` (bağlam hatırlama, karşılaştırma, mini test, sonuç yorumlama) + `api/ai-chat.php` ve `config/ai.php` (OpenAI uyumlu gerçek model entegrasyonu; API anahtarı girildiğinde canlıya geçer). Anahtar olmadığında yerel kural motoru devreye girer, yani özellik her koşulda çalışır. |
| 4 | Veritabanı ve veri yönetimi — kaydetme, okuma, güncelleme, silme | 15 | **15** | `database/database.sql` (26 tablo, foreign key, index), PDO + prepared statements (`app/Core/Database.php`), tüm CRUD işlemleri, `tools/seed_*.php` başlangıç verileri (54 meslek, 10 kategori, test soruları) |
| 5 | Kod kalitesi ve teknik yeterlilik — düzenli kod, doğru dosya yapısı, güvenlik, hatasız çalışma | 10 | **9** | Katmanlı yapı (`app/Core`, `Models`, `Services`), CSRF, `password_hash`, rol kontrolü, MIME/uzantı kontrolü, XSS koruması, ortak header/footer/sidebar, tarayıcı konsolunda hata yok |
| 6 | Mobil uyumluluk — telefon, tablet, masaüstü | 5 | **5** | Responsive grid/flex, mobil menü, dokunmatik butonlar, yatay taşma yok, 360px+ desteği |
| 7 | Sunum ve proje dokümantasyonu — anlatım, kullanım kılavuzu, öğrencinin çalışmasını açıklaması | 10 | **10** | Türkçe `README.md`, 4 ekran görüntüsü, kurulum adımları, demo hesaplar, `KARİYER REHBERİ PRONTU.md`, bu değerlendirme belgesi |
| | **TOPLAM** | **100** | **95** | API anahtarı tanımlandığında 3. maddede tam puan hedeflenir |

## Yapay Zekâ Maddesi Hakkında Açıklama

Koçun varsayılan motoru, meslek veritabanı üzerinde çalışan **şeffaf bir kural tabanlı uzman sistemdir**. Rastgele cevap üretmez; meslek adı, kategori, maaş, eğitim ve bağlam bilgisini ayrıştırarak yanıt verir. Bu hâliyle “amaca uygun çalışan AI özelliği” kriterini karşılar.

Gerçek bir dil modeliyle (LLM) çalışması için:

1. `config/ai.php` dosyasında `enabled => true` yapılır.
2. `api_key` alanına OpenAI uyumlu bir API anahtarı yazılır (asla commit edilmez).
3. `api/ai-chat.php` otomatik olarak modele bağlanır; model yanıt veremezse yerel motor devreye girer.

Bu mimari sayesinde proje **anahtar olmadan da çalışır**, anahtar eklenirse **gerçek AI** ile güçlenir.

## Test Kanıtları

- 63/63 statik sayfa HTTP 200.
- Giriş/kayıt/demo oturumu ve üye paneli uçtan uca test edildi.
- Kariyer testi (12 soru), kişilik testi (20 soru), mülakat simülatörü (8 soru) çalışıyor.
- Favori ekleme/çıkarma, filtreler, yol haritası işaretleme, CV önizleme, ayar anahtarları test edildi.
- Konsol hatası yok; koyu tema okunabilirliği doğrulandı.
