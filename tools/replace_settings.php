<?php
$p = dirname(__DIR__) . '/netlify-src/assets/js/panel.js';
$s = file_get_contents($p);
$start = strpos($s, '  function renderSettings(){');
$end = strpos($s, '  /* ---------------- Kişilik testi ---------------- */');
if ($start === false || $end === false) { echo "markers bulunamadi\n"; exit(1); }
$new = <<<'JS'
  function renderSettings(){
    var prefs=JSON.parse(localStorage.getItem('akk_prefs')||'{}');
    var theme=localStorage.getItem('akk-theme')||(document.documentElement.getAttribute('data-theme')==='dark'?'dark':'light');
    var accent=localStorage.getItem('akk-accent')||'teal';
    var tog=function(k,l,d,on){return '<div class="px-setting-row"><div><b>'+l+'</b><small>'+d+'</small></div>'+
      '<button type="button" class="px-toggle'+(on?' on':'')+'" data-pref="'+k+'" aria-pressed="'+on+'"><span></span></button></div>';};
    var tabs=[['account','Hesap','user'],['appearance','Görünüm','palette'],['notifications','Bildirimler','bell'],
      ['privacy','Gizlilik','shield'],['security','Güvenlik','lock'],['data','Veri & Depolama','database'],['help','Yardım','circle-help']];
    var faq=[['Kariyer testi nasıl çalışır?','12 soruluk test ilgi alanlarını, yeteneklerini ve çalışma tercihlerini 4 boyutta değerlendirir. Sonuçlara göre meslek önerileri uyum puanıyla sıralanır.'],
      ['Verilerim nerede saklanıyor?','Bu statik sürümde favoriler, test ilerlemesi ve tercihler yalnızca kullandığın tarayıcıda saklanır.'],
      ['AI koç gerçek yapay zekâ mı?','Koç, platformun meslek veritabanı üzerinde çalışan kural tabanlı bir asistandır. Şeffaf ve hızlı yanıt verir.'],
      ['Üyelik ücretli mi?','Hayır. Tüm temel özellikler ücretsizdir.']];
    return '<div class="px-settings-wide">'+
      '<section class="px-settings-hero"><div class="dash-hero-glow"></div><div><span class="dash-eyebrow">'+icon('settings')+' HESAP MERKEZİ</span>'+
        '<h2>Ayarlarını kişiselleştir</h2><p>Hesabın, görünümün, bildirimlerin ve gizliliğin — hepsi tek ekranda.</p></div>'+
        '<div class="px-settings-hero-side"><div class="px-setting-orb">'+icon('sliders-horizontal')+'</div><span>'+tabs.length+' bölüm · '+document.querySelectorAll('[data-pref]').length+' seçenek</span></div></section>'+
      '<nav class="px-settings-tabs" id="settingsTabs">'+tabs.map(function(t,i){
        return '<button type="button" class="'+(i===0?'active':'')+'" data-tab="'+t[0]+'">'+icon(t[2])+'<span>'+t[1]+'</span></button>';}).join('')+'</nav>'+

      '<section class="px-tabpane active" data-pane="account"><div class="px-pane-grid">'+
        '<div class="panel-card px-account-card"><div class="px-account-top"><div class="px-account-avatar" data-user-initials>AK</div><div><h3 data-user-name>Üye</h3><p data-user-email>eposta</p>'+badge('Aktif üye','success')+'</div></div>'+
          '<div class="px-setting-row"><div><b>E-posta adresi</b><small>Giriş için kullandığın adres</small></div><span data-user-email>hesap</span></div>'+
          '<div class="px-setting-row"><div><b>Üyelik tipi</b><small>Ücretsiz plan · tüm temel özellikler</small></div>'+badge('Ücretsiz','accent')+'</div>'+
          '<div class="px-setting-row"><div><b>Katılım</b><small>Hesap oluşturulma tarihi</small></div><span>'+new Date().toLocaleDateString('tr-TR')+'</span></div>'+
          '<div class="panel-actions"><a class="primary" href="/dashboard/profile.html" data-nav="profile">'+icon('user-cog')+' Profili düzenle</a><button type="button" data-logout class="danger">Çıkış yap</button></div></div>'+
        '<div><div class="panel-card">'+cardHead('Profil tamamlılığı','Daha iyi öneriler için profilini tamamla.')+
          '<div class="px-completion"><div class="px-donut" style="--pct:82"><div><b>82</b><span>/100</span></div></div><ul><li class="ok">'+icon('check')+' E-posta doğrulandı</li><li class="ok">'+icon('check')+' İlgi alanları seçildi</li><li>'+icon('circle')+' Eğitim bilgisi eklenmedi</li><li>'+icon('circle')+' Biyografi yazılmadı</li></ul></div></div>'+
          '<div class="panel-card" style="margin-top:18px">'+cardHead('Hızlı erişim','Sık kullanılan ayarlar.')+'<div class="px-quick-grid">'+
          '<a href="/dashboard/profile.html" data-nav="profile">'+icon('user')+' Profilim</a><a href="/dashboard/cv-builder.html" data-nav="cv-builder">'+icon('file-text')+' CV Oluşturucu</a></div></div></div>'+
      '</div></section>'+

      '<section class="px-tabpane" data-pane="appearance"><div class="px-pane-grid">'+
        '<div class="panel-card">'+cardHead('Tema','Görünümünü seç. Değişiklik anında uygulanır.')+
          '<div class="px-theme-cards"><button type="button" class="px-theme-card '+(theme!=='dark'?'active':'')+'" data-theme-set="light"><span class="px-theme-preview light"><i></i><i></i><i></i></span><b>☀️ Açık tema</b><small>Beyaz zemin, lacivert vurgu</small></button>'+
          '<button type="button" class="px-theme-card '+(theme==='dark'?'active':'')+'" data-theme-set="dark"><span class="px-theme-preview dark"><i></i><i></i><i></i></span><b>🌙 Koyu tema</b><small>Koyu zemin, turkuaz vurgu</small></button></div></div>'+
        '<div class="panel-card">'+cardHead('Vurgu rengi','Panelin ana rengini değiştir.')+'<div class="px-accent-grid">'+
          [['teal','#16B8B0'],['blue','#2D7DD2'],['violet','#8B5CF6'],['rose','#E05A4E'],['amber','#F0A43A']].map(function(a){
            return '<button type="button" class="px-accent'+(accent===a[0]?' active':'')+'" data-accent="'+a[0]+'" style="--sw:'+a[1]+'" title="'+a[0]+'"><span></span></button>';}).join('')+'</div>'+
          '<p class="muted">Seçtiğin renk tüm panelde anında uygulanır.</p></div>'+
        '<div class="panel-card">'+cardHead('Arayüz','Davranış tercihleri.')+
          tog('animations','Hareketli animasyonlar','Sayfa geçişleri ve mikro etkileşimler',prefs.animations!==false)+
          tog('compact','Sıkışık görünüm','Daha az boşluk, daha fazla içerik',!!prefs.compact)+'</div>'+
      '</div></section>'+

      '<section class="px-tabpane" data-pane="notifications"><div class="px-pane-grid">'+
        '<div class="panel-card">'+cardHead('Bildirim tercihleri','Sadece ilgilendiğin güncellemeleri al.')+
          tog('mailTips','E-posta ipuçları','Kariyer ipuçları ve öneriler',!!prefs.mailTips)+
          tog('newCareer','Yeni meslekler','Kataloğa eklenen meslekler',prefs.newCareer!==false)+
          tog('testResults','Test sonuçları','Yeni sonuç hazır olduğunda',prefs.testResults!==false)+
          tog('weekly','Haftalık özet','Haftalık kariyer raporu',!!prefs.weekly)+'</div>'+
        '<div class="panel-card px-info-card">'+icon('bell')+'<h3>Bildirim özeti</h3><p class="muted">Bu statik sürümde bildirimler tercih olarak saklanır; e-posta gönderimi için sunucu entegrasyonu gerekir.</p>'+badge('Demo modu','warning')+'</div>'+
      '</div></section>'+

      '<section class="px-tabpane" data-pane="privacy"><div class="px-pane-grid">'+
        '<div class="panel-card">'+cardHead('Gizlilik tercihleri','Verilerinin nasıl kullanıldığını kontrol et.')+
          tog('analytics','Kullanım analizi','Deneyimi iyileştirmek için anonim veri',prefs.analytics!==false)+
          tog('personalized','Kişiselleştirilmiş öneriler','Test sonuçlarına göre öneri göster',prefs.personalized!==false)+
          tog('cookies','Çerezler','Tema ve oturum tercihlerini hatırla',prefs.cookies!==false)+
          tog('publicProfile','Herkese açık profil','Profil bilgilerini keşfet sayfasında göster',!!prefs.publicProfile)+'</div>'+
        '<div><div class="panel-card">'+cardHead('Verilerini yönet','Yedek al veya sıfırla.')+
          '<div class="panel-actions"><button type="button" id="exportData">'+icon('download')+' Verilerimi indir (JSON)</button><button type="button" class="px-danger-btn" id="resetData">Tüm veriyi sıfırla</button></div></div>'+
          '<div class="panel-card" style="margin-top:18px">'+cardHead('Aydınlatma metni','Kişisel verilerin korunması.')+'<p class="muted">Verilerin yalnızca bu tarayıcıda tutulur, üçüncü taraflarla paylaşılmaz.</p>'+
          '<div class="panel-actions"><a href="/kvkk">KVKK metni →</a><a href="/privacy">Gizlilik politikası →</a></div></div></div>'+
      '</div></section>'+

      '<section class="px-tabpane" data-pane="security"><div class="px-pane-grid">'+
        '<div class="panel-card">'+cardHead('Güvenlik','Hesabını koruma altına al.')+
          '<div class="px-setting-row"><div><b>Şifre</b><small>Demo oturumunda şifre sunucuda doğrulanmaz</small></div>'+badge('Demo','warning')+'</div>'+
          tog('twoFactor','İki adımlı doğrulama','Ek güvenlik katmanı (demo)',!!prefs.twoFactor)+
          '<div class="px-setting-row"><div><b>Oturum süresi</b><small>Tarayıcı kapanana kadar geçerli</small></div>'+icon('clock')+'</div>'+
          '<div class="panel-actions"><button type="button" data-logout class="danger">Tüm oturumları kapat</button></div></div>'+
        '<div class="panel-card">'+cardHead('Aktif oturumlar','Bu tarayıcıdaki oturumlar.')+
          '<div class="px-session"><span class="px-session-ic">'+icon('monitor')+'</span><div><b>Bu tarayıcı</b><small>'+navigator.userAgent.slice(0,60)+'…</small></div>'+badge('Aktif','success')+'</div>'+
          '<div class="px-session"><span class="px-session-ic">'+icon('smartphone')+'</span><div><b>Mobil cihaz</b><small>Örnek oturum kaydı</small></div><button type="button" class="px-danger-btn">Kapat</button></div></div>'+
      '</div></section>'+

      '<section class="px-tabpane" data-pane="data"><div class="px-pane-grid">'+
        '<div class="panel-card">'+cardHead('Depolama kullanımı','Tarayıcında saklanan veriler.')+
          '<div class="px-storage"><div><span>Favoriler</span><b>'+favs().length+' meslek</b></div><div class="px-task-bar"><i style="width:'+Math.min(100,favs().length*10)+'%"></i></div></div>'+
          '<div class="px-storage"><div><span>Yol haritası</span><b>'+JSON.parse(localStorage.getItem('akk_roadmap')||'[]').length+' / 5 adım</b></div><div class="px-task-bar"><i style="width:'+(JSON.parse(localStorage.getItem('akk_roadmap')||'[]').length*20)+'%"></i></div></div>'+
          '<div class="px-storage"><div><span>Tercihler</span><b>'+Object.keys(prefs).length+' ayar</b></div><div class="px-task-bar"><i style="width:'+Math.min(100,Object.keys(prefs).length*12)+'%"></i></div></div></div>'+
        '<div class="panel-card">'+cardHead('Veri işlemleri','Yedekle veya temizle.')+
          '<div class="panel-actions"><button type="button" id="exportData2">'+icon('download')+' JSON indir</button><button type="button" id="resetData2" class="px-danger-btn">Sıfırla</button></div>'+
          '<p class="muted">Verilerini indirerek yedekleyebilir veya bu cihazdan tamamen silebilirsin.</p></div>'+
      '</div></section>'+

      '<section class="px-tabpane" data-pane="help"><div class="px-pane-grid">'+
        '<div class="panel-card">'+cardHead('Sık sorulan sorular','En çok merak edilenler.')+'<div class="px-faq">'+
          faq.map(function(f,i){return '<div class="px-faq-item"><button type="button" data-faq="'+i+'">'+f[0]+' <span>+</span></button><p>'+f[1]+'</p></div>';}).join('')+'</div></div>'+
        '<div class="panel-card">'+cardHead('Yardım & iletişim','Bize ulaş.')+
          '<div class="px-help-link"><i data-lucide="mail"></i><div><b>E-posta</b><small>info@akillikariyer.com</small></div></div>'+
          '<div class="px-help-link"><i data-lucide="book-open"></i><div><b>Kariyer rehberi</b><small>Meslekler ve kariyer yolları</small></div></div>'+
          '<div class="px-help-link"><i data-lucide="message-circle"></i><div><b>İletişim formu</b><small>Bize mesaj gönder</small></div></div>'+
          '<div class="panel-actions"><a class="primary" href="/contact">İletişime geç →</a></div></div>'+
      '</div></section>'+
    '</div>';
  }

JS;
file_put_contents($p, substr($s, 0, $start) . $new . substr($s, $end));
echo "settings yenilendi\n";
