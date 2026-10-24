# HANDOFF.md — Owl I Health Care Centre / Arokya Payanam 2026

> **What this file is:** the complete project handoff — the single document to
> read first when taking over this project. It records the live state at
> handoff, how to operate everything, every pending action, and every rule
> that must never be broken. It supersedes the numbers in older docs.
>
> **Handoff date: 2026-10-26. Git: `main` = `d231d1b` (clean, pushed, deployed).**
> Site: `https://owlihealthcare.com` (Hostinger Premium, GitHub auto-deploy on
> push → `public_html/`). DB: `u810328541_owlievents` (user `u810328541_owli`).
> This file is web-blocked (403) by the root `.htaccess`, like every `.md`.

---

## 0. Read this first — five facts

1. **Registration is LIVE right now** — both switches are `open` and the
   **General tier is still ₹10** (the payment-test price). **Step 1 below
   (change the tier to ₹499, or pause registration) is the most urgent
   action.** Anyone who finds `/events/` today can register at ₹10.
2. **Everything else works end-to-end.** The full chain is live-proven with
   real money: register → Coaxlure checkout (new tab) → server-verified paid
   detection → PDF ticket with QR → admin console → cron rechecks → emails.
3. **Two switches control registration** (§5). The server switch
   (`owli-config.php` on Hostinger) is the authority and changes take effect
   instantly, without any deploy. The client switch
   (`js/event-config.js`) needs a Git push (~5 min deploy).
4. **No secrets are in this repo** and it must stay that way: DB credentials,
   Coaxlure Client ID/Secret, the admin password hash, and coupon codes all
   live ONLY on the server (git-ignored config) or in the database.
5. **Prices are database rows, not code.** Changing a price = one SQL
   statement in phpMyAdmin. No deploy, instant effect (§3).

---

## 1. What this project is

A bilingual (English/தமிழ்) static website for **Owl I Health Care Centre**
(naturopathy clinic, Tirunelveli) + a PHP 8.3/MySQL backend for registration
and payments for **Arokya Payanam 2026** (wellness event, Sunday
**15 November 2026**, AR Group of Institutions, Kadayam — 9:00–16:00).

| Piece | Where | Notes |
|---|---|---|
| Clinic homepage | `/` (`index.html`) | All sections in one page; i18n via `js/i18n.js` |
| Event page | `/events/` | Details + registration form + Booking-ID lookup |
| Registration API | `api/*.php` | quote / register / status / ticket / cron / helpers |
| Admin console | `/admin/` | Password-protected staff tool (unlinked, noindex) |
| Payments | Coaxlure (hosted checkout) | Server-only pricing; browser never sends an amount |
| Deploy | GitHub → Hostinger `public_html/` | Auto-deploy on every push to `main` |
| Docs | this repo's `.md` files | Index in §10 |

Architecture in one line: the browser submits details → `register.php`
validates and **re-prices from the DB** → creates a pending row + Coaxlure
order → the Coaxlure page opens in a new tab → the form page polls
`status.php` (server verifies with Coaxlure by **order id**) → paid flips the
row, sends the confirmation email, and offers the **PDF ticket**
(`api/ticket.php`, Booking ID + QR) → cron catches paid-but-closed browsers
within 5 min, sweeps abandoned pendings after 2 days, and emails an admin
digest every 2 days.

---

## 2. Live status at handoff (verified 2026-10-26)

| Check | Result |
|---|---|
| Deploy | ✅ `d231d1b` live on Hostinger; assets byte-identical to repo |
| Public site | ✅ `/`, `/events/`, `/admin/`, `robots.txt` all 200 |
| API | ✅ healthy — `mode:"open"`; docs + internal files all 403-blocked |
| Prices (live DB) | student ₹299 · doctor ₹399 · bni ₹399 · **general ₹10 (test price — change it)** |
| Coaxlure paid detection | ✅ live-proven with the real ₹10 payment (`AP26-WC4DV9`, paid) |
| Ticket PDF | ✅ streams for paid refs (paid-only, 404 otherwise) |
| Cron (5 min) | ✅ running; 3 passes: recheck ≤3-day pendings, sweep >2-day stale, digest every 2 days |
| Admin console | ✅ login + dashboard + filters/sorts + CSV + tickets + coupons + staff entries |
| Coupon packs in live DB | ❌ **none created yet** (owner step 4) |
| `registrations.source` column | ❌ **ALTER not run yet on live DB** (owner step 2) |
| `payment_attempts.status` ENUM incl. `failed` | ❌ **ALTER not run yet** (owner step 2) |
| `admin.digest_to` in config | ❌ **not set yet** (owner step 5) |
| Test rows in live DB | `AP26-WC4DV9` (paid ₹10 — valid record, keep or delete) · `AP26-5DC2FK` (stale pending — will flip to failed by the sweep once the ENUM ALTER runs) |
| Validation suite | ✅ 70/70 locally; run on Hostinger after the ALTERs |

---

## 3. How to change a price (tier) — including ₹10 → ₹499

**All money is stored in PAISE: ₹499 = `49900`, ₹10 = `1000`.**

1. hPanel → **Databases → phpMyAdmin** → select `u810328541_owlievents` → **SQL** tab.
2. Run:

```sql
UPDATE tiers SET amount_paise = 49900 WHERE category = 'general';
```

3. **Verify instantly** (no deploy, no Git, effective immediately):

```
https://owlihealthcare.com/api/quote.php?category=all
→ {"ok":true,"mode":"open","open":true,"prices":{"student":299,"doctor":399,"bni":399,"general":499}}
```

Hard-refresh `/events/` — the dropdown price list and "Amount to pay" show ₹499.

**What it affects:**
- **New registrations** — priced at the new amount.
- **Pending/failed retries** — when the same mobile+email resubmits, the row is
  **re-priced to the current tier** (the old attempt's stored amount stays in
  the attempt history — that's fine and correct).
- The admin console's **Add entry** preview and the digest/CSV amounts — all
  read the current tier or the row's stored amount respectively.

**What it does NOT change:**
- Already-**paid** rows (their `amount_paise` is the immutable record — e.g.
  `AP26-WC4DV9` stays ₹10).
- Anything in Git — prices are never in code.

**Other price recipes** (phpMyAdmin → SQL):

```sql
-- any other category (paise: 29900=₹299, 39900=₹399, 49900=₹499)
UPDATE tiers SET amount_paise = 44900 WHERE category = 'general';
-- temporary early-bird instead of overwriting: INSERT a second row with
-- valid_from/valid_until (the newest in-window row wins — see owli_quote()):
INSERT INTO tiers (category, label, amount_paise, valid_from, valid_until)
VALUES ('general', 'Early bird', 39900, NOW(), '2026-11-01 23:59:59');
-- review current tiers
SELECT id, category, label, amount_paise, valid_from, valid_until, active FROM tiers;
```

**If the price becomes permanent (₹499), update the two doc lines that say
₹449**: `README.md` §10 ("Prices (no GST): … General public ₹449") and
`PRD.md` §4.5 ("General: ₹449"). That IS a Git change (commit + push) —
docs only, no behaviour.

---

## 4. Pending owner actions — in this order

1. **Change the tier** ₹10 → ₹499 (§3) — or, if not ready to take live
   registrations yet, pause instead: File Manager →
   `domains/owlihealthcare.com/owli-config.php` → `'mode' => 'preview'`
   (instant; the form locks, prices stay visible).
2. **Run the two one-time ALTERs** (phpMyAdmin → SQL) — they enable the
   admin console's staff entries + delete-staff-only, and the
   abandoned-payment sweep:

```sql
ALTER TABLE registrations
  ADD COLUMN source ENUM('web','staff') NOT NULL DEFAULT 'web' AFTER reference;

ALTER TABLE payment_attempts
  MODIFY status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending';
```

3. **Re-run validation** (hPanel → Terminal) — expect all green incl. the
   `registrations.source` check:

```bash
php domains/owlihealthcare.com/public_html/scripts/validate.php
```

4. **Create the coupon packs** — admin console → **Coupons → Generate codes**
   (codes print once; they live only in the DB — never write them into any
   committed file):

| Type | Value | How many | Uses per code | Prefix |
|---|---|---|---|---|
| percent | 10 | 1 | unlimited | `EARLY` |
| percent | 20 | 1 | unlimited | `PAYANAM` |
| percent | 100 | 20 | 1 | `FREEAP26-` |

5. **Set the digest recipient** — `owli-config.php` → `'admin'` block →
   `'digest_to' => 'arokyapayanam@gmail.com'` (falls back to `mail_from`).
   The 2-day digest then emails automatically (first one triggers on the next
   cron run if ≥2 days have passed).
6. **Confirm the mailbox `registrations@owlihealthcare.com` exists**
   (hPanel → Emails) — confirmation emails + digests are silently skipped
   otherwise (payments are unaffected). To test:
   `php .../api/cron-recheck.php` from the hPanel Terminal.
7. *(Optional)* **Clean the test rows** (attempts first — foreign key):

```sql
DELETE FROM payment_attempts WHERE registration_id IN
  (SELECT id FROM registrations WHERE reference IN ('AP26-WC4DV9','AP26-5DC2FK'));
DELETE FROM registrations WHERE reference IN ('AP26-WC4DV9','AP26-5DC2FK');
```

8. **Confirm live Coaxlure mode.** During the ₹10 run the checkout displayed
   "Test Mode" wording — ask Coaxlure support to confirm the account is
   live-ready for real sales before marketing starts.
9. *(Content, before marketing)* `images/og-image.jpg` is missing (social
   shares show no image — add 1200×630 JPG); gallery/blog images and
   testimonials are still placeholders; decide whether the Site24x7 RUM
   beacon stays (`index.html` — flagged "remove before production").

---

## 5. Operating the system

**The two switches** — registration is live only when BOTH say `open`:

| Switch | File | Change speed |
|---|---|---|
| Server (authority) | Hostinger `/home/u810328541/domains/owlihealthcare.com/owli-config.php` → `'registration' => ['mode' => …]` | instant, no deploy |
| Client (UI) | `js/event-config.js` → `registrationMode: 'open'` (currently `open`, `?v=6`) | commit + push → ~5 min deploy |

Modes: `closed` ("Registration Opens Soon") · `preview` (prices + form demo,
server refuses 503 `not_open` — the safe state) · `open` (live payments).
**Emergency stop = flip the server switch to `'preview'`.** One edit, no
deploy, instant. Never `git checkout`/rewrite `js/event-config.js` beyond the
mode value.

**Where things live:**

| Thing | Location |
|---|---|
| Secrets (DB creds, Coaxlure client_id/secret, admin hash, digest_to) | `/home/u810328541/domains/owlihealthcare.com/owli-config.php` — above `public_html`, git-ignored. Template: `api/config.sample.php` |
| Cron (every 5 min) | `/usr/bin/php /home/u810328541/domains/owlihealthcare.com/public_html/api/cron-recheck.php` — output visible in the hPanel cron page |
| Admin console | `https://owlihealthcare.com/admin/` — password = `php api/admin-pass.php 'new-password'` → paste the printed `$2y$…` hash into the config's `admin.password_hash` (empty hash = console disabled/403) |
| Prices / coupons | DB tables `tiers` / `coupons` (§3; coupons also manageable in the console) |
| Validation | `php public_html/scripts/validate.php` (70 checks; exit 1 on failure) |
| PHP errors (live) | hPanel → Advanced → PHP Error Log — every line is prefixed `owli ` (prefix table: `API-TROUBLESHOOTING.md` §4) |
| Web protection | root `.htaccess` blocks `*.md`, `*.sql`, `*.log`, `*.ini` (403); `api/.htaccess` + in-file guards block internal PHP; `scripts/.htaccess` denies all |

**Emails:** confirmation (with the ticket link) on paid; digest every 2 days
to `admin.digest_to`. Both via PHP `mail()` — need the mailbox to exist.

**Money flow reference:** form → `register.php` (re-prices, pending row,
Coaxlure order) → hosted checkout `app.coaxlure.com/customer/coaxlure-payment/…`
(singular route — Coaxlure's own plural `/payment-link` URL renders blank;
`coax_create_payment()` passes `merchantDomain` + rewrites plural→singular) →
polling `status.php` (verifies by **order id** first — Coaxlure rotates the
payment token at capture, so the create-time token dies; paid wording =
`orderStatus:"PAID"` / `paymentStatus:"success"`) → paid + email + ticket.
Free coupons (100% off) skip Coaxlure entirely — instant paid, ₹0.

**Identity rules:** one registration per **mobile+email pair** (same pair
reuses the row while pending/failed; 409 `already_registered` after paid;
same mobile + different email = separate person). No separate payment phone
number exists — the attendee's mobile is what Coaxlure receives.

---

## 6. Verification & testing — what exists, how to run it

| Check | Command | What it protects |
|---|---|---|
| Pre-deploy validation (70 checks) | `php scripts/validate.php` (or `docker exec owli-web php /srv/public_html/scripts/validate.php`) | Config, DB+schema, all endpoints, admin, cron, doc/internal-file web-blocks, lookup card, button-visibility CSS, box order, Coaxlure reachability (no creds sent) |
| i18n parity | `node scripts/check-i18n.js` | Every `data-i18n` key exists in BOTH EN + TA (357 keys) |
| Pay-flow regression | `node scripts/check-pay-flow.js` | New-tab navigation contract, label contract (no invisible buttons), no reopen link, check-status button — scenarios A–H |
| Lint | `php -l <file>` / `node --check <file>` | Syntax before any commit |
| Mobile audit | headless-Chrome script — see `LOCAL-TESTING.md` §4.6 | 375/414/768px: no horizontal scroll, ≥40px tap targets |
| Live smoke | `curl -s https://owlihealthcare.com/api/quote.php?category=all` | Prices + mode; `status.php?ref=…` for a booking; `/api/ticket.php?ref=…` for a paid PDF |

**Local stack** (full guide: `LOCAL-TESTING.md`): Docker `owli-web` (Apache,
`http://localhost:8800/`, repo bind-mounted at `/srv/public_html`) + `owli-db`
(MariaDB, db `owli`, user `owliuser`). The local config is
`~/work/owli-config-freetest.php` (currently **dev Coaxlure + `open` mode**,
mounted read-only into the container). Canonical local config:
`~/work/owli-config.php`. **Never POST to `register.php` on the LIVE server
while it's `open` — it creates real registrations/orders; test locally.**

**The three rules of a good deploy:** run the checkers locally → commit only
reviewed files → after ~5 min verify live (`quote.php`, the changed asset,
`?v=` cache bump). Any `.md`/`sql` regression fails validation (they must 403).

**Debugging anything:** `API-TROUBLESHOOTING.md` — the six-step workflow
(reproduce outside the browser → read the API's verbatim code → read the
`owli `-prefixed log line → read DB evidence → isolate the layer → fix +
verify), every endpoint's status codes, the log-prefix table, and four real
case studies from this project.

---

## 7. Safety rules — never break these

1. **Never commit secrets or coupon codes.** The repo is public via GitHub
   deploy; live Coaxlure credentials exist only in the server config and the
   local CSVs (`~/work/coaxlure/*.csv`, chmod 600). The admin console stores a
   bcrypt hash, never the password.
2. **The browser is never trusted with money.** Amounts are recomputed
   server-side from `tiers`/`coupons`; a payment redirect is never treated as
   proof of payment — only Coaxlure's API answer is.
3. **Website-sourced rows (`source='web'`) are undeletable** from the admin —
   the SQL itself carries `AND source='staff'`. Only staff-added entries can be
   deleted. Do not "fix" this by removing the guard.
4. **Never serve `.md`/`.sql`/`.log`/`ini` files** — the root `.htaccess` +
   `validate.php` enforce this (they once leaked every coupon code via
   `/GO-LIVE-STEPS.md`; closed, guarded, and now gated).
5. **Rollback is always one edit:** server switch → `'preview'`. No deploy.
6. **Cache-busting:** when editing any public CSS/JS, bump its `?v=` in the
   HTML (`event-config.js?v=6`, `event-register.js?v=11`, `i18n.js?v=8`,
   `events.css?v=3`, `styles.css`/`main.js`/`contact.js` have none yet) and
   purge Hostinger's CDN cache if assets look stale.
7. **Schema changes:** `sql/schema.sql` is the source of truth for fresh
   installs — keep it in sync with any ALTER run on the live DB, and document
   the live-DB migration beside it (as done for `source` and the ENUM).
8. **Local vs live credentials:** dev/test Coaxlure creds → dev API only;
   live creds → live API only. Local Docker values never go to Hostinger.

---

## 8. Known open items (all non-blocking, tracked)

| Item | Where |
|---|---|
| `images/og-image.jpg` missing | social shares show no image — add 1200×630 |
| `www.` doesn't redirect to apex | cosmetic; canonical tags already point to the apex |
| Gallery/blog/testimonial placeholders | content work before marketing (`README.md` §11) |
| Site24x7 RUM beacon | decide keep/remove (`index.html`, self-labelled pre-prod note) |
| `sitemap.xml` `lastmod` | update on the final content release |
| Event hard-close | `register.php` self-closes after `2026-11-15T16:00+05:30` (410 `closed`) — no action needed |
| Test rows | `AP26-WC4DV9` (paid ₹10) / `AP26-5DC2FK` (stale) — §4 step 7 |
| Coaxlure "Test Mode" wording seen on checkout | confirm with Coaxlure support before heavy marketing |
| Admin "last cron ran" display | offered, not built — hPanel cron page shows output instead |

---

## 9. Key technical facts (fast context)

- **Stack:** static HTML/CSS/vanilla JS (no build step) · PHP 8.3.35 ·
  MariaDB 11.8.9 · Coaxlure Business API (no Composer, no GD/Imagick needed).
- **Money = paise, integers everywhere.** Reference format `AP26-XXXXXX`
  (6 chars from a 32-char alphabet). Identity = mobile+email pair.
- **Ticket PDF:** `api/ticket.php?ref=…` — paid-only (404 for anything else;
  probing leaks nothing), 12 downloads/10 min/IP, one A4 page: Booking ID,
  vector QR (→ `https://owlihealthcare.com/events/?ref=…`), name (Tamil-capable
  via vendored tFPDF + MuktaMalar font), category, amount/FREE, event facts.
  Licenses: `api/lib/LICENSE.txt`.
- **Cron passes:** ① recheck pendings ≤3 days (5s throttle per attempt),
  ② sweep attempts >2 days (final Coaxlure check first — money always wins),
  ③ digest every 2 days (stamp written only after a successful send).
  Output line: `checked=N newly_paid=N abandoned_failed=N`.
- **Waiting-box UX:** payment tab opens once (pre-opened `about:blank` inside
  the click gesture — `window.open` with `noopener` returns null on success,
  which once caused a double-navigation bug); page polls 2s→5s for ~10 min
  (Coaxlure links expire ~10 min); amber do-not-refresh warning; "Check
  payment status now" button; NO reopen link (by owner decision).
- **Page order contract:** form → payment status box → Booking-ID lookup card
  (enforced by validation).
- **Free coupon flow:** 100% coupon → `{ok:true, free:true}` → instant paid,
  same three actions (Download ticket · Get ticket on WhatsApp → official
  number +91 63796 10554 with Booking ID pre-typed · Done). No payment page.
- **WhatsApp automation** was deliberately NOT built (Meta onboarding + fees);
  the `wa.me` handoff covers the need. Upgrade path documented in
  `CHANGE-LOG.md` (2026-10-25 entry).
- **i18n:** all visible strings via `data-i18n` keys in `js/i18n.js`,
  EN + TA parity enforced (357 keys). The admin console is English-only by design.

---

## 10. Document index — which file answers what

| Need | File |
|---|---|
| **Start here / current truth** | `HANDOFF.md` (this file) |
| Everyday maintenance (edit text/images/doctors/services, deploy, cache) | `README.md` |
| Full developer spec (structure, palette, rules, API map) | `CLAUDE.md` (+ `docs/CLAUDE-*.md`) |
| Product requirements + launch criteria | `PRD.md` |
| Go-live / ₹10 test history / switch flips / DB migrations | `GO-LIVE-STEPS.md` |
| Live status snapshot + decision log (pre-handoff numbers) | `PROJECT-STATUS.md` |
| API debugging runbook (workflow, codes, log prefixes, case studies) | `API-TROUBLESHOOTING.md` |
| Local Docker stack + checkers + verification techniques | `LOCAL-TESTING.md` |
| Every dated change, why + how verified | `CHANGE-LOG.md` |

---

## 11. After the event (retirement checklist)

1. Flip the server switch to `'closed'` (or `'preview'` to keep the page as a
   record). The API also self-closes after 15 Nov 2026, 16:00 IST.
2. Export the final CSV from the admin console (keeps the full dataset).
3. Remove the event banner + its script/link tags, `events/`, `events.css`,
   `images/events/`, the footer link, and the `/events/` sitemap entry —
   full checklist in `README.md` §10 "To remove the event entirely".
4. Keep the DB rows (financial record) or export + archive; the `registrations`
   table can then be left as-is.

---

*End of handoff. Prepared after a full audit of the repo, the live site, and
the local stack on 2026-10-26. Everything stated above was verified live
(except where marked as a pending owner action).*
