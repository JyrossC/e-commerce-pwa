/**
 * NovaMart — PWA Service Worker Registration
 */
(function () {
  'use strict';

  if ('serviceWorker' in navigator) {
    const appUrl = document.querySelector('meta[name="app-url"]')?.content || '';

    window.addEventListener('load', () => {
      navigator.serviceWorker
        .register(appUrl + '/sw.js', { scope: appUrl + '/' })
        .then((reg) => {
          console.log('[PWA] Service Worker registered. Scope:', reg.scope);

          // Check for updates
          reg.addEventListener('updatefound', () => {
            const newWorker = reg.installing;
            newWorker?.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                if (window.NovaMart) {
                  window.NovaMart.toast('info', 'Update available!', 'Refresh the page for the latest version.');
                }
              }
            });
          });
        })
        .catch((err) => {
          console.warn('[PWA] Service Worker registration failed:', err);
        });
    });
  }
})();
