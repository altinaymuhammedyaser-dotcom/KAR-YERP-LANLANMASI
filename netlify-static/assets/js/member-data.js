/* Üye verileri — CV ve test cevapları (statik demo veri katmanı) */
(function () {
  'use strict';
  var KEY = 'akk_members_v1';
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function get() {
    try { var v = JSON.parse(localStorage.getItem(KEY) || 'null'); if (Array.isArray(v)) return v; } catch (e) {}
    return seed();
  }
  function set(list) { try { localStorage.setItem(KEY, JSON.stringify(list)); } catch (e) {} }
  function seed() {
    var d = new Date().toLocaleString('tr-TR');
    var list = [
      { id: 'm1', name: 'Elif Kaya', email: 'elif@ornek.com', demo: false, cv: { name: 'Elif Kaya', email: 'elif@ornek.com', phone: '+90 532 111 22 33', city: 'İzmir', edu: 'Bilgisayar Mühendisliği, Lisans', skills: 'JavaScript, HTML, CSS, Analitik düşünme', summary: 'Front-end geliştirici olarak deneyim kazanmak istiyorum.' }, tests: {
        career: [{ date: d, score: 78, answers: [{ q: 'Yeni bir yazılım aracını öğrenmek keyif verir.', a: '4' }, { q: 'Bir topluluk önünde fikrini anlatmak kolaydır.', a: '3' }, { q: 'Karmaşık problemi parçalara ayırıp çözmeyi severim.', a: '5' }] }],
        interview: [{ date: d, score: 75, answers: [{ q: 'Mülakatı nasıl hazırlandınız?', a: 'Şirketi araştırdım ve örnekler hazırladım.', correct: true }, { q: 'Bir takım çatışmasını nasıl çözdünüz?', a: 'Bilmiyorum.', correct: false }, { q: 'En çok zorlandığınız proje?', a: 'Zaman baskısını planlayarak yönettim.', correct: true }] }]
      } },
      { id: 'm2', name: 'Mert Yılmaz', email: 'mert@ornek.com', demo: false, cv: { name: 'Mert Yılmaz', email: 'mert@ornek.com', phone: '+90 533 444 55 66', city: 'Ankara', edu: 'İşletme, Lisans', skills: 'Pazarlama, İletişim, Proje yönetimi', summary: 'Pazarlama alanında kariyer hedefliyorum.' }, tests: {
        career: [{ date: d, score: 64, answers: [{ q: 'Yeni bir yazılım aracını öğrenmek keyif verir.', a: '2' }, { q: 'Bir topluluk önünde fikrini anlatmak kolaydır.', a: '5' }, { q: 'Karmaşık problemi parçalara ayırıp çözmeyi severim.', a: '3' }] }],
        interview: [{ date: d, score: 58, answers: [{ q: 'Mülakatı nasıl hazırlandınız?', a: 'Kısa bir hazırlık yaptım.', correct: false }, { q: 'Bir takım çatışmasını nasıl çözdünüz?', a: 'Önce tarafları dinledim, ortak çözüm buldum.', correct: true }, { q: 'En çok zorlandığınız proje?', a: 'Erteleme','correct': false }] }]
      } },
      { id: 'm3', name: 'Zeynep Aydın', email: 'zeynep@ornek.com', demo: false, cv: { name: 'Zeynep Aydın', email: 'zeynep@ornek.com', phone: '+90 555 777 88 99', city: 'İstanbul', edu: 'Halkla İlişkiler, Lisans', skills: 'İletişim, Yaratıcılık, Sosyal medya', summary: 'Kurumsal iletişim uzmanı olmak istiyorum.' }, tests: {
        personality: [{ date: d, score: 82, answers: [{ q: 'Sıra dışı fikirler üretmek keyif verir.', a: '5' }, { q: 'Yeni insanlarla sohbet etmek beni mutlu eder.', a: '4' }] }]
      } }
    ];
    set(list);
    return list;
  }
  function currentUser() {
    try { return JSON.parse(localStorage.getItem('akk_session') || '{}'); } catch (e) { return {}; }
  }
  function memberFor(email, name) {
    var list = get();
    var m = list.filter(function (x) { return x.email === email; })[0];
    if (!m) { m = { id: 'u' + Date.now(), name: name || email, email: email, demo: true, cv: {}, tests: {} }; list.push(m); set(list); }
    return m;
  }
  function saveCV(cv) {
    var u = currentUser();
    if (!u.email) return;
    var m = memberFor(u.email, u.name || cv.name);
    m.name = cv.name || m.name;
    m.cv = cv;
    var list = get(), idx = -1;
    list.forEach(function (x, i) { if (x.email === u.email) idx = i; });
    if (idx >= 0) list[idx] = m; else list.push(m);
    set(list);
  }
  function addTest(key, attempt) {
    var u = currentUser();
    if (!u.email) return;
    var m = memberFor(u.email, u.name);
    if (!m.tests) m.tests = {};
    if (!Array.isArray(m.tests[key])) m.tests[key] = [];
    m.tests[key].push(attempt);
    if (m.tests[key].length > 6) m.tests[key] = m.tests[key].slice(-6);
    var list = get(), idx = -1;
    list.forEach(function (x, i) { if (x.email === u.email) idx = i; });
    if (idx >= 0) list[idx] = m; else list.push(m);
    set(list);
  }
  window.MemberData = { get: get, saveCV: saveCV, addTest: addTest, seed: seed, esc: esc };
})();
