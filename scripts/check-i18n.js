/**
 * CHECK-I18N.JS — bilingual parity checker for js/i18n.js.
 *
 * PURPOSE: Proves the English and Tamil translation tables carry exactly the
 *          same keys, and that every data-i18n key used by BOTH public pages
 *          exists in both tables. Run it before every commit that touches
 *          js/i18n.js, index.html or events/index.html.
 *
 * HOW TO RUN:  node scripts/check-i18n.js
 *              (exit code 1 = a mismatch was found, so CI can gate on it)
 *
 * HISTORY: Started life as .claude/check-i18n.js (machine-local, never
 *          committed). It moved into scripts/ so the repository keeps its
 *          only automated test, and gained the events page + runtime-key
 *          coverage the original lacked.
 */
const fs = require('fs');

const i18n = fs.readFileSync('js/i18n.js', 'utf8');
const enStart = i18n.indexOf('en: {');
const taStart = i18n.indexOf('ta: {');
if (enStart < 0 || taStart < 0) {
  console.error('FAIL: could not find the en/ta blocks in js/i18n.js');
  process.exit(1);
}
const enBlock = i18n.slice(enStart, taStart);
const taBlock = i18n.slice(taStart, i18n.indexOf('var langData') >= 0 ? i18n.indexOf('var langData') : undefined);

/** Set of keys defined in one translation block. */
function keys(block) {
  const set = new Set();
  for (const m of block.matchAll(/^\s*([a-z0-9_]+):\s*['"`]/gm)) set.add(m[1]);
  return set;
}

const ek = keys(enBlock);
const tk = keys(taBlock);
let bad = false;

console.log(`EN keys: ${ek.size} | TA keys: ${tk.size}`);

const missTa = [...ek].filter(k => !tk.has(k));
const missEn = [...tk].filter(k => !ek.has(k));
if (missTa.length) { console.log('Missing in TA:', missTa.join(', ')); bad = true; }
if (missEn.length) { console.log('Missing in EN:', missEn.join(', ')); bad = true; }
if (!missTa.length && !missEn.length) console.log('Parity: both tables carry the same keys');

/* data-i18n coverage for BOTH public pages (the events page is checked too
   — the old version only looked at index.html). */
for (const page of ['index.html', 'events/index.html']) {
  const html = fs.readFileSync(page, 'utf8');
  const used = new Set();
  for (const m of html.matchAll(/data-i18n(?:-tooltip|-placeholder|-label|-title)?="([a-z0-9_]+)"/g)) used.add(m[1]);
  const broken = [...used].sort().filter(k => !ek.has(k) || !tk.has(k));
  console.log(`${page}: ${used.size} data-i18n keys used${broken.length ? ' — MISSING: ' + broken.join(', ') : ' — all present'}`);
  if (broken.length) bad = true;
}

/* event-config.js + event-register.js build some keys at RUNTIME by joining
   a prefix with a category (e.g. 'evf_cat_' + 'student'). Those prefixes are
   not real keys — expand them against the four category names so the check
   still covers the keys that actually get requested. */
const CATEGORIES = ['student', 'doctor', 'bni', 'general'];
const dynamicSources = ['js/event-config.js', 'js/event-register.js'];
for (const src of dynamicSources) {
  const code = fs.readFileSync(src, 'utf8');
  const dyn = new Set();
  /* Keys built as 'prefix' + category, e.g. t('evf_cat_' + c). Capture the
     prefix, then expand it across the four category names. */
  for (const m of code.matchAll(/'(evf?_[a-z0-9_]*)'\s*\+\s*c\b/g)) {
    if (m[1].endsWith('_')) {
      for (const c of CATEGORIES) dyn.add(m[1] + c);
    } else {
      dyn.add(m[1]);
    }
  }
  /* Plain literal keys (skip anything captured above that is a prefix —
     the regex for literal keys is greedy enough to match 'evf_cat_' too). */
  for (const m of code.matchAll(/'((?:ev|evf)_[a-z0-9_]+)'/g)) {
    if (!m[1].endsWith('_')) dyn.add(m[1]);
  }
  const missing = [...dyn].sort().filter(k => !ek.has(k) || !tk.has(k));
  if (missing.length) {
    console.log(`${src}: runtime keys MISSING from i18n: ${missing.join(', ')}`);
    bad = true;
  } else if (dyn.size) {
    console.log(`${src}: all ${dyn.size} runtime keys exist in EN + TA`);
  }
}

if (bad) {
  console.log('\nRESULT: FAIL — fix the keys above before committing.');
  process.exit(1);
}
console.log('\nRESULT: PASS');
