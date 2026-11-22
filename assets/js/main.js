/* =====================================================================
   AKILLI KARİYER - Ana uygulama JavaScript
   ===================================================================== */
(function () {
  'use strict';

  const $  = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

  /* ---------------------------------------------------------------
     CSRF token yardimcisi
  --------------------------------------------------------------- */
  const CSRF = {
    token() {
      const meta = document.querySelector('meta[name="csrf-token"]');
      return meta ? meta.getAttribute('content') : '';
    }
  };
  window.AK = window.AK || {};
  window.AK.csrf = CSRF.token;

  /* ---------------------------------------------------------------
     Toast bildirimleri
  --------------------------------------------------------------- */
  function toast(message, type) {
    let stack = $('.toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    const el = document.createElement('div');
    el.className = 'toast ' + (type || 'success');
    const icons = { success: 'check-circle-2', error: 'alert-circle', info: 'info' };
    el.innerHTML = '<i data-lucide="' + (icons[type] || icons.success) + '"></i><span></span>';
    el.querySelector('span').textContent = message;
    stack.appendChild(el);
    if (window.lucide) window.lucide.createIcons();

    setTimeout(() => {
      el.classList.add('hide');
      setTimeout(() => el.remove(), 300);
    }, 3600);
  }
  window.AK.toast = toast;

  /* ---------------------------------------------------------------
     Mobil navigasyon
  --------------------------------------------------------------- */
  function initMobileNav() {
    const toggle = $('.nav-toggle');
    const nav = $('.mobile-nav');
    if (!toggle || !nav) return;

    const close = () => {
      nav.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    };

    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.style.overflow = open ? 'hidden' : '';
    });

    nav.addEventListener('click', (e) => {
      if (e.target === nav || e.target.closest('.mn-close')) close();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') close();
    });

    $$('.mobile-nav-links a').forEach((a) => a.addEventListener('click', close));
  }

  /* ---------------------------------------------------------------
     Kullanici acilir menusu
  --------------------------------------------------------------- */
  function initUserMenu() {
    $$('.user-menu').forEach((menu) => {
      const btn = $('.user-menu-btn', menu);
      if (!btn) return;
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('open');
      });
    });
    document.addEventListener('click', () => {
      $$('.user-menu.open').forEach((m) => m.classList.remove('open'));
    });
  }

  /* ---------------------------------------------------------------
     SSS akordiyon
  --------------------------------------------------------------- */
  function initFaq() {
    $$('.faq-q').forEach((btn) => {
      btn.addEventListener('click', () => {
        const item = btn.closest('.faq-item');
        const open = item.classList.contains('open');
        $$('.faq-item.open').forEach((i) => i.classList.remove('open'));
        if (!open) item.classList.add('open');
      });
    });
  }

  /* ---------------------------------------------------------------
     Header golge + yukari cik
  --------------------------------------------------------------- */
  function initScroll() {
    const header = $('.site-header');
    const top = $('.scroll-top');

    const onScroll = () => {
      const y = window.scrollY;
      if (header) header.classList.toggle('scrolled', y > 8);
      if (top) top.classList.toggle('show', y > 420);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (top) {
      top.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }
  }

  /* ---------------------------------------------------------------
     Sifre goster / gizle
  --------------------------------------------------------------- */
  function initPasswordToggles() {
    $$('.pw-toggle').forEach((btn) => {
      btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('input');
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = '<i data-lucide="' + (show ? 'eye-off' : 'eye') + '"></i>';
        if (window.lucide) window.lucide.createIcons();
      });
    });
  }

  /* ---------------------------------------------------------------
     Sifre guc gostergesi
  --------------------------------------------------------------- */
  function initPasswordMeter() {
    const input = $('[data-pw-strength]');
    if (!input) return;
    const meter = $('[data-pw-meter]');
    const label = $('[data-pw-label]');

    input.addEventListener('input', () => {
      const v = input.value;
      let score = 0;
      if (v.length >= 8) score++;
      if (v.length >= 12) score++;
      if (/[A-Za-zÇĞİÖŞÜçğıöşü]/.test(v) && /\d/.test(v)) score++;
      if (/[^A-Za-z0-9]/.test(v)) score++;

      const levels = [
        { w: '0%', c: 'transparent', t: 'Şifre gücü' },
        { w: '25%', c: '#E05A4E', t: 'Zayıf' },
        { w: '50%', c: '#F0A43A', t: 'Orta' },
        { w: '75%', c: '#16B8B0', t: 'İyi' },
        { w: '100%', c: '#20A779', t: 'Çok güçlü' }
      ];
      const lv = levels[Math.min(score, 4)];
      if (meter) { meter.style.width = lv.w; meter.style.background = lv.c; }
      if (label) label.textContent = v ? lv.t : '';
    });
  }

  /* ---------------------------------------------------------------
     Favori butonlari (AJAX)
  --------------------------------------------------------------- */
  function initFavorites() {
    document.addEventListener('click', async function (e) {
      const btn = e.target.closest('[data-fav]');
      if (!btn) return;
      e.preventDefault();
      if (btn.dataset.busy === '1') return;

      const careerId = btn.dataset.fav;
      const wasFavorite = btn.classList.contains('is-favorite');
      const action = wasFavorite ? 'remove' : 'add';

      btn.dataset.busy = '1';
      btn.style.opacity = '.6';

      try {
        const res = await fetch(url('api/favorites.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF.token() },
          body: JSON.stringify({ action: action, career_id: careerId })
        });
        const data = await res.json();

        if (data.success) {
          btn.classList.toggle('is-favorite', data.favorite);
          const countEl = document.querySelector('[data-fav-count]');
          if (countEl && typeof data.count === 'number') {
            countEl.textContent = data.count;
          }
          toast(data.message, 'success');
        } else {
          toast(data.message || 'İşlem tamamlanamadı.', 'error');
          if (data.redirect) setTimeout(() => { window.location.href = data.redirect; }, 900);
        }
      } catch (err) {
        toast('Bağlantı hatası oluştu.', 'error');
      } finally {
        btn.dataset.busy = '0';
        btn.style.opacity = '';
      }
    });
  }

  /* ---------------------------------------------------------------
     Form gonderiminde buton kilidi (cift tiklama onleme)
  --------------------------------------------------------------- */
  function initFormGuards() {
    $$('form[data-guard]').forEach((form) => {
      form.addEventListener('submit', function () {
        const btn = form.querySelector('[type="submit"]');
        if (!btn) return;
        btn.classList.add('is-loading');
        btn.disabled = true;
        setTimeout(function () {
          btn.classList.remove('is-loading');
          btn.disabled = false;
        }, 12000);
      });
    });
  }

  /* ---------------------------------------------------------------
     Otomatik kaydirma (hash)
  --------------------------------------------------------------- */
  function initHashScroll() {
    if (!window.location.hash) return;
    const el = document.querySelector(window.location.hash);
    if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 220);
  }

  /* ---------------------------------------------------------------
     Hero barlari animasyonu
  --------------------------------------------------------------- */
  function initHeroBars() {
    const bars = $$('[data-bar]');
    if (!bars.length) return;
    const animate = () => bars.forEach((b) => { b.style.width = (b.dataset.bar || 60) + '%'; });
    setTimeout(animate, 420);
  }

  /* ---------------------------------------------------------------
     Bootstrap ogelerini baslat
  --------------------------------------------------------------- */
  function initBootstrap() {
    if (window.bootstrap && window.bootstrap.Tooltip) {
      $$('[data-bs-toggle="tooltip"]').forEach((el) => new window.bootstrap.Tooltip(el));
    }
  }

  /* ---------------------------------------------------------------
     Grafikleri baslat
  --------------------------------------------------------------- */
  function initCharts() {
    if (!window.Chart) return;
    const nodes = $$('[data-chart]');
    if (!nodes.length) return;

    const palette = ['#16B8B0', '#1C466D', '#20A779', '#F0A43A', '#5B6BA8', '#B4568C', '#D4703A', '#2D7DD2'];
    const grid = 'rgba(20,43,74,.08)';
    const tick = '#748399';

    nodes.forEach((node) => {
      let config;
      try {
        config = JSON.parse(node.dataset.chart);
      } catch (e) {
        return;
      }
      if (!config || !config.type) return;

      const type = config.type;
      const data = config.data || {};
      const labels = data.labels || [];

      if (Array.isArray(data.datasets)) {
        data.datasets.forEach((ds, i) => {
          if (!ds.backgroundColor) {
            ds.backgroundColor = type === 'doughnut'
              ? palette
              : (ds.borderRadius !== undefined ? palette[i % palette.length] : palette[i % palette.length] + '22');
          }
          if (ds.borderWidth === undefined) ds.borderWidth = type === 'doughnut' ? 0 : 2;
          if (!ds.borderColor && type !== 'doughnut') ds.borderColor = palette[i % palette.length];
        });
      }

      const options = config.options || {};
      options.responsive = true;
      options.maintainAspectRatio = false;
      options.animation = { duration: 900, easing: 'easeOutQuart' };

      if (type !== 'doughnut' && type !== 'pie') {
        options.plugins = options.plugins || {};
        options.plugins.legend = options.plugins.legend || { display: false };
        options.plugins.tooltip = options.plugins.tooltip || {
          backgroundColor: '#142B4A',
          padding: 12,
          cornerRadius: 10,
          titleFont: { size: 13, weight: '700' },
          bodyFont: { size: 12 },
          displayColors: true,
          boxPadding: 4
        };
        options.scales = options.scales || {};
        options.scales.x = Object.assign({
          grid: { display: false, drawBorder: false },
          ticks: { color: tick, font: { size: 11 } },
          border: { display: false }
        }, options.scales.x || {});
        options.scales.y = Object.assign({
          grid: { color: grid, drawBorder: false },
          ticks: { color: tick, font: { size: 11 }, precision: 0 },
          border: { display: false },
          beginAtZero: true,
          max: 100
        }, options.scales.y || {});
      } else {
        options.plugins = options.plugins || {};
        options.plugins.legend = options.plugins.legend || {
          position: 'bottom',
          labels: { color: tick, usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 12, weight: '600' } }
        };
        options.plugins.tooltip = options.plugins.tooltip || {
          backgroundColor: '#142B4A',
          padding: 12,
          cornerRadius: 10,
          displayColors: true
        };
        if (options.cutout === undefined) options.cutout = '62%';
      }

      config.options = options;
      config.type = type;

      try {
        new window.Chart(node.getContext('2d'), config);
      } catch (e) {
        console.warn('Grafik oluşturulamadı:', e);
      }
    });
  }

  /* ---------------------------------------------------------------
     Gorsel yukleme yedegi
     ---------------------------------------------------------------
     `loading="lazy"` desteklemeyen veya viewport hesabini yapmayan
     tarayicilarda gorsellerin hic yuklenmemesini onlemek icin,
     baslatilmayan gorseller zorla "eager" moda alinir. AJAX ile
     sonradan eklenen gorseller de MutationObserver ile izlenir.
  --------------------------------------------------------------- */
  function initImageFallback() {
    var SELECTOR = 'img.career-photo, img.blog-art, img[data-fallback-load]';
    var handled = new WeakSet();

    var force = function (img) {
      if (!img || img.tagName !== 'IMG') return;
      if (img.complete && img.naturalWidth > 0) return;
      if (handled.has(img)) return;
      handled.add(img);
      img.loading = 'eager';
    };

    var scan = function (root) {
      if (!root) return;
      // Element (1), Document (9) ve DocumentFragment (11) taranabilir
      if (root.nodeType === 1 && root.tagName === 'IMG') {
        if (root.matches && root.matches(SELECTOR)) force(root);
        return;
      }
      if (root.querySelectorAll) {
        root.querySelectorAll(SELECTOR).forEach(force);
      }
    };

    scan(document);
    setTimeout(function () { scan(document); }, 500);

    if (window.MutationObserver) {
      var observer = new MutationObserver(function (mutations) {
        for (var i = 0; i < mutations.length; i++) {
          mutations[i].addedNodes.forEach(function (node) { scan(node); });
        }
      });
      observer.observe(document.documentElement, { childList: true, subtree: true });
    }
  }

  /* ---------------------------------------------------------------
     Baslangic
  --------------------------------------------------------------- */
  function init() {
    initMobileNav();
    initUserMenu();
    initFaq();
    initScroll();
    initPasswordToggles();
    initPasswordMeter();
    initFavorites();
    initFormGuards();
    initHashScroll();
    initHeroBars();
    initBootstrap();
    initCharts();
    initImageFallback();
    initDarkMode();
    initCompare();

    if (window.lucide) {
      window.lucide.createIcons();
    }
  }

  function initDarkMode() {
    const toggle = document.getElementById('dark-mode-toggle');
    if (!toggle) return;

    const sunIcon = toggle.querySelector('[data-lucide="sun"]');
    const moonIcon = toggle.querySelector('[data-lucide="moon"]');

    // Load saved theme
    const saved = localStorage.getItem('akk-kariyer-theme');
    if (saved === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      sunIcon.style.display = 'none';
      moonIcon.style.display = 'block';
    }

    toggle.addEventListener('click', () => {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      if (isDark) {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('akk-kariyer-theme', 'light');
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
      } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('akk-kariyer-theme', 'dark');
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
      }
      if (window.lucide) window.lucide.createIcons();
    });
  }

  function initCompare() {
    const compareBtns = document.querySelectorAll('[data-compare]');
    if (!compareBtns.length) return;

    let compareList = JSON.parse(localStorage.getItem('akk-kariyer-compare') || '[]');

    function updateUI() {
      compareBtns.forEach(btn => {
        const id = btn.dataset.compare;
        const isAdded = compareList.includes(id);
        btn.classList.toggle('is-selected', isAdded);
        btn.innerHTML = isAdded
          ? '<i data-lucide="check" style="width:16px;height:16px"></i>'
          : '<i data-lucide="git-compare" style="width:16px;height:16px"></i>';
      });
      if (window.lucide) window.lucide.createIcons();
    }

    compareBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.compare;
        const idx = compareList.indexOf(id);
        if (idx >= 0) {
          compareList.splice(idx, 1);
        } else if (compareList.length < 3) {
          compareList.push(id);
        } else {
          window.AK.toast('En fazla 3 meslek karşılaştırılabilir.', 'warning');
          return;
        }
        localStorage.setItem('akk-kariyer-compare', JSON.stringify(compareList));
        updateUI();
      });
    });

    updateUI();

    // Add compare button to header if items exist
    if (compareList.length > 0) {
      const headerActions = document.querySelector('.header-actions');
      if (headerActions && !document.getElementById('compare-btn')) {
        const cmpBtn = document.createElement('button');
        cmpBtn.type = 'button';
        cmpBtn.id = 'compare-btn';
        cmpBtn.className = 'btn btn-accent btn-sm';
        cmpBtn.innerHTML = '<i data-lucide="git-compare" style="width:18px;height:18px"></i> Karşılaştır (' + compareList.length + ')';
        cmpBtn.addEventListener('click', () => {
          window.location.href = url('career-compare.php?id=' + compareList.join(','));
        });
        headerActions.insertBefore(cmpBtn, headerActions.firstChild);
        if (window.lucide) window.lucide.createIcons();
      }
    }
  }

  function initDarkMode() {
    const toggle = document.getElementById('dark-mode-toggle');
    if (!toggle) return;

    const sunIcon = toggle.querySelector('[data-lucide="sun"]');
    const moonIcon = toggle.querySelector('[data-lucide="moon"]');

    // Load saved theme
    const saved = localStorage.getItem('akk-kariyer-theme');
    if (saved === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      sunIcon.style.display = 'none';
      moonIcon.style.display = 'block';
    }

    toggle.addEventListener('click', () => {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      if (isDark) {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('akk-kariyer-theme', 'light');
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
      } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('akk-kariyer-theme', 'dark');
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
      }
      if (window.lucide) window.lucide.createIcons();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
