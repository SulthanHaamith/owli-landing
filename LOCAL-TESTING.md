# LOCAL-TESTING.md — How to test the Owli stack locally

> **What this file is:** the complete, reproducible recipe for the local Docker
> test stack, the three automated checkers, and the manual verification
> techniques used throughout this project. Everything here runs on **your
> machine only** — none of it talks to Hostinger or the live Coaxlure server
> unless you deliberately point the local config at the dev API.
>
> Companion docs: `PROJECT-STATUS.md` (where things stand),
> `API-TROUBLESHOOTING.md` (what to do when something breaks on the live
> server), `GO-LIVE-STEPS.md` (the ₹10 payment-test runbook),
> `README.md` §10 (full backend reference).
>
> **Last updated: 2026-10-25.** Web-blocked by the root `.htaccess` (`*.md`
> rule) — this is a team document, not public content.

---

## 1. The test pyramid — which check runs when

| # | Check | Command | What it catches | When to run |
|---|---|---|---|---|
| 1 | PHP lint | `docker exec owli-web php -l <file>` | Syntax errors | After every PHP edit |
| 2 | JS syntax | `node --check js/<file>.js` | Syntax errors | After every JS edit |
| 3 | i18n parity | `node scripts/check-i18n.js` | EN/TA key mismatches, missing `data-i18n` keys, missing runtime keys | After touching `i18n.js`, either HTML page, `event-config.js`, `event-register.js` |
| 4 | Pay-flow regression | `node scripts/check-pay-flow.js` | Payment-tab navigation violations (double-navigation, stale tabs, hijacked form page) | After touching `js/event-register.js` |
| 5 | Full suite | `docker exec owli-web php /srv/public_html/scripts/validate.php` | PHP runtime, config, DB, schema, endpoints, admin, protections, docs 403 | Before every push |
| 6 | Browser test | `http://localhost:8800/events/` | The real UX (form, pricing, new-tab payment, polling, ticket download) | After pushing UI changes |

**Rule of thumb:** 1–4 after the relevant edit, 5 before every push, 6 for
anything the user sees. All of 1–5 exit non-zero on failure, so they can gate
a commit.

---

## 2. The local Docker stack

### 2.1 What it is

Two containers on a private network (`owli-net`), simulating the Hostinger
production stack (Apache + PHP + MariaDB):

| Container | Image | Role | Exposure |
|---|---|---|---|
| `owli-web` | `php:8.2-apache` | Serves the repo (bind-mounted at `/srv/public_html`, docroot configured to it) + `.htaccess` support | `http://localhost:8800` |
| `owli-db` | `mariadb:11` | The `owli` database; port 3306 internal only — never published to the host | none (inside `owli-net`) |

The repo directory is **bind-mounted live** into the container — edits to
HTML/JS/CSS are visible on refresh, no rebuild needed. PHP files too.

Optionally a third container, `owli-mock` — a fake Coaxlure API for fully
offline testing (see §5). It is currently not running.

### 2.2 One-time setup

```bash
# 1. Create the private network
docker network create owli-net

# 2. Start the database (password lives in the local config, not here)
docker run -d --name owli-db --network owli-net \
  -e MARIADB_ROOT_PASSWORD=<your-local-root-password> \
  -e MARIADB_DATABASE=owli \
  mariadb:11

# 3. Import the schema (copy schema.sql into the container first)
docker cp sql/schema.sql owli-db:/tmp/schema.sql
docker exec owli-db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" owli < /tmp/schema.sql'

# 4. Create the least-privilege DB user (match the values in owli-config.php)
docker exec owli-db sh -c 'mariadb -u root -p"$MARIADB_ROOT_PASSWORD" -e "
  CREATE USER IF NOT EXISTS \"owliuser\"@\"%\" IDENTIFIED BY \"<local-db-password>\";
  GRANT SELECT, INSERT, UPDATE ON owli.* TO \"owliuser\"@\"%\";
  FLUSH PRIVILEGES;"'

# 5. Start the web container — see the next section for the config mount
```

### 2.3 The local secrets file (the one manual step)

The API reads its config from a fixed path: **two levels above the repo**, i.e.
`/home/<you>/work/owli-config.php` (on Hostinger the same relative position is
one level above `public_html/`). Start from `api/config.sample.php` and fill in:

| Block | Local values |
|---|---|
| `db` | host `owli-db`, name `owli`, user `owliuser`, pass `<local-db-password>` |
| `coaxlure` | **dev server** `https://dev.coaxlure.com/merchant/business-api` + **test** credentials (from `~/work/coaxlure/test-…-credentials.csv`, chmod 600), or the mock (§5) |
| `registration` | `'mode' => 'preview'` (flip to `'open'` locally only, to exercise the form) |
| `admin` | a `password_hash()` from `docker exec owli-web php /srv/public_html/api/admin-pass.php '<password>'` |

**Never commit this file and never copy it to Hostinger.** The container mounts
it **read-only**, so editing it reconfigures the local site on the next
request — no restart.

### 2.4 Creating / recreating the web container

`owli-web` mounts the repo and the config. To create it (or recreate it after
a config-file *replacement* — see the inode note below):

```bash
docker run -d --name owli-web --network owli-net \
  -p 8800:80 \
  -v /home/<you>/work/owli-landing:/srv/public_html \
  -v /home/<you>/work/owli-config.php:/srv/owli-config.php:ro \
  php:8.2-apache
```

Then configure Apache **inside the container** once (this is what makes the
docroot and `.htaccess` behave like Hostinger):

```bash
# Point the docroot at the repo
docker exec owli-web sed -i 's|DocumentRoot /var/www/html|DocumentRoot /srv/public_html|' \
  /etc/apache2/sites-enabled/000-default.conf
# Allow .htaccess overrides in the docroot (Hostinger allows them; without this
# the root/api/sql/scripts .htaccess rules would be ignored locally)
docker exec owli-web sed -i '/<Directory \/srv\/public_html>/,/AllowOverride/{s/AllowOverride None/AllowOverride All/}' \
  /etc/apache2/apache2.conf
docker exec owli-web apachectl restart
```

(The stock `php:8.2-apache` image defaults to `/var/www/html` with
`AllowOverride None`; the sed lines above match the running container's
actual files, where the docroot block lives in `apache2.conf`.)

**Note on editing the mounted config:** a **bind mount pins the file's inode**.
Editing the config *in place* (File editor, `nano`) updates the container
instantly. But replacing the file with `sed -i` / `mv` / "save as" creates a
**new inode** and the container keeps serving the old content until it is
recreated. Symptom: you changed `registration.mode` and nothing changed. Fix:
recreate `owli-web` with the exact `docker run` command above.

### 2.5 Teardown / reset

```bash
docker rm -f owli-web                       # rebuild web (keeps DB)
docker rm -f owli-db                        # destroys all local data
docker network rm owli-net                  # only when both containers are gone
```

To reset the database only: `docker rm -f owli-db`, re-run the setup steps
2–4, recreate `owli-web`.

---

## 3. The automated checkers

### 3.1 `scripts/validate.php` — full-stack suite (57 checks)

```bash
docker exec owli-web php /srv/public_html/scripts/validate.php   # local Docker
php public_html/scripts/validate.php                             # Hostinger (hPanel Terminal)
```

Covers, in order: PHP ≥ 8.2 + the curl/pdo_mysql/mbstring/session extensions,
config file presence and shape (values never printed), DB connection + all 5
tables + key columns, the 4 price tiers, live endpoint behaviour
(`quote.php` prices; `register.php` refusing with 503 while not open — the
register probe is **skipped when the server is open** so it can never create a
real order), `status.php` rejecting unknown refs, admin console reachability
+ zero data pre-login, cron file + web-block, internal-file protection
(403 *or* 404 both count — Apache's `.htaccess` fires first on Hostinger, the
files' own PHP self-guards answer 404 on a server without `.htaccess` support),
all project docs web-blocked, Coaxlure host reachability with an **empty**
request (no credentials sent or printed), and the static pages.

Exit code 1 on any failure → safe to gate deploys. Output is credential-free
and safe to paste anywhere.

### 3.2 `scripts/check-i18n.js` — translation parity

```bash
node scripts/check-i18n.js        # exit 1 on any missing/mismatched key
```

Asserts: EN and TA tables carry **exactly the same keys**; every
`data-i18n`/`data-i18n-placeholder`/… key used by `index.html` **and**
`events/index.html` exists in both tables; and every runtime-built key in
`event-config.js`/`event-register.js` (e.g. `'evf_cat_' + category`) is
expanded across the four categories and checked too.

### 3.3 `scripts/check-pay-flow.js` — payment navigation contract

```bash
node scripts/check-pay-flow.js   # exit 1 on any contract violation
```

Loads the **real** `js/event-register.js` module inside a Node VM with a fake
DOM (whose `window.open` faithfully reproduces the HTML spec rule that caused
the 2026-10-24 double-navigation bug: `window.open(url,'_blank','noopener')`
returns `null` even on success) and drives six scenarios:

- **A** normal payment → exactly **one** tab, pre-opened as `about:blank`, then
  navigated to Coaxlure with `opener=null`; the form page never navigates; the
  tab stays open; polling is armed
- **B** popup blocked → same-tab redirect (the one documented fallback)
- **C** free coupon → tab closed, zero navigation, button re-enabled
- **D** server validation error → tab closed, button re-enabled
- **E** network failure → tab closed, button re-enabled
- **F** "Reopen the payment page" → `preventDefault` always runs, second tab to
  the same URL, form page stays put

**Regression-proof:** run against the pre-fix code it fails 8 assertions;
against the fixed code all six scenarios pass. Keep it that way.

---

## 4. Manual verification techniques (the project's working method)

These are the techniques behind every "verified live" claim in this repo.

### 4.1 Probe the API directly (outside the browser)

```bash
# Prices + server mode (GET, safe anywhere)
curl -s http://localhost:8800/api/quote.php?category=all

# Payment status by booking reference (GET, safe — no PII in the reply)
curl -s 'http://localhost:8800/api/status.php?ref=AP26-XXXXXX'

# Coupon preview (GET, safe)
curl -s 'http://localhost:8800/api/quote.php?category=general&coupon=CODE'
```

> ⚠️ **Never POST to `api/register.php` against a server whose mode is `open`
> unless you intend to create a real registration and a real Coaxlure order.**
> That is true locally (dev API) and doubly true on Hostinger (live API). The
> validation suite deliberately skips that probe when the mode is `open`.

When a browser shows an error, the browser's job is to *render* — yours is to
*read the raw reply*. DevTools → Network tab → the failed request → Response
gives you the same thing as the curl commands above.

### 4.2 Watch the server log

```bash
docker logs owli-web --tail 50          # Apache + PHP output
docker exec owli-web tail -50 /var/log/apache2/error.log
```

On Hostinger: hPanel → Logs, or `php -i | grep error_log` in the Terminal for
the exact error-log path. Every API file writes `owli `-prefixed lines — the
full prefix→meaning table is in `API-TROUBLESHOOTING.md` §4.

### 4.3 Read the DB evidence

```bash
docker exec owli-db mariadb -u owliuser -p'<local-db-pass>' owli \
  -e "SELECT reference,status,category,source FROM registrations;"
```

Or phpMyAdmin on Hostinger. The single most useful column:
`payment_attempts.raw_status` — the verbatim Coaxlure reply ("Coaxlure said"
in the admin console). It answered every payment question this project hit.

### 4.4 Verify the ticket PDF

```bash
curl -s -o /tmp/ticket.pdf 'http://localhost:8800/api/ticket.php?ref=AP26-XXXXXX'
pdftotext /tmp/ticket.pdf -            # every string, incl. Tamil names
pdftoppm -r 300 -png /tmp/ticket.pdf /tmp/ticket   # render at 300 dpi
```

QR verification: render at 300 dpi, crop the QR region, decode with any QR
reader (`zbarimg`, a phone camera, an online decoder). The QR encodes
`https://owlihealthcare.com/events/?ref=<REF>` — scanning it must open the
events page showing that reference as **PAID**.

### 4.5 Headless browser (used once, for the Coaxlure blank-page diagnosis)

When a page renders blank, a raw `curl` of it proves nothing — SPAs load
content via JS. Use a headless browser to see what the page *does*:

```bash
python3 -c "
from playwright.sync_api import sync_playwright
with sync_playwright() as p:
    b = p.chromium.launch()
    pg = b.new_page()
    reqs = []
    pg.on('request', lambda r: reqs.append(r.url))
    pg.goto('https://app.coaxlure.com/customer/coaxlure-payment/<token>')
    pg.wait_for_timeout(4000)
    print('API calls made:', len(reqs))
    print(pg.evaluate('document.body.innerText'))
    b.close()
"
```

That is how the plural-vs-singular route bug was found: the dead route made
**zero** API calls and rendered an empty shell.

---

## 5. Offline mock Coaxlure (optional third container)

For API tests without touching any Coaxlure server, a tiny PHP fake of the
Business API exists in `/tmp/owli-test/mock/router.php`. To use it:

```bash
# 1. Build + run the mock container
docker run -d --name owli-mock --network owli-net -p 8081:8081 \
  -v /tmp/owli-test/mock/router.php:/srv/router.php \
  $(docker build -q -f /tmp/owli-test/Dockerfile /tmp/owli-test)
```

(The router must actually listen on 8081 — e.g. `php -S 0.0.0.0:8081 router.php`
— or use `php:8.2-cli` with that as the container command; adapt the run to
however you start it.)

2. Point the local config's `api_base` at
   `http://owli-mock:8081/merchant/business-api`.

3. The mock answers `/auth` with a fixed token, `/order/create-order` with a
   summed total, `/payment-link` with an `app.coaxlure.com` URL, and
   `/payment-status` with PAID only for tokens listed in `/tmp/paid.txt`
   inside the container. To simulate a successful payment:

```bash
docker exec owli-mock sh -c 'echo <order-token> >> /tmp/paid.txt'
```

**Why the mock matters less now:** the paid-detection code is verified against
the **real** dev API (the ₹10 test on 2026-10-24 proved the live wording:
`orderStatus:"PAID"`, `paymentStatus:"success"`). The mock's fixed wording
(`paymentStatus:"PAID"`) only matters for ancient, already-fixed bugs. Prefer
the dev API for anything payment-related; keep the mock for air travel /
offline days. **The mock is not a production fixture** — `router.php` lives in
`/tmp` (wiped on reboot). If it earns a permanent place in the workflow,
move it into the repo first (`api-mock/` or similar), then update this file.

---

## 6. Recurring quick-start (the whole stack in four commands)

```bash
docker network create owli-net 2>/dev/null; docker start owli-db owli-web
docker exec owli-web php /srv/public_html/scripts/validate.php
curl -s http://localhost:8800/api/quote.php?category=all
open http://localhost:8800/events/   # or paste into a browser
```

**If the containers are gone** (machine reboot, `docker rm`), follow §2.2 →
§2.4 to recreate them; the repo itself is bind-mounted, so no code is ever
stored inside a container.
