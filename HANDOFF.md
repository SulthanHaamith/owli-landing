# HANDOFF.md — Owl I Health Care Centre / Arokya Payanam 2026

> **URGENT CURRENT STATE:** The event is cancelled. Hostinger registration mode is confirmed closed and live submissions return HTTP 503. The cancellation patch is uncommitted and not deployed: homepage promotion, full `/events/`, event assets and admin cancellation guards remain public/active in the deployed code; quote mode is closed but still returns prices. Do not use admin Add Entry/coupon controls until the patch deploys. Review/deploy to hide these surfaces. Keep attendee/payment records and existing ticket/status support.

> **What this file is:** the complete project handoff — the single document to
> read first when taking over this project. It records the live state, how to
> operate everything, every pending action, and every rule that must never be
> broken. It supersedes the numbers in older docs.
>
> **Last handoff refresh: 2026-11-12. Current workspace: cancellation patch is under review and not deployed. Hostinger's server mode is closed.**
> Site: `https://owlihealthcare.com` (Hostinger Premium, GitHub auto-deploy on
> push → `public_html/`). DB: `u810328541_owlievents` (user `u810328541_owli`).
> This file is web-blocked (403) by the root `.htaccess`, like every `.md`.

---

## 0. Read this first — six facts

1. **The event is CANCELLED.** Hostinger `registration.mode` is confirmed `closed`; live registration returns 503 `not_open`. The local cancellation patch removes promotion, blocks event assets, replaces `/events/` with booking recovery, and prevents quotes from exposing prices. It has not been deployed: live probes show the full event page/assets still return 200 and quote still includes prices. Local patch passes 85/85 validation. Preserve all attendee/payment records; avoid live admin Add Entry/coupon actions until deployment.
2. **The full chain is live-proven with real money**: register → Coaxlure
   checkout (new tab, working singular URL) → server-verified paid detection
   (by order id, Coaxlure's real wording matched) → PDF ticket with QR →
   confirmation email with ticket link → admin console → cron rechecks →
   abandoned-payment sweep → 2-day digest.
3. **Cancellation code guard is local only** until deployment. Hostinger's `registration.mode = 'closed'` is the currently verified server-side safety switch; keep it closed.
4. **No secrets are in this repo** and it must stay that way: DB credentials,
   Coaxlure Client ID/Secret, the admin password hash, digest recipient, and
   coupon codes live ONLY on the server (git-ignored config) or in the DB.
5. **The old event price tiers remain historical database data.** Do not change them for sales or reopen registration unless the owner explicitly reinstates the event.
6. **Official emails changed (2026-10-26):** homepage contact is
   `support@owlihealthcare.com`; the event side (form + confirmation mail +
   ticket footer) is `arokyapayanam@owlihealthcare.com`. Both Gmail addresses
   are retired — verify those two mailboxes exist in hPanel → Emails.

---

## 1. What this project is

A bilingual (English/தமிழ்) clinic website for **Owl I Health Care Centre**
(naturopathy clinic, Tirunelveli). The former PHP/MySQL Arokya Payanam 2026
registration/payment system is retained for existing attendee support and
financial reconciliation; the event is cancelled. Hostinger's config blocks new registrations, but the local hide/recovery patch has not yet been deployed.

| Piece | Where | Notes |
|---|---|---|
| Clinic homepage | `/` (`index.html`) | Live clinic page still contains event references until the cancellation patch deploys |
| Event page | `/events/` | Live currently serves the full event page; local patch replaces it with existing-booking recovery |
| Registration API | `api/*.php` | Live config refuses new registrations; cancellation quote guard is local pending deploy |
| Admin console | `/admin/` | Password-protected; cancellation guards are local and not deployed, so avoid write actions until deploy |
| Payments | Coaxlure (hosted checkout) | No new public registrations (server mode closed); retain status/ticket/reconciliation tools |
| Deploy | GitHub → Hostinger `public_html/` | Auto-deploy on push to `main`; cancellation changes still need deployment |
| Docs | this repo's `.md` files | Index in §10 |

Historical architecture: the former registration flow created a pending row, opened Coaxlure checkout, verified by order ID, and issued a PDF ticket. **Current post-cancellation public flow:** no new quotes or registrations; `/events/?ref=AP26-XXXXXX` remains available to look up an existing booking and download a paid ticket. Admin/cron/payment history remain for reconciliation.

---

## 2. Current status (live probed 2026-11-12; cancellation patch not deployed)

| Check | Result |
|---|---|
| Git/workspace | Cancellation patch is uncommitted on `main`; latest base commit is `9d494f9` |
| Hostinger config/API | `registration.mode` set to `closed`; live register safely returns HTTP 503 `not_open` |
| Live quote | HTTP 200, `mode:closed`, but current deployed quote endpoint still returns stored prices (cancellation code not deployed) |
| Live public pages/assets | `/events/`, `/events/index.html`, event artwork, event JS/CSS return HTTP 200; homepage still contains event references |
| Local cancellation code | Removes homepage promotion, blocks full event/artwork/assets, makes `/events/` a booking-support page, hides closed quotes, blocks admin entries/coupon generation/activation (deactivation remains available) |
| Prices | Historical DB values; quote API still exposes values until cancellation patch deploys; do not adjust or use for sales |
| Coaxlure paid detection | ✅ live-proven with the real ₹10 payment (`AP26-WC4DV9`, paid) |
| Ticket PDF | ✅ streams for paid refs (paid-only, 404 otherwise); link also in the confirmation email |
| Cron (5 min) | ✅ running; 3 passes: recheck ≤3-day pendings, sweep >2-day stale, digest every 2 days |
| Admin console | Live admin remains deployed; cancellation-only add/coupon guards are not deployed, so staff write actions remain available—avoid using them until the patch lands |
| Validation suite | PHP lint, JS syntax, i18n, pay-flow, and local HTTP/DB cancellation suite pass (**85/85**) using a temporary isolated local DB; live hide/admin-guard verification awaits deploy |
| Emails on the site | ✅ `support@…` / `arokyapayanam@…` live; **no Gmail address in any served file** |
| DB migrations/digest recipient | Historical setup only; do not run schema changes during cancellation unless reviewed for reconciliation |
| Coupons | Existing codes/usage retained for records; generation and reactivation disabled while cancelled |
| Existing payment records | Preserve all rows; reconcile pending orders/refunds. Do not run historical test-row deletion SQL |

---

## 3. Tier prices — historical reference only

Prices were stored in the `tiers` database table as integer paise (₹1 = 100 paise). The last pre-cancellation values reported were Student ₹349, Doctor ₹399, BNI ₹399, General ₹449. **Do not update prices, enable coupons, or reopen registration while cancelled.** If the owner formally reinstates the event, first review this handoff/runbook and only then use the prior tier SQL procedure from Git history. Existing paid rows remain immutable; preserve all records.

---

## 4. Owner actions after cancellation

Hostinger `owli-config.php` has been checked safely: `registration.mode` is now `'closed'`. The live API immediately refuses new registrations. The local cancellation patch has passed 85/85 full-stack validation against an isolated local DB.

1. **Review the uncommitted patch** (homepage, API quote guard, `/events/` recovery page, `.htaccess` asset blocks, admin guards, validator and docs). Until it deploys, do not use the live admin Add Entry/coupon write controls.
2. **Checks already run:** PHP lint, JS syntax, i18n parity, pay-flow regression, and local Docker cancellation validation (85/85).
3. **Commit/push/deploy after review.** Then verify `/events/` serves the minimal existing-booking status/ticket page; `/events/index.html`, event artwork, and event-only JS/CSS return 403/404; `quote.php?category=all` returns `mode:closed` with empty prices; admin history/ticket support remains available but entry/coupon writes are disabled.
4. **Reconcile outstanding payments/refunds** with Coaxlure and contact affected attendees. Keep the 5-minute cron running until pending orders/refunds are resolved.
5. **Preserve all database records.** Do not run historical test-row deletion, table wipe, coupon-generation, or tier-update procedures during cancellation. Data retention/deletion requires a separate owner decision after financial/support obligations are settled.

---

## 5. Historical owner setup actions — ARCHIVE ONLY

The following legacy launch setup SQL is kept as project history and **must not be executed** as part of cancellation. Do not run the ALTERs, coupon generation, price changes, test-row deletion, or full-table wipe now. Preserve records. The actionable cancellation steps are in §4.

1. **Run the two one-time ALTERs** (phpMyAdmin → SQL) — they enable the admin
   console's staff entries + delete-staff-only, and the abandoned-payment sweep:

```sql
ALTER TABLE registrations
  ADD COLUMN source ENUM('web','staff') NOT NULL DEFAULT 'web' AFTER reference;

ALTER TABLE payment_attempts
  MODIFY status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending';
```

2. **Re-run validation** (hPanel → Terminal) — expect all green incl. the
   `registrations.source` check:

```bash
php domains/owlihealthcare.com/public_html/scripts/validate.php
```

3. **Create the coupon packs** — admin console → **Coupons → Generate codes**
   (codes print once; they live only in the DB — never in any committed file):

| Type | Value | How many | Uses per code | Prefix |
|---|---|---|---|---|
| percent | 10 | 1 | unlimited | `EARLY` |
| percent | 20 | 1 | unlimited | `PAYANAM` |
| percent | 100 | 20 | 1 (single-use) | `FREEAP26-` |

4. **Set the digest recipient** — `owli-config.php` → `'admin'` block →
   `'digest_to' => 'arokyapayanam@owlihealthcare.com'` (falls back to
   `mail_from`). The 2-day digest then emails automatically.
5. **Confirm the mailboxes exist** (hPanel → Emails):
   `registrations@owlihealthcare.com` (sender for confirmations + digests),
   `support@owlihealthcare.com` and `arokyapayanam@owlihealthcare.com`
   (the two new contact addresses). Emails are silently skipped if the
   sender mailbox is missing — payments are unaffected.
6. *(Historical only; do NOT run after cancellation)* Clean the two test rows (attempts first — foreign key):

```sql
DELETE FROM payment_attempts WHERE registration_id IN
  (SELECT id FROM registrations WHERE reference IN ('AP26-WC4DV9','AP26-5DC2FK'));
DELETE FROM registrations WHERE reference IN ('AP26-WC4DV9','AP26-5DC2FK');
```

   **Never run this full wipe while cancelled** (historical pre-launch recipe only):

```sql
DELETE FROM payment_attempts;
DELETE FROM registrations;
ALTER TABLE registrations  AUTO_INCREMENT = 1;
ALTER TABLE payment_attempts AUTO_INCREMENT = 1;
```

   Leave `tiers`, `coupons`, `settings` untouched. Verify with
   `status.php?ref=AP26-WC4DV9` → 404 and a 0-row admin console.
7. **Confirm live Coaxlure mode.** During the ₹10 run the checkout displayed
   "Test Mode" wording — ask Coaxlure support to confirm the account is
   live-ready for real sales before marketing starts.
8. *(Content, before marketing)* `images/og-image.jpg` is missing (social
   shares show no image — add 1200×630 JPG); gallery/blog images and
   testimonials are still placeholders; decide whether the Site24x7 RUM
   beacon stays (`index.html` — self-labelled "remove before production").

---

## 6. Operating the system after cancellation

**Live safety switch:** Hostinger `owli-config.php` is currently set to `registration.mode = 'closed'`; the live register endpoint confirms HTTP 503 `not_open`. The local `OWLI_EVENT_CANCELLED` guard closes quotes and registrations regardless of config, but is not live until deployed. The local patch also removes homepage event UI, blocks event page/artwork/assets, preserves `/events/?ref=…` booking recovery, and disables admin entry/coupon writes. Until deploy, do not assume those presentation/admin changes are active in production.


**Where things live:**

| Thing | Location |
|---|---|
| Secrets (DB creds, Coaxlure client_id/secret, admin hash, digest_to) | `/home/u810328541/domains/owlihealthcare.com/owli-config.php` — above `public_html`, git-ignored. Template: `api/config.sample.php` |
| Cron (every 5 min) | `/usr/bin/php /home/u810328541/domains/owlihealthcare.com/public_html/api/cron-recheck.php` — output visible in the hPanel cron page |
| Admin console | `https://owlihealthcare.com/admin/` — password = `php api/admin-pass.php 'new-password'` → paste the printed `$2y$…` hash into the config's `admin.password_hash` (empty hash = console disabled/403) |
| Prices / coupons | DB tables `tiers` / `coupons` (§3; coupons manageable in the console) |
| Validation | `php public_html/scripts/validate.php` (**85 checks**; exit 1 on failure) |
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

## 7. Verification & testing — what exists, how to run it

| Check | Command | What it protects |
|---|---|---|
| Pre-deploy validation | `php scripts/validate.php` (or Docker equivalent) | Config, DB/schema, APIs/admin/protections, booking recovery route, cancellation guard, blocked event assets; 85/85 locally |
| i18n parity | `node scripts/check-i18n.js` | Every `data-i18n` key exists in BOTH EN + TA (**370 keys**, incl. agenda + competitions) |
| Pay-flow regression | `node scripts/check-pay-flow.js` | New-tab navigation contract, **label contract** (no invisible buttons), no reopen link, check-status button, WhatsApp/ticket action counts — scenarios A–H |
| Lint | `php -l <file>` / `node --check <file>` | Syntax before any commit |
| Mobile audit | headless-Chrome script — see `LOCAL-TESTING.md` §4.6 | 375/414/768px: no horizontal scroll, ≥40px tap targets |
| Live smoke (after deploy) | quote endpoint + `/events/?ref=<ID>` + `/admin/` | quote is closed/no prices; existing paid ref remains checkable/downloadable; admin read/export works |

**Local stack** (full guide: `LOCAL-TESTING.md`): Docker `owli-web` (Apache,
`http://localhost:8800/`, repo bind-mounted at `/srv/public_html`) + `owli-db`
(MariaDB, db `owli`, user `owliuser`). The local config currently mounted
read-only into the container is `~/work/owli-config-freetest.php` (**dev
Coaxlure + `open` mode** — for exercising the sweep/ticket flow locally);
canonical local config: `~/work/owli-config.php`. **Never POST to
`register.php` on the LIVE server while it's `open` — it creates real
registrations/orders; test locally.**

**The three rules of a good deploy:** run the checkers locally → commit only
reviewed files → after ~5 min verify live (`quote.php`, the changed asset,
`?v=` cache bump). Any `.md`/`sql` regression fails validation (they must 403).

**Debugging anything:** `API-TROUBLESHOOTING.md` — the six-step workflow
(reproduce outside the browser → read the API's verbatim code → read the
`owli `-prefixed log line → read DB evidence → isolate the layer → fix +
verify), every endpoint's status codes, the log-prefix table, and four real
case studies from this project.

---

## 8. Safety rules — never break these

1. **Never commit secrets or coupon codes.** The repo is public via GitHub
   deploy; live Coaxlure credentials exist only in the server config and the
   local CSVs (`~/work/coaxlure/*.csv`, chmod 600). The admin console stores a
   bcrypt hash, never the password.
2. **The browser is never trusted with money.** Amounts are recomputed
   server-side from `tiers`/`coupons`; a payment redirect is never treated as
   proof of payment — only Coaxlure's API answer is.
3. **Website-sourced rows (`source='web'`) are undeletable** from the admin —
   the SQL itself carries `AND source='staff'`. Only staff-added entries can
   be deleted. Do not "fix" this by removing the guard.
4. **Never serve `.md`/`.sql`/`.log`/`ini` files** — the root `.htaccess` +
   `validate.php` enforce this (they once leaked every coupon code via
   `/GO-LIVE-STEPS.md`; closed, guarded, and now gated).
5. **Cancellation backup:** keep Hostinger server switch at `'closed'`; do not set it to `preview` or `open` without explicit owner reinstatement.
6. **Cache-busting:** when editing any public CSS/JS, bump its `?v=` in the
   HTML. Current versions: `event-config.js?v=8`, `event-register.js?v=12`,
   `i18n.js?v=12`, `events.css?v=7`, `event-banner.css?v=2`,
   `event-banner.js?v=2`. (`styles.css`, `responsive.css`, `main.js`,
   `contact.js`, `lightbox.js`, `testimonials.js`, `treatment-details.js`
   have no `?v=` yet — add one the next time any of them is edited.)
   Purge Hostinger's CDN cache if assets look stale.
7. **Schema changes:** `sql/schema.sql` is the source of truth for fresh
   installs — keep it in sync with any ALTER run on the live DB, and document
   the live-DB migration beside it (as done for `source` and the ENUM).
8. **Local vs live credentials:** dev/test Coaxlure creds → dev API only;
   live creds → live API only. Local Docker values never go to Hostinger.

---

## 9. Known open items (historical)

| Item | Where |
|---|---|
| `images/og-image.jpg` missing | social shares show no image — add 1200×630 |
| `www.` doesn't redirect to apex | cosmetic; canonical tags already point to the apex |
| Gallery/blog/testimonial placeholders | content work before marketing (`README.md` §11) |
| Site24x7 RUM beacon | decide keep/remove (`index.html`, self-labelled pre-prod note) |
| `sitemap.xml` `lastmod` | update on the final content release |
| Event hard-close | `register.php` self-closes after `2026-11-15T16:00+05:30` (410 `closed`) — no action needed |
| Test rows | Historical references only; preserve until payment/refund reconciliation and retention review |
| Coaxlure historical checkout wording | retain as a historical reconciliation note |
| Admin "last cron ran" display | not built — hPanel cron page shows output instead |
| Event mailbox | confirm `arokyapayanam@…` exists if needed for attendee support |

---

## 10. Key technical facts (fast context)

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
- **Content (final state, 2026-10-25):** official agenda from the owner's
  docx — 4 scientific sessions, all named (Dr. Saranya D. 10:30 · Dr.
  Kalaiselvan G. 11:10 · Dr. Priya P. 13:30 · **Dr. L. Beena Theres 14:20**,
  "The Heart Under Pressure: Yoga and Naturopathy in Hypertensive Heart
  Disease"); 4 panel discussions; prize distribution 15:20–15:45; stats
  1350+ total footfall. Competitions: **one common Google-Form registration
  link** below the three cards + the separate reels-submission link on the
  reels card (owner decision 2026-10-25). Audience grid: **1000+ students ·
  100+ doctors · 50+ BNI** (General Public count card removed by owner; the
  General Public description card and its registration category remain).
  Contact emails: `support@owlihealthcare.com` (homepage) ·
  `arokyapayanam@owlihealthcare.com` (event + confirmation Reply-To + ticket
  footer). Marketing strikethrough prices live in `strikePrices`
  (`js/event-config.js`).
- **Booking-ID lookup** (`#ticket-lookup`): paid → the three paid actions
  (Download ticket · Get ticket on WhatsApp · Done); anything else → one
  uniform invalid message (leaks nothing).
- **Free coupon flow:** 100% coupon → `{ok:true, free:true}` → instant paid,
  no payment tab. Coupon codes are server-verified only — shipping hashed
  codes to the browser was deliberately **rejected** (offline brute-force).
- **WhatsApp automation** was deliberately NOT built (Meta onboarding + fees);
  the `wa.me` handoff (official number +91 63796 10554, event + Booking ID
  pre-typed) covers the need. Upgrade path documented in `CHANGE-LOG.md`.
- **i18n:** all visible strings via `data-i18n` keys in `js/i18n.js`,
  EN + TA parity enforced (**370 keys**). The admin console is English-only by
  design.
- **Marketing price display:** the price list shows strikethrough
  "before" prices from `strikePrices` in `js/event-config.js` (2026-10-26
  owner change) — keep them in sync with tier changes.

---

## 11. Document index — which file answers what

| Need | File |
|---|---|
| **Start here / current truth** | `HANDOFF.md` (this file) |
| Everyday maintenance (edit text/images/doctors/services, deploy, cache) | `README.md` |
| Full developer spec (structure, palette, rules, API map) | `CLAUDE.md` (+ `docs/CLAUDE-*.md`) |
| Product requirements + launch criteria | `PRD.md` |
| Go-live / ₹10 test history / switch flips / DB migrations | `GO-LIVE-STEPS.md` |
| Live status snapshot + decision log (pre-refresh numbers) | `PROJECT-STATUS.md` |
| API debugging runbook (workflow, codes, log prefixes, case studies) | `API-TROUBLESHOOTING.md` |
| Local Docker stack + checkers + verification techniques | `LOCAL-TESTING.md` |
| Every dated change, why + how verified | `CHANGE-LOG.md` |

---

## 12. Cancellation status and retained support

Arokya Payanam 2026 is cancelled. The local patch removes homepage promotion, blocks the former full event page/artwork/event-only assets, removes the sitemap entry, and serves a noindex `/events/` booking-recovery page. It closes quotes, keeps registrations refused, and disables new admin entries/coupon activation or generation while preserving history/status/tickets. **These code changes are not deployed yet.** Live currently has the server registration switch closed, but the old event promotion/page/assets remain public and quote still returns stored prices. No database rows are deleted by the patch.

## 13. After the event (retirement checklist)

1. Reconcile/refund pending Coaxlure orders and respond to attendee enquiries.
2. Export final registration/payment data from the admin console for records.
3. Once support/refund and retention obligations are settled, separately decide whether to archive/retain data. Do not run a bulk wipe as part of cancellation.
4. Keep the minimal `/events/?ref=…` recovery route available as long as existing tickets may need validation/download.

---

*End of handoff. Updated 2026-11-12 after confirming Hostinger `registration.mode` is closed and probing live endpoints. New registration is refused, but event promotion/page/assets are still public until the uncommitted cancellation patch is reviewed and deployed. No API 500 was reproduced. Preserve existing attendee/payment records; do not run historical launch or cleanup instructions.*

<!-- Historical project handoff text follows; current operator state is in sections 0–12 above. -->

<!--
the complete project history: preview/open modes, the ₹10 live payment test
(real money, paid detection proven), the checkout blank-page fix (plural→
singular URL) and token-rotation fix (order-id verification), the invisible-
button bug class (closed by the label + light-surface guards), the docx
content deploy (official agenda, new emails, ₹349 student, strikethrough
prices, competition links consolidated to one common form + reels),
Scientific Session 4 (Dr. L. Beena Theres) and the final audience cards
(1000+ students / 100+ doctors / 50+ BNI; General Public count card
removed by owner decision, description card kept). Fresh audit of the live
site, local stack, and full test battery (72/72 validate, 370/370 i18n,
pay-flow PASS). Everything stated above was verified live except where
marked as a pending owner action.
-->
