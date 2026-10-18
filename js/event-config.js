/**
 * EVENT-CONFIG.JS — Arokya Payanam 2026
 *
 * PURPOSE: One place to control the event's registration state.
 *          Used by BOTH the homepage banner and the /events/ page.
 *
 * ┌──────────────────────────────────────────────────────────┐
 * │  HOW TO OPEN REGISTRATION (after Razorpay is approved):  │
 * │                                                          │
 * │  1. Paste your Razorpay payment page / form link into    │
 * │     `registrationUrl` below (must start with https://).  │
 * │  2. Commit and push to GitHub. Done!                     │
 * │                                                          │
 * │  Every "Registration Opens Soon" label becomes           │
 * │  "Register Now" and the buttons start working.           │
 * │  To close registration again, set it back to ''.         │
 * └──────────────────────────────────────────────────────────┘
 *
 * HOW IT WORKS:
 *   - Elements with data-reg-soon="key1" data-reg-open="key2" get their
 *     translation key swapped depending on the state.
 *   - Elements with data-reg-btn become a real link when registration is open.
 *
 * DEPENDENCIES: i18n.js (and events-i18n.js on the /events/ page) must load first.
 */
window.OWLI_EVENT = {
  /* Razorpay link goes here, e.g. 'https://rzp.io/l/your-link'. Empty = "opens soon". */
  registrationUrl: '',

  /* The banner stops showing after this moment (the day after the event, IST). */
  endDate: '2026-11-16T00:00:00+05:30'
};

(function () {
  var cfg = window.OWLI_EVENT;
  var isOpen = /^https:\/\//i.test(cfg.registrationUrl || '');

  /* --- 1. Swap translation keys on labels / notes / status pills --- */
  document.querySelectorAll('[data-reg-soon]').forEach(function (el) {
    var key = el.getAttribute(isOpen ? 'data-reg-open' : 'data-reg-soon');
    if (!key) return;
    el.setAttribute('data-i18n', key);

    /* Write the English text now; if Tamil is saved, i18n.js re-applies it on DOMContentLoaded. */
    if (typeof translations !== 'undefined' && translations.en[key] !== undefined) {
      el.textContent = translations.en[key];
    }
  });

  /* --- 2. Turn registration buttons on (or keep them disabled) --- */
  document.querySelectorAll('[data-reg-btn]').forEach(function (btn) {
    if (isOpen) {
      btn.setAttribute('href', cfg.registrationUrl);
      btn.setAttribute('target', '_blank');
      btn.setAttribute('rel', 'noopener noreferrer');
      btn.removeAttribute('aria-disabled');
      btn.removeAttribute('tabindex');
      btn.classList.remove('is-disabled');
    } else {
      btn.removeAttribute('href');
      btn.setAttribute('aria-disabled', 'true');
      btn.setAttribute('tabindex', '-1');
      btn.classList.add('is-disabled');
    }
  });
})();
