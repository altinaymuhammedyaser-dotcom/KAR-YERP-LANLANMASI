/* ==========================================================================
   AI Kariyer Koçu v2 — bağlam hatırlayan, karşılaştırma ve mini test yapabilen
   kural tabanlı profesyonel kariyer asistanı
   ========================================================================== */
(function () {
  'use strict';

  var ICON = {
    bot: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="8" width="16" height="12" rx="3"/><path d="M12 8V4M9 14h.01M15 14h.01M9.5 17h5"/></svg>',
    send: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>',
    spark: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3Z"/><path d="m19 16 .7 1.8L21.5 18l-1.8.7L19 20.5l-.7-1.8L16.5 18l1.8-.2L19 16Z"/></svg>',
    trash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>',
    down: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>',
    copy: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>'
  };

  var appBase = (window.AK && window.AK.base) || '/';
  var coachDataUrl = (window.AK && window.AK.coachData) || '/assets/data/careers.json';
  var careers = [];
  var MEM_KEY = 'akk_coach_v3';

  var mem = { lastCareer: '', lastCategory: '', name: '', history: [], feedback: {} };
  try {
    var saved = JSON.parse(localStorage.getItem(MEM_KEY) || '{}');
    Object.keys(mem).forEach(function (k) { if (saved[k] !== undefined) mem[k] = saved[k]; });
  } catch (e) {}

  var state = {
    open: false, busy: false, turns: 0, mini: null,
    awaiting: null, unread: false
  };
  var resolveData;
  var dataReady = new Promise(function (res) { resolveData = res; });

  function saveMem() {
    try {
      mem.history = mem.history.slice(-24);
      localStorage.setItem(MEM_KEY, JSON.stringify(mem));
    } catch (e) {}
  }

  function normalize(s) {
    var n = String(s || '').toLocaleLowerCase('tr-TR')
      .replace(/ı/g, 'i').replace(/ş/g, 's').replace(/ğ/g, 'g')
      .replace(/ü/g, 'u').replace(/ö/g, 'o').replace(/ç/g, 'c')
      .replace(/[^\w\s?]/g, ' ').replace(/\s+/g, ' ').trim();
    var aliases = [
      [/yazilimci|developer|kodcu/g, 'yazilim'],
      [/hekim|doktor/g, 'doktor'],
      [/ogretmen/g, 'ogretmen'],
      [/bilisimci|itci/g, 'teknoloji'],
      [/muhasebeci|muhasebe/g, 'muhasebeci'],
      [/mimar/g, 'mimar'],
      [/sicer|siber guvenlik/g, 'siber']
    ];
    aliases.forEach(function (a) { n = n.replace(a[0], a[1]); });
    return n;
  }

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }
  function u(path) {
    if (!path) return appBase;
    if (/^https?:\/\//.test(path)) return path;
    return appBase.replace(/\/$/, '') + '/' + String(path).replace(/^\//, '');
  }
  var PAGE_DEFAULTS = {
    test: 'career-test', personality: 'dashboard/personality-test.html',
    interview: 'dashboard/interview-sim.html', cv: 'dashboard/cv-builder.html',
    results: 'dashboard/results.html', profile: 'dashboard/profile.html'
  };
  function pageUrl(k) {
    var m = (window.AK && window.AK.urls) || {};
    return u(m[k] || PAGE_DEFAULTS[k] || '');
  }
  function careerUrl(slug) {
    var t = (window.AK && window.AK.careerTpl) || '';
    return t ? u(t.replace('{slug}', encodeURIComponent(slug))) : u('career-detail.php?slug=' + encodeURIComponent(slug));
  }
  function link(href, text) {
    var a = el('a', 'ac-link', text);
    a.href = href;
    return a;
  }

  /* ---------------- Arayüz iskeleti ---------------- */
  var root = el('div');
  root.innerHTML =
    '<div class="ac-fab-wrap">' +
      '<span class="ac-fab-tip">AI Kariyer Koçun</span>' +
      '<button type="button" class="ac-fab" aria-label="AI Kariyer Koçunu aç" aria-expanded="false">' + ICON.bot + '<span class="ac-fab-pulse"></span><span class="ac-fab-badge"></span></button>' +
    '</div>' +
    '<section class="ac-panel" role="dialog" aria-label="AI Kariyer Koçu">' +
      '<header class="ac-head">' +
        '<div class="ac-avatar">' + ICON.bot + '<span class="ac-avatar-ring"></span></div>' +
        '<div class="ac-head-text"><div class="ac-title">AI Kariyer Koçu <span class="ac-pro">PRO</span></div>' +
          '<div class="ac-status"><i></i> Çevrimiçi · meslek veritabanı bağlı</div></div>' +
        '<div class="ac-head-actions">' +
          '<button type="button" class="ac-ico-btn" data-act="clear" title="Sohbeti temizle" aria-label="Sohbeti temizle">' + ICON.trash + '</button>' +
          '<button type="button" class="ac-ico-btn" data-act="close" title="Kapat" aria-label="Kapat">✕</button>' +
        '</div>' +
      '</header>' +
      '<div class="ac-body-wrap">' +
        '<div class="ac-body" aria-live="polite"></div>' +
        '<button type="button" class="ac-scroll-down" title="En alta git" aria-label="En alta git">' + ICON.down + '</button>' +
      '</div>' +
      '<div class="ac-chips"></div>' +
      '<div class="ac-foot">' +
        '<form class="ac-form"><input class="ac-input" type="text" placeholder="Bir meslek, maaş veya kariyer sorusu yaz…" maxlength="300" autocomplete="off" aria-label="Mesajınız">' +
        '<button class="ac-send" type="submit" aria-label="Gönder">' + ICON.send + '</button></form>' +
        '<div class="ac-hint">Enter ile gönder · koç bilgilendirme amaçlıdır</div>' +
      '</div>' +
    '</section>';
  document.body.appendChild(root);

  var fab = root.querySelector('.ac-fab');
  var panel = root.querySelector('.ac-panel');
  var body = root.querySelector('.ac-body');
  var chipsBox = root.querySelector('.ac-chips');
  var form = root.querySelector('.ac-form');
  var input = root.querySelector('.ac-input');
  var scrollBtn = root.querySelector('.ac-scroll-down');
  var badge = root.querySelector('.ac-fab-badge');

  function scrollBottom(force) {
    if (force || body.scrollHeight - body.scrollTop - body.clientHeight < 160) {
      body.scrollTop = body.scrollHeight;
      scrollBtn.classList.remove('show');
    } else {
      scrollBtn.classList.add('show');
    }
  }
  body.addEventListener('scroll', function () {
    if (body.scrollHeight - body.scrollTop - body.clientHeight > 160) scrollBtn.classList.add('show');
    else scrollBtn.classList.remove('show');
  });
  scrollBtn.addEventListener('click', function () { scrollBottom(true); });

  function appendRich(node, str) {
    String(str).split(/(\*\*[^*]+\*\*)/g).forEach(function (part) {
      if (!part) return;
      if (part.indexOf('**') === 0 && part.lastIndexOf('**') === part.length - 2) {
        node.appendChild(el('b', '', part.slice(2, -2)));
      } else {
        node.appendChild(document.createTextNode(part));
      }
    });
  }

  function rp(text) { var p = el('p'); appendRich(p, text); return p; }

  function addMessage(text, who, extra, opts) {
    opts = opts || {};
    var wrap = el('div', 'ac-msg ac-msg--' + (who === 'user' ? 'user' : 'bot'));
    var bubble = el('div', 'ac-bubble');
    if (typeof text === 'string') appendRich(bubble, text);
    else if (text && typeof text.nodeType === 'number') bubble.appendChild(text);
    if (extra) bubble.appendChild(extra);
    wrap.appendChild(bubble);
    var meta = el('span', 'ac-meta');
    meta.textContent = (who === 'user' ? 'Sen' : 'AI Koç') + ' · ' + new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
    if (who === 'bot' && !opts.noActions) {
      var acts = el('span', 'ac-msg-actions');
      ['up', 'down'].forEach(function (k) {
        var b = el('button', 'ac-rate', k === 'up' ? '👍' : '👎');
        b.type = 'button';
        b.title = k === 'up' ? 'Yanıtı faydalı buldum' : 'Yanıtı geliştirebilir';
        b.addEventListener('click', function () {
          mem.feedback[mem.history.length] = k;
          saveMem();
          acts.querySelectorAll('button').forEach(function (x) { x.classList.remove('on'); });
          b.classList.add('on');
        });
        acts.appendChild(b);
      });
      var cp = el('button', 'ac-rate', '⧉');
      cp.type = 'button'; cp.title = 'Yanıtı kopyala';
      cp.addEventListener('click', function () {
        var t = bubble.innerText;
        if (navigator.clipboard) navigator.clipboard.writeText(t);
      });
      acts.appendChild(cp);
      meta.appendChild(acts);
    }
    wrap.appendChild(meta);
    body.appendChild(wrap);
    scrollBottom();
    return wrap;
  }

  function setChips(items) {
    chipsBox.innerHTML = '';
    (items || []).slice(0, 4).forEach(function (c) {
      var label = typeof c === 'string' ? c : c.label;
      var b = el('button', 'ac-chip', label);
      b.type = 'button';
      if (typeof c !== 'string' && c.icon) b.insertAdjacentHTML('afterbegin', c.icon + ' ');
      b.addEventListener('click', function () { ask(label); });
      chipsBox.appendChild(b);
    });
  }

  function cardGrid(list, scoreFn) {
    var box = el('div', 'ac-cards');
    list.slice(0, 3).forEach(function (c, i) {
      var a = el('a', 'ac-card');
      a.href = careerUrl(c.slug);
      if (c.photo) {
        var img = document.createElement('img');
        img.src = c.photo; img.alt = ''; img.loading = 'lazy';
        img.onerror = function () { img.style.visibility = 'hidden'; };
        a.appendChild(img);
      } else {
        a.appendChild(el('span', 'ac-card-photo', String(c.name || '?').slice(0, 1)));
      }
      var info = el('div', 'ac-card-info');
      info.appendChild(el('b', '', c.name));
      info.appendChild(el('small', '', [c.category, c.salary].filter(Boolean).join(' · ').slice(0, 62)));
      a.appendChild(info);
      if (scoreFn) a.appendChild(el('span', 'ac-card-score', scoreFn(c, i)));
      else a.appendChild(el('span', 'ac-card-go', '›'));
      box.appendChild(a);
    });
    return box;
  }

  /* ---------------- Arama / bilgi ---------------- */
  function searchCareer(q) {
    var n = normalize(q);
    if (!n) return [];
    var words = n.split(' ').filter(function (w) { return w.length > 2; });
    return careers.map(function (c) {
      var name = normalize(c.name), cat = normalize(c.category), sum = normalize(c.summary);
      var score = 0;
      if (name === n) score += 120;
      if (name.indexOf(n) !== -1) score += 75;
      words.forEach(function (w) {
        if (name.indexOf(w) !== -1) score += 26;
        if (cat.indexOf(w) !== -1) score += 13;
        if (sum.indexOf(w) !== -1) score += 4;
      });
      return { c: c, s: score };
    }).filter(function (x) { return x.s > 0; })
      .sort(function (a, b) { return b.s - a.s; })
      .slice(0, 6).map(function (x) { return x.c; });
  }

  var CATEGORY_MAP = [
    [/teknoloji|yazilim|bilisayar|kod|it|siber|veri|yapay|web/, 'Teknoloji'],
    [/saglik|doktor|hemsire|eczane|diyet|fizyo|tip/, 'Sağlık'],
    [/egitim|ogretmen|okul|universite|akademik/, 'Eğitim'],
    [/isletme|finans|muhasebe|banka|pazarlama|girisim|lojistik|insan kaynaklari/, 'İşletme'],
    [/muhendis|insaat|cevre|gida|elektrik|makine|havacilik|uretim/, 'Mühendislik'],
    [/sanat|tasarim|mimar|fotograf|animasyon|gorsel|medya/, 'Sanat ve Tasarım'],
    [/hukuk|avukat|noter|savci|adalet/, 'Hukuk'],
    [/spor|antrenor|futbol|beden/, 'Spor'],
    [/iletisim|gazete|haber|sosyal medya|kriz|icerik/, 'İletişim'],
    [/bilim|arastirma|laboratuvar|fizik|kimya|meteoroloji|matematik/, 'Bilim']
  ];
  function detectCategory(n) {
    for (var i = 0; i < CATEGORY_MAP.length; i++) {
      if (CATEGORY_MAP[i][0].test(n)) return CATEGORY_MAP[i][1];
    }
    return null;
  }
  function careersIn(cat) {
    var c = normalize(cat);
    return careers.filter(function (x) { return normalize(x.category) === c; });
  }
  function findCareer(name) {
    if (!name) return null;
    var list = searchCareer(name);
    if (!list.length) return null;
    var n = normalize(name);
    return list.filter(function (c) { return normalize(c.name).indexOf(n) !== -1; })[0] || list[0];
  }
  function strictCareer(name) {
    if (!name) return null;
    var n = normalize(name);
    return searchCareer(name).filter(function (c) {
      var cn = normalize(c.name);
      return cn.indexOf(n) !== -1 || (n.indexOf(cn) !== -1 && cn.length > 4);
    })[0] || null;
  }
  function careerInQuery(q) {
    var n = normalize(q), best = null, bestLen = 0;
    careers.forEach(function (c) {
      var cn = normalize(c.name);
      if (cn.length > 3 && n.indexOf(cn) !== -1 && cn.length > bestLen) { best = c; bestLen = cn.length; }
    });
    if (!best && /yazilim/.test(n)) best = findCareer('yazilim');
    if (!best && /doktor|hekim/.test(n)) best = findCareer('doktor');
    if (!best && /muhasebe/.test(n)) best = findCareer('muhasebeci');
    return best;
  }
  function contextCareer(q) {
    var found = strictCareer(q);
    if (found) return found;
    return mem.lastCareer ? findCareer(mem.lastCareer) : null;
  }
  function rememberCareer(c) {
    if (!c) return;
    mem.lastCareer = c.name; mem.lastCategory = c.category; saveMem();
  }
  function educationLabel(v) {
    return { lise: 'Lise', universite: 'Üniversite', ortaokul: 'Ortaokul', diger: 'Diğer' }[v] || v || 'Belirtilmemiş';
  }

  /* ---------------- Yanıt motoru ---------------- */
  function answer(q) {
    var raw = String(q || '').trim();
    var n = normalize(raw);

    if (/^(merhaba|selam|hi|hey|gunaydin|iyi aksamlar|iyi gunler|selamlarik)\b/.test(n)) {
      return { text: 'Merhaba' + (mem.name ? ' ' + mem.name : '') + '! 👋 Ben AI Kariyer Koçu. 54 meslek, maaş aralığı, eğitim gereksinimi ve kariyer yolları hakkında sorabilirsin.\n\nBir meslek adı yaz ya da aşağıdaki hızlı seçeneklerden birini dene.', chips: ['Bana uygun meslekleri söyle', 'Yazılımcı maaşı ne kadar?', 'Günün mesleği', 'Mini kariyer testi'] };
    }
    if (/tesekkur|sag ol|eyvallah|tevhikkur/.test(n)) return { text: 'Rica ederim! 🌱 İstersen mini kariyer testiyle yönünü netleştirebilir veya bir mesleği diğeriyle karşılaştırabiliriz.', chips: ['Mini kariyer testi', 'Meslekleri karşılaştır', 'Mülakata hazırlan'] };
    if (/kimsin|ne yaparsin|yeteneklerin|yardim|help|nasil calisir/.test(n)) {
      var b = el('div');
      b.appendChild(el('p', '', 'Ben bu platformun kariyer asistanıyım. Meslek veritabanını kullanarak:\n• meslek, maaş ve eğitim sorularını yanıtlarım,\n• iki mesleği karşılaştırırım,\n• mini testle yön öneririm,\n• yol haritası ve mülakat ipuçları veririm.'));
      b.appendChild(el('p', '', 'Yapay zekâ görünümlü şeffaf bir kural motoruyum; veriler platformun meslek kataloğundan gelir. Yanıtları 👍/👎 ile değerlendirebilirsin.'));
      return { node: b, chips: ['Meslekleri karşılaştır', 'Mini kariyer testi', 'Kariyer testi nasıl çalışır?'] };
    }

    if (/adim|isim|benim adim/.test(n) && !mem.name) {
      var m = raw.match(/(?:adım|adim|benim adım|isim)\s+([A-Za-zÇĞİÖŞÜçğıöşü]{2,20})/i);
      if (m) { mem.name = m[1]; saveMem(); return { text: 'Tanışmak güzel, ' + mem.name + '! 😊 Sana özel öneriler sunabilirim. Hangi alanı merak ediyorsun?', chips: ['Teknoloji', 'Sağlık', 'Bana uygun meslekleri söyle'] }; }
    }

    if (/test sonucum|sonucuma gore|sonucumu yorumla|kariyer testi sonucum/.test(n)) {
      var saved = null;
      try { saved = JSON.parse(localStorage.getItem('akk_test_result') || 'null'); } catch (e) {}
      if (saved && saved.scores) {
        var recs = (saved.careers || []).map(function (s) { return careers.filter(function (c) { return c.slug === s; })[0]; }).filter(Boolean);
        var sb = el('div');
        sb.appendChild(rp('Kayıtlı kariyer testi sonucuna göre güçlü yönlerin: **' + (saved.top || []).join(' ve ') + '**. Genel uyumun: **%' + saved.average + '**.'));
        if (recs.length) sb.appendChild(cardGrid(recs));
        sb.appendChild(link(pageUrl('results'), '→ Paneldeki sonuçlarımı aç'));
        return { node: sb, chips: ['Bu meslekleri karşılaştır', 'Maaşlar ne kadar?', 'Nasıl olurum?'] };
      }
      return { text: 'Henüz kayıtlı bir kariyer testi sonucun yok. Testi çözüp sonucunu kaydettiğinde buradan sana özel yorumlayabilirim.', chips: ['Kariyer testine başla', 'Mini kariyer testi', 'Meslek öner'] };
    }

    if (/mini test|mini kariyer testi|hizli test|kisa test|hangisi bana uygun|ne yapmaliyim|yon bul/.test(n)) return startMiniQuiz();

    if (/gunun meslegi|rastgele meslek|s urpriz|surpriz|bana bir meslek oner/.test(n)) {
      var pick = careers[Math.floor(Math.random() * careers.length)];
      if (pick) {
        rememberCareer(pick);
        var box = el('div');
        box.appendChild(rp('🎲 Günün mesleği: **' + pick.name + '**\n\n' + (pick.summary || '') + '\n\nAlan: ' + pick.category + (pick.salary ? ' · Maaş: ' + pick.salary : '')));
        box.appendChild(cardGrid([pick]));
        box.appendChild(link(careerUrl(pick.slug), '→ Mesleği detaylı incele'));
        return { node: box, chips: ['Nasıl olurum?', 'Maaşı ne kadar?', 'Benzer meslekler'] };
      }
    }

    if (/karsilastir|kıyasla|karsila|vs\b|versus|ikisi arasinda/.test(n)) {
      var names = n.split(/\s+(?:ile|ve|vs|versus|veya)\s+/);
      var a = findCareer(names[0]) || contextCareer(q);
      var b = findCareer(names[1] || names[0]) || null;
      if (!b && mem.lastCareer) b = findCareer(mem.lastCareer);
      if (a && b && a.slug !== b.slug) return compareView(a, b);
      return { text: 'İki mesleği karşılaştırmak için adlarını yaz. Örn: “Yazılım Mühendisi ile Veri Bilimci karşılaştır”.', chips: ['Yazılımcı ile Veri Bilimci karşılaştır', 'Doktor ile Eczacı karşılaştır', 'Günün mesleği'] };
    }

    if (/favori|kaydettiklerim/.test(n)) {
      var box2 = el('div');
      box2.appendChild(el('p', '', 'Favorilerin panelindeki **Favorilerim** sayfasında saklanıyor. Meslek kartlarındaki kalp ikonuyla yeni meslekler ekleyebilirsin.'));
      box2.appendChild(link(pageUrl('profile'), '→ Profile git'));
      return { node: box2, chips: ['Meslek öner', 'Bilişim meslekleri', 'Kariyer testi'] };
    }

    if (/kariyer testi|test nasil|12 soru/.test(n)) {
      var t = el('div');
      t.appendChild(el('p', '', '12 soruluk kariyer testi; ilgi, yetenek, çalışma tercihi ve çalışma ortamı olmak üzere 4 boyutu ölçer. Sonuçta meslekler uyum puanıyla sıralanır. Üye olduktan sonra sonuçların panelinde saklanır.'));
      t.appendChild(link(pageUrl('test'), '→ Kariyer testine başla'));
      return { node: t, chips: ['Mini kariyer testi', 'Kişilik testi', 'Meslek öner'] };
    }
    if (/kişilik|kisilik|big five|personality/.test(n)) {
      var p = el('div');
      p.appendChild(el('p', '', 'Big Five modeli; açıklık, sorumluluk, dışadönüklük, uyumluluk ve duygusal denge boyutlarını ölçer. 20 soruluk test sonunda meslek önerileri üretir.'));
      p.appendChild(link(pageUrl('personality'), '→ Kişilik testini çöz'));
      return { node: p, chips: ['Kariyer testi', 'Mini test', 'Mülakat ipuçları'] };
    }

    if (/mulakat|görüşme|gorusme|interview/.test(n)) {
      var iv = el('div');
      iv.appendChild(el('p', '', 'Mülakat simülatöründe 8 soruya cevap verir, her cevap için puan ve örnek yanıt görürsün. 🎤\n\nSTAR yöntemini kullan: Durum → Görev → Eylem → Sonuç.'));
      iv.appendChild(link(pageUrl('interview'), '→ Simülatörü aç'));
      return { node: iv, chips: ['CV nasıl hazırlanır?', 'Kariyer yol haritası', 'Meslek öner'] };
    }
    if (/cv|ozgecmis|özkaynak|basvuru|başvuru/.test(n)) {
      var cv = el('div');
      cv.appendChild(el('p', '', 'İyi bir CV: iletişim, eğitim, deneyim ve beceriler bölümlerinden oluşur. Başvurduğun mesleğin anahtar kelimelerini beceriler kısmına ekle. 📄'));
      cv.appendChild(link(pageUrl('cv'), '→ CV oluşturucuyu aç'));
      return { node: cv, chips: ['Mülakat ipuçları', 'Meslek öner', 'Yol haritası'] };
    }

    if (/benzer|similar|alternatif|baska meslek|başka meslek/.test(n)) {
      var cur = contextCareer(q);
      if (cur) {
        var sim = careers.filter(function (c) { return c.slug !== cur.slug && c.category === cur.category; }).slice(0, 3);
        if (sim.length) {
          var sb = el('div');
          sb.appendChild(el('p', '', cur.name + ' ile aynı alandaki benzer meslekler:'));
          sb.appendChild(cardGrid(sim));
          return { node: sb, chips: ['Maaşları karşılaştır', 'Kariyer yol haritası', 'Kariyer testi'] };
        }
      }
    }

    if (/maas|maş|kazan|ucret|ücret|para|gelir|kac lira|kaç lira/.test(n)) {
      var salCat = detectCategory(n);
      var target = careerInQuery(q) || (!salCat && mem.lastCareer ? findCareer(mem.lastCareer) : null);
      if (!target && salCat) {
        var salList = careersIn(salCat).slice(0, 3);
        if (salList.length) {
          var sb2 = el('div');
          sb2.appendChild(el('p', '', salCat + ' alanındaki mesleklerin maaş aralıkları:'));
          sb2.appendChild(cardGrid(salList, function (c) { return c.salary ? c.salary.split(' ')[0] : '—'; }));
          return { node: sb2, chips: ['Meslekleri karşılaştır', 'Bana uygun meslekleri söyle', 'Mini kariyer testi'] };
        }
      }
      if (target) {
        rememberCareer(target);
        return { text: target.name + ' için maaş aralığı: ' + (target.salary || 'Veri bulunamadı') + '.\n\nNot: Maaş; deneyim, şehir ve sektöre göre değişir. ' + target.category + ' alanındaki diğer meslekleri de karşılaştırabilirsin.', cards: [target], chips: ['Benzer meslekler', 'Nasıl olurum?', 'Meslekleri karşılaştır'] };
      }
      var lb = el('div');
      lb.appendChild(el('p', '', 'Hangi mesleğin maaşını merak ediyorsun? Örnekler:'));
      lb.appendChild(cardGrid(careers.slice(0, 3)));
      return { node: lb, chips: ['Yazılımcı maaşı', 'Doktor maaşı', 'Öğretmen maaşı'] };
    }

    if (/egitim|eğitim|lise|universite|üniversite|okul|nasil olurum|nasıl olurum|baslamak|başlamak|roadmap|yol haritasi|adim adim/.test(n)) {
      var tc = contextCareer(q);
      if (tc) {
        rememberCareer(tc);
        var rb = el('div');
        rb.appendChild(el('p', '', tc.name + ' için örnek yol haritası:\n1. Temel bilgi: ' + educationLabel(tc.education) + ' düzeyinde eğitim.\n2. Uygulamalı projeler ve portfolyo.\n3. Staj veya giriş seviyesi deneyim.\n4. Sertifika ve güncel beceriler.\n5. Uzmanlaşma ve ileri roller.'));
        rb.appendChild(cardGrid([tc]));
        return { node: rb, chips: ['Maaşı ne kadar?', 'Benzer meslekler', 'Kişilik testi'] };
      }
      return { text: 'Hangi mesleği hedeflediğini söyle, sana adım adım yol haritası çıkarayım. Örn: “Yazılımcı nasıl olurum?”', chips: ['Yazılımcı nasıl olurum?', 'Doktor nasıl olurum?', 'Meslek öner'] };
    }

    if (/calisma|çalışma|ofis|uzaktan|remote|sehir|şehir|ortam/.test(n) && !mem.lastCareer) {
      return { text: 'Çalışma ortamı tercihini öğrenmem için kariyer testini çözebilirsin. Şimdiden hangi mesleği merak ettiğini söyle, o mesleğin çalışma alanlarından bahsedeyim.', chips: ['Bilişim meslekleri', 'Sağlık meslekleri', 'Kariyer testi'] };
    }

    var cat = detectCategory(n);
    if (cat && !strictCareer(q)) {
      var list = careersIn(cat);
      if (list.length) {
        mem.lastCategory = cat; saveMem();
        var cb = el('div');
        cb.appendChild(el('p', '', cat + ' alanında ' + list.length + ' meslek buldum. Öne çıkanlar:'));
        cb.appendChild(cardGrid(list));
        return { node: cb, chips: ['Maaşları karşılaştır', 'Nasıl olurum?', 'Kariyer testi'] };
      }
    }

    var found = searchCareer(q);
    if (found.length) {
      var top = found[0];
      rememberCareer(top);
      var fb = el('div');
      fb.appendChild(el('p', '', top.name + ' hakkında:\n' + (top.summary || '') + '\n\nAlan: ' + top.category + (top.education ? ' · Eğitim: ' + educationLabel(top.education) : '') + (top.salary ? ' · Maaş: ' + top.salary : '')));
      if (top.work) fb.appendChild(el('p', '', 'Çalışma alanları: ' + top.work));
      if (found.length > 1) fb.appendChild(cardGrid(found.slice(1)));
      fb.appendChild(link(careerUrl(top.slug), '→ Detaylı incele'));
      return { node: fb, chips: ['Maaşı ne kadar?', 'Nasıl olurum?', 'Benzer meslekler'] };
    }

    return { text: 'Bu soruyu tam anlayamadım 🙂 Meslek adı, maaş, eğitim, karşılaştırma, mini test veya kariyer testi hakkında sorabilirsin.\n\nÖrnek: "Yazılımcı ile Veri Bilimci karşılaştır" veya "Günün mesleği ne?"', chips: ['Meslek öner', 'Günün mesleği', 'Mini kariyer testi', 'Kariyer testi'] };
  }

  function compareView(a, b) {
    var box = el('div', 'ac-compare');
    [a, b].forEach(function (c) {
      var col = el('div', 'ac-compare-col');
      if (c.photo) { var img = document.createElement('img'); img.src = c.photo; img.alt = ''; col.appendChild(img); }
      col.appendChild(el('b', '', c.name));
      col.appendChild(el('small', '', c.category));
      [['Maaş', c.salary], ['Eğitim', educationLabel(c.education)], ['Çalışma', c.work], ['Özet', (c.summary || '').slice(0, 58)]].forEach(function (r) {
        var row = el('div', 'ac-compare-row');
        row.appendChild(el('span', '', r[0]));
        row.appendChild(el('b', '', r[1] || '—'));
        col.appendChild(row);
      });
      var a2 = el('a', 'ac-link', 'İncele →'); a2.href = careerUrl(c.slug); col.appendChild(a2);
      box.appendChild(col);
    });
    var wrap = el('div');
    wrap.appendChild(el('p', '', a.name + ' ve ' + b.name + ' karşılaştırması:'));
    wrap.appendChild(box);
    return { node: wrap, chips: ['Maaşları karşılaştır', 'Benzer meslekler', 'Mini test'] };
  }

  function startMiniQuiz() {
    var qs = [
      ['Hangisi seni daha çok heyecanlandırır?', [['Teknoloji ve kod', 'Teknoloji'], ['İnsanlara yardım etmek', 'Sağlık'], ['Fikir üretmek ve tasarlamak', 'Sanat ve Tasarım']]],
      ['Bir sorunla karşılaşınca ilk tepkin?', [['Veriyi analiz etmek', 'Bilim'], ['Bir uzmana danışmak', 'Hukuk'], ['Farklı bir yol denemek', 'Mühendislik']]],
      ['Ideal çalışma günün nasıl olurdu?', [['Bilgisayar başında üretmek', 'Teknoloji'], ['Ekip içinde konuşmak', 'Eğitim'], ['Sahada hareket etmek', 'Spor']]]
    ];
    state.mini = { i: 0, picks: [] };
    return { node: renderMini(qs), chips: ['Meslekleri karşılaştır', 'Kariyer testi', 'Günün mesleği'] };
  }
  function renderMini(qs) {
    var q = qs[state.mini.i];
    var box = el('div', 'ac-mini');
    box.appendChild(el('span', 'ac-mini-step', 'Mini test · ' + (state.mini.i + 1) + '/' + qs.length));
    box.appendChild(el('b', 'ac-mini-q', q[0]));
    q[1].forEach(function (o) {
      var b = el('button', 'ac-mini-opt', o[0]);
      b.type = 'button';
      b.addEventListener('click', function () {
        state.mini.picks.push(o[1]);
        if (state.mini.i < qs.length - 1) {
          state.mini.i++;
          var mid = addMessage('', 'bot', renderMini(qs), { noActions: true });
          scrollBottom(true);
        } else {
          var counts = {};
          state.mini.picks.forEach(function (c) { counts[c] = (counts[c] || 0) + 2; });
          var top = Object.keys(counts).sort(function (x, y) { return counts[y] - counts[x]; })[0];
          var list = careersIn(top).slice(0, 3);
          mem.lastCategory = top; saveMem();
          var res = el('div');
          res.appendChild(rp('Mini test tamamlandı! 🎯 İlgi alanın: **' + top + '**. Bu alanda öne çıkan meslekler:'));
          if (list.length) res.appendChild(cardGrid(list));
          res.appendChild(link(pageUrl('test'), '→ Tam kariyer testini çöz'));
          addMessage('', 'bot', res);
          setChips(['Bu meslekleri karşılaştır', 'Maaşlar ne kadar?', 'Tam teste başla']);
          state.mini = null;
        }
      });
      box.appendChild(b);
    });
    return box;
  }

  /* ---------------- Mesaj gönderme ---------------- */
  function respond(text) {
    if (state.busy) return;
    state.busy = true;
    var typing = el('div', 'ac-msg ac-msg--bot');
    var tb = el('div', 'ac-bubble ac-typing');
    tb.innerHTML = '<span></span><span></span><span></span>';
    typing.appendChild(tb);
    body.appendChild(typing);
    scrollBottom();

    var result;
    try { result = answer(text); }
    catch (err) { result = { text: 'Bir hata oluştu, lütfen tekrar dene.' }; console.error(err); }

    setTimeout(function () {
      typing.remove();
      if (result.node) addMessage(result.node, 'bot');
      else addMessage(result.text, 'bot', result.cards ? cardGrid(result.cards) : null);
      mem.history.push({ r: 'u', t: String(text).slice(0, 200) });
      mem.history.push({ r: 'b', t: (result.text || 'Öneri kartları').slice(0, 220) });
      saveMem();
      setChips(result.chips);
      state.turns++;
      state.busy = false;
      if (!state.open) { state.unread = true; badge.classList.add('show'); }
      scrollBottom(true);
    }, 420 + Math.min(520, String(text).length * 9));
  }

  function ask(text) {
    text = String(text || '').trim();
    if (!text) return;
    addMessage(text, 'user');
    setChips([]);
    input.value = '';
    if (careers.length) respond(text);
    else {
      var typing = el('div', 'ac-msg ac-msg--bot');
      var tb = el('div', 'ac-bubble ac-typing');
      tb.innerHTML = '<span></span><span></span><span></span>';
      typing.appendChild(tb);
      body.appendChild(typing); scrollBottom();
      dataReady.then(function () { typing.remove(); respond(text); });
    }
  }

  function welcome() {
    var w = el('div', 'ac-welcome');
    w.innerHTML = '<div class="ac-welcome-ic">' + ICON.spark + '</div>' +
      '<b>Merhaba' + (mem.name ? ', ' + el('span', '', mem.name).textContent : '') + '! 👋</b>' +
      '<p>Ben AI Kariyer Koçu. 54 mesleğin maaş, eğitim ve kariyer verilerini kullanarak sorularını yanıtlıyorum.</p>' +
      '<div class="ac-welcome-grid"><span>🎯 Meslek önerisi</span><span>💰 Maaş bilgisi</span><span>⚖️ Karşılaştırma</span><span>🧭 Yol haritası</span></div>';
    addMessage(w, 'bot', null, { noActions: true });
    setChips(['Test sonucuma göre öner', 'Yazılımcı maaşı ne kadar?', 'Meslekleri karşılaştır', 'Mini kariyer testi']);
  }

  function restoreHistory() {
    if (!mem.history.length) { welcome(); return; }
    var div = el('div', 'ac-divider', 'Önceki sohbet');
    body.appendChild(div);
    mem.history.slice(-8).forEach(function (m) {
      var wrap = el('div', 'ac-msg ac-msg--' + (m.r === 'u' ? 'user' : 'bot'));
      wrap.appendChild(el('div', 'ac-bubble', m.t));
      body.appendChild(wrap);
    });
    setChips(['Devam et: meslek öner', 'Günün mesleği', 'Mini test', 'Sohbeti temizle']);
  }

  function toggle(force) {
    state.open = typeof force === 'boolean' ? force : !state.open;
    panel.classList.toggle('is-open', state.open);
    fab.setAttribute('aria-expanded', String(state.open));
    fab.setAttribute('aria-label', state.open ? 'AI Kariyer Koçunu kapat' : 'AI Kariyer Koçunu aç');
    if (state.open) {
      state.unread = false; badge.classList.remove('show');
      setTimeout(function () { input.focus(); scrollBottom(true); }, 80);
    }
  }

  fab.addEventListener('click', function () { toggle(); });
  root.querySelector('[data-act="close"]').addEventListener('click', function () { toggle(false); });
  root.querySelector('[data-act="clear"]').addEventListener('click', function () {
    mem.history = []; mem.feedback = {}; mem.lastCareer = ''; mem.lastCategory = '';
    saveMem();
    body.innerHTML = '';
    welcome();
  });
  form.addEventListener('submit', function (e) { e.preventDefault(); ask(input.value); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && state.open) toggle(false);
    if ((e.ctrlKey || e.metaKey) && e.key === 'k' && !state.open) { e.preventDefault(); toggle(true); }
  });

  fetch(coachDataUrl).then(function (r) {
    if (!r.ok) throw new Error('veri yüklenemedi');
    return r.json();
  }).then(function (data) {
    if (Array.isArray(data)) careers = data;
    resolveData();
  }).catch(function () { careers = []; resolveData(); });

  restoreHistory();

  window.AI_COACH = {
    ask: ask,
    open: function () { toggle(true); },
    toggle: toggle,
    data: function () { return careers; },
    clear: function () { root.querySelector('[data-act="clear"]').click(); }
  };
})();
