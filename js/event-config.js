/**
 * EVENT-CONFIG.JS — Arokya Payanam 2026
 *
 * PURPOSE: One place to control the event's registration state.
 *          Used by BOTH the homepage banner and the /events/ page.
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │  REGISTRATION MODES (registrationMode):                      │
 * │                                                              │
 * │  'preview' — Progress is visible: ticket prices, the full    │
 * │              form and coupon previews all work, but the      │
 * │              "Proceed to Payment" button stays disabled and  │
 * │              the server REFUSES registrations (503 not_open).│
 * │              Safe state while the payment integration is     │
 * │              still being verified.                           │
 * │  'open'    — Buttons say "Register Now", the form submits    │
 * │              and the server accepts registrations. Flip to   │
 * │              this ONLY after live Coaxlure credentials and  │
 * │              a successful Rs 1 test payment (README §10).    │
 * │  'closed'  — Everything reads "Registration Opens Soon" and │
 * │              the form is locked. Use to take it down again.  │
 * └──────────────────────────────────────────────────────────────┘
 *
 * IMPORTANT: The server has its OWN switch (owli-config.php ->
 * registration.mode; api/quote.php reports it as "mode"). The form
 * only unlocks when BOTH sides say 'open', so editing this file
 * alone can never open payments on the live site.
 *
 * HOW IT WORKS:
 *   - Elements with data-reg-soon / data-reg-preview / data-reg-open
 *     get the translation key matching the current mode.
 *   - Elements with data-reg-btn only become real links in 'open' mode.
 *
 * DEPENDENCIES: js/i18n.js must load first.
 */
window.OWLI_EVENT = {
  /* MAIN SWITCH for the built-in registration form (/events/#register-form).
     'preview' | 'open' | 'closed' — see the box comment above. */
  registrationMode: 'preview',

  /* Optional external link (legacy). If set, buttons open it instead of the form. */
  registrationUrl: '',

  /* The banner stops showing after this moment (the day after the event, IST). */
  endDate: '2026-11-16T00:00:00+05:30'
};

(function () {
  var cfg = window.OWLI_EVENT || {};

  /* Accept the old boolean flag too, so a stale cached copy still behaves sanely. */
  var mode = cfg.registrationMode;
  if (mode !== 'preview' && mode !== 'open' && mode !== 'closed') {
    mode = cfg.registrationOpen === true ? 'open' : 'closed';
  }
  var hasExternalUrl = /^https:\/\//i.test(cfg.registrationUrl || '');
  if (hasExternalUrl) mode = 'open';

  /* --- 1. Swap translation keys on labels / notes / status pills --- */
  var keyAttr = mode === 'open' ? 'data-reg-open' : (mode === 'preview' ? 'data-reg-preview' : 'data-reg-soon');
  document.querySelectorAll('[data-reg-soon]').forEach(function (el) {
    var key = el.getAttribute(keyAttr) || el.getAttribute('data-reg-soon');
    if (!key) return;
    el.setAttribute('data-i18n', key);

    /* Write the English text now; if Tamil is saved, i18n.js re-applies it on DOMContentLoaded. */
    if (typeof translations !== 'undefined' && translations.en[key] !== undefined) {
      el.textContent = translations.en[key];
    }
  });

  /* --- 2. Registration buttons work only when registration is open --- */
  document.querySelectorAll('[data-reg-btn]').forEach(function (btn) {
    if (mode === 'open') {
      if (hasExternalUrl) {
        btn.setAttribute('href', cfg.registrationUrl);
        btn.setAttribute('target', '_blank');
        btn.setAttribute('rel', 'noopener noreferrer');
      } else {
        /* Built-in form: scroll to it on /events/, or go there from other pages */
        btn.setAttribute('href', document.body.classList.contains('events-page') ? '#register-form' : '/events/#register-form');
      }
      btn.removeAttribute('aria-disabled');
      btn.removeAttribute('tabindex');
      btn.classList.remove('is-disabled');
    } else {
      /* 'closed' AND 'preview': buttons stay disabled (preview shows progress elsewhere) */
      btn.removeAttribute('href');
      btn.setAttribute('aria-disabled', 'true');
      btn.setAttribute('tabindex', '-1');
      btn.classList.add('is-disabled');
    }
  });
})();
