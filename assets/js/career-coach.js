/* ==========================================================================
   AI Kariyer Koçu — kural tabanlı, bağlam hatırlayan akıllı asistan
   ========================================================================== */
(function () {
  'use strict';

  var FAB_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0-6 6c0 2.1 1.1 3.9 2.7 5-.9.6-1.5 1.5-1.6 2.6h9.8c-.1-1.1-.7-2-1.6-2.6A5.98 5.98 0 0 0 18 9a6 6 0 0 0-6-6Z"/><path d="M9.5 20h5M10 17h4"/></svg>';
  var BOT_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="8" width="16" height="12" rx="3"/><path d="M12 8V4M9 14h.01M15 14h.01M9.5 17h5"/></svg>';

  var appBase = (window.AK && window.AK.base) || '/';
  var coachDataUrl = (window.AK && window.AK.coachData) || 'assets/data/careers.json';
  var careers = [];
  var state = { open: false, busy: false, lastCareer: null, lastList: [], turns: 0, askedName: false, name: '' };

  try {
    var saved = JSON.parse(localStorage.getItem('akk_coach_memory') || '{}');
    if (saved.lastCareer) state.lastCareer = saved.lastCareer;
    if (saved.name) state.name = saved.name;
  } catch (e) { /* yoksay */ }

  function saveMemory() {
    try { localStorage.setItem('akk_coach_memory', JSON.stringify({ lastCareer: state.lastCareer, name: state.name })); } catch (e) {}
  }

  var ALIASES = [
    [/yazilimci|softwareci|yazilimci|kodcu|developer/g, 'yazilim'],
    [/programci|programmer/g, 'program'],
    [/muhendis|mhendis/g, 'muhendis'],
    [/hekim|doktor/g, 'doktor'],
    [/ogretmen|ogretim/g, 'ogretmen'],
    [/hemsire|hemşire/g, 'hemsire'],
    [/muhasebe|accountant/g, 'muhasebeci'],
    [/avukat|lawyer/g, 'avukat'],
    [/mimar|architect/g, 'mimar'],
    [/futbolcu|futbol/g, 'futbol'],
    [/bilisimci|itci/g, 'teknoloji']
  ];

  function normalize(s) {
    var n = String(s || '').toLocaleLowerCase('tr-TR')
      .replace(/ı/g, 'i').replace(/İ/g, 'i').replace(/ş/g, 's').replace(/ğ/g, 'g')
      .replace(/ü/g, 'u').replace(/ö/g, 'o').replace(/ç/g, 'c')
      .replace(/[^\w\s?]/g, ' ').replace(/\s+/g, ' ').trim();
    ALIASES.forEach(function (a) { n = n.replace(a[0], a[1]); });
    return n;
  }

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }

  function link(href, text) {
    var a = el('a', '', text);
    a.href = href;
    a.style.cssText = 'color:var(--c-accent);font-weight:700;text-decoration:underline;text-underline-offset:2px';
    return a;
  }

  function u(path) {
    if (!path) return appBase;
    if (/^https?:\/\//.test(path)) return path;
    return appBase.replace(/\/$/, '') + '/' + String(path).replace(/^\//, '');
  }

  var root = el('div');
  root.innerHTML =
    '<button type="button" class="ai-coach-fab" aria-label="AI Kariyer Koçunu aç" aria-expanded="false">' + FAB_SVG + '<span class="ai-coach-dot"></span></button>' +
    '<section class="ai-coach" role="dialog" aria-label="AI Kariyer Koçu" aria-modal="false">' +
      '<header class="ai-coach__head">' +
        '<div class="ai-coach__avatar">' + BOT_SVG + '</div>' +
        '<div><div class="ai-coach__title">AI Kariyer Koçu</div><div class="ai-coach__sub"><i></i> Çevrimiçi · 7/24 yanında</div></div>' +
        '<button type="button" class="ai-coach__close" aria-label="Kapat">&times;</button>' +
      '</header>' +
      '<div class="ai-coach__body" aria-live="polite"></div>' +
      '<div class="ai-coach__chips"></div>' +
      '<div class="ai-coach__foot">' +
        '<form class="ai-coach__form">' +
          '<input class="ai-coach__input" type="text" placeholder="Meslek, maaş, kariyer sor…" autocomplete="off" maxlength="300" aria-label="Mesajınız">' +
          '<button class="ai-coach__send" type="submit" aria-label="Gönder">' +
            '<svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>' +
          '</button>' +
        '</form>' +
        '<div class="ai-coach__hint">Koç bilgilendirme amaçlıdır; kararlarını uzman görüşüyle destekle.</div>' +
      '</div>' +
    '</section>';

  document.body.appendChild(root);

  var fab = root.querySelector('.ai-coach-fab');
  var panel = root.querySelector('.ai-coach');
  var body = root.querySelector('.ai-coach__body');
  var chipsBox = root.querySelector('.ai-coach__chips');
  var form = root.querySelector('.ai-coach__form');
  var input = root.querySelector('.ai-coach__input');

  function isDark() { return document.documentElement.getAttribute('data-theme') === 'dark'; }

  function scrollBottom() { body.scrollTop = body.scrollHeight; }

  function addMessage(text, who, extra) {
    var wrap = el('div', 'ai-coach__msg ai-coach__msg--' + (who === 'user' ? 'user' : 'bot'));
    var bubble = el('div', 'ai-coach__bubble');
    if (typeof text === 'string') {
      bubble.textContent = text;
    } else if (text && typeof text.nodeType === 'number') {
      bubble.appendChild(text);
    }
    if (extra) bubble.appendChild(extra);
    wrap.appendChild(bubble);
    wrap.appendChild(el('span', 'ai-coach__time', who === 'user' ? 'Sen' : 'AI Koç · ' + new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })));
    body.appendChild(wrap);
    scrollBottom();
    return bubble;
  }

  function setChips(items) {
    chipsBox.innerHTML = '';
    (items || []).slice(0, 4).forEach(function (label) {
      var b = el('button', 'ai-coach__chip', label);
      b.type = 'button';
      b.addEventListener('click', function () { ask(label); });
      chipsBox.appendChild(b);
    });
  }

  function careerCards(list) {
    var box = el('div', 'ai-coach__cards');
    list.slice(0, 3).forEach(function (c) {
      var a = el('a', 'ai-coach__card');
      a.href = u('career-detail.php?slug=' + encodeURIComponent(c.slug));
      if (c.photo) {
        var img = document.createElement('img');
        img.src = c.photo; img.alt = ''; img.loading = 'lazy';
        img.onerror = function () { img.style.visibility = 'hidden'; };
        a.appendChild(img);
      } else {
        var ph = el('div', 'ai-coach__card-photo');
        ph.style.cssText = 'width:42px;height:42px;flex:0 0 42px;border-radius:9px;background:var(--c-accent-soft)';
        a.appendChild(ph);
      }
      var info = el('div');
      info.appendChild(el('b', '', c.name));
      info.appendChild(el('small', '', [c.category, c.salary].filter(Boolean).join(' · ').slice(0, 70)));
      a.appendChild(info);
      a.appendChild(el('span', 'ai-coach__go', '›'));
      box.appendChild(a);
    });
    return box;
  }

  function searchCareer(q) {
    var n = normalize(q);
    if (!n) return [];
    var words = n.split(' ').filter(function (w) { return w.length > 2; });
    var scored = careers.map(function (c) {
      var name = normalize(c.name);
      var cat = normalize(c.category);
      var sum = normalize(c.summary);
      var score = 0;
      if (name === n) score += 100;
      if (name.indexOf(n) !== -1) score += 60;
      if (n.indexOf(name) !== -1 && name.length > 3) score += 45;
      words.forEach(function (w) {
        if (name.indexOf(w) !== -1) score += 22;
        if (cat.indexOf(w) !== -1) score += 12;
        if (sum.indexOf(w) !== -1) score += 4;
      });
      return { c: c, score: score };
    }).filter(function (x) { return x.score > 0; });
    scored.sort(function (a, b) { return b.score - a.score; });
    return scored.slice(0, 6).map(function (x) { return x.c; });
  }

  function byCategory(q) {
    var n = normalize(q);
    var map = [
      [/bilisim|bilgisayar|yazilim|kod|it|teknoloji|siber|veri|yapay/, 'Teknoloji'],
      [/saglik|tip|doktor|hemsire|eczane|diyet|fizyo/, 'Sağlık'],
      [/egitim|ogretmen|akademik|okul|universite|danisman/, 'Eğitim'],
      [/isletme|finans|muhasebe|banka|ekonomi|ticaret|pazarlama|girisim|lojistik|insan kaynaklari/, 'İşletme'],
      [/sanat|tasarim|muzik|medya|gorsel|mimar|fotograf|animasyon/, 'Sanat ve Tasarım'],
      [/hukuk|adalet|avukat|noter|savci/, 'Hukuk'],
      [/muhendis|uretim|enerji|insaat|cevre|gida|elektrik|makine|havacilik/, 'Mühendislik'],
      [/spor|antrenor|futbol|beden/, 'Spor'],
      [/iletisim|gazete|haber|sosyal medya|kriz|icerik/, 'İletişim'],
      [/bilim|arastirma|laboratuvar|fizik|kimya|meteoroloji|matematik/, 'Bilim']
    ];
    var cat = null;
    for (var i = 0; i < map.length; i++) { if (map[i][0].test(n)) { cat = map[i][1]; break; } }
    if (!cat) return [];
    return careers.filter(function (c) { return normalize(c.category).indexOf(normalize(cat)) !== -1; }).slice(0, 6);
  }

  function answer(q) {
    var n = normalize(q);
    var raw = String(q || '').trim();

    if (/^(merhaba|selam|selamlarik|hi|hey|gunaydin|iyi aksamlar|iyi gunler)\b/.test(n)) {
      return { text: 'Merhaba' + (state.name ? ' ' + state.name : '') + '! 👋 Ben AI Kariyer Koçu. Meslekleri keşfetmen, maaş aralıklarını öğrenmen ve sana uygun yolu bulman için buradayım.\n\nNe merak ediyorsun?', chips: ['Bana uygun meslekleri söyle', 'Yazılımcı maaşı ne kadar?', 'Kariyer testi nasıl çalışır?'] };
    }
    if (/tesekkur|sag ol|tevhikkurlar|eyvallah/.test(n)) return { text: 'Rica ederim! 🌱 İstersen birlikte mülakat pratiği de yapabiliriz. Başka bir şey sorabilir misin?', chips: ['Mülakata hazırlan', 'Kariyer testine başla', 'Meslek öner'] };
    if (/kimsin|ne yaparsin|yeteneklerin|help|yardim|nasil calisir/.test(n)) {
      return { text: 'Ben bu platformun AI koçuyum. 54 mesleğin verilerini kullanarak:\n• meslek ve maaş sorularını yanıtlarım,\n• ilgi alanına göre meslek öneririm,\n• kariyer yol haritası ve mülakat ipuçları veririm.\n\nYapay zekâ yerine şeffaf kural tabanlı bir analiz kullanırım; veriler platformun meslek veritabanından gelir.', chips: ['Meslek öner', 'Maaşları karşılaştır', 'Mülakat ipuçları'] };
    }
    if (/nevabil|naber|nasilsin/.test(n)) return { text: 'Teşekkürler, ben iyiyim! 😊 Senin kariyer yolculuğuna odaklanalım. Hangi mesleği merak ediyorsun?', chips: ['Meslek öner', 'Bilişim meslekleri', 'Kariyer testine başla'] };

    if (/adim|isim|beni tanı|adimi/.test(n) && !state.name) {
      var m = raw.match(/(?:adım|adim|benim adım|isim)\s+([A-Za-zÇĞİÖŞÜçğıöşü]{2,20})/i);
      if (m) { state.name = m[1]; saveMemory(); return { text: 'Tanışmak güzel, ' + state.name + '! 😊 Bundan sana özel öneriler sunabilirim. Hangi alana ilgin var?', chips: ['Bilişim', 'Sağlık', 'Bana uygun meslekleri söyle'] }; }
    }

    if (/kariyer testi|test|hangi meslek|ne meslek|uygun meslek|ne olmaliyim|ne olabilirim|meslek oner|oneri|tavsiye/.test(n)) {
      var sug = state.lastCareer ? searchCareer(state.lastCareer) : [];
      var txt = 'En doğru sonuç için 12 soruluk kariyer testini çözmeni öneririm. Test; ilgi alanlarını, çalışma stilini ve güçlü yönlerini analiz ederek sana özel meslekler listeler. 🧭';
      var box = el('div');
      box.appendChild(el('p', '', txt));
      box.appendChild(link(u('career-test.php'), '→ Kariyer testine başla'));
      if (state.lastCareer && sug.length) {
        box.appendChild(el('p', '', '\nŞimdiden ilgini çekebilecek meslekler:'));
        box.appendChild(careerCards(sug));
      }
      return { node: box, chips: ['Yazılımcı maaşı', 'Bilişim meslekleri', 'Mülakat ipuçları'] };
    }

    if (/kisilik|big five|kişilik testi|personality/.test(n)) {
      var p = el('div');
      p.appendChild(el('p', '', 'Big Five (Beş Büyük) kişilik modeli; açıklık, sorumluluk, dışadönüklük, uyumluluk ve duygusal denge boyutlarını ölçer. Sonuçlara göre meslek önerileri üretiriz.'));
      p.appendChild(link(u('dashboard/personality-test.php'), '→ Kişilik testini çöz'));
      return { node: p, chips: ['Kariyer testi', 'Meslek öner', 'Mülakat ipuçları'] };
    }

    if (/mulakat|görüşme|gorusme|interview|soru sor/.test(n)) {
      var p2 = el('div');
      p2.appendChild(el('p', '', 'Mülakat pratiği için simülatörümüzü kullanabilirsin. 8 soruluk senaryoda cevaplarını puanlarız ve her soruda geri bildirim veririz. 🎤'));
      p2.appendChild(link(u('dashboard/interview-sim.php'), '→ Mülakat simülatörünü aç'));
      p2.appendChild(el('p', '', '\nHızlı ipucu: Cevaplarında "STAR" yöntemini kullan — Durum (Situation), Görev (Task), Eylem (Action), Sonuç (Result).'));
      return { node: p2, chips: ['CV nasıl hazırlanır?', 'Kariyer yol haritası', 'Meslek öner'] };
    }

    if (/cv|ozgecmis|özkaynak|özd|bazvuru|başvuru/.test(n)) {
      var p3 = el('div');
      p3.appendChild(el('p', '', 'CV\'nde 4 bölüm şart: iletişim bilgileri, eğitim, deneyim ve beceriler. Başvurduğun mesleğin anahtar kelimelerini beceriler bölümüne ekle; bu, tarama sistemlerinde görünürlüğünü artırır. 📄'));
      p3.appendChild(link(u('dashboard/cv-builder.php'), '→ CV oluşturucuyu aç'));
      return { node: p3, chips: ['Mülakat ipuçları', 'Meslek öner', 'Kariyer yol haritası'] };
    }

    if (/yol haritasi|yolharitasi|nasil olurum|nasil baslarim|adim adim|roadmap|gelisme|öğrenme/.test(n)) {
      var c = state.lastCareer ? searchCareer(state.lastCareer)[0] : null;
      if (c) {
        var p4 = el('div');
        p4.appendChild(el('p', '', c.name + ' için örnek yol haritası:\n1. Temel bilgiyi edin: ' + (c.education || 'ilgili lisans/eğitim') + '.\n2. Uygulamalı projeler yap ve portfolyo oluştur.\n3. Staj veya giriş seviyesi işle deneyim kazan.\n4. Sertifika ve güncel becerilerle uzmanlaş.'));
        p4.appendChild(careerCards([c]));
        return { node: p4, chips: ['Maaşı ne kadar?', 'Benzer meslekler', 'Kariyer testi'] };
      }
      return { text: 'Önce hangi mesleği hedeflediğini söyle; sana adım adım bir yol haritası çıkarayım. Örn: "Yazılımcı nasıl olurum?"', chips: ['Yazılımcı nasıl olurum?', 'Doktor nasıl olurum?', 'Meslek öner'] };
    }

    if (/maas|maş|kazan|ucret|ücret|para|gelir|fiyat|kaç lira|kac lira/.test(n)) {
      var target = state.lastCareer ? searchCareer(state.lastCareer)[0] : null;
      var direct = searchCareer(q)[0] || null;
      var chosen = direct || target;
      if (chosen) {
        state.lastCareer = chosen.name; saveMemory();
        return { text: chosen.name + ' için platformumuzdaki maaş aralığı: ' + (chosen.salary || 'Veri bulunamadı') + '.\n\nNot: Maaşlar deneyim, şehir ve sektöre göre değişir. ' + chosen.category + ' alanındaki benzer meslekleri de karşılaştırabilirsin.', cards: [chosen], chips: ['Benzer meslekleri gör', 'Kariyer yol haritası', 'Kariyer testi'] };
      }
      var listed = careers.slice(0, 3);
      var box2 = el('div');
      box2.appendChild(el('p', '', 'Hangi mesleğin maaşını merak ediyorsun? Örnek olarak en popüler birkaçı:'));
      box2.appendChild(careerCards(listed));
      return { node: box2, chips: ['Yazılımcı maaşı', 'Doktor maaşı', 'Öğretmen maaşı'] };
    }

    if (/benzer|similar|başka meslek|baska meslek|alternatif/.test(n) && state.lastCareer) {
      var cur = searchCareer(state.lastCareer)[0];
      var sim = cur ? careers.filter(function (c) { return c.slug !== cur.slug && c.category === cur.category; }).slice(0, 3) : [];
      if (sim.length) {
        var box3 = el('div');
        box3.appendChild(el('p', '', cur.name + ' ile aynı alandaki benzer meslekler:'));
        box3.appendChild(careerCards(sim));
        return { node: box3, chips: ['Maaşları karşılaştır', 'Kariyer testi', 'Mülakat ipuçları'] };
      }
    }

    var catList = byCategory(q);
    if (catList.length) {
      var box4 = el('div');
      box4.appendChild(el('p', '', 'Bu alanda ' + catList.length + ' meslek buldum. İşte öne çıkanlar:'));
      box4.appendChild(careerCards(catList));
      state.lastList = catList.map(function (c) { return c.slug; });
      return { node: box4, chips: ['Maaşları karşılaştır', 'Kariyer yol haritası', 'Kariyer testi'] };
    }

    var found = searchCareer(q);
    if (found.length) {
      var top = found[0];
      state.lastCareer = top.name; saveMemory();
      var box5 = el('div');
      box5.appendChild(el('p', '', top.name + ' hakkında kısa bilgi:\n' + (top.summary || 'Bu meslek hakkında detaylı bilgi sayfasında yer alıyor.') + '\n\nAlan: ' + top.category + (top.education ? ' · Eğitim: ' + top.education : '') + (top.salary ? ' · Maaş: ' + top.salary : '')));
      if (found.length > 1) box5.appendChild(careerCards(found.slice(1)));
      box5.appendChild(link(u('career-detail.php?slug=' + encodeURIComponent(top.slug)), '→ Detaylı incele'));
      return { node: box5, chips: ['Maaşı ne kadar?', 'Nasıl olurum?', 'Benzer meslekler'] };
    }

    return { text: 'Bu soruyu tam anlayamadım 🙂 Meslek adı, maaş, eğitim, mülakat veya kariyer testi hakkında sorabilirsin.\n\nÖrnekler: "Yazılımcı maaşı ne kadar?", "Sağlık alanındaki meslekler", "Mülakata nasıl hazırlanırım?"', chips: ['Meslek öner', 'Bilişim meslekleri', 'Kariyer testine başla'] };
  }

  function respond(text) {
    if (state.busy) return;
    state.busy = true;
    var typing = el('div', 'ai-coach__msg ai-coach__msg--bot');
    var tb = el('div', 'ai-coach__bubble ai-coach__typing');
    tb.innerHTML = '<span></span><span></span><span></span>';
    typing.appendChild(tb);
    body.appendChild(typing);
    scrollBottom();

    var result;
    try { result = answer(text); }
    catch (err) { result = { text: 'Bir hata oluştu, lütfen tekrar dene.' }; console.error(err); }

    var delay = 380 + Math.min(650, String(text || '').length * 12);
    setTimeout(function () {
      typing.remove();
      if (result.node) addMessage(result.node, 'bot');
      else addMessage(result.text, 'bot', result.cards ? careerCards(result.cards) : null);
      if (result.text && result.cards) { /* kartlar zaten eklendi */ }
      setChips(result.chips);
      state.turns++;
      state.busy = false;
      scrollBottom();
    }, delay);
  }

  function ask(text) {
    text = String(text || '').trim();
    if (!text) return;
    addMessage(text, 'user');
    setChips([]);
    input.value = '';
    respond(text);
  }

  function toggle(force) {
    state.open = typeof force === 'boolean' ? force : !state.open;
    panel.classList.toggle('is-open', state.open);
    fab.setAttribute('aria-expanded', String(state.open));
    fab.setAttribute('aria-label', state.open ? 'AI Kariyer Koçunu kapat' : 'AI Kariyer Koçunu aç');
    if (state.open) { setTimeout(function () { input.focus(); scrollBottom(); }, 60); }
  }

  fab.addEventListener('click', function () { toggle(); });
  root.querySelector('.ai-coach__close').addEventListener('click', function () { toggle(false); });
  form.addEventListener('submit', function (e) { e.preventDefault(); ask(input.value); });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && state.open) toggle(false);
  });

  fetch(coachDataUrl).then(function (r) {
    if (!r.ok) throw new Error('veri yüklenemedi');
    return r.json();
  }).then(function (data) {
    if (Array.isArray(data)) careers = data;
  }).catch(function () {
    careers = [];
  });

  var welcome = el('div');
  welcome.appendChild(el('p', '', 'Merhaba! 👋 Ben AI Kariyer Koçu. 54 mesleğin verilerini kullanarak sorularını yanıtlıyorum. Sana nasıl yardımcı olabilirim?'));
  addMessage(welcome, 'bot');
  setChips(['Bana uygun meslekleri söyle', 'Yazılımcı maaşı ne kadar?', 'Mülakata hazırlan', 'Kariyer yol haritası']);

  window.AI_COACH = { ask: ask, open: function () { toggle(true); }, data: function () { return careers; } };
})();
