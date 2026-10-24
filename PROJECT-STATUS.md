# PROJECT-STATUS.md — Owli Healthcare: Arokya Payanam 2026

> **What this file is:** the single-page status of everything the project does,
> everything we changed, and every decision/wish the owner requested. It is a
> live snapshot — read it to know exactly where things stand and what is left.
> Technical background: `README.md` (how to run/maintain), `CLAUDE.md` (the
> specification), `GO-LIVE-STEPS.md` (the payment-test runbook),
> `API-TROUBLESHOOTING.md` (the debugging runbook).
>
> **Last updated: 2026-10-24.** Git: `main` = 980d624 (clean, pushed, deployed).
> This file is web-blocked by the root `.htaccess` — it is for the team, not the public.

---

## 1. What this project is

A bilingual (EN/தமிழ்) landing page for **Owl I Health Care Centre**, Tirunelveli,
with an event registration + payment backend for **Arokya Payanam 2026**:

| Piece | Where | What it does |
|---|---|---|
| Public site | `/`, `/events/` | Clinic info + event details, prices, registration form |
| Registration API | `api/` (PHP 8.2+, MySQL) | Validates, server-prices, stores registrations, creates Coaxlure orders |
| Payment | Coaxlure (hosted checkout) | The browser never sees an amount — the server alone decides the price |
| Admin console | `/admin/` | Password-protected: registrations, statuses, coupons, CSV export, offline entries |
| Deployment | GitHub → Hostinger (`public_html/`) | Auto-deploy on every push to `main` |

**Two switches control registration** (both must say `open`):

| Switch | File | Effect |
|---|---|---|
| Server (authority) | `owli-config.php` on Hostinger → `registration.mode` | Instant, no deploy |
| Client (UI) | `js/event-config.js` → `registrationMode` | Needs commit+push (~5 min deploy) |

---

## 2. Current live status — verified 2026-10-24

| Check | Result |
|---|---|
| Deploy (latest code on Hostinger) | ✅ live — assets match the repo exactly (`event-config.js`, `event-register.js`, `i18n.js`) |
| Repo → GitHub → Hostinger | ✅ `main` clean & pushed (980d624) |
| Public site, events page, admin, robots | ✅ all HTTP 200 |
| API | ✅ healthy — `mode:"open"` — **registration is LIVE at ₹10 (General)** |
| Prices (live DB) | student ₹299 · doctor ₹399 · bni ₹399 · **general ₹10 (test price)** |
| Docs off the web | ✅ `GO-LIVE-STEPS.md`, `README.md`, `scripts/`, `sql/`, `api/_bootstrap.php` → all HTTP 403 |
| Validation suite (local) | ✅ **57/57 checks passed** · pay-flow regression `check-pay-flow.js` **6/6 scenarios PASS** |
| Coaxlure integration | ✅ live-tested — the ₹10 test payment was captured and detected as paid |
| Payment navigation (new tab only) | ✅ fixed 2026-10-24 — the form page no longer hijacks itself to the payment page (was a `noopener`+`window.open` return-value bug); guarded by `scripts/check-pay-flow.js` |
| Coupons in live DB | ❌ **none yet** (`EARLY10`/`PAYANAM20` → `invalid`) |
| `source` column on live DB | ❌ **not yet** — the ALTER is still pending (step 1 below) |
| Test rows in live DB | ₹10 paid row `AP26-WC4DV9` (paid ✅); `AP26-5DC2FK` (pending, real unpaid Coaxlure order) |

**The ₹10 test (the last blocker) PASSED.** The full chain now works: register →
working checkout URL → pay → automatic paid detection. The two bugs the first
attempt found were fixed and verified live (see §4).

---

## 3. What the owner must do next (exact steps)

### Step 1 — one-time DB migration (phpMyAdmin → SQL)

```sql
ALTER TABLE registrations
  ADD COLUMN source ENUM('web','staff') NOT NULL DEFAULT 'web' AFTER reference;
```

This enables the admin console's **Add entry** (offline cash/UPI payments) and
**delete-staff-entries-only** features on the live DB. Until it runs, the console
shows a polite notice and hides those features. The admin's `validate.php` output
also flags it if missing.

### Step 2 — create the coupon packs (admin console, not SQL)

`https://owlihealthcare.com/admin/` → **Coupons panel → Generate codes** — three
separate generations:

| Type | Value | How many | Uses per code | Prefix |
|---|---|---|---|---|
| percent | 10 | 1 | unlimited | `EARLY` |
| percent | 20 | 1 | unlimited | `PAYANAM` |
| percent | 100 | 20 | 1 (single-use) | `FREEAP26-` |

The generated codes are printed **once** in the console — copy them out and keep
them wherever you keep credentials. They live only in the database; the repo
contains none. (Codes were previously listed in `GO-LIVE-STEPS.md` and that file
was publicly served — that hole is now closed, see §4.)

### Step 3 — decide the registration window

Both switches are `open` right now. General is ₹10 for your testing — anyone who
finds the form in that window pays ₹10. So either:

- **Keep testing** — leave everything as is; restore the tier when done:
  ```sql
  UPDATE tiers SET amount_paise = 44900 WHERE category = 'general';
  ```
- **Or pause registrations** — File Manager → `owli-config.php` → change
  `'mode' => 'open'` to `'preview'` (instant; the form locks, no deploy needed).
  Restore to `'open'` whenever you want to accept registrations again.

### Step 4 (optional) — clean up the two test rows

If you want the DB clean before launch (order matters — foreign key):

```sql
DELETE FROM payment_attempts WHERE registration_id IN
  (SELECT id FROM registrations WHERE reference IN ('AP26-WC4DV9','AP26-5DC2FK'));
DELETE FROM registrations WHERE reference IN ('AP26-WC4DV9','AP26-5DC2FK');
```

### Step 5 — re-run the validation suite

In hPanel Terminal (after step 1):

```bash
php domains/owlihealthcare.com/public_html/scripts/validate.php
```

Expect the **`registrations.source`** check to pass now, plus the existing
checks (59/59 after the two new doc-block checks). Everything else stays as-is —
no redeploy needed for any of these steps
(they are all DB/config-side).

### Step 6 — known open items (low priority, non-blocking)

| Item | Notes |
|---|---|
| `images/og-image.jpg` missing | Social shares show no image until the clinic supplies one (1200×630) |
| `www.` doesn't redirect to the apex domain | Cosmetic — canonical tags already point to the apex |
| Cron heartbeat | Optional admin-console "last cron ran" display — offered, not built |
| Test-mode wording on Coaxlure checkout | The checkout page said "Test Mode" during the ₹10 run — confirm with Coaxlure that live credentials are correct if anything looks off |

---

## 4. What we built and changed (decision log)

### Payments — the ₹10 test and its two fixes (2026-10-24)

The first live test exposed two real bugs, both fixed and verified:

1. **Blank checkout page.** Coaxlure's `/payment-link` API returns a plural URL
   (`/customer/coaxlure-payments/<token>`) that their own checkout app does not
   serve — the browser landed on an empty page. Fixed in `coax_create_payment()`:
   the server now passes `merchantDomain` (config `pay_page_base`) and rewrites
   any plural URL to the working singular route (`/customer/coaxlure-payment/...`).
2. **Paid never detected.** Coaxlure rotates the payment token at capture, so the
   stored token stops working after payment. Fixed in `coax_fetch_status()`: look
   up by the stable **order id** first (token as fallback), and match the verified
   live wording — `orderStatus: "PAID"`, `paymentStatus: "success"`.

**The whole chain is now proven end-to-end with real money.**

### Registration identity rules (owner question, then tightened)

- There is **no separate payment phone number**: the attendee's own 10-digit
  mobile is the only number in the flow (also what Coaxlure receives).
- Identity key = **mobile + email pair**. Same pair → one row total (reused while
  pending; refused with 409 after paid). Different email on the same mobile →
  a separate person. Two people cannot farm one pair into unlimited pending rows.

### Free-ticket coupons (owner request)

A coupon covering the whole price (percent 100, or flat ≥ base) now completes
registration **without any payment page** — the server marks the row paid
instantly, the browser shows the success box in-page, the confirmation email says
"Amount: FREE (coupon applied)", and the admin console shows it as a ₹0 paid row.
Single-use codes stop working after one use (`used_up`).

### Coupons are server-verified, never web-visible (owner request)

- The form **auto-applies** a coupon ~0.8s after typing stops (one cheap server
  call; the Apply button remains as a manual fallback).
- We **deliberately rejected shipping hashed codes to the browser**: a hash table
  would let anyone brute-force codes offline with no rate limit. Server-side
  lookup keeps every code secret.
- Codes live **only in the database**, generated in the admin console. The old
  coupon list in `GO-LIVE-STEPS.md` was removed, and the root `.htaccess` now
  blocks `.md`/`.sql`/`.log`/`.ini` from the web (it was found serving
  `GO-LIVE-STEPS.md` with all 22 codes — closed same day).

### Admin console (owner request)

- **Add entry** — records an offline cash/UPI payment as a paid row
  (`source='staff'`), with live price preview and optional coupon.
- **Delete is staff-only, enforced in SQL** — the button only renders on staff
  rows, and the `DELETE` carries `AND source='staff'`, so even a forged POST
  cannot remove a website row. Website rows are permanent records.
- Full form details are shown: address, base price, discount, paid-at, source
  badges (WEB/STAFF), payment-attempt history, "Coaxlure said" wording.
- **Coupons panel** — generate/deactivate codes; printed once, database-only.
- **Export CSV** — filter-aware, UTF-8 BOM for Excel, includes Source column.

### Other shipped items

- **Preview mode** (3-state: closed/preview/open) with the server-side switch —
  the whole point: flipping only the JS file can never open payments.
- **Validation suite** `scripts/validate.php` — 59 checks, gates deploys, safe to
  paste (no credentials ever printed).
- **i18n checker** `scripts/check-i18n.js` — EN/TA parity (355/355 keys).
- **Pay-flow regression** `scripts/check-pay-flow.js` — drives the real form
  module in a Node VM across six scenarios and enforces the navigation
  contract: **the payment page opens in exactly one new tab; the form page
  never navigates to it** (popup blocked is the one documented exception), the
  tab is closed on every non-payment outcome, and `preventDefault` always runs
  on the reopen link. Catches the `window.open(…, 'noopener')` returns-null-on-
  success trap that caused the double-navigation bug.
- **Credential hygiene** — `owli-config.php` above `public_html` + git-ignored;
  admin console stores a bcrypt hash, never the password; live/dev Coaxlure
  credentials only in the CSVs on the local machine (chmod 600), never in Git.
- **Mandatory fields** — full name + mobile enforced at every layer; labels marked `*`.
- **Lucide CDN removed** — dead dependency, replaced by inline SVGs.

---

## 5. How the money flows (one-paragraph reference)

Visitor submits the form → `api/register.php` validates and **re-prices from the
DB** (the browser never sends an amount) → a *pending* registration is created (or
the existing one for that mobile+email is reused) → a Coaxlure order is made and
the browser is redirected to the **hosted** checkout on `app.coaxlure.com`
(singular `coaxlure-payment` route) → on return (or within 5 minutes via cron)
`status.php` / `cron-recheck.php` ask Coaxlure **by order id** whether the payment
was captured → a paid row is marked paid once, inside a transaction, with the
confirmation email sent. A free coupon skips the payment half entirely.

---

## 6. Where things live (quick map)

| Thing | Location |
|---|---|
| Secrets (DB, Coaxlure, admin hash) | `/home/u810328541/domains/owlihealthcare.com/owli-config.php` — **not in Git** |
| Coaxlure credentials CSVs (local machine only) | `~/work/coaxlure/*.csv` (chmod 600, never committed) |
| Prices / coupons | DB tables `tiers` / `coupons` — change via phpMyAdmin or the admin console |
| Registration switches | Server: `owli-config.php`; client: `js/event-config.js` |
| Cron (payment recheck) | hPanel, every 5 min, `/usr/bin/php .../api/cron-recheck.php` |
| Validation | `php public_html/scripts/validate.php` (hPanel Terminal) |
| API debugging runbook | `API-TROUBLESHOOTING.md` — workflow, endpoint codes, log prefixes, real case studies |
| Local preview | `http://localhost:8800/` (Docker: `owli-web`, `owli-db`) — full setup + checkers: `LOCAL-TESTING.md` |
| Admin console | `https://owlihealthcare.com/admin/` |
