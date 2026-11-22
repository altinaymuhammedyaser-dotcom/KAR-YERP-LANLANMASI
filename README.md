# Akıllı Kariyer Rehberi

Öğrencilerin ilgi alanlarını, yeteneklerini ve çalışma tercihlerini analiz ederek kişiselleştirilmiş meslek önerileri sunan kariyer rehberi platformu.

Bu depo uygulamanın **eksiksiz** kaynak kodunu içerir:

| Klasör | Açıklama |
| --- | --- |
| `/` (kök) | PHP 8.2 + MySQL/MariaDB uygulaması (PDO, oturum, CSRF, `password_hash`) |
| `admin/` | Yönetim paneli |
| `dashboard/` | Üye paneli (testler, öneriler, favoriler, kişilik testi, mülakat simülatörü, CV oluşturucu vb.) |
| `api/` | JSON API uç noktaları (Range destekli video akışı dahil) |
| `app/`, `config/`, `includes/`, `database/`, `tools/` | Çekirdek, yapılandırma, ortak şablonlar, şema/tohum betikleri |
| `assets/` | CSS, JavaScript, ikonlar, görseller, meslek verisi (`careers.json`) |
| `netlify-static/` | Netlify'da yayında olan **statik sürümün tamamı** (AI koç v2, üye alanı demosu, premium UI/UX) |
| `deploy_server/` | Netlify Drop yükleme yardımcıları |
| `netlify-deploy/` | Statik dışa aktarma betiği |

## Özellikler

- 54 meslek, 10 kategori, 15 ilgi alanı, 12 soruluk kariyer testi
- 4 boyutlu uyum analizi ve meslek önerileri
- Big Five kişilik testi (20 soru)
- 8 soruluk mülakat simülatörü (puanlama + geri bildirim)
- AI Kariyer Koçu v2: bağlam hatırlama, meslek karşılaştırma, mini kariyer testi, günün mesleği, sonuç yorumlama
- Favoriler, kariyer yol haritası, beceri analizi, CV oluşturucu
- Açık/koyu tema, mobil uyumlu premium arayüz
- Üyeliğe özel kariyer testi ve panel

## Yerel kurulum (PHP sürümü)

Gereksinimler: PHP 8.2+, MySQL/MariaDB, PDO eklentisi.

```bash
# MySQL'i başlatıp veritabanını oluştur
mysql -u root < database/database.sql

# Sunucuyu başlat
php -S 127.0.0.1:8080 -t .
```

Demo hesapları:
- `demo@akillikariyer.com` / `Demo!2345`
- `admin@akillikariyer.com` / `Admin!2345`

> Veritabanı bağlantı bilgileri `config/app.php` içindedir; kendi ortamına göre düzenle.

## Statik (Netlify) sürümü

`netlify-static/` klasörünü doğrudan Netlify Drop'a sürükleyebilirsin. Bu sürümde oturum, favoriler ve test sonuçları tarayıcıda (`localStorage`) tutulur; PHP/MySQL gerektirmez.

Canlı site: https://akillikariyer.netlify.app

## Lisans / notlar

Eğitim amaçlı geliştirilmiştir. Kariyer testi klinik bir psikolojik değerlendirme değil, rehberlik aracıdır.
