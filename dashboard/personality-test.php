<?php
/**
 * Kişilik Testi — Big Five (Beş Büyük) modeli
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

Auth::requireLogin();

$panelTitle    = 'Kişilik Testi';
$panelSubtitle = 'Beş Büyük kişilik modeliyle güçlü yönlerini keşfet ve meslek önerileri al.';
$panelActive   = 'personality-test';

$careers = Database::fetchAll("
    SELECT c.slug, c.name, c.short_description AS summary, cat.name AS category
    FROM careers c
    LEFT JOIN career_categories cat ON cat.id = c.category_id
    WHERE c.is_active = 1
    ORDER BY cat.name, c.name
");

$photoMapPath = dirname(__DIR__) . '/career_photos.json';
$photoMap = is_file($photoMapPath) ? (json_decode(file_get_contents($photoMapPath), true) ?: []) : [];
foreach ($careers as &$c) {
    $c['photo'] = $photoMap[$c['slug']] ?? '';
}
unset($c);

$careerJson = json_encode($careers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP);

require_once __DIR__ . '/../includes/panel-header.php';
?>

<style>
.pt-wrap{max-width:900px;margin:0 auto}
.pt-hero{background:linear-gradient(120deg,#142B4A,#1C466D 65%,#16B8B0);color:#fff;border-radius:20px;padding:28px 28px 24px;position:relative;overflow:hidden;box-shadow:var(--sh-md,0 12px 30px rgba(12,30,53,.16))}
.pt-hero:after{content:"";position:absolute;width:240px;height:240px;border-radius:50%;right:-70px;top:-110px;background:rgba(255,255,255,.09)}
.pt-hero h1{margin:0 0 8px;font-size:1.55rem;font-weight:800;color:#fff}
.pt-hero p{margin:0;color:rgba(255,255,255,.88);font-size:.92rem;line-height:1.6;max-width:640px}
.pt-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.22);border-radius:999px;padding:5px 12px;font-size:.73rem;font-weight:700;margin-bottom:13px}
.pt-card{background:var(--c-bg-card,#fff);border:1px solid var(--c-border);border-radius:18px;padding:24px;margin-top:18px;box-shadow:var(--sh-sm,0 2px 8px rgba(0,0,0,.05))}
.pt-card h2{color:var(--c-primary);font-size:1.1rem;font-weight:800;margin:0 0 6px}
.pt-card .pt-lead{color:var(--c-muted);font-size:.85rem;margin-bottom:20px}
.pt-q{margin-bottom:22px}
.pt-q-title{display:flex;gap:10px;align-items:flex-start;font-weight:650;color:var(--c-text);font-size:.94rem;line-height:1.5;margin-bottom:11px}
.pt-q-num{flex:0 0 26px;height:26px;border-radius:8px;background:var(--c-accent-soft);color:var(--c-accent-dark);display:grid;place-items:center;font-size:.75rem;font-weight:800}
.pt-scale{display:grid;grid-template-columns:repeat(5,1fr);gap:7px}
.pt-scale button{border:1px solid var(--c-border-dark);background:var(--c-bg);color:var(--c-muted);border-radius:11px;padding:9px 4px;cursor:pointer;font-size:.79rem;font-weight:700;transition:.15s ease}
.pt-scale button:hover{border-color:var(--c-accent);color:var(--c-accent-dark);transform:translateY(-2px)}
.pt-scale button.selected{background:linear-gradient(135deg,var(--c-accent),var(--c-accent-dark));border-color:transparent;color:#fff;box-shadow:0 5px 14px rgba(22,184,176,.3)}
.pt-scale-labels{display:flex;justify-content:space-between;color:var(--c-muted);font-size:.7rem;margin-top:6px}
.pt-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:8px}
.pt-progress{height:8px;border-radius:99px;background:var(--c-border);overflow:hidden;margin-top:18px}
.pt-progress span{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#1C466D,#16B8B0);transition:width .25s ease}
.pt-trait{margin-bottom:16px}
.pt-trait-top{display:flex;justify-content:space-between;align-items:center;font-size:.85rem;font-weight:700;color:var(--c-text);margin-bottom:6px}
.pt-trait-top b{color:var(--c-accent-dark)}
.pt-bar{height:9px;border-radius:99px;background:var(--c-border);overflow:hidden}
.pt-bar span{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#142B4A,#16B8B0)}
.pt-result-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:13px;margin-top:18px}
.pt-result-card{border:1px solid var(--c-border);border-radius:15px;overflow:hidden;background:var(--c-bg-card,#fff);text-decoration:none;color:inherit;display:block;transition:.18s ease}
.pt-result-card:hover{transform:translateY(-4px);border-color:var(--c-accent);box-shadow:var(--sh-md,0 10px 24px rgba(12,30,53,.14))}
.pt-result-card img{width:100%;height:122px;object-fit:cover;display:block;background:var(--c-accent-soft)}
.pt-result-body{padding:12px 13px}
.pt-result-body b{display:block;color:var(--c-primary);font-size:.92rem;margin-bottom:4px}
.pt-result-body small{display:block;color:var(--c-muted);font-size:.73rem;line-height:1.45}
.pt-result-body em{display:inline-block;margin-top:8px;color:var(--c-accent-dark);font-size:.7rem;font-style:normal;font-weight:800}
.pt-note{background:var(--c-accent-soft);border:1px solid rgba(22,184,176,.25);border-radius:13px;padding:13px 15px;color:var(--c-accent-dark);font-size:.82rem;line-height:1.6;margin-top:18px}
[data-theme="dark"] .pt-card,[data-theme="dark"] .pt-result-card{border-color:#2A3D57}
[data-theme="dark"] .pt-scale button{background:#18283F;border-color:#2A3D57;color:#9FB2CB}
@media(max-width:560px){.pt-hero{padding:22px}.pt-hero h1{font-size:1.3rem}.pt-scale{gap:4px}.pt-scale button{font-size:.72rem;padding:8px 2px}}
</style>

<div class="pt-wrap">
  <div class="pt-hero">
    <span class="pt-badge">🧠 Big Five · Beş Büyük Kişilik Modeli</span>
    <h1>Kişiliğini keşfet, kariyerini şekillendir</h1>
    <p>20 kısa soruyla açıklık, sorumluluk, dışadönüklük, uyumluluk ve duygusal denge boyutlarını ölçüyoruz. Sonuçlarına göre sana uygun meslekleri birlikte keşfedelim.</p>
  </div>

  <div id="ptQuiz" class="pt-card">
    <h2>20 soruluk kişilik envanteri</h2>
    <p class="pt-lead">Her ifadeye katılım düzeyine göre 1 (hiç katılmıyorum) – 5 (tamamen katılıyorum) arasında puan ver.</p>
    <div id="ptQuestions"></div>
    <div class="pt-actions">
      <button type="button" class="btn btn-primary" id="ptSubmit" disabled>Sonuçlarımı gör</button>
      <span id="ptCount" style="color:var(--c-muted);font-size:.8rem">0 / 20 yanıtlandı</span>
    </div>
    <div class="pt-progress"><span id="ptProgress" style="width:0"></span></div>
  </div>

  <div id="ptResult" class="pt-card" style="display:none"></div>
</div>

<script>
(function () {
  'use strict';
  var careers = <?= $careerJson ?>;
  var base = <?= json_encode(url('')) ?>;

  var TRAITS = [
    { key: 'O', name: 'Açıklık', desc: 'Merak, yaratıcılık ve yeniliğe açıklık', cats: ['Sanat ve Tasarım', 'Bilim', 'İletişim'] },
    { key: 'C', name: 'Sorumluluk', desc: 'Planlı, disiplinli ve hedef odaklı olma', cats: ['İşletme', 'Hukuk', 'Sağlık'] },
    { key: 'E', name: 'Dışadönüklük', desc: 'Sosyallik, iletişim ve enerji', cats: ['Eğitim', 'Spor', 'İletişim'] },
    { key: 'A', name: 'Uyumluluk', desc: 'Empati, iş birliği ve güven', cats: ['Sağlık', 'Eğitim', 'Hukuk'] },
    { key: 'N', name: 'Duygusal Denge', desc: 'Stresle başa çıkma ve sakinlik', cats: ['Teknoloji', 'Mühendislik', 'Bilim'] }
  ];

  var QUESTIONS = [
    ['O', 'Yeni fikirleri ve farklı bakış açılarını keşfetmeyi severim.'],
    ['C', 'Plan yapar ve verdiğim sözleri zamanında tutarım.'],
    ['E', 'Kalabalık ortamlarda insanlarla konuşmaktan keyif alırım.'],
    ['A', 'Başkasının duygularını anlamak bana kolay gelir.'],
    ['N', 'Beklenmedik sorunlarda sakin kalabilirim.'],
    ['O', 'Sanat, müzik veya tasarıma ilgi duyarım.'],
    ['C', 'Masamı ve işlerimi düzenli tutarım.'],
    ['E', 'Ekip çalışmasında sık sık ben konuşurum.'],
    ['A', 'Anlaşmazlıkta önce karşımdakini dinlerim.'],
    ['N', 'Eleştiri alınca hemen moralim bozulmaz.'],
    ['O', 'Alışılmadık yöntemler denemekten çekinmem.'],
    ['C', 'Uzun vadeli hedeflerim için düzenli çalışırım.'],
    ['E', 'Yeni insanlarla tanışmak beni enerjilendirir.'],
    ['A', 'Yardıma ihtiyacı olan birine destek olurum.'],
    ['N', 'Yoğun tempoda bile odaklanmamı korurum.'],
    ['O', 'Detaylardaki güzellikleri fark ederim.'],
    ['C', 'Hatalarımı analiz edip aynısını tekrar etmemeye çalışırım.'],
    ['E', 'Sosyal etkinliklerde aktif rol alırım.'],
    ['A', 'İş birliği yapmak, rekabet etmekten daha keyiflidir.'],
    ['N', 'Stresli günlerde bile duygularımı yönetebilirim.']
  ];

  var answers = new Array(QUESTIONS.length).fill(null);
  var box = document.getElementById('ptQuestions');
  var submit = document.getElementById('ptSubmit');
  var count = document.getElementById('ptCount');
  var progress = document.getElementById('ptProgress');

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  QUESTIONS.forEach(function (q, i) {
    var wrap = document.createElement('div');
    wrap.className = 'pt-q';
    var title = document.createElement('div');
    title.className = 'pt-q-title';
    var num = document.createElement('span');
    num.className = 'pt-q-num';
    num.textContent = String(i + 1);
    title.appendChild(num);
    title.appendChild(document.createTextNode(esc(q[1])));
    wrap.appendChild(title);
    var scale = document.createElement('div');
    scale.className = 'pt-scale';
    for (let v = 1; v <= 5; v++) {
      var b = document.createElement('button');
      b.type = 'button';
      b.textContent = v;
      b.setAttribute('aria-label', q[1] + ' — ' + v);
      b.addEventListener('click', function () {
        answers[i] = v;
        Array.prototype.forEach.call(scale.children, function (x) { x.classList.remove('selected'); });
        this.classList.add('selected');
        update();
      });
      scale.appendChild(b);
    }
    var labels = document.createElement('div');
    labels.className = 'pt-scale-labels';
    labels.innerHTML = '<span>Hiç katılmıyorum</span><span>Katılıyorum</span><span>Tamamen katılıyorum</span>';
    wrap.appendChild(scale);
    wrap.appendChild(labels);
    box.appendChild(wrap);
  });

  function update() {
    var done = answers.filter(function (x) { return x !== null; }).length;
    count.textContent = done + ' / ' + QUESTIONS.length + ' yanıtlandı';
    progress.style.width = Math.round(done / QUESTIONS.length * 100) + '%';
    submit.disabled = done !== QUESTIONS.length;
  }

  function norm(s) {
    return String(s || '').toLocaleLowerCase('tr-TR')
      .replace(/ı/g, 'i').replace(/ş/g, 's').replace(/ğ/g, 'g')
      .replace(/ü/g, 'u').replace(/ö/g, 'o').replace(/ç/g, 'c').trim();
  }
  function categoryMatch(trait, cat) {
    var c = norm(cat);
    return trait.cats.some(function (x) { return norm(x) === c; });
  }

  function showResults() {
    var scores = {};
    TRAITS.forEach(function (t) { scores[t.key] = []; });
    QUESTIONS.forEach(function (q, i) { scores[q[0]].push(answers[i]); });
    var pct = {};
    TRAITS.forEach(function (t) {
      pct[t.key] = Math.round(scores[t.key].reduce(function (a, b) { return a + b; }, 0) / (scores[t.key].length * 5) * 100);
    });

    var ranked = TRAITS.slice().sort(function (a, b) { return pct[b.key] - pct[a.key]; });
    var top = ranked.slice(0, 2);

    var rec = [];
    top.forEach(function (t) {
      careers.forEach(function (c) { if (categoryMatch(t, c.category)) rec.push({ c: c, t: t }); });
    });
    if (rec.length < 6) {
      careers.forEach(function (c) { if (!rec.some(function (x) { return x.c.slug === c.slug; })) rec.push({ c: c, t: ranked[0] }); });
    }

    var out = document.getElementById('ptResult');
    out.innerHTML = '';
    var h = document.createElement('h2');
    h.textContent = 'Kişilik profilin 🧠';
    out.appendChild(h);
    var lead = document.createElement('p');
    lead.className = 'pt-lead';
    lead.textContent = 'En güçlü boyutların: ' + top.map(function (t) { return t.name; }).join(' ve ') + '. Bu özellikler aşağıdaki mesleklerde öne çıkıyor.';
    out.appendChild(lead);

    TRAITS.forEach(function (t) {
      var row = document.createElement('div');
      row.className = 'pt-trait';
      var top2 = document.createElement('div');
      top2.className = 'pt-trait-top';
      var name = document.createElement('span');
      name.textContent = t.name + ' — ' + t.desc;
      var val = document.createElement('b');
      val.textContent = pct[t.key] + '%';
      top2.appendChild(name);
      top2.appendChild(val);
      var bar = document.createElement('div');
      bar.className = 'pt-bar';
      var fill = document.createElement('span');
      fill.style.width = pct[t.key] + '%';
      bar.appendChild(fill);
      row.appendChild(top2);
      row.appendChild(bar);
      out.appendChild(row);
    });

    var heading = document.createElement('h2');
    heading.style.marginTop = '26px';
    heading.textContent = 'Sana uygun meslekler';
    out.appendChild(heading);

    var grid = document.createElement('div');
    grid.className = 'pt-result-grid';
    rec.slice(0, 6).forEach(function (item) {
      var a = document.createElement('a');
      a.className = 'pt-result-card';
      a.href = base + 'career-detail.php?slug=' + encodeURIComponent(item.c.slug);
      if (item.c.photo) {
        var img = document.createElement('img');
        img.src = item.c.photo; img.alt = ''; img.loading = 'lazy';
        img.onerror = function () { img.style.visibility = 'hidden'; };
        a.appendChild(img);
      }
      var body = document.createElement('div');
      body.className = 'pt-result-body';
      var b = document.createElement('b'); b.textContent = item.c.name; body.appendChild(b);
      var s = document.createElement('small'); s.textContent = (item.c.category || '') + ' — ' + (item.c.summary || '').slice(0, 90); body.appendChild(s);
      var em = document.createElement('em'); em.textContent = item.t.name + ' öne çıkıyor'; body.appendChild(em);
      a.appendChild(body);
      grid.appendChild(a);
    });
    out.appendChild(grid);

    var note = document.createElement('div');
    note.className = 'pt-note';
    note.textContent = 'Bu test bilgilendirme amaçlıdır. Kişilik tek başına kariyer kararını belirlemez; ilgi alanlarını, becerilerini ve deneyimlerini de değerlendirmek için kariyer testimizi de çözebilirsin.';
    out.appendChild(note);

    var actions = document.createElement('div');
    actions.className = 'pt-actions';
    actions.style.marginTop = '18px';
    var again = document.createElement('button');
    again.type = 'button';
    again.className = 'btn btn-outline';
    again.textContent = 'Testi tekrar çöz';
    again.addEventListener('click', function () {
      answers.fill(null);
      Array.prototype.forEach.call(box.querySelectorAll('.pt-scale button'), function (b) { b.classList.remove('selected'); });
      document.getElementById('ptQuiz').style.display = 'block';
      out.style.display = 'none';
      window.scrollTo({ top: 0, behavior: 'smooth' });
      update();
    });
    var ct = document.createElement('a');
    ct.className = 'btn btn-primary';
    ct.href = base + 'career-test.php';
    ct.textContent = 'Kariyer testine başla';
    actions.appendChild(again);
    actions.appendChild(ct);
    out.appendChild(actions);

    document.getElementById('ptQuiz').style.display = 'none';
    out.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  submit.addEventListener('click', showResults);
})();
</script>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
