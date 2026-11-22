(function () {
  'use strict';

  const filters = document.getElementById('career-filters');
  const results = document.getElementById('career-results');
  const countEl = document.getElementById('result-count');
  const chipsEl = document.getElementById('filter-chips');

  if (!filters || !results) return;

  const base = results.dataset.base || '';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  let timer = null;
  let lastPayload = '';

  const labels = {
    q: 'Arama',
    category: 'Kategori',
    education: 'Eğitim',
    interest: 'İlgi alanı',
    sort: 'Sıralama'
  };

  const catNames = {};
  document.querySelectorAll('#f-category option').forEach((o) => { catNames[o.value] = o.textContent.trim(); });
  const eduNames = {};
  document.querySelectorAll('#f-education option').forEach((o) => { eduNames[o.value] = o.textContent.trim(); });
  const intNames = {};
  document.querySelectorAll('#f-interest option').forEach((o) => { intNames[o.value] = o.textContent.trim(); });
  const sortNames = {};
  document.querySelectorAll('#f-sort option').forEach((o) => { sortNames[o.value] = o.textContent.trim(); });

  function humanValue(key, value) {
    if (key === 'category') return catNames[value] || value;
    if (key === 'education') return eduNames[value] || value;
    if (key === 'interest') return intNames[value] || value;
    if (key === 'sort') return sortNames[value] || value;
    return value;
  }

  function collect() {
    const data = {};
    const search = document.getElementById('f-search').value.trim();
    const category = document.getElementById('f-category').value;
    const education = document.getElementById('f-education').value;
    const interest = document.getElementById('f-interest').value;
    const sort = document.getElementById('f-sort').value;
    const page = document.getElementById('f-page') ? document.getElementById('f-page').value : '';

    if (search) data.q = search;
    if (category) data.category = category;
    if (education) data.education = education;
    if (interest) data.interest = interest;
    if (sort && sort !== 'popular') data.sort = sort;
    if (page && page > 1) data.sayfa = page;
    return data;
  }

  function renderChips(data) {
    if (!chipsEl) return;
    chipsEl.innerHTML = '';
    Object.keys(data).forEach((key) => {
      const chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'badge badge-accent';
      chip.style.cursor = 'pointer';
      chip.style.border = '0';
      chip.innerHTML = '<span>' + (labels[key] || key) + ': ' + humanValue(key, data[key]) + '</span>' +
                       '<i data-lucide="x" style="width:13px;height:13px;margin-left:4px"></span>';
      chip.addEventListener('click', () => {
        const el = document.getElementById('f-' + key);
        if (el) el.value = '';
        if (key === 'sort') document.getElementById('f-sort').value = 'popular';
        lastPayload = '';
        apply();
      });
      chipsEl.appendChild(chip);
    });
    if (window.lucide) window.lucide.createIcons();
  }

  function showLoading() {
    results.style.opacity = '.45';
    results.style.pointerEvents = 'none';
  }

  function hideLoading() {
    results.style.opacity = '';
    results.style.pointerEvents = '';
  }

  async function apply() {
    const data = collect();
    const payload = JSON.stringify(data);

    renderChips(data);

    const qs = new URLSearchParams(data);
    const url = base + '/api/careers.php?' + qs.toString();
    window.history.replaceState({}, '', base + '/careers.php' + (qs.toString() ? '?' + qs.toString() : ''));

    if (payload === lastPayload) return;
    lastPayload = payload;

    showLoading();
    try {
      const res = await fetch(url, { headers: { 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' } });
      const json = await res.json();

      if (json.success) {
        results.innerHTML = json.html;
        if (countEl) countEl.innerHTML = '<strong>' + json.total + '</span> meslek bulundu';
        if (window.lucide) window.lucide.createIcons();
        bindPagination();
      } else {
        window.AK.toast(json.message || 'Sonuçlar alınamadı.', 'error');
      }
    } catch (e) {
      window.AK.toast('Bağlantı hatası oluştu.', 'error');
    } finally {
      hideLoading();
    }
  }

  function bindPagination() {
    results.querySelectorAll('.pagination-nav a').forEach((a) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        const href = a.getAttribute('href');
        if (!href) return;
        const u = new URL(href, window.location.origin);
        const params = new URLSearchParams(u.search);
        document.getElementById('f-search').value = params.get('q') || '';
        document.getElementById('f-category').value = params.get('category') || '';
        document.getElementById('f-education').value = params.get('education') || '';
        document.getElementById('f-interest').value = params.get('interest') || '';
        document.getElementById('f-sort').value = params.get('sort') || 'popular';
        document.getElementById('f-page').value = params.get('sayfa') || '1';
        lastPayload = '';
        apply();
        window.scrollTo({ top: results.offsetTop - 90, behavior: 'smooth' });
      });
    });
  }

  filters.addEventListener('input', (e) => {
    if (e.target.id === 'f-search') {
      clearTimeout(timer);
      timer = setTimeout(apply, 320);
    }
  });

  filters.addEventListener('change', () => {
    lastPayload = '';
    apply();
  });

  filters.addEventListener('submit', (e) => { e.preventDefault(); apply(); });

  renderChips(collect());
  bindPagination();
})();