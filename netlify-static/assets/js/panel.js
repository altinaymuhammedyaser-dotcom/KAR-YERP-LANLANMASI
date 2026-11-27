/* Üye paneli — premium, sayfaya özel zengin içerikler */
(function () {
  'use strict';
  var page = document.body.getAttribute('data-dashboard');
  if (!page) return;
  var careers = window.CAREER_DATA || [];
  var S = window.AKSession;

  var PAGE_BY_PATH = {
    'index.html':'index','tests.html':'tests','results.html':'results',
    'recommendations.html':'recommendations','explore.html':'explore',
    'favorites.html':'favorites','activities.html':'activities',
    'career-roadmap.html':'roadmap','skill-gap.html':'skill-gap',
    'cv-builder.html':'cv-builder','interview-sim.html':'interview-sim',
    'personality-test.html':'personality-test','profile.html':'profile',
    'settings.html':'settings','exams.html':'exams','certificates.html':'certificates',
    'notifications.html':'notifications','skills.html':'skills','leaderboard.html':'leaderboard',
    'test-compare.html':'test-compare','member-data.html':'member-data'
  };

  var TITLES = {
    index:['Genel Bakış','Kariyer yolculuğuna dair her şey tek ekranda.'],
    tests:['Kariyer Testim','Kendini keşfetmek için testleri keşfet.'],
    results:['Test Sonuçlarım','İlgi, yetenek ve uyum analizlerin.'],
    recommendations:['Meslek Önerilerim','Profiline göre uyum puanıyla sıralanmış meslekler.'],
    explore:['Meslekleri Keşfet','54 mesleği ara, filtrele ve karşılaştır.'],
    favorites:['Favorilerim','Kaydettiğin meslekler ve hızlı erişim.'],
    activities:['Aktivitelerim','Platformdaki tüm hareketlerinin geçmişi.'],
    roadmap:['Kariyer Yol Haritam','Hedeflediğin mesleğe giden adımlar.'],
    'skill-gap':['Beceri Analizim','Güçlü yönlerin ve geliştirme fırsatların.'],
    'cv-builder':['CV Oluşturucu','Profesyonel özgeçmişini oluştur ve önizle.'],
    'interview-sim':['Mülakat Simülatörü','Pratik yap, geri bildirim al, geliş.'],
    'personality-test':['Kişilik Testi','Big Five ile güçlü yönlerini keşfet.'],
    profile:['Profilim','Kariyer kimliğini oluştur.'],
    settings:['Ayarlar','Hesap, görünüm, bildirim ve gizlilik tercihleri.'],
    exams:['Sınavlar','Kısa sınavları çöz, sertifika kazan.'],
    certificates:['Sertifikalarım','Kazandığın sertifikalar.'],
    notifications:['Bildirimler','Sınav ve sistem bildirimleri.'],
    skills:['Becerilerim','Beceri düzeylerini takip et.'],
    leaderboard:['Liderlik Tablosu','En yüksek test skorları.'],
    'test-compare':['Test Karşılaştırma','Son iki denemeni karşılaştır.'],
    'member-data':['Üye Verileri','Üyelerin CV ve test cevapları']
  };

  var NAV = [
    ['index','Genel Bakış','layout-dashboard','index.html'],
    ['tests','Kariyer Testim','clipboard-check','tests.html'],
    ['results','Test Sonuçlarım','file-bar-chart','results.html'],
    ['recommendations','Meslek Önerilerim','sparkles','recommendations.html'],
    ['explore','Meslekleri Keşfet','search','explore.html'],
    ['favorites','Favorilerim','heart','favorites.html'],
    ['activities','Aktivitelerim','activity','activities.html'],
    ['sep1','','',''],
    ['roadmap','Kariyer Yol Haritam','map','career-roadmap.html'],
    ['skill-gap','Beceri Analizim','target','skill-gap.html'],
    ['cv-builder','CV Oluşturucu','file-text','cv-builder.html'],
    ['interview-sim','Mülakat Simülatörü','mic','interview-sim.html'],
    ['personality-test','Kişilik Testi','brain','personality-test.html'],
    ['exams','Sınavlar','clipboard-list','exams.html'],
    ['certificates','Sertifikalarım','award','certificates.html'],
    ['notifications','Bildirimler','bell','notifications.html'],
    ['skills','Becerilerim','layers','skills.html'],
    ['leaderboard','Liderlik','trophy','leaderboard.html'],
    ['test-compare','Test Karşılaştırma','git-compare','test-compare.html'],
    ['member-data','Üye Verileri','users','member-data.html'],
    ['sep2','','',''],
    ['profile','Profilim','user-cog','profile.html'],
    ['settings','Ayarlar','settings','settings.html']
  ];

  function h(x){ return String(x==null?'':x).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }
  function icon(n,st){ return '<i data-lucide="'+n+'"'+(st?' style="'+st+'"':' class="px-ic"')+'></i>'; }
  function refreshIcons(){ if(window.lucide) window.lucide.createIcons(); }
  function navHtml(){
    return NAV.map(function(n){
      if(n[0].indexOf('sep')===0) return '<div class="nav-sep"></div>';
      return '<a href="/dashboard/'+n[3]+'" data-nav="'+n[0]+'">'+icon(n[2])+'<span>'+n[1]+'</span></a>';
    }).join('');
  }
  function favs(){ try{return JSON.parse(localStorage.getItem('akk_favorites')||'[]');}catch(e){return [];} }
  function saveFavs(f){ localStorage.setItem('akk_favorites',JSON.stringify(f)); }
  function toggleFav(slug){ var f=favs(),i=f.indexOf(slug); if(i<0)f.push(slug);else f.splice(i,1); saveFavs(f); render(); }
  function norm(s){ return String(s||'').toLocaleLowerCase('tr-TR').replace(/ı/g,'i').replace(/ş/g,'s').replace(/ğ/g,'g').replace(/ü/g,'u').replace(/ö/g,'o').replace(/ç/g,'c'); }
  function cardHead(title,sub,action){ return '<div class="px-card-head"><div><h2>'+title+'</h2><p class="muted">'+sub+'</p></div>'+(action||'')+'</div>'; }
  function badge(text,tone){ return '<span class="px-badge '+(tone||'')+'">'+text+'</span>'; }
  function chip(text,active,attr){ return '<button type="button" class="px-chip'+(active?' active':'')+'" '+(attr||'')+'>'+text+'</button>'; }

  function careerCard(c,score){
    var fav = favs().indexOf(c.slug)!==-1;
    return '<article class="px-career">'+
      '<a class="px-career-main" href="/meslek/'+encodeURIComponent(c.slug)+'">'+
        (c.photo?'<img src="'+h(c.photo)+'" alt="" loading="lazy">':'<div class="px-career-photo">'+icon('briefcase')+'</div>')+
        '<div class="px-career-info"><b>'+h(c.name)+'</b><small>'+h(c.category)+'</small>'+
        '<span>'+h((c.summary||'').slice(0,68))+'…</span></div>'+
      '</a>'+
      '<div class="px-career-foot">'+(c.salary?'<span>'+icon('wallet')+' '+h(c.salary)+'</span>':'<span>'+icon('graduation-cap')+' '+h(c.education||'—')+'</span>')+
        (score?'<b class="px-score">%'+score+'</b>':'')+
        '<button type="button" class="px-fav'+(fav?' on':'')+'" data-fav="'+h(c.slug)+'" aria-label="Favorilere ekle">'+icon('heart')+'</button>'+
      '</div></article>';
  }
  function statCards(){
    return '<div class="panel-stats">'+
      '<div class="panel-stat"><div class="ic">'+icon('layers')+'</div><b>10</b><span>Meslek kategorisi</span></div>'+
      '<div class="panel-stat"><div class="ic">'+icon('briefcase')+'</div><b>54</b><span>Tanıtılan meslek</span></div>'+
      '<div class="panel-stat"><div class="ic">'+icon('clipboard-check')+'</div><b>12</b><span>Kariyer testi sorusu</span></div>'+
      '<div class="panel-stat"><div class="ic">'+icon('heart')+'</div><b>'+favs().length+'</b><span>Favori meslek</span></div>'+
      '</div>';
  }

  /* ---------------- Genel Bakış ---------------- */
  function renderIndex(){
    var s=S?S.get():null, hr=new Date().getHours();
    var greet=hr<12?'Günaydın':hr<18?'İyi günler':'İyi akşamlar';
    var recent=careers.slice().sort(function(){return Math.random()-.5;}).slice(0,3);
    return '<section class="dash-hero"><div class="dash-hero-glow"></div><div class="dash-hero-copy">'+
      '<span class="dash-eyebrow">'+icon('sparkles')+' KARİYER MERKEZİ</span>'+
      '<h2>'+greet+', <em>'+h(s&&s.name?s.name.split(' ')[0]:'Üye')+'</em> 👋</h2>'+
      '<p>Kariyerinde bugün bir adım daha ileri git. İlerlemen, önerilerin ve araçların hazır.</p>'+
      '<div class="panel-actions"><a class="primary" href="/career-test">'+icon('arrow-right')+' Teste devam et</a>'+
      '<a href="/dashboard/personality-test.html" data-nav="personality-test">'+icon('brain')+' Kişilik testi</a></div></div>'+
      '<div class="dash-hero-score"><span>Uyum skorun</span><b>94<small>/100</small></b><i>Yazılım Mühendisliği</i></div></section>'+
      statCards()+
      '<div class="px-two-col">'+
        '<div class="panel-card">'+cardHead('Bugünün odağı','Küçük adımlar, büyük fark.',badge('3 görev','accent'))+
          '<div class="px-task-list">'+
          task((window.TestRetry&&TestRetry.attempts('career').length)?'Kariyer testi tamamlandı':'Kariyer testine başla',(window.TestRetry&&TestRetry.attempts('career').length)?100:0,'/career-test')+
          task((window.TestRetry&&TestRetry.attempts('personality').length)?'Kişilik testi tamamlandı':'Kişilik envanterini çöz',(window.TestRetry&&TestRetry.attempts('personality').length)?100:0,'/dashboard/personality-test.html')+
          task((window.TestRetry&&TestRetry.attempts('interview').length)?'Mülakat pratiği tamamlandı':'Mülakat pratiğini yap',(window.TestRetry&&TestRetry.attempts('interview').length)?100:0,'/dashboard/interview-sim.html')+
          '</div></div>'+
        '<div class="panel-card px-coach-card">'+cardHead('AI Kariyer Koçu','Aklına takılanı anında sor.',badge('Çevrimiçi','success'))+
          '<p class="muted">Maaşlar, meslek detayları, yol haritası ve mülakat ipuçları — tek tıkla yanında.</p>'+
          '<div class="px-prompt-grid"><button data-ask-coach="Yazılımcı maaşı ne kadar?">💰 Maaşlar</button><button data-ask-coach="Bana uygun meslekleri söyle">🎯 Öneriler</button><button data-ask-coach="Mülakata nasıl hazırlanırım?">🎤 Mülakat</button></div></div>'+
      '</div>'+
      '<div class="panel-card">'+cardHead('Sana özel meslekler','İlgi alanlarına göre seçilmiş örnek öneriler.','<a class="px-link" href="/dashboard/recommendations.html" data-nav="recommendations">Tümünü gör →</a>')+
        '<div class="panel-grid">'+careers.slice(0,3).map(function(c,i){return careerCard(c,94-i*6);}).join('')+'</div></div>'+
      '<div class="px-two-col" style="margin-top:18px">'+
        '<div class="panel-card">'+cardHead('Son incelediklerin','Geçmişte göz attığın meslekler.')+'<div class="panel-grid">'+recent.map(function(c){return careerCard(c);}).join('')+'</div></div>'+
        '<div class="panel-card">'+cardHead('Hareket akışın','Son aktiviteler.')+
          '<div class="px-timeline"><div class="px-timeline-item"><span class="px-timeline-ic success">'+icon('check')+'</span><div><b>Kariyer testini tamamladın</b><small>Genel uyum %94</small></div><time>Bugün</time></div>'+
          '<div class="px-timeline-item"><span class="px-timeline-ic accent">'+icon('eye')+'</span><div><b>Yazılım Mühendisliği incelendi</b><small>Meslek detayı</small></div><time>Dün</time></div>'+
          '<div class="px-timeline-item"><span class="px-timeline-ic warning">'+icon('heart')+'</span><div><b>Yeni favori eklendi</b><small>Front-End Geliştirici</small></div><time>2 gün önce</time></div></div></div>'+
      '</div>';
  }
  function task(name,pct,link){
    return '<a class="px-task" href="'+link+'"><span class="px-task-ic">'+icon(pct===100?'check-circle':'circle')+'</span>'+
      '<span class="px-task-body"><b>'+name+'</b><span class="px-task-bar"><i style="width:'+pct+'%"></i></span></span><small>%'+pct+'</small></a>';
  }

  /* ---------------- Testler ---------------- */
  function renderTests(){
    var items=[
      ['career','clipboard-check','Kariyer Testi','İlgi, yetenek ve çalışma tercihlerini 4 boyutta analiz eder.','12 soru','~5 dk','/career-test'],
      ['personality','brain','Kişilik Testi','Big Five modeliyle kişilik profilini çıkarır, meslek önerir.','20 soru','~6 dk','/dashboard/personality-test.html'],
      ['interview','mic','Mülakat Simülatörü','Sorulara cevap ver, anlık puan ve geri bildirim al.','8 soru','~10 dk','/dashboard/interview-sim.html']
    ];
    var attemptsBy=function(k){return (window.TestRetry?TestRetry.attempts(k):[])||[];};
    var history=[];
    var cards=items.map(function(x){
      var arr=attemptsBy(x[0]);
      var done=arr.length>0;
      var last=done?arr[arr.length-1]:null;
      var status=done?'Test tamamlandı':'Teste başla';
      var tone=done?'success':'warning';
      if(done){history.push({name:x[2],date:last.date||'',score:(last.score!=null?last.score:(last.average!=null?last.average:0)),count:arr.length});}
      return '<article class="panel-card px-test-card"><div class="px-test-ic">'+icon(x[1])+'</div>'+
        '<div class="px-test-meta">'+badge(status,tone)+'</div><h2>'+x[2]+'</h2><p class="muted">'+x[3]+'</p>'+
        '<div class="px-test-facts"><span>'+icon('list-checks')+' '+x[4]+'</span><span>'+icon('clock')+' '+x[5]+'</span>'+
        (done?'<span>'+icon('check-circle')+' '+arr.length+' deneme</span>':'')+'</div>'+
        '<div class="px-task-bar"><i style="width:'+(done?100:0)+'%"></i></div>'+
        '<div class="panel-actions"><a class="primary" href="'+x[6]+'">'+status+'</a>'+(done?'<a href="/dashboard/results.html" data-nav="results">Sonuçlar</a>':'')+'</div></article>';
    }).join('');
    var historyHtml=history.length?history.map(function(r){
      return '<div class="px-history-row"><span class="px-history-ic">'+icon('check')+'</span><div><b>'+h(r.name)+'</b><small>'+r.count+' deneme · son skor %'+r.score+'</small></div><time>'+h(r.date)+'</time></div>';
    }).join(''):'<div class="px-history-row"><span class="px-history-ic alt">'+icon('compass')+'</span><div><b>Henüz test tamamlanmadı</b><small>Testleri tamamladıkça geçmişin burada oluşur.</small></div></div>';
    return '<div class="px-feature-banner"><div>'+badge('TEST MERKEZİ','accent')+'<h2>Kendini keşfetmenin en keyifli yolu</h2>'+
      '<p>Her test farklı bir yönünü ortaya çıkarır. Sonuçların profilinde birikir ve meslek önerilerini güçlendirir.</p></div>'+icon('sparkles','width:74px;height:74px;opacity:.35')+'</div>'+
      '<div class="panel-grid" style="margin-top:20px">'+cards+'</div>'+
      '<div class="panel-card" style="margin-top:18px">'+cardHead('Test geçmişin','Hesabınla tamamladığın değerlendirmeler.')+historyHtml+'</div>';
  }

  /* ---------------- Sonuçlar ---------------- */
  function renderResults(){
    var saved=null;
    try{saved=JSON.parse(localStorage.getItem('akk_test_result')||'null');}catch(e){}
    var DIM_LABEL={T:'Teknoloji İlgisi',A:'Analitik Düşünme',S:'İletişim Becerisi',Y:'Yaratıcılık'};
    var bars=saved?Object.keys(saved.scores||{}).map(function(k){return [DIM_LABEL[k]||k,saved.scores[k]];}):
      [['Teknoloji İlgisi',92],['Analitik Düşünme',88],['İletişim Becerisi',71],['Yaratıcılık',63]];
    var avg=saved?saved.average:81;
    var topCareers=(saved&&saved.careers||[]).map(function(slug){return careers.filter(function(c){return c.slug===slug;})[0];}).filter(Boolean);
    if(!topCareers.length)topCareers=careers.slice(0,3);
    var top=(saved&&saved.top)||['Teknoloji İlgisi','Analitik Düşünme'];
    var date=saved?new Date(saved.date).toLocaleDateString('tr-TR'):'—';
    var head=saved
      ? cardHead('Yetenek haritan','12 soruluk kariyer testi sonucun · '+date,badge('Gerçek sonuç','success'))
      : cardHead('Yetenek haritan','Henüz test çözmedin. Aşağıdaki örnek analizi inceleyebilirsin.',badge('Örnek','warning'));
    var extra=saved?'<div class="panel-actions" style="margin-top:16px"><a class="primary" href="/career-test">Testi tekrar çöz</a></div>':
      '<div class="panel-actions" style="margin-top:16px"><a class="primary" href="/career-test">Kariyer testini çöz</a></div>';
    return '<div class="px-two-col">'+
      '<div class="panel-card">'+head+
        '<div class="px-radar">'+bars.map(function(b){return '<div class="px-radar-row"><span>'+h(b[0])+'</span><div class="px-radar-track"><i style="width:'+b[1]+'%"></i></div><b>%'+b[1]+'</b></div>';}).join('')+'</div>'+extra+'</div>'+
      '<div class="panel-card px-score-card">'+cardHead('Genel uyum','En güçlü eşleşmen.')+
        '<div class="px-donut" style="--pct:'+avg+'"><div><b>'+avg+'</b><span>/100</span></div></div>'+
        '<h3>'+h(top[0])+'</h3><p class="muted">'+top.map(function(t){return h(t);}).join(' · ')+'</p>'+
        '<div class="panel-actions"><a class="primary" href="/dashboard/recommendations.html" data-nav="recommendations">Tüm sonuçlar</a></div></div>'+
      '</div>'+
      '<div class="px-two-col" style="margin-top:18px">'+
        '<div class="panel-card">'+cardHead('Güçlü yönlerin','Öne çıkan özellikler.')+'<div class="px-tag-cloud">'+top.map(function(t){return '<span>'+icon('check')+h(t)+'</span>';}).join('')+'</div></div>'+
        '<div class="panel-card">'+cardHead('Sana önerilen meslekler','Test sonucuna göre.')+'<div class="panel-grid" style="margin-top:10px">'+topCareers.slice(0,3).map(function(c){return careerCard(c);}).join('')+'</div></div>'+
      '</div>'+
      '<div class="panel-card" style="margin-top:18px">'+cardHead('Sonuç detayları','Boyut bazında açıklamalar.')+
        '<div class="px-detail-grid">'+bars.map(function(b){
          return '<div class="px-detail"><div class="px-detail-top"><b>'+h(b[0])+'</b><span>%'+b[1]+'</span></div><div class="px-task-bar"><i style="width:'+b[1]+'%"></i></div><p>'+(b[1]>=70?'Bu boyutta güçlü bir potansiyelin var.':b[1]>=45?'Bu alanda gelişime açık yönlerin var.':'Bu boyutu geliştirmek için pratik yapabilirsin.')+'</p></div>';
        }).join('')+'</div></div>';
  }

  /* ---------------- Öneriler ---------------- */
  function renderRecommendations(){
    return '<div class="px-feature-banner alt"><div>'+badge('AKILLI EŞLEŞTİRME','accent')+'<h2>Profiline en uygun 8 meslek</h2>'+
      '<p>Uyum puanları; ilgi alanların, yetenek sonuçların ve meslek gereksinimleri birlikte değerlendirilerek hesaplanır.</p></div>'+icon('sparkles','width:74px;height:74px;opacity:.35')+'</div>'+
      '<div class="px-filter-bar" id="recFilters"><button class="px-chip active" data-cat="all">Tümü</button>'+
        ['Teknoloji','Sağlık','Eğitim','İşletme','Mühendislik','Sanat ve Tasarım','Spor'].map(function(c,i){return chip(c,i===0,'data-cat="'+c+'"');}).join('')+'</div>'+
      '<div class="panel-card">'+cardHead('Uyum listesi','Kategoriye göre filtreleyebilirsin.')+'<div class="panel-grid" id="recList"></div></div>';
  }
  function renderRecList(cat){
    var list=careers.filter(function(c){return cat==='all'||norm(c.category)===norm(cat);}).slice(0,8);
    if(!list.length) list=careers.slice(0,8);
    var box=document.getElementById('recList'); if(!box)return;
    box.innerHTML=list.map(function(c,i){return careerCard(c,96-i*4);}).join(''); refreshIcons();
  }

  /* ---------------- Keşfet ---------------- */
  function renderExplore(){
    return '<div class="panel-card">'+cardHead('Meslek kataloğu','54 meslek · 10 kategori. İsim veya kategori ile ara.')+
      '<div class="px-search-wrap">'+icon('search')+'<input id="panelSearch" type="search" placeholder="Örn: yazılım, doktor, tasarım…" autocomplete="off"></div>'+
      '<div class="px-filter-bar" id="exploreFilters">'+['Tümü','Teknoloji','Sağlık','Eğitim','İşletme','Mühendislik','Sanat ve Tasarım','Spor','Hukuk','Bilim'].map(function(c,i){return chip(c,i===0,'data-cat="'+c+'"');}).join('')+'</div>'+
      '<div class="panel-grid" id="exploreList"></div></div>';
  }
  function renderExploreList(q,cat){
    q=norm(q||''); cat=cat||'Tümü';
    var list=careers.filter(function(c){
      var hay=norm(c.name+' '+c.category+' '+(c.summary||''));
      return hay.indexOf(q)!==-1 && (cat==='Tümü'||norm(c.category)===norm(cat));
    }).slice(0,12);
    var box=document.getElementById('exploreList'); if(!box)return;
    box.innerHTML=list.length?list.map(function(c){return careerCard(c);}).join(''):'<div class="panel-empty" style="grid-column:1/-1">'+icon('search')+'<b>Sonuç bulunamadı</b><p class="muted">Farklı bir kelime veya kategori dene.</p></div>';
    refreshIcons();
  }

  /* ---------------- Favoriler ---------------- */
  function renderFavorites(){
    var list=careers.filter(function(c){return favs().indexOf(c.slug)!==-1;});
    var html='<div class="px-feature-banner"><div>'+badge('KAYDEDİLENLER','accent')+'<h2>'+list.length+' meslek favorilerinde</h2>'+
      '<p>Favorilerin tarayıcında saklanır; meslek sayfalarındaki kalp ikonuyla yeni eklemeler yapabilirsin.</p></div>'+icon('heart','width:74px;height:74px;opacity:.35')+'</div>';
    if(!list.length){
      html+='<div class="panel-card"><div class="panel-empty"><div class="em-ic">'+icon('heart')+'</div><h2>Henüz favorin yok</h2><p class="muted">Meslek kartlarındaki kalp ikonuna dokunarak burada biriktir.</p>'+
        '<div class="panel-actions" style="justify-content:center"><a class="primary" href="/careers">Meslekleri keşfet</a><a href="/dashboard/explore.html" data-nav="explore">Panelde keşfet</a></div></div></div>';
    } else {
      html+='<div class="panel-card" style="margin-top:18px">'+cardHead('Favori mesleklerin','Hızlıca tekrar incele.')+'<div class="panel-grid">'+list.map(function(c){return careerCard(c);}).join('')+'</div></div>';
    }
    html+='<div class="panel-card" style="margin-top:18px">'+cardHead('Bunlar da ilgini çekebilir','Favorilerine benzer meslekler.')+'<div class="panel-grid">'+careers.slice(4,7).map(function(c){return careerCard(c);}).join('')+'</div></div>';
    return html;
  }

  /* ---------------- Aktiviteler ---------------- */
  function renderActivities(){
    var acts=[
      (window.TestRetry&&TestRetry.attempts('career').length)?['check','Kariyer testini tamamladın','Son deneme kaydedildi','Bugün','success']:['play','Kariyer testine başla','Henüz tamamlanmadı','—','warning'],
      ['sparkles','Meslek önerilerin güncellendi','6 yeni öneri eklendi','Bugün','accent'],
      ['eye','Yazılım Mühendisliği sayfasını ziyaret ettin','Meslek detayı · 3 dk','Dün','info'],
      ['heart','Front-End Geliştirici mesleğini favoriledin','Favorilerine eklendi','2 gün önce','warning'],
      ['brain','Kişilik testine başladın','4 / 20 soruyanıtlandı','3 gün önce','accent'],
      ['user-plus','Hesabını oluşturdun','Hoş geldin!','1 hafta önce','muted']
    ];
    return '<div class="px-two-col"><div class="panel-card">'+cardHead('Tüm hareketler','Zaman sırasına göre.',badge('6 kayıt'))+
      '<div class="px-timeline">'+acts.map(function(a){return '<div class="px-timeline-item"><span class="px-timeline-ic '+a[4]+'">'+icon(a[0])+'</span><div><b>'+a[1]+'</b><small>'+a[2]+'</small></div><time>'+a[3]+'</time></div>';}).join('')+'</div></div>'+
      '<div><div class="panel-card">'+cardHead('Bu hafta','Aktivite özeti.')+'<div class="px-mini-stats"><div><b>12</b><span>sayfa görüntüleme</span></div><div><b>3</b><span>test etkileşimi</span></div><div><b>1</b><span>yeni favori</span></div></div></div>'+
      '<div class="panel-card" style="margin-top:18px">'+cardHead('Serin','Düzenli kal, önerilerin iyileşsin.')+'<div class="px-streak">🔥 <b>3 gün</b><span>Üst üste ziyaret</span></div></div></div></div>';
  }

  /* ---------------- Yol haritası ---------------- */
  function renderRoadmap(){
    var steps=[
      ['01','Temeli öğren','İlgilendiğin mesleğin temel kavramlarını ve gereksinimlerini öğren.','1–2 ay','check-circle'],
      ['02','Pratik yap','Küçük projeler üret, örnek çalışmalar hazırla.','2–3 ay','code'],
      ['03','Portfolyo oluştur','Çalışmalarını düzenli bir portfolyoda topla.','1 ay','folder-open'],
      ['04','Sertifika al','Alanında tanınan bir sertifikayla bilgini belgele.','1–2 ay','award'],
      ['05','Başvur','Staj veya giriş seviyesi işlere başvur.','sürekli','send']
    ];
    var done=JSON.parse(localStorage.getItem('akk_roadmap')||'[]');
    return '<div class="px-feature-banner alt"><div>'+badge('HEDEF: YAZILIM MÜHENDİSLİĞİ','accent')+'<h2>5 adımlı kariyer yolun</h2>'+
      '<p>Her adımı tamamladıkça ilerlemen burada güncellenir. Tahmini süre: 6–9 ay.</p></div>'+icon('map','width:74px;height:74px;opacity:.35')+'</div>'+
      '<div class="panel-card" style="margin-top:18px">'+cardHead('Yol haritan','Adımları işaretle, ilerlemeyi gör.','<span class="px-progress-label">'+done.length+' / '+steps.length+' tamamlandı</span>')+
      '<div class="px-stepper">'+steps.map(function(s,i){
        return '<div class="px-step'+(done.indexOf(i)!==-1?' done':'')+'"><div class="px-step-no">'+s[0]+'</div>'+
          '<div class="px-step-body"><div class="px-step-top"><b>'+s[1]+'</b>'+badge(s[3])+'</div><p>'+s[2]+'</p></div>'+
          '<button type="button" class="px-step-check" data-step="'+i+'" aria-label="Adımı tamamla">'+icon(done.indexOf(i)!==-1?'check-circle':'circle')+'</button></div>';
      }).join('')+'</div></div>';
  }

  /* ---------------- Beceri analizi ---------------- */
  function renderSkillGap(){
    var skills=[['Problem çözme',88,92,'güçlü'],['Analitik düşünme',84,90,'güçlü'],['İletişim',71,85,'gelişim'],['Takım çalışması',76,88,'gelişim'],['Zaman yönetimi',64,82,'öncelik'],['Teknik bilgi',58,90,'öncelik']];
    return '<div class="px-two-col"><div class="panel-card">'+cardHead('Beceri düzeylerin','Mevcut düzey ve hedef karşılaştırması.')+
      '<div class="px-skill-list">'+skills.map(function(s){return '<div class="px-skill"><div class="px-skill-top"><b>'+s[0]+'</b>'+badge(s[3],s[3]==='öncelik'?'danger':s[3]==='gelişim'?'warning':'success')+'</div>'+
        '<div class="px-skill-track"><i class="cur" style="width:'+s[1]+'%"></i><i class="target" style="left:'+s[2]+'%"></i></div>'+
        '<div class="px-skill-foot"><span>Mevcut %'+s[1]+'</span><span>Hedef %'+s[2]+'</span></div></div>';}).join('')+'</div></div>'+
      '<div><div class="panel-card px-score-card">'+cardHead('Genel hazırlık','Hedef mesleğe göre.')+
        '<div class="px-donut" style="--pct:72"><div><b>72</b><span>/100</span></div></div><p class="muted">Teknik bilgi ve zaman yönetimi öne çıkan gelişim alanların.</p></div>'+
        '<div class="panel-card" style="margin-top:18px">'+cardHead('Önerilen kaynaklar','Sana özel içerikler.')+
        '<div class="px-resource">'+icon('book-open')+'<div><b>Problem Çözme Teknikleri</b><small>Rehber · 8 dk okuma</small></div></div>'+
        '<div class="px-resource">'+icon('video')+'<div><b>Zaman Yönetimi Masterclass</b><small>Video · 24 dk</small></div></div>'+
        '<div class="px-resource">'+icon('code')+'<div><b>Teknik Portfolyo Oluşturma</b><small>Rehber · 12 dk okuma</small></div></div></div></div></div>';
  }

  /* ---------------- CV ---------------- */
  function renderCv(){
    return '<div class="px-two-col cv-layout"><div><div class="panel-card">'+cardHead('CV bilgileri','Bilgiler tarayıcında saklanır, yazdıkça önizleme güncellenir.')+
      '<form class="panel-form" id="cvForm">'+
      '<div class="px-form-row"><label>Ad Soyad<input name="name" data-user-name placeholder="Ad Soyad"></label><label>E-posta<input name="email" data-user-email placeholder="ornek@eposta.com"></label></div>'+
      '<div class="px-form-row"><label>Telefon<input name="phone" placeholder="+90 5xx xxx xx xx"></label><label>Şehir<input name="city" placeholder="İstanbul"></label></div>'+
      '<label>Eğitim<input name="edu" placeholder="Örn: Bilgisayar Mühendisliği, Lisans"></label>'+
      '<label>Beceriler<textarea name="skills" rows="2" placeholder="Problem çözme, JavaScript, takım çalışması"></textarea></label>'+
      '<label>Özet<textarea name="summary" rows="3" placeholder="Kendini kısaca anlat"></textarea></label>'+
      '<div class="panel-actions"><button class="primary" type="submit">Önizlemeyi güncelle</button><button type="button" id="cvPrint">Yazdır / PDF</button></div></form></div>'+
      '<div class="panel-card" style="margin-top:18px">'+cardHead('CV tamamlılığı','Daha eksiksiz bir CV için ipuçları.')+
      '<div class="px-task-bar big"><i style="width:68%"></i></div><p class="muted">%68 tamamlandı. İş deneyimi ve portfolyo linki ekleyerek tamamlayabilirsin.</p></div></div>'+
      '<div class="panel-card"><div class="px-cv-paper" id="cvPreview"><div class="px-cv-head"><h3 data-user-name>AD SOYAD</h3><p data-user-email>eposta@ornek.com</p><span>Bilgisayar Mühendisliği · İstanbul</span></div>'+
      '<div class="px-cv-section"><h4>PROFİL</h4><p>Kariyer hedeflerini ve güçlü yönlerini burada anlat.</p></div>'+
      '<div class="px-cv-section"><h4>EĞİTİM</h4><p>Bilgisayar Mühendisliği, Lisans</p></div>'+
      '<div class="px-cv-section"><h4>BECERİLER</h4><p>Problem çözme · İletişim · Takım çalışması</p></div></div></div></div>';
  }

  /* ---------------- Profil ---------------- */
  function renderProfile(){
    var s=S?S.get():null;
    return '<section class="px-profile-hero"><div class="px-profile-avatar" data-user-initials>AK</div><div><span class="dash-eyebrow">ÜYE PROFİLİ</span><h2 data-user-name>Üye</h2><p data-user-email>eposta</p></div>'+
      '<div class="px-profile-score"><b>%82</b><span>Profil tamamlılığı</span></div></section>'+
      '<div class="px-two-col"><div class="panel-card">'+cardHead('Kişisel bilgiler','Profilini güncelle.')+
      '<form class="panel-form" id="profileForm"><div class="px-form-row"><label>Ad Soyad<input name="name" value="'+h(s&&s.name)+'"></label><label>E-posta<input name="email" value="'+h(s&&s.email)+'" readonly></label></div>'+
      '<div class="px-form-row"><label>Telefon<input name="phone" placeholder="+90 5xx xxx xx xx"></label><label>Şehir<input name="city" placeholder="İstanbul"></label></div>'+
      '<label>İlgi alanı<input name="interest" placeholder="Örn: Teknoloji, tasarım, spor"></label>'+
      '<label>Kısa biyografi<textarea name="bio" rows="3" placeholder="Kendini tanıt"></textarea></label>'+
      '<div class="panel-actions"><button class="primary" type="submit">Değişiklikleri kaydet</button></div></form><div id="profileMsg" class="px-success-msg"></div></div>'+
      '<div><div class="panel-card">'+cardHead('Kariyer özeti','Profilindeki veriler.')+
        '<div class="px-mini-stats"><div><b>12</b><span>test sorusu</span></div><div><b>'+favs().length+'</b><span>favori</span></div><div><b>94</b><span>uyum skoru</span></div></div></div>'+
        '<div class="panel-card" style="margin-top:18px">'+cardHead('Hızlı erişim','Sık kullandığın araçlar.')+
        '<div class="px-quick-grid"><a href="/dashboard/cv-builder.html" data-nav="cv-builder">'+icon('file-text')+'CV Oluşturucu</a><a href="/dashboard/skill-gap.html" data-nav="skill-gap">'+icon('target')+'Beceri Analizi</a><a href="/dashboard/interview-sim.html" data-nav="interview-sim">'+icon('mic')+'Mülakat Pratiği</a><a href="/dashboard/settings.html" data-nav="settings">'+icon('settings')+'Ayarlar</a></div></div></div></div>';
  }

  /* ---------------- Ayarlar ---------------- */
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
  /* ---------------- Kişilik testi ---------------- */
  var P_TRAITS=[['O','Açıklık',['Sanat ve Tasarım','Bilim','İletişim']],['C','Sorumluluk',['İşletme','Hukuk','Sağlık']],['E','Dışadönüklük',['Eğitim','Spor','İletişim']],['A','Uyumluluk',['Sağlık','Eğitim','Hukuk']],['N','Duygusal Denge',['Teknoloji','Mühendislik','Bilim']]];
  var P_Q=[['O','Yeni fikirleri keşfetmeyi severim.'],['C','Plan yapar, sözlerimi tutarım.'],['E','Kalabalıkta konuşmaktan keyif alırım.'],['A','Başkasının duygularını anlarım.'],['N','Sorunlarda sakin kalabilirim.'],['O','Sanata ve tasarıma ilgi duyarım.'],['C','Düzenli ve disiplinliyim.'],['E','Ekipte sık sık ben konuşurum.'],['A','Önce karşımdakini dinlerim.'],['N','Eleştiriden moralim bozulmaz.'],['O','Yeni yöntemler denerim.'],['C','Uzun vadeli hedeflerim için çalışırım.'],['E','Yeni insanlarla tanışmak enerji verir.'],['A','Yardıma ihtiyacı olana destek olurum.'],['N','Yoğun tempoda odaklanırım.'],['O','Detaylardaki güzellikleri fark ederim.'],['C','Hatalarımdan ders çıkarırım.'],['E','Sosyal etkinliklerde aktifim.'],['A','İş birliği bana keyif verir.'],['N','Stresli günlerde duygularımı yönetirim.']];
  var P_Q_ALT=[['O','Sıra dışı fikirler üretmek bana keyif verir.'],['C','Verdiğim sözleri mutlaka yerine getiririm.'],['E','Yeni insanlarla sohbet etmek beni mutlu eder.'],['A','Başkalarının üzüntüsünü anlamak kolaydır.'],['N','Ani değişikliklerde sakin kalırım.'],['O','Görsel ve estetik detaylara dikkat ederim.'],['C','İşlerimi planlayıp takvimle yönetirim.'],['E','Grup içinde fikrimi rahatça savunurum.'],['A','Anlaşmazlıkta ortak nokta ararım.'],['N','Olumsuz geri bildirimden yılmam.'],['O','Alışılmışın dışında yollar denerim.'],['C','Uzun süreli hedefler için istikrarlı çalışırım.'],['E','Tanımadığım insanlarla iletişim kurmaktan hoşlanırım.'],['A','Birine yardım etmek bana iyi gelir.'],['N','Baskı altında bile odaklanabilirim.'],['O','Küçük ayrıntılarda anlam ararım.'],['C','Yaptığım işi gözden geçirip iyileştiririm.'],['E','Sosyal ortamlarda enerji bulurum.'],['A','Ekip çalışması bana daha verimli gelir.'],['N','Stresli anlarda duygularımı kontrol ederim.']];
  function initPersonality(){
    if(!window.TestRetry){runPersonality(false);return;}
    TestRetry.prompt('personality','Kişilik Testi').then(function(r){
      if(r.mode==='cancel'){
        var out=document.getElementById('panelContent');
        TestRetry.showLastAttempt('personality',out,'Kişilik Testi — Önceki Cevapların',initPersonality);
        return;
      }
      runPersonality(r.different);
    });
  }
  function runPersonality(different){
    var PQ=different?P_Q_ALT:P_Q;
    var out=document.getElementById('panelContent'),ans=new Array(PQ.length).fill(null);
    out.innerHTML='<div class="px-feature-banner"><div>'+badge('BİG FIVE','accent')+'<h2>Kişilik profilini keşfet</h2><p>20 soru, 5 boyut. Sonuçlara göre sana uygun meslekleri birlikte keşfedelim.</p></div>'+icon('brain','width:74px;height:74px;opacity:.35')+'</div>'+
      '<div class="panel-card" style="margin-top:18px">'+cardHead('20 soruluk envanter','1 = hiç katılmıyorum · 5 = tamamen katılıyorum','<span id="pCount" class="px-progress-label">0 / 20</span>')+
      '<div class="px-question-grid">'+PQ.map(function(q,i){return '<div class="px-question"><b>'+(i+1)+'. '+q[1]+'</b><div class="px-scale" data-i="'+i+'">'+[1,2,3,4,5].map(function(v){return '<button type="button" data-v="'+v+'">'+v+'</button>';}).join('')+'</div></div>';}).join('')+'</div>'+
      '<div class="panel-actions"><button class="primary" id="pSubmit" disabled>Sonuçları gör</button></div></div>';
    var sb=document.getElementById('pSubmit');
    out.querySelectorAll('.px-scale').forEach(function(sc){
      sc.addEventListener('click',function(e){var b=e.target.closest('button[data-v]');if(!b)return;var i=+sc.getAttribute('data-i');ans[i]=+b.getAttribute('data-v');
        Array.prototype.forEach.call(sc.children,function(x){x.classList.remove('active');});b.classList.add('active');
        var d=ans.filter(function(x){return x!==null;}).length;document.getElementById('pCount').textContent=d+' / 20';sb.disabled=d!==20;});
    });
    sb.addEventListener('click',function(){
      var pct={};P_TRAITS.forEach(function(t){var v=[];PQ.forEach(function(q,i){if(q[0]===t[0])v.push(ans[i]);});pct[t[0]]=Math.round(v.reduce(function(a,b){return a+b;},0)/(v.length*5)*100);});
      var top=P_TRAITS.slice().sort(function(a,b){return pct[b[0]]-pct[a[0]];}).slice(0,2),rec=[];
      top.forEach(function(t){careers.forEach(function(c){if(t[2].some(function(x){return norm(x)===norm(c.category);}))rec.push(c);});});
      careers.forEach(function(c){if(rec.length<6&&!rec.some(function(x){return x.slug===c.slug;}))rec.push(c);});
      if(window.TestRetry){
        TestRetry.add('personality',{date:new Date().toLocaleString('tr-TR'),mode:different?'different':'same',
          answers:PQ.map(function(q,i){return {q:q[1],a:String(ans[i]||'-')};}),score:Math.round(Object.keys(pct).reduce(function(a,k){return a+pct[k];},0)/5)});
      }
      out.innerHTML='<div class="panel-card">'+cardHead('Kişilik profilin 🧠','Güçlü boyutların: '+top.map(function(t){return t[1];}).join(' ve '))+
        '<div class="px-radar">'+P_TRAITS.map(function(t){return '<div class="px-radar-row"><span>'+t[1]+'</span><div class="px-radar-track"><i style="width:'+pct[t[0]]+'%"></i></div><b>%'+pct[t[0]]+'</b></div>';}).join('')+'</div>'+
        '<h2 style="margin-top:24px">Sana uygun meslekler</h2><div class="panel-grid" style="margin-top:12px">'+rec.slice(0,6).map(function(c){return careerCard(c);}).join('')+'</div>'+
        '<div class="panel-actions" style="margin-top:18px"><button id="pAgain">Testi tekrar çöz</button><a class="primary" href="/career-test">Kariyer testine başla</a>'+
        (window.TestRetry&&TestRetry.attempts('personality').length>=2?'<button id="pCompare">2 denemeyi karşılaştır</button>':'')+'</div></div>';
      document.getElementById('pAgain').addEventListener('click',initPersonality);
      var pc=document.getElementById('pCompare');if(pc)pc.addEventListener('click',function(){TestRetry.compare('personality','Kişilik Testi — Deneme Karşılaştırması');});
      refreshIcons();
      if(different&&window.TestRetry&&TestRetry.attempts('personality').length>=2){setTimeout(function(){TestRetry.compare('personality','Kişilik Testi — Deneme Karşılaştırması');},500);}
    });
  }

  /* ---------------- Mülakat ---------------- */
  var I_Q=[['Mülakatı nasıl hazırlandığınızı anlatın.','Şirketi araştırdım, güçlü yönlerimi örneklerle destekledim.',85],['Bir takım çatışmasını nasıl çözdünüz?','Önce tarafları dinledim, ortak çözüm buldum.',90],['En çok zorlandığınız proje neydi?','Zaman baskısını planlama ile yönettim.',70],['Müşteri memnuniyetini nasıl artırırsınız?','İhtiyaçları anlayıp uygun çözüm sundum.',80],['Hata yaptığınızda ne yaparsınız?','Nedenini bulup düzeltir, tekrarını önlerim.',85],['Projeyi nasıl yönetirsiniz?','Hedefleri belirler, plan ve takip yaparım.',90],['Takım arkadaşının performansı düşerse?','Nedenini konuşup birlikte çözüm ararım.',80],['İş etik kuralları ne ifade ediyor?','Dürüstlük, saygı ve güvenilirlik.',95]];
  var I_Q_ALT=[['Kariyer hedefini nasıl belirledin?','İlgi alanlarımı ve güçlü yönlerimi değerlendirerek bir hedef belirledim.',85],['Zor bir teslim tarihinde ekibi nasıl yönettin?','Görevleri önceliklendirdim ve düzenli iletişim kurdum.',90],['Bir projede yaptığın en büyük hata neydi?','Hatayı kabul edip nedenini analiz ettim ve süreci düzelttim.',75],['Müşteri itirazına nasıl cevap verirsin?','Önce endişesini dinler, sonra somut çözüm sunarım.',80],['Yeni bir beceriyi nasıl öğreniyorsun?','Kaynak bulup küçük uygulamalarla pekiştiriyorum.',85],['Takım içinde anlaşmazlık yaşarsan ne yaparsın?','Tarafsız kalıp ortak çözüm ararım.',88],['Bir görevi ertelemek zorunda kaldığında?','Öncelikleri yeniden değerlendirip paydaşları bilgilendirdim.',78],['Mesleki gelişim için ne yapıyorsun?','Sertifika ve projelerle becerilerimi güncelliyorum.',92]];
  function initInterview(){
    if(!window.TestRetry){runInterview(false);return;}
    TestRetry.prompt('interview','Mülakat Simülatörü').then(function(r){
      if(r.mode==='cancel'){
        var out=document.getElementById('panelContent');
        TestRetry.showLastAttempt('interview',out,'Mülakat — Önceki Cevapların',initInterview);
        return;
      }
      runInterview(r.different);
    });
  }
  function runInterview(different){
    var IQ=different?I_Q_ALT:I_Q;
    var out=document.getElementById('panelContent'),i=0,total=0,answers=[];
    function step(){
      if(i>=IQ.length){var avg=Math.round(total/IQ.length);
        if(window.TestRetry) TestRetry.add('interview',{date:new Date().toLocaleString('tr-TR'),mode:different?'different':'same',answers:answers.slice(),score:avg});
        out.innerHTML='<div class="panel-card px-score-card" style="text-align:center"><div class="px-donut" style="--pct:'+avg+'"><div><b>'+avg+'</b><span>/100</span></div></div><h2>Mülakat tamamlandı!</h2><p class="muted">Cevaplarının ortalaması. STAR yöntemiyle cevaplarını yapılandırarak gelişebilirsin.</p><div class="panel-actions" style="justify-content:center"><button class="primary" id="iAgain">Tekrar dene</button><a href="/dashboard/tests.html" data-nav="tests">Diğer testler</a>'+
          (window.TestRetry&&TestRetry.attempts('interview').length>=2?'<button id="iCompare">2 denemeyi karşılaştır</button>':'')+'</div></div>';
        document.getElementById('iAgain').addEventListener('click',initInterview);
        var ic=document.getElementById('iCompare');if(ic)ic.addEventListener('click',function(){TestRetry.compare('interview','Mülakat — Doğru/Yanlış Karşılaştırma');});
        if(different&&window.TestRetry&&TestRetry.attempts('interview').length>=2){setTimeout(function(){TestRetry.compare('interview','Mülakat — Doğru/Yanlış Karşılaştırma');},500);}
        return;}
      var q=IQ[i];
      out.innerHTML='<div class="panel-card"><div class="px-stepper-head"><span class="dash-eyebrow">SORU '+(i+1)+' / '+IQ.length+'</span><div class="px-task-bar big"><i style="width:'+Math.round(i/IQ.length*100)+'%"></i></div></div>'+
        '<h2 style="margin:16px 0 6px">'+q[0]+'</h2><p class="muted">Cevabını yaz, puan ve örnek cevabı gör.</p>'+
        '<textarea id="iAnswer" rows="5" class="px-textarea" placeholder="Cevabını buraya yaz…"></textarea>'+
        '<div class="panel-actions"><button class="primary" id="iNext">Cevabı puanla</button><button id="iSkip">Örnek cevabı göster</button></div><div id="iFeedback"></div></div>'+
        '<div class="panel-card" style="margin-top:18px">'+cardHead('STAR yöntemi','İyi bir cevap nasıl kurulur?')+'<div class="px-star-grid"><div><b>S · Durum</b><small>Ne olduğunu anlat</small></div><div><b>T · Görev</b><small>Sorumluluğunu söyle</small></div><div><b>A · Eylem</b><small>Ne yaptığını açıkla</small></div><div><b>R · Sonuç</b><small>Ne elde ettiğini paylaş</small></div></div></div>';
      document.getElementById('iNext').addEventListener('click',function(){
        var txt=document.getElementById('iAnswer').value.trim(),len=Math.min(100,Math.max(30,Math.round(txt.length/3))),sc=txt.length?Math.round((len+q[2])/2):25;total+=sc;
        answers.push({q:q[0],a:txt||'(boş)',correct:sc>=70});
        document.getElementById('iFeedback').innerHTML='<div class="px-feedback"><b>Puanın: %'+sc+'</b><p>Örnek cevap: '+q[1]+'</p></div><div class="panel-actions" style="margin-top:12px"><button class="primary" id="iContinue">Sonraki soru →</button></div>';
        document.getElementById('iContinue').addEventListener('click',function(){i++;step();});});
      document.getElementById('iSkip').addEventListener('click',function(){document.getElementById('iAnswer').value=q[1];});
    }
    step();
  }

  /* ---------------- Sınav / Sertifika / Bildirim / Beceri / Liderlik ---------------- */
  var SLS={
    get:function(k,d){try{var v=localStorage.getItem(k);return v?JSON.parse(v):d;}catch(e){return d;}},
    set:function(k,v){try{localStorage.setItem(k,JSON.stringify(v));}catch(e){}}
  };
  function notify(title,msg,type,url){
    var a=SLS.get('akk_notifications_v1',[]);
    a.unshift({title:title,message:msg,type:type||'info',url:url||'',date:new Date().toLocaleString('tr-TR'),is_read:false});
    SLS.set('akk_notifications_v1',a.slice(0,30));
  }
  var EXAMS=[
    {id:1,title:'Temel Beceriler Sınavı',cat:'Teknoloji',min:5,pass:70,q:[
      {q:'Hangisi veritabanı yönetim sistemidir?',o:['MySQL','Photoshop','Word','Excel'],c:0},
      {q:'HTML bir programlama dili midir?',o:['Evet','Hayır'],c:1},
      {q:'CSS neyi tanımlar?',o:['Sunucu','Stil ve görünüm','Veritabanı','İşletim sistemi'],c:1}
    ]},
    {id:2,title:'İşletme ve Yönetim',cat:'İşletme',min:5,pass:70,q:[
      {q:'SWOT analizinde "S" neyi ifade eder?',o:['Strengths','Sales','Strategy','Support'],c:0},
      {q:'Pazar payı hangi alanın konusudur?',o:['Pazarlama','Üretim','Muhasebe','İK'],c:0},
      {q:'Proje planında ilk adım nedir?',o:['Hedefleri belirlemek','Hemen koda başlamak','Rapor yazmak','Toplantı iptal etmek'],c:0}
    ]},
    {id:3,title:'Sağlık Meslekleri',cat:'Sağlık',min:5,pass:70,q:[
      {q:'Diyetisyen hangi alanda çalışır?',o:['Beslenme','Makine','Hukuk','Tasarım'],c:0},
      {q:'Fizyoterapist hangi konuda yardımcı olur?',o:['Hareket ve rehabilitasyon','Maliyet','Yazılım','Pazarlama'],c:0},
      {q:'Eczacı hangi ürünleri verir?',o:['İlaç','Gıda','Giyim','Araç'],c:0}
    ]}
  ];
  function renderExams(){
    var results=SLS.get('akk_exam_results_v1',[]);
    return '<div class="px-two-col"><div class="panel-card">'+cardHead('Mevcut Sınavlar','Sınavı tamamla, sertifika kazan.')+
      EXAMS.map(function(e){
        var best=results.filter(function(r){return r.exam===e.id;}).sort(function(a,b){return b.score-a.score;})[0];
        return '<div style="border:1px solid var(--c-border);border-radius:14px;padding:15px;margin-bottom:12px">'+
          '<div style="display:flex;justify-content:space-between;gap:10px"><b style="color:var(--c-primary)">'+e.title+'</b>'+badge(e.cat)+'</div>'+
          '<div class="muted" style="font-size:.78rem;margin:6px 0 10px">'+e.q.length+' soru · '+e.min+' dakika · geçme %'+e.pass+'</div>'+
          '<div class="panel-actions"><button class="primary" data-start-exam="'+e.id+'">'+(best?'Tekrar dene':'Sınava başla')+'</button>'+
          (best?'<span class="px-badge '+(best.passed?'success':'danger')+'">En iyi %'+best.score+'</span>':'')+'</div></div>';
      }).join('')+'</div>'+
      '<div class="panel-card">'+cardHead('Sınav Rehberi','Nasıl çalışır?')+
        '<div class="px-timeline"><div class="px-timeline-item"><span class="px-timeline-ic success">'+icon('clipboard-check')+'</span><div><b>Sınavı seç</b><small>3 soruluk kısa sınavlar</small></div></div>'+
        '<div class="px-timeline-item"><span class="px-timeline-ic">'+icon('clock')+'</span><div><b>Süre içinde tamamla</b><small>Süre dolduğunda otomatik biter</small></div></div>'+
        '<div class="px-timeline-item"><span class="px-timeline-ic warning">'+icon('award')+'</span><div><b>Sertifika kazan</b><small>%70 ve üzeri geçer</small></div></div></div></div></div>';
  }
  function startExam(id){
    var e=EXAMS.filter(function(x){return x.id===id;})[0];if(!e)return;
    var out=document.getElementById('panelContent'),qi=0,score=0,left=e.min*60;
    var timer=setInterval(function(){left--;var t=document.getElementById('examTime');if(t)t.textContent=Math.floor(left/60)+':'+String(left%60).padStart(2,'0');if(left<=0){clearInterval(timer);finishExam();}},1000);
    function draw(){
      if(qi>=e.q.length){clearInterval(timer);finishExam();return;}
      var q=e.q[qi];
      out.innerHTML='<div class="panel-card"><div class="pc-head"><div><h2>'+e.title+'</h2><p class="pc-sub">Soru '+(qi+1)+' / '+e.q.length+'</p></div><span class="px-badge accent" id="examTime">'+e.min+':00</span></div>'+
        '<div class="ct-question" style="font-size:1.1rem;font-weight:700;margin-bottom:16px">'+h(q.q)+'</div>'+
        '<div class="ct-options">'+q.o.map(function(o,k){return '<button type="button" class="ct-opt" data-o="'+k+'"><span class="ct-letter">'+String.fromCharCode(65+k)+'</span><span>'+h(o)+'</span></button>';}).join('')+'</div></div>';
      out.querySelectorAll('[data-o]').forEach(function(b){b.addEventListener('click',function(){if(+b.getAttribute('data-o')===q.c)score++;qi++;draw();});});
    }
    function finishExam(){
      clearInterval(timer);
      var pct=Math.round(score/e.q.length*100),passed=pct>=e.pass;
      var results=SLS.get('akk_exam_results_v1',[]);results.unshift({exam:e.id,score:pct,passed:passed,date:new Date().toLocaleString('tr-TR')});SLS.set('akk_exam_results_v1',results.slice(0,20));
      if(passed){
        var certs=SLS.get('akk_certificates_v1',[]);
        certs.unshift({code:'AK-'+Math.random().toString(36).slice(2,10).toUpperCase(),exam:e.title,date:new Date().toLocaleString('tr-TR')});
        SLS.set('akk_certificates_v1',certs.slice(0,20));
        notify('Sertifika kazandın!',e.title+' sınavını %'+pct+' ile geçtin.','success','/dashboard/certificates.html');
      } else { notify('Sınav tamamlandı',e.title+' sınavında %'+pct+' aldın. Tekrar deneyebilirsin.','warning','/dashboard/exams.html'); }
      out.innerHTML='<div class="panel-card px-score-card" style="text-align:center"><div class="px-donut" style="--pct:'+pct+'"><div><b>'+pct+'</b><span>/100</span></div></div><h2>'+(passed?'Tebrikler, sertifika kazandın!':'Geçemedin, tekrar dene')+'</h2><p class="muted">'+e.title+' · '+score+' / '+e.q.length+' doğru</p><div class="panel-actions" style="justify-content:center"><button class="primary" data-back>Sınavlara dön</button><a href="/dashboard/certificates.html" data-nav="certificates">Sertifikalarım</a></div></div>';
      out.querySelector('[data-back]').addEventListener('click',function(){render();});
    }
    draw();
  }
  function renderCertificates(){
    var certs=SLS.get('akk_certificates_v1',[]);
    if(!certs.length)return '<div class="panel-card"><div class="empty-state" style="text-align:center;padding:40px"><div class="es-ico">'+icon('award','',40)+'</div><h3>Henüz sertifikan yok</h3><p>Sınavlardan %70 ve üzeri alarak sertifika kazanabilirsin.</p><a href="/dashboard/exams.html" data-nav="exams" class="btn btn-accent">Sınavlara git</a></div></div>';
    return '<div class="panel-card">'+cardHead('Sertifikalarım','Kazandığın sınav sertifikaları.')+'<div class="panel-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:15px">'+
      certs.map(function(c){return '<div style="border:2px solid var(--c-accent);border-radius:16px;padding:18px;background:linear-gradient(145deg,var(--c-bg-card,#fff),var(--c-accent-soft))">'+
        '<div style="display:flex;justify-content:space-between"><b style="color:var(--c-primary)">'+h(c.exam)+'</b><span class="px-badge success">Geçti</span></div>'+
        '<div style="font-size:1.1rem;font-weight:800;color:var(--c-accent-dark);margin:10px 0">'+h(c.code)+'</div>'+
        '<div class="muted" style="font-size:.75rem">'+h(c.date)+'</div>'+
        '<div class="panel-actions"><button data-cert="'+h(c.code)+'">Görüntüle / indir</button></div></div>';}).join('')+'</div></div>';
  }
  function renderNotifications(){
    var notes=SLS.get('akk_notifications_v1',[]);
    if(!notes.length)return '<div class="panel-card"><div class="empty-state" style="text-align:center;padding:40px"><div class="es-ico">'+icon('bell','',40)+'</div><h3>Henüz bildirim yok</h3><p>Sınav sonuçları ve sertifikalar burada görünecek.</p></div></div>';
    return '<div class="panel-card">'+cardHead('Bildirimler','Sınav ve sistem bildirimleri.')+'<div class="data-table-wrap"><table class="data-table"><thead><tr><th>Bildirim</th><th>İçerik</th><th>Tarih</th><th>Durum</th><th></th></tr></thead><tbody>'+
      notes.map(function(n,i){return '<tr><td><strong>'+h(n.title)+'</strong></td><td>'+h(n.message)+'</td><td class="td-nowrap"><span class="badge badge-muted">'+h(n.date)+'</span></td><td>'+(n.is_read?'<span class="badge badge-success">Okundu</span>':'<span class="badge badge-accent">Yeni</span>')+'</td><td>'+(n.is_read?'': '<button class="btn btn-outline btn-sm" data-note="'+i+'">Okundu</button>')+'</td></tr>';}).join('')+
      '</tbody></table></div></div>';
  }
  function renderSkills(){
    var attempts=window.TestRetry?TestRetry.attempts('career').length:0;
    var skills=[['Problem çözme',78],['Analitik düşünme',84],['İletişim',71],['Yaratıcılık',66],['Teknik beceri',58],['Zaman yönetimi',64]];
    return '<div class="panel-card">'+cardHead('Beceri Takibi','Güçlü yönlerin ve geliştirme alanların.')+'<div class="panel-grid" style="grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px">'+
      skills.map(function(s){return '<div class="panel-card"><div style="display:flex;justify-content:space-between"><b style="color:var(--c-primary)">'+s[0]+'</b><span class="px-badge">'+s[1]+'</span></div><div class="px-task-bar" style="margin-top:8px"><i style="width:'+s[1]+'%"></i></div></div>';}).join('')+'</div>'+
      '<div class="muted" style="font-size:.78rem;margin-top:14px">Tamamlanan kariyer testi denemesi: <strong>'+attempts+'</strong></div></div>';
  }
  function renderLeaderboard(){
    var best=0;
    if(window.TestRetry){['career','personality','interview'].forEach(function(k){TestRetry.attempts(k).forEach(function(a){best=Math.max(best,a.score||a.average||0);});});}
    var users=[['Elif Kaya',93],['Mert Yılmaz',88],['Sen',best],['Zeynep A.',76],['Can D.',71]];
    users.sort(function(a,b){return b[1]-a[1];});
    return '<div class="panel-card">'+cardHead('Liderlik Tablosu','En yüksek test skorları.')+'<div class="data-table-wrap"><table class="data-table"><thead><tr><th>Sıra</th><th>Kullanıcı</th><th>En İyi Skor</th></tr></thead><tbody>'+
      users.map(function(u,i){return '<tr><td><strong style="color:var(--c-primary)">'+(i+1)+'</strong></td><td>'+(u[0]==='Sen'?'<b style="color:var(--c-accent-dark)">Sen</b>':h(u[0]))+'</td><td><span class="badge badge-success">%'+u[1]+'</span></td></tr>';}).join('')+'</tbody></table></div></div>';
  }
  function demoOnly(){
    var s=(window.AKSession&&AKSession.get())||{};
    if(s.demo)return null;
    return '<div class="panel-card"><div class="empty-state" style="text-align:center;padding:48px 20px"><div class="es-ico">'+icon('lock-keyhole','',40)+'</div><h3>Bu sayfa sadece demo hesabına açıktır</h3><p class="muted">Üye verileri ve test karşılaştırmalarını yalnızca demo hesabı görüntüleyebilir.</p><a href="/dashboard/index.html" class="btn btn-accent">Panele dön</a></div></div>';
  }
  function renderTestCompare(){
    var denial=demoOnly();if(denial)return denial;
    var rows=['career','personality','interview'].map(function(k){
      var n=window.TestRetry?TestRetry.attempts(k).length:0;
      var label={career:'Kariyer Testi',personality:'Kişilik Testi',interview:'Mülakat Simülatörü'}[k];
      return '<div style="border:1px solid var(--c-border);border-radius:14px;padding:15px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:10px"><div><b style="color:var(--c-primary)">'+label+'</b><div class="muted" style="font-size:.76rem">'+n+' deneme kayıtlı</div></div>'+
        (n>=2?'<button class="btn btn-accent btn-sm" data-cmp="'+k+'">Karşılaştır</button>':'<span class="px-badge">En az 2 deneme gerekli</span>')+'</div>';
    }).join('');
    return '<div class="panel-card">'+cardHead('Test Karşılaştırma','Son iki denemeni soru soru karşılaştır.')+rows+'</div>';
  }

  /* ---------------- Üye verileri (demo panel) ---------------- */
  function renderMemberData(){
    var denial=demoOnly();if(denial)return denial;
    var list=(window.MemberData?MemberData.get():[]);
    if(!list.length)return '<div class="panel-card"><div class="empty-state" style="text-align:center;padding:40px"><h3>Henüz üye verisi yok</h3></div></div>';
    var esc2=function(v){return h(v==null?'':v);};
    var card=function(m){
      var cv=m.cv||{};
      var cvRows=[['Ad Soyad',cv.name],['E-posta',cv.email],['Telefon',cv.phone],['Şehir',cv.city],['Eğitim',cv.edu],['Beceriler',cv.skills],['Özet',cv.summary]]
        .filter(function(r){return r[1];}).map(function(r){return '<div style="display:flex;gap:8px;padding:7px 0;border-bottom:1px solid var(--c-border);font-size:.8rem"><b style="color:var(--c-muted);min-width:88px">'+r[0]+'</b><span style="color:var(--c-text)">'+esc2(r[1])+'</span></div>';}).join('');
      var tests=m.tests||{};
      var testHtml=Object.keys(tests).map(function(k){
        var label={career:'Kariyer Testi',personality:'Kişilik Testi',interview:'Mülakat Simülatörü'}[k]||k;
        var arr=tests[k]||[];
        return '<div style="margin-top:14px"><h4 style="margin:0 0 8px;color:var(--c-accent-dark);font-size:.85rem">'+label+' · '+arr.length+' deneme</h4>'+
          arr.map(function(a,ai){
            var rows=(a.answers||[]).map(function(q,qi){
              var mark=q.correct===true?'<span class="badge badge-success">Doğru</span>':q.correct===false?'<span class="badge badge-danger">Yanlış</span>':'<span class="badge badge-muted">Cevap</span>';
              return '<div style="padding:8px 0;border-bottom:1px solid var(--c-border)"><div style="font-size:.78rem;color:var(--c-text);font-weight:650">'+(qi+1)+'. '+esc2(q.q)+'</div><div style="display:flex;gap:8px;align-items:center;margin-top:4px"><span style="font-size:.78rem;color:var(--c-accent-dark)">'+esc2(q.a)+'</span>'+mark+'</div></div>';
            }).join('');
            return '<details style="border:1px solid var(--c-border);border-radius:12px;padding:10px;margin-bottom:8px"><summary style="cursor:pointer;font-weight:700;color:var(--c-primary);font-size:.8rem">Deneme '+(ai+1)+' · '+esc2(a.date||'')+(typeof a.score==='number'?' · %'+a.score:'')+'</summary><div style="margin-top:8px">'+rows+'</div></details>';
          }).join('')+'</div>';
      }).join('');
      return '<div class="panel-card" style="margin-bottom:16px"><div class="px-card-head"><div><h3 style="margin:0;color:var(--c-primary)">'+esc2(m.name||m.email)+'</h3><p class="muted">'+esc2(m.email||'')+'</p></div>'+(m.demo?'<span class="px-badge accent">Demo hesap</span>':'<span class="px-badge success">Gerçek üye</span>')+'</div>'+
        (m.registeredAt?'<div class="muted" style="font-size:.72rem;margin:-4px 0 12px">Kayıt: '+esc2(m.registeredAt)+'</div>':'')+
        '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px"><div><h4 style="margin:0 0 6px;color:var(--c-primary);font-size:.85rem">CV Bilgileri</h4>'+(cvRows||'<span class="muted">CV girilmemiş</span>')+'</div><div>'+(testHtml||'<p class="muted">Test cevabı yok</p>')+'</div></div></div>';
    };
    if(!window.__memberDataLiveBound){
      window.__memberDataLiveBound=true;
      window.addEventListener('storage',function(e){
        if(page!=='member-data')return;
        if(e.key==='akk_members_v1'||e.key==='akk_test_attempts_v1'||e.key==='akk_session'){
          render();
        }
      });
    }
    return '<div class="panel-card">'+cardHead('Üye Verileri','Müşterilerin CV bilgileri ve test cevapları · canlı güncellenir.','<span class="px-badge accent">'+list.length+' üye</span>')+
      list.map(card).join('')+'</div>';
  }

  /* ---------------- Ortak render ---------------- */
  function render(){
    var out=document.getElementById('panelContent');if(!out)return;
    var map={index:renderIndex,tests:renderTests,results:renderResults,recommendations:renderRecommendations,
      explore:renderExplore,favorites:renderFavorites,activities:renderActivities,roadmap:renderRoadmap,
      'skill-gap':renderSkillGap,'cv-builder':renderCv,'personality-test':initPersonality,
      'interview-sim':initInterview,profile:renderProfile,settings:renderSettings,
      exams:renderExams,certificates:renderCertificates,notifications:renderNotifications,
      skills:renderSkills,leaderboard:renderLeaderboard,'test-compare':renderTestCompare,'member-data':renderMemberData};
    var rendered=(map[page]||renderIndex)();
    if(rendered!==undefined) out.innerHTML=rendered;
    var t=TITLES[page]||TITLES.index;
    var ti=document.getElementById('panelTitle'),su=document.getElementById('panelSubtitle');
    if(ti)ti.textContent=t[0];if(su)su.textContent=t[1];
    refreshIcons();bindPage();
  }

  function bindPage(){
    var s=document.getElementById('panelSearch');
    if(s){s.addEventListener('input',function(){renderExploreList(s.value,document.querySelector('#exploreFilters .active').getAttribute('data-cat'));});renderExploreList('','Tümü');}
    document.querySelectorAll('#exploreFilters .px-chip').forEach(function(b){b.addEventListener('click',function(){
      document.querySelectorAll('#exploreFilters .px-chip').forEach(function(x){x.classList.remove('active');});b.classList.add('active');
      renderExploreList(document.getElementById('panelSearch').value,b.getAttribute('data-cat'));});});
    if(page==='recommendations'){renderRecList('all');
      document.querySelectorAll('#recFilters .px-chip').forEach(function(b){b.addEventListener('click',function(){
        document.querySelectorAll('#recFilters .px-chip').forEach(function(x){x.classList.remove('active');});b.classList.add('active');renderRecList(b.getAttribute('data-cat'));});});}
    document.querySelectorAll('[data-fav]').forEach(function(b){b.addEventListener('click',function(){toggleFav(b.getAttribute('data-fav'));});});
    document.querySelectorAll('[data-ask-coach]').forEach(function(b){b.addEventListener('click',function(e){e.preventDefault();if(window.AI_COACH){window.AI_COACH.open();window.AI_COACH.ask(b.getAttribute('data-ask-coach'));}});});
    document.querySelectorAll('[data-theme-set]').forEach(function(b){b.addEventListener('click',function(){
      var v=b.getAttribute('data-theme-set');
      if(v==='dark'){document.documentElement.setAttribute('data-theme','dark');localStorage.setItem('akk-theme','dark');document.cookie='akk_theme=dark; path=/; max-age=31536000';}
      else{document.documentElement.removeAttribute('data-theme');localStorage.setItem('akk-theme','light');document.cookie='akk_theme=light; path=/; max-age=31536000';}
      render();});});
    document.querySelectorAll('[data-step]').forEach(function(b){b.addEventListener('click',function(){
      var d=JSON.parse(localStorage.getItem('akk_roadmap')||'[]'),i=+b.getAttribute('data-step'),k=d.indexOf(i);
      if(k<0)d.push(i);else d.splice(k,1);localStorage.setItem('akk_roadmap',JSON.stringify(d));render();});});
    document.querySelectorAll('[data-pref]').forEach(function(b){b.addEventListener('click',function(){
      var p=JSON.parse(localStorage.getItem('akk_prefs')||'{}'),k=b.getAttribute('data-pref');p[k]=!p[k];localStorage.setItem('akk_prefs',JSON.stringify(p));
      b.classList.toggle('on');b.setAttribute('aria-pressed',String(!!p[k]));});});
    document.querySelectorAll('#settingsTabs [data-tab]').forEach(function(b){b.addEventListener('click',function(){
      document.querySelectorAll('#settingsTabs [data-tab]').forEach(function(x){x.classList.remove('active');});b.classList.add('active');
      document.querySelectorAll('.px-tabpane').forEach(function(p){p.classList.toggle('active',p.getAttribute('data-pane')===b.getAttribute('data-tab'));});});});
    document.querySelectorAll('[data-accent]').forEach(function(b){b.addEventListener('click',function(){
      var key=b.getAttribute('data-accent');
      var pal={teal:['#16B8B0','#0E918B','#E4F7F6'],blue:['#2D7DD2','#1B5EA8','#E9F2FC'],violet:['#8B5CF6','#6D3FD4','#F1EBFE'],rose:['#E05A4E','#B83E34','#FDECEA'],amber:['#F0A43A','#C98217','#FEF4E4']}[key];
      if(pal){document.documentElement.style.setProperty('--c-accent',pal[0]);document.documentElement.style.setProperty('--c-accent-dark',pal[1]);document.documentElement.style.setProperty('--c-accent-soft',pal[2]);localStorage.setItem('akk-accent',key);}
      document.querySelectorAll('[data-accent]').forEach(function(x){x.classList.remove('active');});b.classList.add('active');
    });});
    function exportData(){var o={session:S.get(),favorites:favs(),roadmap:JSON.parse(localStorage.getItem('akk_roadmap')||'[]'),prefs:JSON.parse(localStorage.getItem('akk_prefs')||'{}')};
      var a=document.createElement('a');a.href=URL.createObjectURL(new Blob([JSON.stringify(o,null,2)],{type:'application/json'}));a.download='akilli-kariyer-verilerim.json';a.click();URL.revokeObjectURL(a.href);}
    ['exportData','exportData2'].forEach(function(id){var b=document.getElementById(id);if(b)b.addEventListener('click',exportData);});
    ['resetData','resetData2'].forEach(function(id){var b=document.getElementById(id);if(b)b.addEventListener('click',function(){
      ['akk_favorites','akk_roadmap','akk_prefs','akk-accent'].forEach(function(k){localStorage.removeItem(k);});
      b.textContent='Temizlendi ✓';setTimeout(function(){b.textContent=id==='resetData2'?'Sıfırla':'Tüm veriyi sıfırla';},1800);});});
    document.querySelectorAll('[data-faq]').forEach(function(b){b.addEventListener('click',function(){
      var item=b.parentElement,open=item.classList.toggle('open');
      document.querySelectorAll('.px-faq-item').forEach(function(x){if(x!==item)x.classList.remove('open');});
    });});
    var accent=localStorage.getItem('akk-accent');
    if(accent&&accent!=='teal'){var pal={blue:['#2D7DD2','#1B5EA8','#E9F2FC'],violet:['#8B5CF6','#6D3FD4','#F1EBFE'],rose:['#E05A4E','#B83E34','#FDECEA'],amber:['#F0A43A','#C98217','#FEF4E4']}[accent];
      if(pal){document.documentElement.style.setProperty('--c-accent',pal[0]);document.documentElement.style.setProperty('--c-accent-dark',pal[1]);document.documentElement.style.setProperty('--c-accent-soft',pal[2]);}}
    var cv=document.getElementById('cvForm');if(cv)cv.addEventListener('submit',function(e){e.preventDefault();var d=new FormData(cv);
      var pv=document.getElementById('cvPreview');
      pv.querySelector('.px-cv-head h3').textContent=(d.get('name')||'AD SOYAD').toLocaleUpperCase('tr-TR');
      pv.querySelector('.px-cv-head p').textContent=d.get('email')||'eposta@ornek.com';
      var ps=pv.querySelectorAll('.px-cv-section p');
      ps[0].textContent=d.get('summary')||'Kariyer hedeflerini ve güçlü yönlerini burada anlat.';
      ps[1].textContent=d.get('edu')||'Eğitim bilgisi eklenmedi.';
      ps[2].textContent=(d.get('skills')||'Beceri eklenmedi.').split(',').map(function(x){return x.trim();}).filter(Boolean).join(' · ');
      var s=S.get()||{};s.name=d.get('name')||s.name;S.set(s);
      document.querySelectorAll('[data-user-name]').forEach(function(n){n.textContent=s.name;});
      if(window.MemberData){
        MemberData.saveCV({name:d.get('name')||'',email:d.get('email')||'',phone:d.get('phone')||'',city:d.get('city')||'',
          edu:d.get('edu')||'',skills:d.get('skills')||'',summary:d.get('summary')||''});
      }
      window.AK && window.AK.toast && window.AK.toast('CV kaydedildi ve üye verilerine eklendi.','success');
    });
    var pr=document.getElementById('cvPrint');if(pr)pr.addEventListener('click',function(){window.print();});
    var pf=document.getElementById('profileForm');if(pf)pf.addEventListener('submit',function(e){e.preventDefault();var d=new FormData(pf),s=S.get()||{};
      s.name=d.get('name')||s.name;S.set(s);
      document.querySelectorAll('[data-user-name]').forEach(function(n){n.textContent=s.name;});
      document.getElementById('profileMsg').textContent='✓ Değişiklikler kaydedildi.';});
    document.querySelectorAll('[data-start-exam]').forEach(function(b){b.addEventListener('click',function(){startExam(+b.getAttribute('data-start-exam'));});});
    document.querySelectorAll('[data-note]').forEach(function(b){b.addEventListener('click',function(){var a=SLS.get('akk_notifications_v1',[]),i=+b.getAttribute('data-note');if(a[i]){a[i].is_read=true;SLS.set('akk_notifications_v1',a);render();}});});
    document.querySelectorAll('[data-cmp]').forEach(function(b){b.addEventListener('click',function(){if(window.TestRetry){var k=b.getAttribute('data-cmp');var t={career:'Kariyer Testi',personality:'Kişilik Testi',interview:'Mülakat'}[k];TestRetry.compare(k,t+' — Deneme Karşılaştırması');}});});
    document.querySelectorAll('[data-cert]').forEach(function(b){b.addEventListener('click',function(){
      var code=b.getAttribute('data-cert'),c=SLS.get('akk_certificates_v1',[]).filter(function(x){return x.code===code;})[0];if(!c)return;
      var u=window.URL||window.webkitURL,blob=new Blob(['<!DOCTYPE html><html><head><meta charset="utf-8"><title>Sertifika</title></head><body><h1>Akıllı Kariyer — Başarı Sertifikası</h1><h2>'+c.exam+'</h2><p>Sertifika kodu: '+c.code+'</p><p>Tarih: '+c.date+'</p></body></html>'],{type:'text/html'});
      var a=document.createElement('a');a.href=u.createObjectURL(blob);a.download='sertifika-'+code+'.html';a.click();u.revokeObjectURL(a.href);
    });});
  }

  function fileFor(key){for(var f in PAGE_BY_PATH)if(PAGE_BY_PATH[f]===key)return f;return 'index.html';}
  function animatePage(){var m=document.querySelector('.panel-main');if(!m)return;m.classList.remove('page-in');void m.offsetWidth;m.classList.add('page-in');}
  function navigate(target,push){
    if(!PAGE_BY_PATH[fileFor(target)])return;
    page=target;document.body.setAttribute('data-dashboard',target);
    if(push)history.pushState({page:target},'','/dashboard/'+fileFor(target));
    var t=TITLES[target]||TITLES.index;document.title=t[0]+' — Akıllı Kariyer';
    render();
    document.querySelectorAll('[data-nav].is-active').forEach(function(a){a.classList.remove('is-active');});
    var a=document.querySelector('[data-nav="'+target+'"]');if(a)a.classList.add('is-active');
    var l=document.getElementById('panel-layout');if(l)l.classList.remove('nav-open');
    animatePage();window.scrollTo({top:0,behavior:'smooth'});
  }
  document.addEventListener('click',function(e){
    var a=e.target.closest&&e.target.closest('a[data-nav]');
    if(!a||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey||e.button!==0)return;
    var k=a.getAttribute('data-nav');if(!PAGE_BY_PATH[fileFor(k)])return;e.preventDefault();navigate(k,true);});
  window.addEventListener('popstate',function(){var f=location.pathname.split('/').pop()||'index.html';navigate(PAGE_BY_PATH[f]||'index',false);});

  if(careers.length){render();animatePage();}
  else fetch('/assets/data/careers.json').then(function(r){return r.json();}).then(function(d){careers=d;render();animatePage();}).catch(function(){render();animatePage();});
})();
