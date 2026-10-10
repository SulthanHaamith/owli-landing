/**
 * TREATMENT DETAILS (View Details / Show Less)
 * ------------------------------------------------
 * PURPOSE: Adds a visible "View Details" button to every service
 *          card. Tapping/clicking it expands a detail panel below
 *          the card with "Show Less" to close. Works with mouse,
 *          touch and keyboard (real <button> elements).
 *
 * NOTES:
 *   - Panel content is built from i18n keys so EN/TA stay in sync.
 *   - Only clinic-approved information is shown. Durations and fees
 *     are intentionally omitted until the clinic provides them.
 *   - Each panel has an "Enquire on WhatsApp" button with the
 *     treatment name pre-filled in the message.
 *   - Re-renders automatically when the language changes by
 *     observing the <html lang> attribute (set by i18n.js).
 */
(function () {
  'use strict';

  var WHATSAPP_NUMBER = '916379610554';
  var TITLE_RE = /^service_(\d+)_title$/;

  /* Current language, mirroring i18n.js storage convention */
  function currentLang() {
    return localStorage.getItem('owli-lang') === 'ta' ? 'ta' : 'en';
  }

  /* Safe lookup in the active language block */
  function t(key) {
    var data = (typeof translations !== 'undefined' && translations[currentLang()]) || {};
    return data[key] !== undefined ? data[key] : '';
  }

  /* Minimal HTML escaping for text inserted via innerHTML */
  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /* Build/refresh the toggle button + panel inside one card */
  function buildForCard(card) {
    var h3 = card.querySelector('h3[data-i18n^="service_"]');
    if (!h3) return;
    var m = h3.getAttribute('data-i18n').match(TITLE_RE);
    if (!m) return;
    var n = m[1];
    var panelId = 'service-details-' + n;

    /* Toggle button (created once) */
    var btn = card.querySelector('.service-details-toggle');
    if (!btn) {
      btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'service-details-toggle';
      card.appendChild(btn);
      btn.addEventListener('click', function () {
        var panel = card.querySelector('.service-details-panel');
        if (!panel) return;
        var open = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!open));
        panel.hidden = open;
        btn.textContent = panel.hidden ? t('service_details_toggle') : t('service_details_show_less');
      });
    }

    /* Panel (created once, content re-rendered on language change) */
    var panel = card.querySelector('.service-details-panel');
    if (!panel) {
      panel = document.createElement('div');
      panel.className = 'service-details-panel';
      panel.id = panelId;
      panel.hidden = true;
      card.appendChild(panel);
    }

    /* Preserve open/closed state across re-renders */
    btn.setAttribute('aria-controls', panelId);
    btn.setAttribute('aria-expanded', String(!panel.hidden));
    btn.textContent = panel.hidden ? t('service_details_toggle') : t('service_details_show_less');

    renderPanel(panel, n);
  }

  /* Fill the panel with current-language content */
  function renderPanel(panel, n) {
    var involves = t('service_' + n + '_desc');
    var title = t('service_' + n + '_title');
    var msg = (t('service_details_wa_msg') || 'Hi! I would like to enquire about {t}.')
      .replace('{t}', title);
    var url = 'https://wa.me/' + WHATSAPP_NUMBER + '?text=' + encodeURIComponent(msg);

    panel.innerHTML =
      '<p>' + escapeHtml(t('service_details_involves')) + escapeHtml(involves) + '.</p>' +
      '<p>' + escapeHtml(t('service_details_plan')) + '</p>' +
      '<p>' + escapeHtml(t('service_details_assessment')) + '</p>' +
      '<a class="service-details-enquire" href="' + url + '" target="_blank" rel="noopener noreferrer">' +
      escapeHtml(t('service_details_enquire')) + '</a>';
  }

  /* Build for all service cards */
  function buildPanels() {
    var cards = document.querySelectorAll('.service-card');
    cards.forEach(buildForCard);
  }

  document.addEventListener('DOMContentLoaded', function () {
    buildPanels();

    /* i18n.js sets document.documentElement lang on language switch —
       observe it and re-render button labels + panel content. */
    var observer = new MutationObserver(buildPanels);
    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['lang']
    });
  });
})();
