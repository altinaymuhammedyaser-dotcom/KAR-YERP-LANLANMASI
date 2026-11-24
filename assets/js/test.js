/* =====================================================================
   AKILLI KARİYER - Kariyer testi JavaScript
   Sorular arası gezinme, cevap kaydetme ve ilerleme takibi
   ===================================================================== */
(function () {
  'use strict';

  const root = document.getElementById('test-root');
  if (!root) return;

  const url = (p) => root.dataset.base + '/' + p;
  const CSRF_TOKEN = root.dataset.csrf;

  let questions = [];
  let answers = {};      // questionId -> { option_ids: [], option_keys: [] }
  let current = 0;
  let sessionId = parseInt(root.dataset.session || '0', 10);
  let total = parseInt(root.dataset.total || '0', 10);
  const answered = new Set();
  const restartMode = root.dataset.restartMode || '';
  const hasCompleted = root.dataset.hasCompleted === '1';
  const completedSessionId = parseInt(root.dataset.completedSessionId || '0', 10);

  /* ---------------------------------------------------------------
     Yardimcilar
  --------------------------------------------------------------- */
  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
  }

  function optionIcon(name) {
    return '<i data-lucide="' + name + '" style="width:18px;height:18px"></i>';
  }

  /* ---------------------------------------------------------------
     Soru render
  --------------------------------------------------------------- */
  function renderQuestion(index) {
    const q = questions[index];
    const wrap = document.getElementById('question-area');
    const total = questions.length;
    const answer = answers[q.id];
    const checked = answer ? answer.option_ids : [];

    let head = `
      <div class="q-head">
        <span class="q-num">Soru ${index + 1} / ${total}</span>
        <span class="badge badge-accent">${esc(q.category_name)}</span>
      </div>`;

    let body = '';
    const opts = q.options || [];

    if (q.question_type === 'scale') {
      body = `<div class="scale-row">`;
      opts.forEach((o) => {
        const on = checked.includes(o.id) ? 'checked' : '';
        body += `
          <label class="scale-opt">
            <input type="radio" name="q_${q.id}" value="${o.id}" ${on}>
            <span class="scale-dot"></span>
            <span class="scale-label">${esc(o.option_text)}</span>
          </label>`;
      });
      body += `</div>`;
    } else if (q.question_type === 'card') {
      body = `<div class="card-grid-options">`;
      opts.forEach((o) => {
        const on = checked.includes(o.id) ? 'checked' : '';
        body += `
          <label class="card-opt">
            <input type="radio" name="q_${q.id}" value="${o.id}" ${on}>
            <span class="co-icon">${optionIcon(q.category_icon || 'sparkles')}</span>
            <span>
              <span class="co-title">${esc(o.option_text)}</span>
              ${o.option_key ? `<span class="co-sub">${esc(o.option_key)}</span>` : ''}
            </span>
          </label>`;
      });
      body += `</div>`;
    } else if (q.question_type === 'multiple') {
      body = `<div class="option-list">`;
      opts.forEach((o) => {
        const on = checked.includes(o.id) ? 'checked' : '';
        body += `
          <label class="option-item multi">
            <input type="checkbox" name="q_${q.id}" value="${o.id}" ${on}>
            <span class="option-marker"></span>
            <span class="option-text">${esc(o.option_text)}</span>
          </label>`;
      });
      body += `</div>`;
    } else if (q.question_type === 'scenario') {
      body = `<div class="option-list">`;
      opts.forEach((o) => {
        const on = checked.includes(o.id) ? 'checked' : '';
        body += `
          <label class="option-item">
            <input type="radio" name="q_${q.id}" value="${o.id}" ${on}>
            <span class="option-marker"></span>
            <span class="option-text">${esc(o.option_text)}</span>
          </label>`;
      });
      body += `</div>`;
    } else {
      body = `<div class="option-list">`;
      opts.forEach((o, i) => {
        const on = checked.includes(o.id) ? 'checked' : '';
        body += `
          <label class="option-item">
            <input type="radio" name="q_${q.id}" value="${o.id}" ${on}>
            <span class="option-marker"></span>
            <span class="option-text">${esc(o.option_text)}</span>
            <span class="option-key">${esc(o.option_key || String.fromCharCode(65 + i))}</span>
          </label>`;
      });
      body += `</div>`;
    }

    const min = q.min_select || 1;
    const max = q.max_select || 1;
    const hint = q.question_type === 'multiple'
      ? `<span class="test-save-note">${optionIcon('info')} En az ${min}, en fazla ${max} seçenek seçebilirsiniz.</span>`
      : '';

    wrap.innerHTML = `
      <div class="question-card" id="question-card">
        ${head}
        <h2 class="q-title">${esc(q.question_text)}</h2>
        ${q.helper_text ? `<p class="q-helper">${esc(q.helper_text)}</p>` : ''}
        ${body}
        <div class="test-footer">
          <div class="test-nav">
            <button type="button" class="btn btn-outline" id="prev-btn" ${index === 0 ? 'disabled' : ''}>
              ${optionIcon('arrow-left')} Önceki
            </button>
            <button type="button" class="btn btn-primary" id="next-btn">
              ${index === total - 1 ? 'Testi Tamamla' : 'Sonraki'} ${optionIcon('arrow-right')}
            </button>
          </div>
          <div>${hint}</div>
        </div>
      </div>`;

    bindQuestionEvents(q);
    updateProgress();
    if (window.lucide) window.lucide.createIcons();

    const card = document.getElementById('question-card');
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  /* ---------------------------------------------------------------
     Soru olaylari
  --------------------------------------------------------------- */
  function bindQuestionEvents(q) {
    const inputs = Array.from(document.querySelectorAll(`[name="q_${q.id}"]`));
    const max = q.max_select || 1;

    inputs.forEach((input) => {
      input.addEventListener('change', () => {
        if (q.question_type === 'multiple' && input.checked) {
          const selected = inputs.filter((i) => i.checked);
          if (selected.length > max) {
            input.checked = false;
            window.AK.toast('Bu soru için en fazla ' + max + ' seçenek seçebilirsiniz.', 'error');
            return;
          }
        }
        collectAndSave(q, inputs);
      });
    });

    const prev = document.getElementById('prev-btn');
    const next = document.getElementById('next-btn');

    if (prev) prev.addEventListener('click', () => {
      if (current > 0) {
        current--;
        renderQuestion(current);
      }
    });

    if (next) next.addEventListener('click', () => {
      const chosen = inputs.filter((i) => i.checked);
      if (chosen.length < (q.min_select || 1)) {
        window.AK.toast('Lütfen bu soru için bir seçim yapın.', 'error');
        return;
      }
      if (current < questions.length - 1) {
        current++;
        renderQuestion(current);
      } else {
        finishTest();
      }
    });
  }

  /* ---------------------------------------------------------------
     Cevap toplama & kaydetme
  --------------------------------------------------------------- */
  function collectAndSave(q, inputs) {
    const selected = inputs.filter((i) => i.checked);
    const ids = selected.map((i) => parseInt(i.value, 10));

    const optionMap = {};
    (q.options || []).forEach((o) => { optionMap[o.id] = o.option_key; });
    const keys = ids.map((id) => optionMap[id]).filter(Boolean);

    answers[q.id] = { option_ids: ids, option_keys: keys };
    answered.add(q.id);

    saveAnswer(q.id, ids);
    updateProgress();
  }

  async function saveAnswer(questionId, optionIds) {
    const note = document.getElementById('save-note-text');
    if (note) note.textContent = 'Kaydediliyor...';

    try {
      const res = await fetch(url('api/test.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({
          action: 'answer',
          session_id: sessionId,
          question_id: questionId,
          option_ids: optionIds
        })
      });
      const data = await res.json();

      if (note) {
        note.textContent = data.success ? 'Cevabınız kaydedildi' : 'Kaydedilemedi';
        note.style.color = data.success ? '#20A779' : '#E05A4E';
      }
      if (!data.success && data.redirect) {
        setTimeout(() => { window.location.href = data.redirect; }, 1000);
      }
    } catch (e) {
      if (note) {
        note.textContent = 'Bağlantı hatası';
        note.style.color = '#E05A4E';
      }
    }
  }

  /* ---------------------------------------------------------------
     Ilerleme guncelleme
  --------------------------------------------------------------- */
  function updateProgress() {
    const pct = questions.length ? Math.round((answered.size / questions.length) * 100) : 0;
    const bar = document.getElementById('progress-bar');
    const label = document.getElementById('progress-label');
    const ring = document.getElementById('progress-ring');
    const ringLabel = document.getElementById('progress-ring-label');
    const countEl = document.getElementById('answered-count');

    if (bar) bar.style.width = pct + '%';
    if (label) label.textContent = '%' + pct;
    if (ring) {
      const c = 2 * Math.PI * 34;
      ring.style.strokeDasharray = c;
      ring.style.strokeDashoffset = c - (c * pct / 100);
    }
    if (ringLabel) ringLabel.textContent = pct + '%';
    if (countEl) countEl.textContent = answered.size + ' / ' + questions.length;

    // Kategori durumu
    document.querySelectorAll('.test-cat-item').forEach((item) => {
      const cat = item.dataset.category;
      const catQs = questions.filter((q) => q.category_name === cat);
      const done = catQs.filter((q) => answered.has(q.id)).length;
      item.classList.toggle('done', done === catQs.length && catQs.length > 0);
    });
  }

  /* ---------------------------------------------------------------
     Testi tamamla
  --------------------------------------------------------------- */
  async function finishTest() {
    if (answered.size < questions.length) {
      const missing = questions.length - answered.size;
      const proceed = window.confirm(
        missing + ' soru cevaplanmadı. Eksik sorularla testi tamamlamak istiyor musunuz? ' +
        'Analiz cevapladığınız sorular üzerinden yapılacaktır.'
      );
      if (!proceed) return;
    }

    const btn = document.getElementById('next-btn');
    if (btn) {
      btn.classList.add('is-loading');
      btn.disabled = true;
    }

    try {
      const res = await fetch(url('api/test.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ action: 'complete', session_id: sessionId })
      });
      const data = await res.json();

      if (data.success && data.redirect) {
        // Eger farkli sorular modu ile ikinci bir test bittiyse, karsilastirma sayfasina git
        if (restartMode === 'different' && data.comparisonUrl) {
          window.location.href = data.comparisonUrl;
        } else {
          window.location.href = data.redirect;
        }
      } else {
        window.AK.toast(data.message || 'Test tamamlanamadı.', 'error');
        if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
      }
    } catch (e) {
      window.AK.toast('Bağlantı hatası oluştu.', 'error');
      if (btn) { btn.classList.remove('is-loading'); btn.disabled = false; }
    }
  }

  /* ---------------------------------------------------------------
     Baslangic
  --------------------------------------------------------------- */
  function init() {
    try {
      questions = JSON.parse(root.dataset.questions || '[]');
    } catch (e) {
      questions = [];
    }

    if (!questions.length) {
      document.getElementById('question-area').innerHTML =
        '<div class="empty-state"><h3>Sorular yüklenemedi</h3><p>Lütfen sayfayı yenileyin veya yöneticinize başvurun.</p></div>';
      return;
    }

    try {
      const saved = JSON.parse(root.dataset.answers || '{}');
      Object.keys(saved).forEach((k) => {
        answers[k] = saved[k];
        if (saved[k].option_ids && saved[k].option_ids.length) answered.add(parseInt(k, 10));
      });
    } catch (e) { /* yoksay */ }

    current = parseInt(root.dataset.current || '0', 10);
    if (isNaN(current) || current < 0) current = 0;
    if (current >= questions.length) current = 0;

    renderQuestion(current);

    // Klavye kisayollari
    document.addEventListener('keydown', (e) => {
      if (e.target.matches('input, textarea')) return;
      if (e.key === 'ArrowRight') {
        const btn = document.getElementById('next-btn');
        if (btn) btn.click();
      }
      if (e.key === 'ArrowLeft') {
        const btn = document.getElementById('prev-btn');
        if (btn) btn.click();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
