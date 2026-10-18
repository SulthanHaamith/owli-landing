/**
 * EVENT-BANNER.JS — Homepage announcement popup
 *
 * PURPOSE: Shows the "Arokya Payanam 2026" popup in the center of the
 *          homepage shortly after it loads, and lets visitors close it.
 *
 * HOW IT WORKS:
 *   1. If the event is over (OWLI_EVENT.endDate in js/event-config.js), the
 *      popup is never shown.
 *   2. If the visitor closed it within the last 24 hours (remembered in
 *      localStorage), it stays hidden. After 24 hours it appears again.
 *   3. Otherwise it is revealed ~800ms after the page loads.
 *   4. While open, the page behind it can't scroll, and it can be closed
 *      by the × button, clicking the dark backdrop, or pressing Escape.
 *
 * TO REMOVE THE POPUP ENTIRELY: delete the <div id="event-banner"> block
 * in index.html (everything else keeps working).
 *
 * DEPENDENCIES: js/event-config.js (optional — popup still works without it)
 */
(function () {
  var overlay = document.getElementById('event-banner');
  if (!overlay) return;

  var STORAGE_KEY = 'owli-event-banner-closed-at';
  var HIDE_FOR_MS = 24 * 60 * 60 * 1000; /* 24 hours */

  /* --- Is the event already over? --- */
  var cfg = window.OWLI_EVENT || {};
  if (cfg.endDate && Date.now() > new Date(cfg.endDate).getTime()) return;

  /* --- Did the visitor close it recently? --- */
  try {
    var closedAt = parseInt(localStorage.getItem(STORAGE_KEY), 10);
    if (closedAt && Date.now() - closedAt < HIDE_FOR_MS) return;
  } catch (e) { /* localStorage blocked — just show the popup */ }

  function openPopup() {
    overlay.hidden = false;
    document.body.style.overflow = 'hidden'; /* lock background scroll */
    requestAnimationFrame(function () {
      overlay.classList.add('is-visible');
    });
  }

  function closePopup() {
    overlay.classList.remove('is-visible');
    document.body.style.overflow = '';
    setTimeout(function () { overlay.hidden = true; }, 300);
    try { localStorage.setItem(STORAGE_KEY, String(Date.now())); } catch (e) { /* ignore */ }
  }

  /* --- Reveal shortly after load --- */
  setTimeout(openPopup, 800);

  /* --- Close button --- */
  var closeBtn = document.getElementById('event-banner-close');
  if (closeBtn) closeBtn.addEventListener('click', closePopup);

  /* --- Click the dark backdrop (outside the card) to close --- */
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closePopup();
  });

  /* --- Escape key to close --- */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !overlay.hidden) closePopup();
  });
})();
