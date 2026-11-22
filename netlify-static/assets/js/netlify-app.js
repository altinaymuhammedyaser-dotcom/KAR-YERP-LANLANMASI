/* Netlify statik sürüm: oturum, üye alanı ve ortak davranışlar */
(function () {
  'use strict';

  var KEY = 'akk_session';
  var base = '/';

  function session() {
    try { return JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) { return null; }
  }
  function setSession(data) {
    localStorage.setItem(KEY, JSON.stringify(data));
  }
  function clearSession() { localStorage.removeItem(KEY); }

  function friendlyName(email) {
    if (!email) return 'Misafir';
    var n = email.split('@')[0].replace(/[._-]+/g, ' ').trim();
    return n.replace(/\b\w/g, function (c) { return c.toLocaleUpperCase('tr-TR'); }) || 'Üye';
  }

  function updateHeader() {
    var s = session();
    var actions = document.querySelector('.header-actions');
    if (!actions) return;
    var loginLink = actions.querySelector('a[href="/login"]');
    var regLink = actions.querySelector('a[href="/register"]');
    if (s) {
      if (loginLink) {
        loginLink.href = '/dashboard/index.html';
        loginLink.textContent = 'Panelim';
        loginLink.setAttribute('aria-label', 'Üye paneli');
      }
      if (regLink) {
        regLink.href = '#';
        regLink.textContent = 'Çıkış Yap';
        regLink.setAttribute('aria-label', 'Çıkış yap');
        regLink.addEventListener('click', function (e) {
          e.preventDefault();
          clearSession();
          location.href = '/';
        });
      }
      document.querySelectorAll('.mobile-nav-links a[href="/login"]').forEach(function (a) {
        a.href = '/dashboard/index.html';
        a.innerHTML = '<i data-lucide="layout-dashboard" style="width:16px;height:16px"></i> Panelim';
      });
      document.querySelectorAll('.mobile-nav-links a[href="/register"]').forEach(function (a) {
        a.href = '#';
        a.textContent = 'Çıkış Yap';
        a.addEventListener('click', function (e) { e.preventDefault(); clearSession(); location.href = '/'; });
      });
    }
  }

  function initAuthForms() {
    var form = document.querySelector('form.auth-form');
    if (!form) return;
    var isRegister = /register/i.test(location.pathname);
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = (form.querySelector('input[name="email"]') || {}).value || '';
      var pw = (form.querySelector('input[name="password"]') || {}).value || '';
      var nameInput = form.querySelector('input[name="name"]');
      if (!/^\S+@\S+\.\S+$/.test(email)) { alert('Geçerli bir e-posta adresi girin.'); return; }
      if (!pw || pw.length < 6) { alert('Şifre en az 6 karakter olmalı.'); return; }
      setSession({
        email: email,
        name: nameInput && nameInput.value ? nameInput.value : friendlyName(email),
        registeredAt: new Date().toISOString(),
        demo: email.toLowerCase() === 'demo@akillikariyer.com'
      });
      location.href = '/dashboard/index.html';
    });
  }

  function initLogout() {
    document.querySelectorAll('[data-logout], a[href="/logout"], a[href="/logout.html"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        clearSession();
        location.href = '/';
      });
    });
  }

  function initDashboard() {
    var page = document.body.getAttribute('data-dashboard');
    if (!page) return;
    var s = session();
    var gate = document.getElementById('authGate');
    if (!s) {
      if (gate) gate.style.display = 'flex';
      return;
    }
    if (gate) gate.style.display = 'none';
    document.querySelectorAll('[data-user-name]').forEach(function (n) { n.textContent = s.name || friendlyName(s.email); });
    document.querySelectorAll('[data-user-email]').forEach(function (n) { n.textContent = s.email; });
    var initials = (s.name || 'AK').split(' ').map(function (x) { return x[0]; }).slice(0, 2).join('').toLocaleUpperCase('tr-TR');
    document.querySelectorAll('[data-user-initials]').forEach(function (n) { n.textContent = initials; });
    var active = document.querySelector('[data-nav="' + page + '"]');
    if (active) active.classList.add('is-active');
  }

  function initDemoLoginButton() {
    document.querySelectorAll('[data-demo-login]').forEach(function (b) {
      b.addEventListener('click', function () {
        setSession({ email: 'demo@akillikariyer.com', name: 'Demo Kullanıcı', demo: true, registeredAt: new Date().toISOString() });
        location.href = '/dashboard/index.html';
      });
    });
  }

  function initMemberOnly() {
    var s = session();
    document.querySelectorAll('a[href="/career-test"], a[href="/career-test/"], [data-member-only]').forEach(function (el) {
      var target = el.closest('li') || el;
      if (!s) {
        target.setAttribute('data-ct-hidden', '1');
        target.style.setProperty('display', 'none', 'important');
      } else if (target.getAttribute('data-ct-hidden')) {
        target.style.removeProperty('display');
        target.removeAttribute('data-ct-hidden');
      }
    });
    var gate = document.getElementById('ctGate');
    if (gate) gate.style.display = s ? 'none' : 'flex';
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else { run(); }

  function run() {
    updateHeader();
    initAuthForms();
    initLogout();
    initDemoLoginButton();
    initDashboard();
    initMemberOnly();
    if (window.lucide) window.lucide.createIcons();
  }

  window.AKSession = { get: session, set: setSession, clear: clearSession, name: friendlyName };
})();
