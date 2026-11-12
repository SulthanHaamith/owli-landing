# PROJECT-STATUS.md — Owli Healthcare: Arokya Payanam 2026

> **What this file is:** the single-page status of everything the project does,
> everything we changed, and every decision/wish the owner requested. It is a
> live snapshot — read it to know exactly where things stand and what is left.
> Technical background: `README.md` (how to run/maintain), `CLAUDE.md` (the
> specification), `GO-LIVE-STEPS.md` (the payment-test runbook),
> `API-TROUBLESHOOTING.md` (the debugging runbook).
>
> **Last updated: 2026-11-12 (cancellation patch review).** Git: `main` has uncommitted cancellation changes; Hostinger server mode is closed, but public page/assets remain live pending deploy. See `HANDOFF.md` for current verified details. This file is web-blocked by the root `.htaccess`.

---

## 1. What this project is

A bilingual (EN/தமிழ்) landing page for **Owl I Health Care Centre**, Tirunelveli,
with an event registration + payment backend for **Arokya Payanam 2026**:

| Piece | Where | What it does |
|---|---|---|
| Public site | `/` | Clinic information and appointment enquiries; local patch removes cancelled-event promotion (not deployed yet) |
| Registration API | `api/` (PHP 8.2+, MySQL) | Hostinger config is closed; cancellation code guard is pending deployment |
| Payment | Coaxlure (hosted checkout) | The browser never sees an amount — the server alone decides the price |
| Admin console | `/admin/` | Password-protected: registrations, statuses, coupons, CSV export, offline entries |
| Deployment | GitHub → Hostinger (`public_html/`) | Auto-deploy on every push to `main` |

**Cancellation overrides registration:** `OWLI_EVENT_CANCELLED` in `api/_bootstrap.php` forces registration closed regardless of either switch. Existing attendee status/ticket/admin tools remain for support.

| Switch | File | Effect |
|---|---|---|
| Cancellation override | `OWLI_EVENT_CANCELLED` in `api/_bootstrap.php` | Forces API closed regardless of config after the local patch deploys |
| Backup server setting | Hostinger `owli-config.php` → `registration.mode = 'closed'` | Owner has set this; live register endpoint refuses submissions |

---

## 2. Historical deployment snapshot; current live details are in HANDOFF.md

| Check | Result |
|---|---|
| Deploy | Cancellation patch is uncommitted; Hostinger config has registration closed |
| Repo → GitHub → Hostinger | Do not consider event promotion hidden until patch is pushed and deployed |
| Live public site | Homepage/event page/artwork/event assets still publicly served (HTTP 200) |
| Local cancellation patch | Removes promotion, blocks full event/assets, adds existing-booking recovery, closes public quotes, disables admin writes; validated 85/85 locally |
| Live API | register returns 503 `not_open`; quote is `mode:closed` but legacy endpoint still returns prices |
| Docs off the web | ✅ `GO-LIVE-STEPS.md`, `README.md`, `scripts/`, `sql/`, `api/_bootstrap.php` → all HTTP 403 |
| Validation suite | PHP lint, JS syntax, i18n, pay-flow and local full-stack suite pass (85/85); production deploy verification pending |
| Coaxlure integration | ✅ live-tested — the ₹10 test payment was captured and detected as paid |
| Payment navigation (new tab only) | ✅ fixed 2026-10-24 — the form page no longer hijacks itself to the payment page (was a `noopener`+`window.open` return-value bug); guarded by `scripts/check-pay-flow.js` |
| Coupons in live DB | ❌ **none yet** (`EARLY10`/`PAYANAM20` → `invalid`) |
| `source` column on live DB | ❌ **not yet** — the ALTER is still pending (step 1 below) |
| Test rows in live DB | ₹10 paid row `AP26-WC4DV9` (paid ✅); `AP26-5DC2FK` (pending, real unpaid Coaxlure order) |

**Historical:** The ₹10 test passed before the event cancellation. The payment chain was verified end-to-end; current operations are governed by `HANDOFF.md` and must preserve records while reconciling outstanding payments/refunds.

**New (2026-10-24): abandoned payments now resolve themselves.** Registrations
pending for more than **2 days** are moved to `failed` by the cron's sweep
(after one final Coaxlure check — a late payment still wins), and the admin
console gained **sorting + one-click filter chips + new filters** (see §4).

**New (2026-10-26): the owner-docx corrections are live** — official programme
(4 scientific sessions with named speakers, prize distribution 3:20–3:45 PM),
stats updated to the real footfall figures, both Google-Form competition
links, the email corrections, and the preserved price work (₹349 student +
marketing strikethrough prices). Details in `CHANGE-LOG.md`.

---

## 3. Current owner action — event cancellation

Hostinger `owli-config.php` is set to `'closed'`: live `/api/register.php` refuses submissions with HTTP 503 `not_open`. The live quote endpoint still returns stored tier prices; the homepage, full `/events/` page, artwork and event assets remain public (HTTP 200). The uncommitted local patch passed 85/85 local full-stack validation. Review/deploy it, then verify recovery route, blocked old page/assets, no-price quotes and retained admin/ticket support. Until deploy, avoid live admin write actions. Preserve records and reconcile outstanding orders/refunds.




The following owner setup instructions are historical and must not be followed during cancellation.

## 4. Historical setup notes (superseded by cancellation)

### Step 1 — two one-time DB migrations (phpMyAdmin → SQL)

```sql
ALTER TABLE registrations
  ADD COLUMN source ENUM('web','staff') NOT NULL DEFAULT 'web' AFTER reference;

ALTER TABLE payment_attempts
  MODIFY status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending';
```

The first enables the admin console's **Add entry** (offline cash/UPI payments)
and **delete-staff-entries-only** features. The second enables the
**abandoned-payment sweep** (pending → failed after 2 days — see GO-LIVE-STEPS.md
"abandoned-payment sweep"). Until they run, the console shows a polite notice
and the cron skips the sweep. `validate.php` flags both if missing.

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

**Superseded by event cancellation.** Do not open registration, alter prices for sales, generate coupons, or clean test rows using the historical instructions below. Preserve the attendee/payment records and follow current cancellation actions in §3 above.

### Step 4 (optional) — clean up the two test rows

If you want the DB clean before launch (order matters — foreign key):

```sql
DELETE FROM payment_attempts WHERE registration_id IN
  (SELECT id FROM registrations WHERE reference IN ('AP26-WC4DV9','AP26-5DC2FK'));
DELETE FROM registrations WHERE reference IN ('AP26-WC4DV9','AP26-5DC2FK');
```

### Step 5 — set the digest recipient (one line, enables the 2-day admin email)

The cron now sends an automatic **registrations digest every 2 days** — every
entry, grouped Paid / Pending / Failed, with money totals. It needs a
recipient. File Manager → `owli-config.php` → inside the `'admin'` block add:

```php
'admin' => [
    'password_hash' => '…unchanged…',
    'digest_to'     => 'arokyapayanam@gmail.com',   // where the digest goes
],
```

Save — instant, no deploy. (If `digest_to` is empty it falls back to
`mail_from`, i.e. `registrations@owlihealthcare.com`.) Test it from the hPanel
Terminal:

```bash
php domains/owlihealthcare.com/public_html/api/cron-recheck.php
```

— when a digest is due it sends one immediately; on Hostinger `mail()` really
sends, unlike the local Docker container (which has no mail agent and simply
logs a retry line).

### Step 6 — re-run the validation suite

In hPanel Terminal (after step 1):

```bash
php domains/owlihealthcare.com/public_html/scripts/validate.php
```

Expect the **`registrations.source`** check to pass now, plus the existing
checks (59/59 after the two new doc-block checks). Everything else stays as-is —
no redeploy needed for any of these steps
(they are all DB/config-side).

### Step 7 — known open items (low priority, non-blocking)

| Item | Notes |
|---|---|
| `images/og-image.jpg` missing | Social shares show no image until the clinic supplies one (1200×630) |
| `www.` doesn't redirect to the apex domain | Cosmetic — canonical tags already point to the apex |
| Cron heartbeat | Optional admin-console "last cron ran" display — offered, not built |
| Test-mode wording on Coaxlure checkout | The checkout page said "Test Mode" during the ₹10 run — confirm with Coaxlure that live credentials are correct if anything looks off |

---

## 5. What we built and changed (decision log)

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

### Ticket delivery: WhatsApp handoff + team downloads (owner request, label bug fixed 2026-10-26)

The WhatsApp handoff button initially shipped **invisible**: every property
(href, target, rel) was assigned but its **text label never was** — an empty
bordered pill inside the white status box. Found via the owner's report
("Get ticket via whatsapp is not visible"), root-caused by reading
`showPaid()`, and fixed by setting `textContent = t('evf_get_wa')` in both the
paid and free-coupon paths. `scripts/check-pay-flow.js` now enforces a **label
contract**: every rendered action must carry a non-empty visible label, so a
label-less button can never pass the gate again. Cache bump
`event-register.js?v=10` (events page).

- **"Get ticket on WhatsApp"** — the paid success box (both the paid-after-
  payment path and the free-coupon path) now offers a third button. It opens
  a `wa.me` chat with the clinic's **official WhatsApp number** (+91 63796
  10554 — the same number the site's floating button and footer already use)
  with the event name and the **Booking ID pre-typed** into the message box;
  the attendee presses send and the team replies with the PDF. Chosen over
  the WhatsApp Business Cloud API deliberately: zero onboarding, zero
  per-message fees, and it routes to the official channel while always naming
  the Booking ID. The full Cloud API procedure (template, token, per-message
  cost, where the credentials would live) is in CLAUDE.md's change log for
  2026-10-25 if the owner ever wants automated sends instead.
- **Team ticket download from the admin** — every paid row's expandable
  details now carry a **"Download ticket (PDF)"** button, streaming the same
  `api/ticket.php` PDF the attendee gets. The team can forward it, print it,
  or hand it over at the desk. Paid rows only (the endpoint enforces that
  server-side; pending rows show the recheck button instead).
- **Confirmation email carries the ticket link** (owner request 2026-10-26):
  the email now includes
  `https://owlihealthcare.com/api/ticket.php?ref=<ID>` plus a "re-download any
  time" note, so the attendee has the ticket even if they never return to the
  site.
- **Automatic admin digest, every 2 days** (owner request 2026-10-26): the
  cron's pass 3 emails **every registration** — grouped PAID / PENDING /
  FAILED with totals and collected money — to `admin.digest_to` (falls back
  to `mail_from`). Best-effort by design: the schedule stamp is written only
  after a successful send, so failures retry next run; 1,500-row cap per mail
  with a pointer to the console.

### Abandoned payments: pending → failed sweep + admin categorization (owner request, 2026-10-24)

**The sweep (efficient and safe by design):** "pending for a long time" is
defined as **2 days** — far beyond any payment link's ~10-minute life and past
the cron's 3-day re-check window, so nothing that could still pay is touched.
The existing 5-minute cron runs it as a second pass: for each stale attempt it
makes **one final Coaxlure check** (money beats expiry — a paid answer marks the
row paid instead of failed), then flips the attempt to `failed` with the reason
stored in `raw_status`, and the registration too when nothing live is left on
it. Limits: 200 attempts per run, all in transactions, every action logged,
nothing ever deleted — history is the point. `status.php` reports `failed`
without another Coaxlure call, and the attendee can simply register again: the
same row (same Booking ID) is revived to pending at the **current** price with a
fresh attempt.

**Admin categorization & sorting (the "easier to sort" half):**

- **Filter chips** with live counts: All · Paid · Pending · Failed · Stale
  pending (7+ days) · Last 24h · Staff-added — one click for the questions
  the team actually asks, each showing its own number.
- **Pending is split by age** in the summary line: "< 1 hour" (someone actively
  paying) vs "older" (probably gone) — so stale money never hides among fresh.
- **New filters:** Source (web/staff), Coupon code, Registered age (last 24h /
  7 days / 7+ days).
- **7 sort orders:** newest, oldest, name A–Z, amount high→low / low→high,
  status-grouped (pending → paid → failed), most recently paid. Whitelisted
  column fragments only — sort keys are code, not user input.
- CSV export follows whatever filters **and sort** are active; the Failed stat
  card is renamed "Failed / abandoned".

**Verified locally end-to-end:** 3-day-old pending attempt → cron sweep →
attempt `failed` ("expired link (unpaid after 2 days)") + registration
`failed`; `status.php` → `failed`; re-registering the same mobile+email revived
the **same reference** as pending at the current price with a new attempt; the
paid-pair 409 lock unaffected; amount sort orders confirmed in the rendered
table and the CSV; 59/59 validation, pay-flow regression and i18n parity PASS.
**One ALTER needed on the live DB** (attempt status ENUM) — documented in
`GO-LIVE-STEPS.md` next to the `source` migration.

The waiting box (while the Coaxlure tab is open beside the page) shows: the
Booking ID + "checking here every few seconds…", a **"Check payment status now"**
button (one server-verified status call; re-arms while pending), and the amber
do-not-refresh warning. The old **"Reopen the payment page" link is removed** by
owner request — the payment tab is already open; if the attendee closed it, the
pending row survives and re-submitting the form creates a fresh attempt on the
same row (no duplicate person, no double charge). The "invisible until hover"
secondary-button bug inside the white status box is fixed (green-on-white
re-skin scoped to `.ev-status-box .btn-secondary`).

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

## 8. Current owner action — cancellation

1. Hostinger `owli-config.php` is already set to `registration.mode = 'closed'`; keep it closed.
2. Review and commit/push/deploy the cancellation patch; verify the booking-recovery page, hidden event assets, closed/no-price quote, and retained admin/ticket support. Local validation passes 85/85.
3. Avoid live admin Add Entry/coupon actions until deployment adds the cancellation guard.
4. Reconcile/refund pending orders and preserve all attendee/payment rows.
5. Do not run old ALTER/coupon/test-row cleanup instructions; data cleanup requires separate support and retention review.

Historical launch steps are superseded and must not be followed while cancelled.
---

## 6. Historical money flow (one-paragraph reference)

Visitor submits the form → `api/register.php` validates and **re-prices from the
DB** (the browser never sends an amount) → a *pending* registration is created (or
the existing one for that mobile+email is reused) → a Coaxlure order is made and
the browser is redirected to the **hosted** checkout on `app.coaxlure.com`
(singular `coaxlure-payment` route) → on return (or within 5 minutes via cron)
`status.php` / `cron-recheck.php` ask Coaxlure **by order id** whether the payment
was captured → a paid row is marked paid once, inside a transaction, with the
confirmation email sent. A free coupon skips the payment half entirely.

---

## 7. Where things live (quick map)

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
