/* ModFarm Book Card Analytics interop
   - Emits a single CustomEvent so ModFarm Core can subscribe
   - Pushes GA4 events if dataLayer/gtag exist
   Drop this in: /assets/js/analytics-book-cards.js
*/

(function () {
  'use strict';

  var CLICK_DEBOUNCE_MS = 500;
  var lastClickAt = 0;

  function now() { return Date.now ? Date.now() : new Date().getTime(); }

  function handleEvent(evtName, detail) {
    // CustomEvent for ModFarm Core
    try {
      var ev = new CustomEvent('modfarm:' + evtName.charAt(0).toUpperCase() + evtName.slice(1), { detail: detail });
      document.dispatchEvent(ev);
    } catch (e) { /* no-op */ }

    // ModFarm Core hook (if provided)
    try {
      if (window.ModFarmCore && typeof window.ModFarmCore.track === 'function') {
        window.ModFarmCore.track(evtName, detail);
      }
    } catch (e) { /* no-op */ }

    // GA4 via dataLayer or gtag (if present)
    try {
      if (window.dataLayer && Array.isArray(window.dataLayer)) {
        window.dataLayer.push(Object.assign({ event: evtName }, detail));
      } else if (typeof window.gtag === 'function') {
        window.gtag('event', evtName, detail);
      }
    } catch (e) { /* no-op */ }
  }

  function extractDetailFromAnchor(a, evtName) {
    return {
      bookId:   a.getAttribute('data-book-id') || '',
      title:    a.getAttribute('data-label')   || '',
      origin:   a.getAttribute('data-origin')  || '',
      series:   a.getAttribute('data-series')  || '',
      format:   a.getAttribute('data-format')  || '',
      href:     a.getAttribute('href')         || '',
      tracker:  a.getAttribute('data-tracker') || '',
      // Helpful GA4 fields:
      value: 1,
      event_label: a.getAttribute('data-label') || '',
      event_category: 'book_card',
      event_action: evtName
    };
  }

  // Delegated click listeners
  document.addEventListener('click', function (e) {
    var t = e.target;
    // bubble up until we find an <a> with the attribute
    while (t && t !== document) {
      if (t.matches && t.matches('a.mfb-button[data-event="book_click"]')) {
        var ts = now();
        if (ts - lastClickAt > CLICK_DEBOUNCE_MS) {
          lastClickAt = ts;
          handleEvent('book_click', extractDetailFromAnchor(t, 'book_click'));
        }
        break;
      }
      if (t.matches && t.matches('a[data-event="book_audio_play"]')) {
        var ts2 = now();
        if (ts2 - lastClickAt > CLICK_DEBOUNCE_MS) {
          lastClickAt = ts2;
          handleEvent('book_audio_play', extractDetailFromAnchor(t, 'book_audio_play'));
        }
        break;
      }
      t = t.parentNode;
    }
  }, true);
})();