/**
 * EVENT-REGISTER.JS — registration form on /events/
 *
 * PURPOSE: Shows the right fields for each category, validates them, shows the
 *          server price, sends the form to api/register.php and opens the
 *          Coaxlure payment page. Also checks payment status when the attendee
 *          comes back.
 *
 * NOTES:   - The price shown is DISPLAY ONLY. api/register.php recalculates it.
 *          - Registration modes (js/event-config.js + the server switch must AGREE):
 *              'open'    -> the form submits and payment proceeds
 *              'preview' -> the form is explorable, prices/coupons work,
 *                           but the Pay button stays disabled and the server
 *                           refuses submissions (503). Shows progress while
 *                           the payment integration is still being verified.
 *              'closed'  -> the whole form is locked
 *          - Only the registration reference is kept in the browser
 *            (localStorage "owli-reg-ref"), never personal details.
 *
 * DEPENDENCIES: i18n.js (translations) and event-config.js must load first.
 */
(function () {
  var form = document.getElementById('register-form');
  if (!form) return;

  var API = '../api/';
  var cfg = window.OWLI_EVENT || {};
  var fields = document.getElementById('evf-fields');
  var closedMsg = document.getElementById('evf-closed');
  var catSelect = document.getElementById('evf-category');
  var priceBox = document.getElementById('evf-price');
  var priceVal = document.getElementById('evf-price-value');
  var formError = document.getElementById('evf-form-error');
  var submitBtn = document.getElementById('evf-submit');
  var couponInput = document.getElementById('evf-coupon');
  var couponBtn = document.getElementById('evf-coupon-apply');
  var statusBox = document.getElementById('evf-status');
  var STORE = 'owli-reg-ref';
  var serverMode = 'closed';   /* reported by api/quote.php ('open'|'preview'|'closed') */

  /* The client-side mode from js/event-config.js (old boolean flag still honoured). */
  function clientMode() {
    var m = cfg.registrationMode;
    if (m !== 'preview' && m !== 'open' && m !== 'closed') {
      m = cfg.registrationOpen === true ? 'open' : 'closed';
    }
    return m;
  }

  /* ---------- helpers ---------- */
  function lang() {
    var l = 'en';
    try { l = localStorage.getItem('owli-lang') || 'en'; } catch (e) {}
    return l === 'ta' ? 'ta' : 'en';
  }
  function t(key) {
    var d = (window.translations && translations[lang()]) || {};
    return d[key] !== undefined ? d[key] : ((translations && translations.en[key]) || key);
  }
  function store(ref) { try { if (ref) localStorage.setItem(STORE, ref); else localStorage.removeItem(STORE); } catch (e) {} }
  function stored() { try { return localStorage.getItem(STORE) || ''; } catch (e) { return ''; } }

  function api(path, opts) {
    return fetch(API + path, opts).then(function (r) {
      return r.json().then(function (j) { return { http: r.status, body: j }; });
    });
  }

  function setError(name, msg) {
    var el = form.querySelector('[data-err-for="' + name + '"]');
    if (el) el.textContent = msg || '';
  }
  function clearErrors() {
    form.querySelectorAll('[data-err-for]').forEach(function (e) { e.textContent = ''; });
    formError.textContent = '';
  }
  function money(n) { return '₹' + n; }

  /* ---------- lock / unlock ----------
     open    : everything active (both switches must say 'open')
     preview : form + prices explorable, Pay button disabled, server refuses
     closed  : whole form locked */
  function applyOpenState() {
    var open    = clientMode() === 'open' && serverMode === 'open';
    var preview = !open && (clientMode() === 'preview' || serverMode === 'preview');

    if (open) {
      closedMsg.hidden = true;
      fields.disabled = false;
      submitBtn.disabled = false;
    } else if (preview) {
      closedMsg.hidden = false;
      closedMsg.setAttribute('data-i18n', 'evf_preview_note');
      closedMsg.textContent = t('evf_preview_note');
      fields.disabled = false;      /* let visitors explore the form */
      submitBtn.disabled = true;    /* ...but never start a payment */
    } else {
      closedMsg.hidden = false;
      closedMsg.setAttribute('data-i18n', 'evf_closed');
      closedMsg.textContent = t('evf_closed');
      fields.disabled = true;
      submitBtn.disabled = true;
    }
  }

  /* ---------- category blocks ---------- */
  function showCategory() {
    var cat = catSelect.value;
    form.querySelectorAll('.ev-cat-block').forEach(function (b) {
      var on = b.getAttribute('data-cat') === cat;
      b.hidden = !on;
      b.querySelectorAll('input, select, textarea').forEach(function (el) { el.disabled = !on; });
    });
    highlightCategory();
    refreshPrice();
  }

  /* ---------- price list under the category dropdown ---------- */
  var priceList = document.getElementById('evf-price-list');
  var CATS = ['student', 'general', 'bni', 'doctor'];

  function renderPriceList(prices) {
    priceList.textContent = '';
    CATS.forEach(function (c) {
      if (prices[c] === undefined) return;
      var li = document.createElement('li');
      li.setAttribute('data-price-cat', c);
      var label = document.createElement('span');
      label.setAttribute('data-i18n', 'evf_cat_' + c);   /* i18n.js keeps this translated */
      label.textContent = t('evf_cat_' + c);
      var amt = document.createElement('strong');
      amt.textContent = money(prices[c]);
      li.appendChild(label);
      li.appendChild(amt);
      priceList.appendChild(li);
    });
    priceList.hidden = !priceList.children.length;
    highlightCategory();
  }
  function highlightCategory() {
    priceList.querySelectorAll('li').forEach(function (li) {
      li.classList.toggle('is-selected', li.getAttribute('data-price-cat') === catSelect.value);
    });
  }

  /* ---------- price preview ---------- */
  function refreshPrice(showCouponError) {
    var cat = catSelect.value;
    setError('coupon', '');
    if (!cat) { priceBox.hidden = true; return; }
    var url = 'quote.php?category=' + encodeURIComponent(cat) +
              '&coupon=' + encodeURIComponent(couponInput.value.trim());
    api(url).then(function (res) {
      var b = res.body || {};
      serverMode = b.mode || (b.open ? 'open' : 'closed');
      applyOpenState();
      if (b.amount === undefined) { priceBox.hidden = true; return; }
      if (b.coupon_error && showCouponError) {
        setError('coupon', t(b.coupon_error === 'used_up' ? 'evf_err_coupon_used' : 'evf_err_coupon'));
      }
      priceVal.textContent = (b.discount > 0 && !b.coupon_error)
        ? money(b.amount) + '  (' + t('evf_discount') + ' ' + money(b.discount) + ')'
        : money(b.amount);
      priceBox.hidden = false;
    }).catch(function () { priceBox.hidden = true; });
  }

  /* ---------- client-side validation (server re-validates everything) ---------- */
  function validate() {
    clearErrors();
    var ok = true;
    function need(name, test, key) {
      var el = form.elements[name];
      if (el && !el.disabled && !test(String(el.value || '').trim())) {
        setError(name, t(key));
        ok = false;
      }
    }
    need('category', function (v) { return v !== ''; }, 'evf_err_category');
    need('name', function (v) { return v.length >= 2; }, 'evf_err_name');
    need('mobile', function (v) {
      var d = v.replace(/\D/g, '');
      if (d.length === 12 && d.indexOf('91') === 0) d = d.slice(2);
      if (d.length === 11 && d.charAt(0) === '0') d = d.slice(1);
      return /^[6-9]\d{9}$/.test(d);
    }, 'evf_err_mobile');
    need('email', function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v); }, 'evf_err_email');
    need('address', function (v) { return v.length >= 5; }, 'evf_err_address');
    need('year_of_study', function (v) { return v !== ''; }, 'evf_err_year');
    need('college', function (v) { return v.length >= 2; }, 'evf_err_college');
    need('chapter', function (v) { return v.length >= 2; }, 'evf_err_chapter');
    need('organisation', function (v) { return v.length >= 2; }, 'evf_err_org');
    need('qualification', function (v) { return v.length >= 2; }, 'evf_err_qual');
    if (!ok) {
      var first = form.querySelector('.ev-error:not(:empty)');
      if (first && first.parentElement) first.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return ok;
  }

  function collect() {
    var data = {};
    ['category', 'name', 'mobile', 'email', 'address', 'coupon', 'website',
     'year_of_study', 'college', 'chapter', 'organisation', 'qualification', 'clinic'].forEach(function (n) {
      var el = form.elements[n];
      if (el && !el.disabled) data[n] = String(el.value || '').trim();
    });
    data.competitions = Array.prototype.map.call(
      form.querySelectorAll('input[name="competitions"]:checked:not(:disabled)'),
      function (c) { return c.value; });
    return data;
  }

  /* ---------- submit ---------- */
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (submitBtn.disabled || !validate()) return;

    submitBtn.disabled = true;
    submitBtn.firstElementChild.textContent = t('evf_wait');

    api('register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(collect())
    }).then(function (res) {
      var b = res.body || {};
      if (b.ok && b.free === true) {
        /* 100%-off coupon: the SERVER already confirmed the registration —
           no payment page exists. Show the paid box (ticket download
           included: a free coupon registration IS a paid row at Rs 0). */
        store(b.reference);
        statusBox.hidden = false;
        statusBox.className = 'ev-status-box is-paid';
        statusBox.textContent = t('evf_free_success') + ' ' + b.reference;

        var dlFree = document.createElement('a');
        dlFree.className = 'btn btn-primary';
        dlFree.href = API + 'ticket.php?ref=' + encodeURIComponent(b.reference);
        dlFree.setAttribute('download', b.reference + '-entry-ticket.pdf');
        dlFree.textContent = t('evf_download_ticket');

        var rowFree = document.createElement('div');
        rowFree.className = 'ev-paid-row';
        rowFree.appendChild(dlFree);
        statusBox.appendChild(rowFree);

        var hintFree = document.createElement('p');
        hintFree.className = 'ev-ticket-hint';
        hintFree.textContent = t('evf_ticket_hint');
        statusBox.appendChild(hintFree);

        submitBtn.disabled = false;
        submitBtn.firstElementChild.textContent = t('evf_pay');
        return;
      }
      if (b.ok && typeof b.payment_url === 'string' && /^https:\/\/app\.coaxlure\.com\//.test(b.payment_url)) {
        store(b.reference);
        window.location.href = b.payment_url;
        return;
      }
      if (b.error === 'validation' && b.field) {
        setError(b.field, b.message || t('evf_err_generic'));
      } else if (b.error === 'already_registered') {
        formError.textContent = t('evf_err_already') + (b.reference ? ' (' + b.reference + ')' : '');
      } else if (b.error === 'rate_limited') {
        formError.textContent = t('evf_err_rate');
      } else if (b.error === 'not_open' || b.error === 'closed') {
        formError.textContent = clientMode() === 'preview' ? t('evf_preview_note') : t('evf_closed');
      } else {
        formError.textContent = t('evf_err_generic');
      }
      submitBtn.disabled = false;
      submitBtn.firstElementChild.textContent = t('evf_pay');
    }).catch(function () {
      formError.textContent = t('evf_err_generic');
      submitBtn.disabled = false;
      submitBtn.firstElementChild.textContent = t('evf_pay');
    });
  });

  /* ---------- paid confirmation (shared by every paid path) ----------
     Shows the success message + the reference, then the DOWNLOAD TICKET
     button. The PDF (api/ticket.php?ref=...) carries the Booking ID and a
     QR code that encodes this page's URL — scanning it re-opens the page and
     the status box verifies the booking as PAID instantly. */
  function showPaid(ref) {
    store('');
    statusBox.hidden = false;
    statusBox.className = 'ev-status-box is-paid';

    statusBox.textContent = t('evf_paid') + ' ' + ref;

    var row = document.createElement('div');
    row.className = 'ev-paid-row';

    var dl = document.createElement('a');
    dl.className = 'btn btn-primary';
    dl.href = API + 'ticket.php?ref=' + encodeURIComponent(ref);
    dl.setAttribute('download', ref + '-entry-ticket.pdf');
    dl.textContent = t('evf_download_ticket');

    var done = document.createElement('button');
    done.type = 'button';
    done.className = 'btn btn-secondary';
    done.textContent = t('evf_done');
    done.addEventListener('click', function () {
      try { localStorage.removeItem(STORE); } catch (e) {}
      window.location.hash = '';
      window.location.href = '#event-top';
      statusBox.hidden = true;
    });

    row.appendChild(dl);
    row.appendChild(done);
    statusBox.appendChild(row);

    var hint = document.createElement('p');
    hint.className = 'ev-ticket-hint';
    hint.textContent = t('evf_ticket_hint');
    statusBox.appendChild(hint);
  }

  /* ---------- payment status (after returning from Coaxlure) ---------- */
  function showStatus(ref, tries) {
    statusBox.hidden = false;
    statusBox.className = 'ev-status-box';
    statusBox.textContent = t('evf_checking');
    api('status.php?ref=' + encodeURIComponent(ref)).then(function (res) {
      var b = res.body || {};
      if (b.ok && b.status === 'paid') {
        showPaid(ref);
        return;
      }
      if (b.ok && b.status === 'pending') {
        statusBox.className = 'ev-status-box is-pending';
        statusBox.textContent = t('evf_pending') + ' ' + ref;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-secondary';
        btn.textContent = t('evf_recheck');
        btn.addEventListener('click', function () { showStatus(ref, 0); });
        statusBox.appendChild(document.createElement('br'));
        statusBox.appendChild(btn);
        if (tries < 6) setTimeout(function () { showStatus(ref, tries + 1); }, 5000);
        return;
      }
      store('');
      statusBox.hidden = true;
    }).catch(function () { statusBox.hidden = true; });
  }

  /* ---------- wire up ---------- */
  catSelect.addEventListener('change', showCategory);
  couponBtn.addEventListener('click', function () { refreshPrice(true); });
  couponInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); refreshPrice(true); }
  });

  /* Coupon auto-apply: the price updates itself ~0.8s after typing stops, so
     the Apply click is optional. Coupon verification stays 100% server-side
     (quote.php now, register.php re-validates on submit) — codes are never
     shipped to the browser, hashed or otherwise, because a client-side
     hash table would let anyone verify guesses offline with no rate limit.
     One call per coupon is the whole cost; the free-coupon case needs no
     second call at all (register.php returns free:true and this page shows
     the success box without ever opening a payment page). */
  var couponTimer = null;
  couponInput.addEventListener('input', function () {
    clearTimeout(couponTimer);
    couponTimer = setTimeout(function () { refreshPrice(true); }, 800);
  });

  showCategory();
  applyOpenState();

  /* Is the server ready? (also shows the starting price once a category is chosen) */
  api('quote.php?category=all').then(function (res) {
    var b = res.body || {};
    serverMode = b.mode || (b.open ? 'open' : 'closed');
    applyOpenState();
    if (b.prices) renderPriceList(b.prices);
  }).catch(function () {});

  var refParam = (new URLSearchParams(window.location.search).get('ref') || '').toUpperCase();
  var ref = /^AP26-[A-Z0-9]{6}$/.test(refParam) ? refParam : stored();
  if (/^AP26-[A-Z0-9]{6}$/.test(ref)) showStatus(ref, 0);
})();
