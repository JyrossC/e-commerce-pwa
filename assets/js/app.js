/**
 * NovaMart — Main Application JavaScript
 * Handles: Toast notifications, cart badge, header scroll, mobile nav, offline detection, theme toggle
 */

const NovaMart = (() => {
  'use strict';

  // ─── Cart Badge ────────────────────────────────────────────────
  function updateCartCount(n) {
    const badge = document.getElementById('cart-count');
    if (!badge) return;
    badge.textContent = n;
    badge.classList.toggle('hidden', n <= 0);
    if (n > 0) {
      badge.style.animation = 'none';
      requestAnimationFrame(() => { badge.style.animation = 'badgePop 0.4s ease'; });
    }
  }

  // ─── Toast Notifications ───────────────────────────────────────
  const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };

  function toast(type = 'info', title = '', message = '', duration = 3500) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.innerHTML = `
      <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
      <div class="toast-body">
        <div class="toast-title">${escHtml(title)}</div>
        ${message ? `<div class="toast-message">${escHtml(message)}</div>` : ''}
      </div>
      <button class="toast-close" onclick="this.parentElement.remove()">✕</button>`;
    container.appendChild(el);

    setTimeout(() => {
      el.classList.add('hiding');
      el.addEventListener('animationend', () => el.remove(), { once: true });
    }, duration);
  }

  // ─── HTML Escaper ──────────────────────────────────────────────
  function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
  }

  // ─── Header scroll effect ──────────────────────────────────────
  function initScrollHeader() {
    const header = document.getElementById('site-header');
    if (!header) return;
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 10);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ─── Mobile Navigation ─────────────────────────────────────────
  function initMobileNav() {
    const hamburger = document.getElementById('hamburger');
    const mobileNav  = document.getElementById('mobile-nav');
    if (!hamburger || !mobileNav) return;

    hamburger.addEventListener('click', () => {
      const open = hamburger.classList.toggle('open');
      hamburger.setAttribute('aria-expanded', open);
      mobileNav.hidden = !open;
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !mobileNav.contains(e.target)) {
        hamburger.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
        mobileNav.hidden = true;
      }
    });
  }

  // ─── Mobile Search Toggle ──────────────────────────────────────
  function initSearchToggle() {
    const toggle    = document.getElementById('search-toggle');
    const mobileSearch = document.getElementById('mobile-search');
    if (!toggle || !mobileSearch) return;

    toggle.addEventListener('click', () => {
      mobileSearch.hidden = !mobileSearch.hidden;
      if (!mobileSearch.hidden) {
        mobileSearch.querySelector('input')?.focus();
      }
    });
  }

  // ─── User Dropdown ─────────────────────────────────────────────
  function initUserDropdown() {
    const toggle  = document.getElementById('user-menu-toggle');
    const dropdown = document.getElementById('user-dropdown');
    if (!toggle || !dropdown) return;

    toggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = dropdown.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open);
    });

    document.addEventListener('click', () => {
      dropdown.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  }

  // ─── Offline Detection ─────────────────────────────────────────
  function initOfflineDetection() {
    const banner = document.getElementById('offline-banner');
    if (!banner) return;

    const update = () => {
      banner.hidden = navigator.onLine;
    };
    window.addEventListener('online',  update);
    window.addEventListener('offline', update);
    update();
  }

  // ─── PWA Install Prompt ────────────────────────────────────────
  function initInstallPrompt() {
    let deferredPrompt = null;
    const promptEl   = document.getElementById('install-prompt');
    const acceptBtn  = document.getElementById('install-accept');
    const dismissBtn = document.getElementById('install-dismiss');

    if (!promptEl) return;

    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      // Show after small delay if not dismissed before
      if (!localStorage.getItem('pwa-dismissed')) {
        setTimeout(() => { promptEl.hidden = false; }, 4000);
      }
    });

    acceptBtn?.addEventListener('click', async () => {
      promptEl.hidden = true;
      if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        deferredPrompt = null;
        if (outcome === 'accepted') toast('success', 'Installed!', APP_NAME + ' added to your home screen.');
      }
    });

    dismissBtn?.addEventListener('click', () => {
      promptEl.hidden = true;
      localStorage.setItem('pwa-dismissed', '1');
    });

    window.addEventListener('appinstalled', () => {
      promptEl.hidden = true;
      toast('success', 'Installed!', 'You can now use ' + APP_NAME + ' from your home screen.');
    });
  }

  // ─── Theme Toggle ──────────────────────────────────────────────
  function initTheme() {
    const saved = localStorage.getItem('nm-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', saved);
  }

  function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next    = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('nm-theme', next);
  }

  // ─── CSRF Token helper ─────────────────────────────────────────
  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  function appUrl() {
    return document.querySelector('meta[name="app-url"]')?.content || '';
  }

  // Read APP_NAME from page title suffix
  const APP_NAME = document.title.includes('—') ? document.title.split('—').pop().trim() : 'NovaMart';

  // ─── Init ──────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initScrollHeader();
    initMobileNav();
    initSearchToggle();
    initUserDropdown();
    initOfflineDetection();
    initInstallPrompt();
  });

  // Public API
  return { toast, updateCartCount, toggleTheme, escHtml, csrfToken, appUrl };
})();

// Global helpers used inline on pages
window.NovaMart = NovaMart;
