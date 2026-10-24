# GO-LIVE-STEPS.md — Arokya Payanam 2026: ₹10 live payment test + opening registration

> **What this file is:** the concrete, operator's runbook for the LAST remaining step
> before registration opens — the ₹10 live payment test, then flipping both switches
> to `'open'`. It is tailored to the **current deployed state** (Hostinger,
> database `u810328541_owlievents`, both switches at `'preview'`, patch `f379888`
> live as of 2026-10-24). The technical background lives in `README.md` §10;
> this file is the step-by-step checklist.
>
> **Why a real ₹10 payment:** Coaxlure has no sandbox. The only unverified
> behaviour left in the whole chain is the exact wording of Coaxlure's *paid*
> `paymentStatus` response. One real ₹10 payment proves everything end-to-end:
> order creation, hosted checkout, server-side verification, the admin console,
> the cron recheck, and the confirmation email.

---

## Current state (all verified 2026-10-24)

| Component | State |
|---|---|
| Deploy (GitHub → Hostinger `public_html/`) | ✅ Live, including the ₹1-minimum patch (`f379888`) |
| Database `u810328541_owlievents` | ✅ 5 tables; prices student 299 / bni 399 / doctor 399 / general 449 |
| `owli-config.php` (above `public_html/`) | ✅ Live DB + live Coaxlure credentials; `registration.mode = 'preview'` |
| Validation on the server | ✅ 51/51 checks (`scripts/validate.php`) |
| Admin console `https://owlihealthcare.com/admin/` | ✅ Login + dashboard + CSV export verified |
| Cron (payment recheck, every 5 min) | ✅ Running; output visible in hPanel |
| Public site | Preview: prices + form visible, Pay button disabled, server refuses (503) |

**Remaining:** the ₹10 test → open both switches. Nothing else.

---

## Read this first — the two switches

Registration is live **only when BOTH say `'open'`**:

| Switch | File | Change takes effect |
|---|---|---|
| **Server** (the authority) | `owli-config.php` on Hostinger → `registration.mode` | **Instantly** — edit + save, no deploy |
| **Client** (the UI) | `js/event-config.js` → `registrationMode` | After **commit + push + deploy** (~5 min) |

Two switches on purpose: flipping the client alone can never open payments (the
server refuses with 503), so an accidental Git push can never take money. The
client flip is the **only** part of this runbook that goes through Git — and if
the test passes, that commit *is* the go-live commit, so nothing is wasted.

> ⚠️ **Correction to an earlier suggestion:** the client flip for the **live**
> test cannot stay "uncommitted/local" — a local edit does not affect Hostinger.
> For this live test it must be committed and pushed (Phase A2). A local-only
> flip only works for testing at `http://localhost:8800/` with dev credentials.

### Phases at a glance

| Phase | Server | Client | Public can… |
|---|---|---|---|
| **Now** | preview | preview | see prices/form; submissions refused (503) |
| **A — prep** | preview | **open** (pushed) | see the open form; submissions STILL refused |
| **B — test** | **open** | open | register + pay ₹10 ← run the test NOW |
| **C1 — success** | open | open | live at real prices (₹449 restored) |
| **C2 — failure** | preview | preview (reverted) | back to demo; one code fix, then retest |

**Keep A → B short** (minutes, not hours). During A the public form says
"Register Now" but the server answers "not enabled yet" — confusing for a real
visitor, so don't leave it sitting overnight.

> **Status 2026-10-24 (after the first attempt + fixes):** A2 is done (client
> `'open'` deployed), the ₹10 tier is set, the server switch was flipped for the
> first attempt, and the two payment-flow bugs it exposed are fixed but **not
> yet deployed** — do Phase B0 when the fixes land. The owner's first ₹10
> payment (`AP26-WC4DV9`) is captured by Coaxlure and should flip to paid after
> the deploy + one recheck.

### Who does what

| You (hPanel + browser) | Me (code / Git) |
|---|---|
| phpMyAdmin price change, server switch flip, the ₹10 payment, admin console check | Commit/push the client flip (`'open'` + `?v=` bump), any paid-detection matcher fix, the revert if the test fails |

---

## Phase A — prep (~10 min, no payments possible yet)

### A1. Set the ₹10 test price (phpMyAdmin)

hPanel → Databases → phpMyAdmin → `u810328541_owlievents` → **SQL** tab:

```sql
UPDATE tiers SET amount_paise = 1000 WHERE category = 'general';
```

Check it: `SELECT category, amount_paise FROM tiers;` → `general` now `1000`.

Note: while still in preview, visitors can *see* the ₹10 price on the price
list (they cannot pay). Another reason to keep the window short.

### A2. Client flip (I do this when you say "go")

`js/event-config.js`: `registrationMode: 'preview'` → `'open'`, plus bump the
cache version `?v=5` → `?v=6` on the script tag in **both** `index.html` and
`events/index.html` (search for `event-config.js?v=`). Commit + push →
GitHub auto-deploys (~5 min).

### A3. Verify the deploy landed (browser, hard-refresh or private window)

- `https://owlihealthcare.com/js/event-config.js` → shows `registrationMode: 'open'`
- `https://owlihealthcare.com/api/quote.php?category=all` → still `"mode":"preview"` (the server switch flips in B — that's correct) **and** `"general":10`

State check: form UI open, server still refusing. If a real visitor submits
during A they get the "not enabled yet" message — harmless, but move on to B.

---

## Phase B — the test (~10 min, live money)

> **⚠️ Read this first — the 2026-10-24 first attempt found two real bugs, both
> fixed (deploy required for B).** What happened then: the browser landed on a
> blank `https://app.coaxlure.com/customer/` page with no way to pay, the ₹10
> was actually captured by Coaxlure on a second link, and the site still showed
> *pending* hours later. Root causes (both verified against the live API):
>
> 1. **Coaxlure's `/payment-link` default URL is broken.** It returns
>    `/customer/coaxlure-payments/<token>` (plural) — a route the checkout app
>    does not serve, hence the blank page. The working route is
>    `/customer/coaxlure-payment/<token>` (singular), the same one the merchant
>    app and the customer SMS use. Fix (in `coax_create_payment()`): the server
>    now passes `'merchantDomain' => 'https://app.coaxlure.com/customer/coaxlure-payment'`
>    (config key `pay_page_base`) and belt-and-braces rewrites any plural URL
>    to the singular route.
> 2. **Coaxlure rotates the payment token when the payment is captured**, so
>    the create-time token stops working afterwards (`BA_4002` "Order not
>    found") and a paid attendee would look *pending* forever. Fix (in
>    `coax_fetch_status()`): status is now looked up by the stable **order id**
>    first (stored since the first integration), token as fallback, and paid
>    detection matches Coaxlure's real wording — `orderStatus: "PAID"` /
>    `paymentStatus: "success"` (verified against a genuine paid live order:
>    the owner's ₹10 payment, invoice `CLI-26104-114458`).
>
> So Phase B needs: **the fix deployed** (below), then the steps as written.
> Your earlier ₹10 payment (`AP26-WC4DV9`) was genuinely captured by Coaxlure —
> after the deploy, its row should flip to **paid** on the next recheck (cron
> within 5 min, or click "Recheck payment now" in the admin console for
> instant confirmation).

### B0. Deploy the payment-flow fixes (once, ~5 min)

I push the fixes → GitHub auto-deploys. Verify from a terminal:

```bash
curl -sS https://owlihealthcare.com/api/quote.php?category=all
```

No deploy marker to look for — instead verify behaviour after B1: the Pay
button must now lead to `app.coaxlure.com/customer/coaxlure-payment/...`
(**singular**, no trailing `/customer/` blank page).

### B1. Flip the server switch

File Manager → `domains/owlihealthcare.com/owli-config.php` → Edit → the
`'registration'` block:

```php
'registration' => [
    'mode' => 'open',   // was 'preview'
],
```

Save. **Instant** — no deploy. Registration is now LIVE at ₹10.

Verify: `https://owlihealthcare.com/api/quote.php?category=all` → `"mode":"open"`.

### B2. Register and pay

Private/incognito window → `https://owlihealthcare.com/events/`:

1. Category: **General** — the form must show **₹10**
2. Full name, 10-digit mobile, **your real email**, address — all mandatory
3. "Proceed to Payment" → Coaxlure's hosted page (`app.coaxlure.com/customer/coaxlure-payment/...`) → **pay the ₹10** (UPI/card)
4. Note your **Registration ID** (`AP26-XXXXXX`) from the confirmation screen

Repeat the test with the **same mobile + email** if needed — the pending row is
reused and re-priced (no duplicate rows, and the 8-per-10-min-per-IP limit is
not hit, since a retry updates rather than inserts).

### B3. Verify paid detection (check all of these)

| Check | Where | Expected |
|---|---|---|
| Admin console | `/admin/` → your reference | Status pill **Paid**, collected ₹10. If not yet: **"Recheck payment now"** |
| Exact Coaxlure wording | Expand the row → payment attempt → **"Coaxlure said"** | `order=paid payment=success by=order_id` (the verified live wording) |
| Status endpoint | `https://owlihealthcare.com/api/status.php?ref=AP26-XXXXXX` | `"status":"paid"` |
| Cron | hPanel cron output (within 5 min) | `checked=1 newly_paid=1` |
| Confirmation email | The inbox of the address you registered | "Registration confirmed - Arokya Payanam 2026" — *only if* the `registrations@owlihealthcare.com` mailbox exists (hPanel → Emails). If it doesn't, the email is silently skipped — **payments are unaffected** |

---

## Phase C — outcome

### C1 — PAID detected → registration is live (finish line)

1. **Restore the real price immediately** (phpMyAdmin → SQL):

   ```sql
   UPDATE tiers SET amount_paise = 44900 WHERE category = 'general';
   ```

2. Both switches stay `'open'`. **Registration is now live at real prices.** No
   further deploy needed — the A2 commit was the go-live commit.
3. Optional — remove the ₹10 test row (phpMyAdmin → SQL; **order matters**,
   payment attempts first, then the registration — there is a foreign key):

   ```sql
   DELETE FROM payment_attempts WHERE registration_id = (SELECT id FROM registrations WHERE reference = 'AP26-XXXXXX');
   DELETE FROM registrations WHERE reference = 'AP26-XXXXXX';
   ```

   (Or keep it — a real paid registration is a valid record. Your call.)
4. Done. Monitor: admin console + hPanel cron output during the first days.

### C2 — NOT detected → safe rollback, one code fix, retest

1. **Kill switch first:** `owli-config.php` → `'mode' => 'preview'` (instant;
   the public form locks again, payments impossible).
2. Send me: the registration reference, the **"Coaxlure said"** string, and the
   time of payment. Keep the row in the DB — `payment_attempts.raw_status`
   preserves the evidence.
3. I adjust the paid-detection matcher (`coax_fetch_status()` in
   `api/_bootstrap.php` — today it matches `paid` case-insensitively on
   `orderStatus`/`paymentStatus`), push, and we repeat Phase B.

---

## One-time DB additions (phpMyAdmin)

### Add the `source` column (enables staff entries in the admin console)

Fresh `sql/schema.sql` imports already have it; the live database was created
before this feature, so run this once (phpMyAdmin → SQL):

```sql
ALTER TABLE registrations
  ADD COLUMN source ENUM('web','staff') NOT NULL DEFAULT 'web' AFTER reference;
```

After it, the admin console gains: **+ Add entry** (records an offline cash /
UPI payment as a PAID row, `source='staff'`, with a live price preview and
optional coupon) and **Delete this staff entry** — staff rows only. Rows made
by the public website (`source='web'`) are permanent records and can never be
deleted from the console.

### Coupon packs (created by the ADMIN console — never stored in the repo)

The console now manages coupon codes for you, so coupon values never sit in a
committed file:

**Admin console → Coupons panel ("Generate codes")** — collapsed card below the stats. Pick:

| Field | Reusable 10% | Reusable 20% | Single-use FREE tickets |
|---|---|---|---|
| Type | percent | percent | percent |
| Value | 10 | 20 | 100 |
| How many | 1 | 1 | 20 |
| Uses per code | unlimited | unlimited | 1 |
| Prefix | EARLY | PAYANAM | FREEAP26- |

The generator invents the code suffixes (mixing letters + digits, unique),
inserts them, and prints the full list **once** — that page is the only place
they are ever shown together. Hand the `FREEAP26-*` codes out one per invited
guest. A used code stops working for everyone else (`used_up`); a free
registration is confirmed **instantly** with no payment page — the attendee
sees the success box and the confirmation email, and the row appears in the
admin console as paid (₹0).

How coupon use is counted (`owli_quote()` in `api/_bootstrap.php`):
`max_uses NULL` = unlimited (counted on PAID rows only; free rows count as
paid). Verify anytime: `SELECT code, discount_type, value, max_uses, active FROM coupons;`

Switch any coupon off later: `UPDATE coupons SET active = 0 WHERE code = '...';`

---

## Safety rules

- **Never commit credentials — or coupon codes.** `owli-config.php` lives only
  on the server (git-ignored); live Coaxlure credentials never appear in this
  repo. The admin console stores a hash, never the password, and coupon codes
  are generated in the console (database-only). The root `.htaccess` blocks
  `.md`/`.sql`/`.log`/`.ini` files from the web, but treat the repository
  itself as public: nothing secret goes in any committed file.
- The test charges **real money** (₹10, live credentials). That is the point —
  there is no sandbox.
- The ₹10 tier is live during Phase B — anyone who finds the form in that
  window pays ₹10. Keep B short and restore the price in C1 immediately.
- **Rollback is always one edit:** server switch → `'preview'`. No deploy, no Git.
- Don't rewrite `js/event-config.js` beyond the one-line mode value — the rest
  of the file carries the mode system (and never use `git checkout` on it).
