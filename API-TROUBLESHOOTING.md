# API-TROUBLESHOOTING.md — Owli Healthcare: the API debugging runbook

> **What this file is:** the step-by-step workflow for diagnosing **any** failure
> in the event-registration system — reproduce the error, read the evidence,
> find the cause, fix it, verify the fix. Written for the operator (hPanel
> access, phpMyAdmin, a terminal) — no framework knowledge needed.
>
> **Companion docs:** `PROJECT-STATUS.md` (where things stand right now and the
> remaining owner steps), `GO-LIVE-STEPS.md` (the ₹10 payment-test runbook),
> `README.md` §10 (technical background). This file is web-blocked by the root
> `.htaccess` (every `*.md` answers 403 on the live site), and
> `scripts/validate.php` asserts exactly that — a deploy FAILS if this file is
> ever web-readable.

---

## 0. Two rules the system is built on

1. **The browser is never trusted.** Prices, amounts and paid-status are decided
   server-side (the database, or Coaxlure's own API). A browser symptom is only
   a *report* — the truth lives on the server, and that is where you look.
2. **Every layer leaves evidence.** The API answers with machine-readable error
   codes (§3), every fault writes a log line starting with `owli ` (§4), and
   every payment attempt stores what Coaxlure literally replied ("Coaxlure said"
   in the admin console). You never have to guess.

---

## 1. Which document do I need?

| Situation | Open |
|---|---|
| "What works today, and what must I still do?" | `PROJECT-STATUS.md` |
| "How do I run the ₹10 payment test / open registration?" | `GO-LIVE-STEPS.md` |
| "Something does not work — how do I find out why?" | **this file** |
| "How does the whole system work?" | `README.md` §5 / §10 |
| "Is the deployment healthy right now?" | `php scripts/validate.php` (§2, Step 6) |

---

## 2. The six-step workflow

> Use these steps in order for ANY API problem: form errors, missing prices,
> payment weirdness, admin console issues. Steps 1–4 are pure reading — do them
> all before changing anything.

### Step 1 — Reproduce it OUTSIDE the browser

Never debug from a description alone. Make the server show *you* the failure:

```bash
# The price list (what the form should display):
curl -sS https://owlihealthcare.com/api/quote.php?category=all

# A coupon preview (display-level check; no registration is created):
curl -sS "https://owlihealthcare.com/api/quote.php?category=general&coupon=EARLY10"

# One registration's payment status (no personal details are returned):
curl -sS "https://owlihealthcare.com/api/status.php?ref=AP26-XXXXXX"
```

Tip: the browser already holds half the evidence — press **F12 → Network tab →
click the failed request → Response tab**. Copy that JSON verbatim before doing
anything else.

> ⚠️ **`register.php` is the one endpoint you must NOT probe on the live server
> while `mode = 'open'`** — a POST with valid data creates a real registration
> AND a real Coaxlure order (it can take money). Test POSTs on the local stack
> (`http://localhost:8800`, dev credentials), or probe while the server is in
> `preview`/`closed` (then it must refuse with 503 — which is itself a good
> test). See §7 Safety.

### Step 2 — Read the API's verbatim answer

Every reply carries `ok`; failures carry `error` (+ `message`, sometimes
`field`). Look the code up in §3 — the table tells you what it means and what
to do. **Most "errors" are the system working correctly**: `not_open` is the
preview gate doing its job, `already_registered` is the one-ticket-per-mobile+email
rule, 422 `validation` means a required field was empty/invalid.

### Step 3 — Read the server's log line

Every fault is written to the PHP error log with a line starting `owli `
(or `owli: ` for warnings). The message names the exact exception.

**On Hostinger:** hPanel → Websites → owlihealthcare.com → **Logs / Error log
viewer** (menu names vary slightly between hPanel versions), and search for
`owli`. From the hPanel Terminal you can also locate the configured log path:

```bash
php -i | grep error_log
```

**Locally (Docker):**

```bash
docker logs owli-web 2>&1 | grep 'owli ' | tail -30
docker exec owli-web sh -c "grep 'owli ' /var/log/apache2/error.log | tail -30"
```

The full prefix table is §4 — it maps every possible line to its meaning.

### Step 4 — Read the database evidence

- **Admin console** (`https://owlihealthcare.com/admin/`) → find the
  registration → expand the row → the payment-attempt list shows
  **"Coaxlure said"** — the raw, stored reply from Coaxlure's
  `payment-status` call (`payment_attempts.raw_status`). This single string is
  the most valuable payment-debugging evidence the system holds.
- **phpMyAdmin** when the console can't show it: `registrations` (status,
  amount, coupon, source) and `payment_attempts` (token, order id, raw_status,
  last_checked_at).

### Step 5 — Isolate the failing layer, then fix

| What the evidence says | Faulting layer | Typical fix |
|---|---|---|
| `curl` shows prices; the browser shows nothing / stale data | browser cache / JS | hard-refresh; bump the `?v=` on the changed asset |
| API answers 4xx with a clear `error` code | none — config or usage | follow §3's "what to do" column |
| API answers 5xx, or HTML instead of JSON | PHP | read the `owli … error:` log line (§4); `php -l` the touched file |
| Admin console shows a migration notice | DB schema | run the pending `ALTER` (see `GO-LIVE-STEPS.md`) |
| Order created but checkout page is wrong/unreachable | Coaxlure link layer | see §6 Case 2; check `pay_page_base` / `allowed_pay_host` |
| Money captured on Coaxlure but site says *pending* | payment verification | see §6 Case 3; admin → "Recheck payment now"; cron catches it within 5 min |
| Confirmation email missing | mail layer | see §4 `confirmation email failed`; payments are NOT affected |

### Step 6 — Verify, then prevent

1. **Verify** the original failure is gone — re-run the exact Step 1 command.
2. **Prevent it from coming back silently:**
   - `php -l <file>` on any PHP file you touched (inside Docker: `docker exec
     owli-web php -l /srv/public_html/api/register.php`).
   - `node scripts/check-i18n.js` if any translation key was touched.
   - `php scripts/validate.php` — locally via `docker exec owli-web php
     /srv/public_html/scripts/validate.php`; on Hostinger **after the deploy
     lands**: `php public_html/scripts/validate.php`. Expected: **57/57**.
   - Bump the `?v=` cache-buster on any JS/CSS file that changed (both pages).
3. Commit + push (auto-deploys in ~5 min), then run `validate.php` on Hostinger.
4. Record what happened in the `CLAUDE.md` change log (project convention).

---

## 3. Endpoint reference — every reply and what it means

### `GET api/quote.php?category=all`

| Reply | Meaning / what to do |
|---|---|
| 200 `{ok:true, mode:"open", prices:{…4 prices…}}` | Healthy, registration live |
| 200 `mode:"preview"` + prices | Healthy demo state; `register.php` still refuses (503) — by design |
| 200 `mode:"closed"`, `prices:{}` | **DB or config not reachable** — not a registration setting. Check `owli-config.php` exists one level above `public_html`; check DB credentials |
| 422 `{error:"category"}` | Called with an unknown category |
| Log: `owli quote(all) error:` | A DB exception occurred while building prices |

`quote.php` degrades gracefully: it never returns 500 — on DB trouble it answers
`mode:"closed"` so the public page still loads.

### `GET api/quote.php?category=X&coupon=CODE`

| Reply | Meaning / what to do |
|---|---|
| 200 `{amount, base, discount, coupon_error:null}` | Coupon applied; `amount` is the preview price |
| `coupon_error:"invalid"` | No such code in **this server's database** (coupons are per-DB — local codes do not exist on live and vice versa; generate in the admin console) |
| `coupon_error:"used_up"` | Single-use code already redeemed (counted on PAID rows) |

### `POST api/register.php` (JSON body — the form submit)

| HTTP | `error` | Meaning / what to do |
|---|---|---|
| 200 | — | Success. `payment_url` → Coaxlure checkout; or `free:true` when a coupon covered the whole price (row instantly PAID, no checkout) |
| 400 | `rejected` | Honeypot field filled — a bot. Correct behaviour |
| 503 | `not_open` | Server switch is `preview`/`closed` — expected before go-live |
| 410 | `closed` | Event window has ended |
| 422 | `validation` + `field` | A field failed — `field` says which (name, mobile, email, address, category fields, coupon) |
| 422 | `amount_too_low` | Tier price is under ₹1 with no full-price coupon — fix the `tiers` row |
| 429 | `rate_limited` | 8+ submissions from one IP in 10 minutes — wait |
| 409 | `already_registered` + `reference` | This mobile+email pair already has a PAID ticket — identity rule, not a bug |
| 500 | `no_price` | No active `tiers` row for that category |
| 500 | `server` | Unexpected exception — **a log line was written**: `owli register error: <message>` |

Note: the request MUST send `Content-Type: application/json` — anything else is
refused by design (this also blocks cross-site form posts).

### `GET api/status.php?ref=AP26-XXXXXX`

| Reply | Meaning |
|---|---|
| 422 `{error:"bad_ref"}` | Reference doesn't match the `AP26-XXXXXX` format |
| 503 `{error:"not_open"}` | **Database not configured** (historical name — nothing to do with registration mode) |
| 404 `{error:"not_found"}` | No registration with that reference |
| 200 `status:"pending"` | Not paid yet (the newest attempt was re-checked with Coaxlure, at most once per 5 s) |
| 200 `status:"paid"` | **Server-verified paid** — the only paid proof the site accepts |
| 500 `{error:"server"}` | Log: `owli status error:` |

### `api/cron-recheck.php` (CLI only — hPanel cron)

| Output | Meaning |
|---|---|
| `checked=0 newly_paid=0` | Nothing pending — the normal line until registrations exist |
| `checked=N newly_paid=M` | Rechecked N attempts, M flipped to paid |
| `Not configured yet.` | Config/DB problem while cron runs |
| Web request → 404/403 | Correct — the file refuses browsers (CLI guard + `.htaccess`) |

### `admin/index.php` (the console)

| Behaviour | Meaning |
|---|---|
| 403, no form at all | Console disabled (`password_hash` empty in config) or IP locked |
| 5 wrong passwords | IP locked 15 minutes — even the correct password refuses until it expires |
| POST actions rejected | Missing/invalid session or CSRF token — by design |
| "source column" notice | The one-time `ALTER` migration hasn't run on this DB yet (`GO-LIVE-STEPS.md`) |

---

## 4. The server error log — every `owli ` prefix and what it tells you

Grep the log for `owli ` (all project lines start with it). Audit lines (added /
deleted / generated) are informational, not faults.

| Log line begins | From | Meaning | What to do |
|---|---|---|---|
| `owli register error: <msg>` | `api/register.php` | A submission crashed — the API answered 500 `server` | The `<msg>` names the fault. Most common: a schema mismatch — e.g. the missing `source` column migration |
| `owli quote error:` / `owli quote(all) error:` | `api/quote.php` | DB exception while building prices | Page degraded to `closed`; check DB credentials / `owli-config.php` |
| `owli status check failed: <msg>` | `api/status.php` | One Coaxlure recheck threw; status stays as stored | Usually transient — retry; if it persists, capture "Coaxlure said" |
| `owli status error: <msg>` | `api/status.php` | The whole status request failed | Read the message |
| `owli cron: <msg>` | `api/cron-recheck.php` | One attempt failed inside a run; the rest continued | Check Coaxlure reachability; look at that attempt's `raw_status` |
| `owli: create-order response had no costDetails.totalAmount; amount not cross-checked` | `_bootstrap.php` | Coaxlure's reply was missing the amount field, so the amount was **not** verified against our price | Treat that order with suspicion; compare the amount in Coaxlure's merchant dashboard |
| `owli: unexpected payment host: <host>` | `_bootstrap.php` | The payment link's host is not in `allowed_pay_host` | Coaxlure changed its link host? Update the allowlist in `owli-config.php` |
| `owli: confirmation email failed: <msg>` | `_bootstrap.php` | The confirmation email could not be sent | **Registration/payment unaffected.** Check the `registrations@…` mailbox exists (hPanel → Emails) |
| `owli admin recheck: / add: / delete: / coupon_gen: / coupon_toggle: <msg>` | `admin/index.php` | A console action failed | Read the message; often the `source` migration |
| `owli admin: staff entry <ref> added (<ip>)` … `deleted (<ip>)` … `N coupon(s) generated` | `admin/index.php` | Audit trail — who did what from which IP | Informational |

---

## 5. Symptom → quick diagnosis

| Symptom | First check | Likely cause |
|---|---|---|
| "Something went wrong" on submit | §4 `owli register error:` | Register returned 500 — the log names the fault |
| Prices don't appear on `/events/` | `curl …/quote.php?category=all` | `mode:"closed"` → config/DB; otherwise stale JS → hard-refresh |
| Form usable but submissions refused (503 `not_open`) | — | Preview mode working as designed; flip per `GO-LIVE-STEPS.md` B1 when intended |
| Pay button → blank `app.coaxlure.com` page | The `payment_url` in the register reply | Wrong checkout route — §6 Case 2 |
| Coaxlure captured the money; site still says *pending* | Admin → "Coaxlure said" | Token rotation — §6 Case 3; click "Recheck payment now" |
| "Already registered" for someone who never did | Search the console for that mobile + email | Another person used the same mobile+email pair (identity rule) |
| 429 Too many attempts | — | Rate limit — 8 submissions / 10 min / IP; wait |
| Coupons always "invalid" on live | `SELECT * FROM coupons;` in phpMyAdmin | No codes generated in **that** database yet — admin console → Coupons |
| Admin console refuses login / 403 | §3 console table | Empty hash (disabled) or 15-min IP lockout |
| Confirmation email not received | §4 `confirmation email failed:` | Mailbox missing or mail layer issue — payment is unaffected |
| Cron output empty / `checked=0` | hPanel cron output | Normal while no pending attempts exist |

---

## 6. Real case studies (this exact workflow, end to end)

### Case 1 — "The API looked broken — it wasn't"

**Symptom:** mid-project, a plan assumed `register.php` was throwing 500s.
**Workflow:** probed every endpoint with curl (Step 1–2). Results: `quote.php`
200 with prices, `status.php` 422 `bad_ref` (called without a ref — correct),
`register.php` 422 `validation` (probe payloads were missing required fields).
Log: zero 5xx lines.
**Lesson:** an error code **with a message** is the API *working*. Only 5xx /
`server` replies (with a matching log line) are faults.

### Case 2 — Blank Coaxlure checkout page

**Symptom:** submit → browser lands on a blank `https://app.coaxlure.com/customer/`
with no way to pay (first live ₹10 test, 2026-10-24).
**Reproduce:** registered a ₹10 test row; read the returned `payment_url`.
**Evidence:** the URL used `/customer/coaxlure-payments/<token>` (**plural**) —
rendering that URL headlessly produced an empty app shell making **zero API
calls**; `/customer/coaxlure-payment/<token>` (**singular** — the route the
merchant app and SMS use) rendered the real order.
**Cause:** Coaxlure's `/payment-link` API returns a route their own checkout app
does not serve.
**Fix:** `coax_create_payment()` now passes `merchantDomain` (config
`pay_page_base`) and defensively rewrites plural URLs to the singular route.
**Verify:** new registrations return the singular URL, which renders the real
checkout (order id, amount, expiry). Commit `1869d04`.

### Case 3 — Paid on Coaxlure, pending on the site, forever

**Symptom:** ₹10 genuinely captured (Coaxlure order `PAID`, invoice
`CLI-26104-114458`) — `status.php` still said *pending* hours later.
**Reproduce/Evidence:** called Coaxlure's `payment-status` with each identifier:
the create-time token → `BA_4002 "Order not found"`; the **order id** →
`orderStatus:"PAID", paymentStatus:"success"`.
**Cause:** Coaxlure **rotates the payment token at capture time**, so the token
our DB stored dies exactly when payment happens.
**Fix:** `coax_fetch_status()` looks up by the stable **order id** first (already
stored in `payment_attempts.coax_order_id`), token as fallback; the paid matcher
matches Coaxlure's real live wording (`PAID` / `success`).
**Verify:** the owner's row flipped to **paid** through the ordinary public
`status.php` within one recheck cycle. Commit `1869d04`.

### Case 4 — The validator that "failed" a healthy server

**Symptom:** `validate.php` on Hostinger reported 4 failures in "internal file
protection" (47/51) — on a correctly configured server.
**Workflow:** curled the four URLs directly (Step 1): all answered **403**
(Apache `.htaccess` denial), while the validator asserted **404** (the local
dev-server code path).
**Cause:** an assertion bug in the *test*, not the system — both codes mean
"blocked"; only 200 would be a real failure.
**Fix:** the checks accept 403 or 404. **Verify:** 51/51 on the live re-run.
**Lesson:** when a check fails, first establish what the system actually did.

---

## 7. Safety rules while debugging

- **Test POSTs locally first.** `http://localhost:8800/` (Docker `owli-web` +
  `owli-db`) uses the **dev** Coaxlure API with test credentials — orders there
  never touch the live account, and the local DB/coupons/admin password are
  completely separate from production.
- **Never POST a real registration to the live API for testing** while
  `mode = 'open'`: one valid POST creates a real Coaxlure order and can take
  money. If a live POST is unavoidable (as the ₹10 test was), set a cheap test
  tier first and clean up afterwards — foreign-key-ordered DELETEs, see
  `GO-LIVE-STEPS.md` C1.
- **Share evidence, not secrets.** Log lines carry exception messages only —
  safe to paste. Never paste `owli-config.php` contents, Coaxlure credentials,
  admin passwords, or payment tokens. `scripts/validate.php` prints no values by
  design — its output is safe to paste anywhere.
- **The kill switch is always one edit:** `owli-config.php` → `'mode' =>
  'preview'` — instant, no deploy; the public form locks while you investigate.
- **Do not edit `paid` rows by hand.** They are financial records; website rows
  (`source='web'`) can never be deleted from the console, by design.
- **`robots.txt` / `.htaccess` / `validate.php` are the exposure guards** — if
  you change any of them, re-run the validation suite before pushing.
