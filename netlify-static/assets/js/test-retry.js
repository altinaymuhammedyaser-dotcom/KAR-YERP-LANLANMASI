/* Test tekrar / deneme karşılaştırma altyapısı (statik sürüm) */
(function () {
  'use strict';

  var KEY = 'akk_test_attempts_v1';
  var store = {};
  try { store = JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) { store = {}; }

  function saveStore() {
    try { localStorage.setItem(KEY, JSON.stringify(store)); } catch (e) {}
  }
  function attempts(key) {
    return Array.isArray(store[key]) ? store[key] : [];
  }
  function add(key, attempt) {
    if (!Array.isArray(store[key])) store[key] = [];
    store[key].push(attempt);
    if (store[key].length > 8) store[key] = store[key].slice(-8);
    saveStore();
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function modal(html) {
    var old = document.getElementById('trModal');
    if (old) old.remove();
    var wrap = document.createElement('div');
    wrap.id = 'trModal';
    wrap.style.cssText = 'position:fixed;inset:0;z-index:6000;display:flex;align-items:center;justify-content:center;padding:18px;background:rgba(8,15,28,.74);backdrop-filter:blur(4px)';
    var box = document.createElement('div');
    box.style.cssText = 'background:var(--c-bg-card,#fff);border:1px solid var(--c-border);border-radius:20px;padding:26px;max-width:680px;width:100%;max-height:86vh;overflow:auto;box-shadow:0 30px 90px -30px rgba(0,0,0,.7)';
    box.innerHTML = html;
    wrap.appendChild(box);
    document.body.appendChild(wrap);
    return { wrap: wrap, box: box, close: function () { wrap.remove(); } };
  }

  /**
   * Kullanıcı testi yeniden başlatmak istediğinde sorar.
   * 0 deneme -> hemen same
   * 1 deneme -> aynı test / aynı konu farklı sorular
   * 2+ deneme -> farklı sorular? (evet -> different)
   */
  function prompt(key, title) {
    return new Promise(function (resolve) {
      var list = attempts(key);
      if (!list.length) { resolve({ mode: 'same', different: false }); return; }

      var name = title || 'Bu test';
      var isThird = list.length >= 2;
      var html = '<h2 style="margin:0 0 8px;color:var(--c-primary)">' + esc(name) + '</h2>' +
        '<p class="muted" style="margin:0 0 18px">' +
        (isThird
          ? 'Bu testi ' + list.length + ' kez tamamladın. Şimdi <b>farklı sorularla</b> bir deneme daha yapmak ister misin?'
          : 'Bu testi daha önce tamamladın. Ne yapmak istersin?') + '</p>' +
        '<div style="display:flex;gap:10px;flex-wrap:wrap">';

      if (!isThird) {
        html += '<button type="button" class="btn btn-outline" data-r="same">Aynı testi tekrar çöz</button>';
      }
      html += '<button type="button" class="btn btn-accent" data-r="different">' +
        (isThird ? 'Evet, farklı sorular çöz' : 'Aynı konu, farklı sorular') + '</button>' +
        '<button type="button" class="btn btn-ghost" data-r="cancel">Vazgeç</button>' +
        '</div>';

      var m = modal(html);
      m.box.querySelectorAll('[data-r]').forEach(function (b) {
        b.addEventListener('click', function () {
          var r = b.getAttribute('data-r');
          m.close();
          resolve({ mode: r, different: r === 'different' });
        });
      });
    });
  }

  function answerLabel(ans) {
    if (ans == null) return '-';
    if (Array.isArray(ans)) return ans.map(answerLabel).join(' + ');
    if (typeof ans === 'object') return ans.label || ans.text || '-';
    return String(ans);
  }

  /** İki denemeyi soru soru karşılaştırır. */
  function compare(key, title) {
    var list = attempts(key);
    if (list.length < 2) return false;
    var a = list[list.length - 2];
    var b = list[list.length - 1];
    var mapA = {}, mapB = {};
    (a.answers || []).forEach(function (q) { mapA[q.q] = q; });
    (b.answers || []).forEach(function (q) { mapB[q.q] = q; });
    var keys = Object.keys(mapA);
    Object.keys(mapB).forEach(function (k) { if (keys.indexOf(k) < 0) keys.push(k); });

    var rows = keys.map(function (k, idx) {
      var qa = mapA[k] || { a: null, correct: null };
      var qb = mapB[k] || { a: null, correct: null };
      var changed = answerLabel(qa.a) !== answerLabel(qb.a);
      var mark = function (q) {
        if (q && q.correct === true) return '<span style="color:var(--c-success);font-weight:800">Doğru</span>';
        if (q && q.correct === false) return '<span style="color:var(--c-danger);font-weight:800">Yanlış</span>';
        return '<span class="muted">—</span>';
      };
      return '<div style="border:1px solid ' + (changed ? 'var(--c-warning)' : 'var(--c-border)') + ';border-left:4px solid ' + (changed ? 'var(--c-warning)' : 'var(--c-accent)') + ';border-radius:12px;padding:12px;margin-bottom:10px;background:var(--c-bg)">' +
        '<div style="font-weight:700;color:var(--c-text);margin-bottom:8px">' + (idx + 1) + '. ' + esc(k) + '</div>' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:.82rem">' +
        '<div><b style="color:var(--c-muted)">1. deneme</b><div>' + esc(answerLabel(qa.a)) + '</div><div>' + mark(qa) + '</div></div>' +
        '<div><b style="color:var(--c-muted)">2. deneme</b><div>' + esc(answerLabel(qb.a)) + '</div><div>' + mark(qb) + '</div></div>' +
        '</div>' +
        (changed ? '<div style="margin-top:8px;color:var(--c-warning);font-size:.72rem;font-weight:700">⚠ Cevap değişti</div>' : '') +
        '</div>';
    }).join('');

    var hasCorrect = (a.answers || []).some(function (q) { return q.correct === true || q.correct === false; });
    var html = '<h2 style="margin:0 0 6px;color:var(--c-primary)">' + esc(title || 'Test Karşılaştırması') + '</h2>' +
      '<p class="muted" style="margin:0 0 14px">Son iki denemenin soru bazlı karşılaştırması' +
      (hasCorrect ? ' (doğru / yanlış)' : ' — bu test tercih bazlıdır, "doğru cevap" yoktur; verdiğin cevaplar karşılaştırılır') + '.</p>' +
      '<div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">' +
      '<span class="px-badge">1. deneme: ' + esc(a.date || '') + '</span>' +
      '<span class="px-badge accent">2. deneme: ' + esc(b.date || '') + '</span></div>' +
      rows +
      '<div class="panel-actions" style="margin-top:16px"><button type="button" class="btn btn-accent" data-close>Kapat</button></div>';
    var m = modal(html);
    m.box.querySelector('[data-close]').addEventListener('click', m.close);
    return true;
  }

  window.TestRetry = {
    attempts: attempts,
    add: add,
    prompt: prompt,
    compare: compare,
    answerLabel: answerLabel
  };
})();
