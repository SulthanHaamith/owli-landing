/**
 * CHECK-PAY-FLOW.JS — regression test for the Pay-button navigation contract.
 *
 * PURPOSE: js/event-register.js must open the Coaxlure payment page in
 *          exactly ONE new tab, and the form page must never navigate to
 *          the payment page when that tab opened. This guards against the
 *          double-navigation bug (fixed 2026-10-24): window.open(url,
 *          '_blank','noopener') returns null EVEN ON SUCCESS, so code that
 *          branched on the return value also redirected the current page —
 *          the payment page loaded in the new tab AND hijacked the form page.
 *
 * HOW IT WORKS: loads the REAL module inside a Node VM with a fake DOM,
 *          drives the submit handler through six scenarios and asserts the
 *          navigation contract for each:
 *
 *            A  normal payment     -> one tab (about:blank) that is then
 *                                      navigated to Coaxlure with opener=null;
 *                                      the form page never navigates;
 *                                      polling starts; the tab is not closed
 *            B  popup blocked       -> same-tab redirect (documented fallback)
 *            C  free coupon         -> tab closed; zero navigation anywhere
 *            D  validation error    -> tab closed; button re-enabled
 *            E  network failure     -> tab closed; button re-enabled
 *            F  waiting box: status-check ONLY -> NO reopen-payment link
 *                                      anywhere (owner request 2026-10-25);
 *                                      the "Check payment status now"
 *                                      button fires ONE status.php call
 *                                      and opens NO new tab
 *
 * HOW TO RUN:  node scripts/check-pay-flow.js      (exit code 1 = FAIL)
 *              Run it before every push that touches js/event-register.js,
 *              alongside scripts/check-i18n.js and scripts/validate.php.
 */
'use strict';

const fs = require('fs');
const path = require('path');
const vm = require('vm');

const SRC_PATH = path.join(__dirname, '..', 'js', 'event-register.js');
const src = fs.readFileSync(SRC_PATH, 'utf8');
const OPEN_TAG = '(function () {';
const iifeStart = src.indexOf(OPEN_TAG);
const iifeEnd = src.lastIndexOf('})();');
if (iifeStart < 0 || iifeEnd < 0 || iifeEnd < iifeStart) {
  console.error('FAIL: could not isolate the IIFE in js/event-register.js');
  process.exit(1);
}
const moduleBody = src.slice(iifeStart + OPEN_TAG.length, iifeEnd);

const PAY_URL = 'https://app.coaxlure.com/customer/coaxlure-payment/tok42';
const REF = 'AP26-HRN001';

/* ------------------------------------------------------------------ */
/* Minimal fake DOM                                                    */
/* ------------------------------------------------------------------ */
function el(tag) {
  return {
    tag, textContent: '', hidden: false, disabled: false, className: '', href: '',
    attrs: {}, children: [], listeners: {},
    setAttribute(k, v) { this.attrs[k] = v; },
    getAttribute(k) { return this.attrs[k] !== undefined ? this.attrs[k] : null; },
    removeAttribute(k) { delete this.attrs[k]; },
    appendChild(c) { this.children.push(c); return c; },
    addEventListener(ev, fn) { this.listeners[ev] = fn; },
    querySelector() { return null; },
    querySelectorAll() { return []; },
    parentElement: { scrollIntoView() {} },
    firstElementChild: null,
    classList: { toggle() {}, add() {}, remove() {} },
  };
}

function freshTab() {
  return {
    closed: false, closeCalled: false, opener: 'stale-opener',
    location: { href: '' },
    document: { title: '', body: { style: { cssText: '' }, textContent: '' } },
    close() { this.closeCalled = true; this.closed = true; },
  };
}

/* ------------------------------------------------------------------ */
/* World: sandboxed window/document + per-scenario behaviour            */
/* ------------------------------------------------------------------ */
function buildWorld(opts) {
  const form = el('form');
  form.elements = {
    category: { value: 'general', disabled: false },
    name: { value: 'Regression Harness', disabled: false },
    mobile: { value: '9000000000', disabled: false },
    email: { value: 'harness@example.com', disabled: false },
    address: { value: 'Test address only', disabled: false },
    coupon: { value: '', disabled: false },
    website: { value: '', disabled: false },
  };
  const catSelect = el('select');
  const couponInput = el('input');
  const submitBtn = el('button');
  submitBtn.firstElementChild = { textContent: '' };
  const statusBox = el('div');
  const state = { opened: [], location: { href: '' }, timers: [], statusCalls: 0 };

  const ok = (body) => Promise.resolve({ status: 200, json: () => Promise.resolve(body) });
  const fetchImpl = (p) => {
    const s = String(p);
    if (s.indexOf('quote.php') >= 0) {
      return ok({ ok: true, mode: 'open', open: true, prices: { student: 299, doctor: 399, bni: 399, general: 449 } });
    }
    if (s.indexOf('status.php') >= 0) {
      state.statusCalls++;
      return ok({ ok: true, status: 'pending', reference: REF });
    }
    if (s.indexOf('register.php') >= 0) {
      if (opts.networkFail) return Promise.reject(new Error('simulated network failure'));
      return ok(opts.registerReply);
    }
    return ok({});
  };

  const win = {
    open(url, target, features) {
      const t = freshTab();
      state.opened.push({ url, target, features, tab: t });
      if (opts.popupBlocked) return null;
      /* HTML spec fidelity — THE rule that caused the real-world bug:
         window.open(url, '_blank', 'noopener') returns null EVEN ON SUCCESS,
         so any code branching on that return value also navigates the current
         page. The old code did exactly that; the harness must reproduce it. */
      if (String(features || '').indexOf('noopener') >= 0) return null;
      return t;
    },
    location: state.location,
  };

  const sandbox = {
    window: win,
    document: {
      getElementById(id) {
        return ({
          'register-form': form, 'evf-fields': el('fieldset'), 'evf-closed': el('div'),
          'evf-category': catSelect, 'evf-price': el('div'), 'evf-price-value': el('span'),
          'evf-form-error': el('div'), 'evf-submit': submitBtn, 'evf-coupon': couponInput,
          'evf-coupon-apply': el('button'), 'evf-status': statusBox, 'evf-price-list': el('ul'),
        })[id] || el('div');
      },
      querySelectorAll() { return []; },
      querySelector() { return null; },
      body: { classList: { contains() { return true; } } },
      createElement: el,
    },
    localStorage: {
      m: {},
      setItem(k, v) { this.m[k] = String(v); },
      getItem(k) { return this.m[k] !== undefined ? this.m[k] : null; },
      removeItem(k) { delete this.m[k]; },
    },
    setTimeout(fn, ms) { state.timers.push({ fn, ms }); return state.timers.length; },
    clearTimeout() {},
    URLSearchParams: class { constructor() { this.get = () => ''; } },
    translations: { en: {}, ta: {} },
    fetch: fetchImpl,
  };
  sandbox.window.OWLI_EVENT = { registrationMode: 'open' };

  vm.createContext(sandbox);
  vm.runInContext('(function(){' + moduleBody + '})()', sandbox);

  return { form, submitBtn, statusBox, state, win };
}

const tick = () => new Promise((r) => setTimeout(r, 25));   /* flush microtasks */

/* ------------------------------------------------------------------ */
/* Assertion helpers                                                    */
/* ------------------------------------------------------------------ */
let failures = 0;
function A(name, ok, detail) {
  console.log((ok ? '  ✓ ' : '  ✗ ') + name + (detail ? '  [' + detail + ']' : ''));
  if (!ok) failures++;
}

async function submitAndSettle(world) {
  world.form.listeners.submit({ preventDefault() {} });
  await tick();
  await tick();
}

async function scenarioA() {
  console.log('A) normal payment: exactly one new tab, form page never navigates');
  const w = buildWorld({ registerReply: { ok: true, reference: REF, amount: 449, payment_url: PAY_URL } });
  await tick();                                    /* let quote.php settle -> button enabled */
  if (w.submitBtn.disabled) { A('button enabled before submit', false, 'still disabled'); return; }
  await submitAndSettle(w);

  A('exactly ONE window.open call', w.state.opened.length === 1, 'opened=' + w.state.opened.length);
  const first = w.state.opened[0];
  A('the tab is pre-opened as about:blank (never the payment URL)', !!first && first.url === 'about:blank', first && first.url);
  const tab = first && first.tab;
  A('the pre-opened tab is navigated to the Coaxlure page', !!tab && tab.location.href === PAY_URL, tab && tab.location.href);
  A('the tab has no opener link back to the form page (noopener parity)', !!tab && tab.opener === null, tab && String(tab.opener));
  A('the form page did NOT navigate to the payment page', w.state.location.href === '', w.state.location.href || '(unchanged)');
  A('the tab is left open for the attendee to pay', !!tab && !tab.closeCalled, 'closeCalled=' + (tab && tab.closeCalled));
  A('same-page payment polling is armed', w.state.timers.length > 0, 'timers=' + w.state.timers.length);

  console.log('F) waiting box: status-check only — no reopen link (owner request 2026-10-25)');
  const anchors = w.statusBox.children.filter((c) => c.tag === 'a' || String(c.className).indexOf('ev-pay-relink') >= 0);
  A('NO reopen-payment link is rendered in the waiting box', anchors.length === 0, 'anchors=' + anchors.length);
  const checkBtn = w.statusBox.children.find((c) => String(c.className).indexOf('ev-check-now') >= 0);
  if (!checkBtn) { A('"Check payment status now" button rendered', false, 'not found'); return; }
  const before = w.state.statusCalls;
  checkBtn.listeners.click();
  await tick();
  await tick();
  A('the check button fires ONE status.php call', w.state.statusCalls === before + 1, 'statusCalls ' + before + ' -> ' + w.state.statusCalls);
  A('the check button opens NO new tab (payment tab count unchanged)', w.state.opened.length === 1, 'opened=' + w.state.opened.length);
  A('the form page still did not navigate', w.state.location.href === '', w.state.location.href || '(unchanged)');
}

async function scenarioB() {
  console.log('B) popup blocked: same-tab redirect is the documented fallback');
  const w = buildWorld({ popupBlocked: true, registerReply: { ok: true, reference: REF, amount: 449, payment_url: PAY_URL } });
  await tick();
  await submitAndSettle(w);
  A('window.open was attempted', w.state.opened.length === 1, 'opened=' + w.state.opened.length);
  A('the form page redirects to the payment page (only automatic route left)', w.state.location.href === PAY_URL, w.state.location.href);
}

async function scenarioC() {
  console.log('C) free coupon (100% off): tab closed, zero navigation');
  const w = buildWorld({ registerReply: { ok: true, free: true, reference: 'AP26-FREE01' } });
  await tick();
  await submitAndSettle(w);
  const tab = w.state.opened[0] && w.state.opened[0].tab;
  A('the pre-opened tab is closed (no blank tab left behind)', !!tab && tab.closeCalled, 'closeCalled=' + (tab && tab.closeCalled));
  A('the tab never went to a payment page', !!tab && tab.location.href === '', tab && tab.location.href);
  A('the form page did not navigate', w.state.location.href === '', w.state.location.href || '(unchanged)');
  A('the submit button is re-enabled', w.submitBtn.disabled === false, 'disabled=' + w.submitBtn.disabled);
}

async function scenarioD() {
  console.log('D) server validation error: tab closed, button re-enabled');
  const w = buildWorld({ registerReply: { ok: false, error: 'validation', field: 'name', message: 'Please enter your full name.' } });
  await tick();
  await submitAndSettle(w);
  const tab = w.state.opened[0] && w.state.opened[0].tab;
  A('the pre-opened tab is closed', !!tab && tab.closeCalled, 'closeCalled=' + (tab && tab.closeCalled));
  A('the form page did not navigate', w.state.location.href === '', w.state.location.href || '(unchanged)');
  A('the submit button is re-enabled', w.submitBtn.disabled === false, 'disabled=' + w.submitBtn.disabled);
}

async function scenarioE() {
  console.log('E) network failure: tab closed, button re-enabled');
  const w = buildWorld({ networkFail: true });
  await tick();
  await submitAndSettle(w);
  const tab = w.state.opened[0] && w.state.opened[0].tab;
  A('the pre-opened tab is closed', !!tab && tab.closeCalled, 'closeCalled=' + (tab && tab.closeCalled));
  A('the form page did not navigate', w.state.location.href === '', w.state.location.href || '(unchanged)');
  A('the submit button is re-enabled', w.submitBtn.disabled === false, 'disabled=' + w.submitBtn.disabled);
}

(async () => {
  console.log('PAY-FLOW REGRESSION — js/event-register.js navigation contract\n');
  await scenarioA();
  console.log('');
  await scenarioB();
  console.log('');
  await scenarioC();
  console.log('');
  await scenarioD();
  console.log('');
  await scenarioE();
  console.log('\nRESULT: ' + (failures === 0 ? 'PASS — the payment flow honours the contract' : 'FAIL — ' + failures + ' contract violation(s) above'));
  process.exit(failures === 0 ? 0 : 1);
})();
