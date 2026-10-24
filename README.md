# Owl I Health and Wellness Center — Website

A professional, bilingual (English/Tamil) landing page for a naturopathy clinic in Tirunelveli, Tamil Nadu, India.

---

## 1. Project Overview

**What is this?**
A bilingual clinic website for Owl I Health Care Centre. The clinic homepage and event information pages are static; the Arokya Payanam 2026 registration flow uses a PHP/MySQL backend for server-priced registration and payment verification.

**Who is it for?**
The clinic owner and staff, to establish an online presence and allow patients to learn about services, meet the doctors, and book appointments via WhatsApp or phone.

**Tech stack:**
- **HTML5/CSS3/vanilla JavaScript** — static bilingual pages; no frontend framework or build step
- **PHP 8.2+** — event registration API
- **MySQL/MariaDB** — registration, payment attempts, tiers and coupon data
- **Google Fonts** — Poppins (English) and Noto Sans Tamil (Tamil)

**Key features:**
- Bilingual support (English ↔ Tamil) with one-click toggle
- WhatsApp-based appointment booking
- Responsive design (mobile, tablet, desktop)
- Testimonial carousel with touch/swipe support
- Gallery with lightbox viewer
- Contact form with validation
- SEO optimized (meta tags, Open Graph, JSON-LD structured data)
- Accessible (ARIA labels, keyboard navigation, focus traps, skip link)

The clinic pages need no build step and can be previewed as static files. To test event registration and its API, serve the project with PHP and connect it to MySQL/MariaDB; the current local preview uses Docker at `http://localhost:8800/`.

---

## 2. File Structure

```
owli-healthcare/
├── index.html              # The entire website — all sections in one page
├── events/
│   └── index.html          # Arokya Payanam 2026 event page (/events/)
├── css/
│   ├── styles.css          # All visual styles, colors, layouts, components
│   ├── responsive.css      # Mobile-first media queries for all screen sizes
│   ├── events.css          # Styles for the /events/ page only
│   └── event-banner.css    # Homepage "Arokya Payanam 2026" popup
├── admin/
│   └── index.php           # Password-protected staff console: registrations, statuses, CSV export
├── api/
│   ├── register.php        # Validate/save a registration and create a Coaxlure payment link
│   ├── quote.php           # Return server-calculated current prices
│   ├── status.php          # Verify payment status server-side
│   ├── cron-recheck.php    # CLI job to recheck pending payments
│   ├── _bootstrap.php      # Shared database, pricing and Coaxlure helpers
│   ├── admin-pass.php      # CLI: hash a password for the admin console
│   ├── config.sample.php   # Safe template; real credentials stay outside the repo
│   └── .htaccess           # Restrict direct access to internal PHP files
├── scripts/
│   ├── validate.php        # Pre-deploy check: PHP, config, DB, API, admin, cron, Coaxlure reachability
│   └── check-i18n.js       # EN/TA translation parity + used-key coverage (node)
├── sql/
│   ├── schema.sql          # MySQL tables and initial category prices
│   └── .htaccess
├── js/
│   ├── event-register.js   # Event form, category fields, pricing and payment redirect
│   ├── event-config.js     # Registration on/off state
│   ├── i18n.js             # All English + Tamil translations, language toggle logic
│   ├── main.js             # Nav menu, sticky header, scroll animations, active nav
│   ├── testimonials.js     # Testimonial carousel (auto-rotate, swipe, dots)
│   ├── lightbox.js         # Gallery lightbox (open, navigate, close, keyboard)
│   ├── contact.js          # Form validation, WhatsApp URL builder, rate limiting
│   ├── treatment-details.js# Service card "View Details" panels
│   ├── event-config.js     # One place to open/close event registration
│   └── event-banner.js     # Homepage event popup (show/close, 24h dismissal)
├── images/
│   ├── hero/               # Hero background image (1920×800px)
│   ├── services/           # Service icons (SVG preferred)
│   ├── doctors/            # Doctor photos (400×400px, square)
│   ├── leadership/         # Founder/Co-Founder photos
│   ├── about/               # Clinic interior photo
│   ├── events/              # Event logo + organiser logo strip
│   ├── gallery/            # Clinic gallery photos (800×600px)
│   ├── blog/               # Blog thumbnail images (600×400px)
│   ├── logo.svg            # Clinic logo (SVG preferred)
│   ├── og-image.jpg        # Social sharing image (1200×630px)
│   └── favicon.ico         # Browser tab icon (32×32px or 64×64px)
├── sitemap.xml             # SEO sitemap — homepage + /events/
├── robots.txt              # Allows all search engine crawlers
├── CLAUDE.md               # Complete project specification (for developers)
├── prompt.md               # Build phase instructions (for developers)
├── GO-LIVE-STEPS.md        # Runbook: Rs 10 live payment test + opening registration
└── README.md               # This file — how to use and maintain the site
```

---

## 3. How to View Locally

### Option A: Just open the file
1. Find `index.html` in your file explorer
2. Double-click it — it opens in your default browser
3. That's it! The site works immediately.

### Option B: Use VS Code Live Server (auto-reload on changes)
1. Install [VS Code](https://code.visualstudio.com/)
2. Open this project folder in VS Code
3. Install the "Live Server" extension (by Ritwick Dey)
4. Right-click `index.html` → "Open with Live Server"
5. The site opens in your browser and auto-refreshes when you save changes

---

## 4. How to Edit Text Content

All display text lives in **`js/i18n.js`** inside the `translations` object. The HTML never contains hardcoded display text — instead, it uses a `data-i18n` attribute system.

### How the system works:
1. Each text element in the HTML has: `data-i18n="some_key"`
2. In `i18n.js`, both `en` and `ta` objects have that same key with the translated text
3. When the user clicks the language toggle, JavaScript swaps all the text

### Example: Changing the hero heading

**Step 1:** Find the key in `index.html`:
```html
<h1 data-i18n="hero_heading">Heal Naturally. Live Fully.</h1>
```
The key is `hero_heading`.

**Step 2:** Open `js/i18n.js` and find `hero_heading` in the `en` object:
```javascript
hero_heading: 'Heal Naturally. Live Fully.',
```

**Step 3:** Change the text:
```javascript
hero_heading: 'Your New Heading Here',
```

**Step 4:** Also update the Tamil translation in the `ta` object:
```javascript
hero_heading: 'உங்கள் புதிய தலைப்பு இங்கே',
```

**Step 5:** Save the file. The change appears on the site.

### Adding a completely new translatable text element:
1. Add `data-i18n="your_new_key"` to the HTML element
2. Add `your_new_key: 'English text',` to the `en` object in `i18n.js`
3. Add `your_new_key: 'தமிழ் உரை',` to the `ta` object in `i18n.js`

---

## 5. How to Replace Placeholder Images

All placeholder content is marked with `<!-- [PLACEHOLDER] -->` comments in the HTML. Here is every image that needs to be replaced:

| Item | File Path | Dimensions | Format |
|------|-----------|------------|--------|
| Hero background | `images/hero/hero-bg.jpg` | 1920 × 800 px | JPG |
| Gallery photo 1 | `images/gallery/gallery-1.jpg` | 800 × 600 px | JPG |
| Gallery photo 2 | `images/gallery/gallery-2.jpg` | 800 × 600 px | JPG |
| Gallery photo 3 | `images/gallery/gallery-3.jpg` | 800 × 600 px | JPG |
| Gallery photo 4 | `images/gallery/gallery-4.jpg` | 800 × 600 px | JPG |
| Gallery photo 5 | `images/gallery/gallery-5.jpg` | 800 × 600 px | JPG |
| Gallery photo 6 | `images/gallery/gallery-6.jpg` | 800 × 600 px | JPG |
| Blog post image 1 | `images/blog/blog-1.jpg` | 600 × 400 px | JPG |
| Blog post image 2 | `images/blog/blog-2.jpg` | 600 × 400 px | JPG |
| Blog post image 3 | `images/blog/blog-3.jpg` | 600 × 400 px | JPG |
| Social sharing image | `images/og-image.jpg` | 1200 × 630 px | JPG |
| Favicon | `images/favicon.ico` | 32 × 32 px | ICO |
| Clinic logo | `images/logo.svg` | Any | SVG preferred |

### How to replace a placeholder:
1. Prepare your image at the recommended dimensions
2. Place the image file in the correct `images/` subfolder
3. In `index.html`, find the placeholder `<div>` and replace it with an `<img>` tag:
   ```html
   <!-- Before (placeholder): -->
   <div class="image-placeholder">...</div>

   <!-- After (real image): -->
   <img src="images/doctors/doctor-1.jpg" alt="Dr. Priya - Chief Naturopathy Physician" loading="lazy">
   ```
4. Always include a descriptive `alt` attribute for accessibility

---

## 6. How to Update Doctor Information

Doctor data lives in two places. There are currently **2 real doctor cards** (Dr. Jospin Sheela.A and Dr. Jebakumar. A) — no placeholders remain here.

### In `index.html`:
Search for `Doctor 1:` or `Doctor 2:` to find each doctor card. Each card has:
- Photo (`<img>` pointing at `images/doctors/doctor1.png` / `doctor2.jpeg`)
- Name, role, qualification, experience, specialization, and areas-of-expertise tags

### In `js/i18n.js`:
Search for `doctor_1_name` / `doctor_2_name` in both the `en` and `ta` objects.

### Step-by-step: replacing Doctor 1

1. **Photo:** Place a 400×400px square photo at `images/doctors/doctor-1.jpg`

2. **HTML** (`index.html`): Find the Doctor 1 card and replace the placeholder div with:
   ```html
   <img src="images/doctors/doctor-1.jpg" alt="Dr. Priya Natarajan - Chief Naturopathy Physician" loading="lazy">
   ```

3. **English text** (`js/i18n.js` → `en` object):
   ```javascript
   doctor_1_name: 'Dr. Priya Natarajan',
   doctor_1_role: 'Chief Naturopathy Physician',
   doctor_1_qualification: 'BNYS',
   doctor_1_experience: '8+ years experience',
   doctor_1_specialization: 'Chronic disease management, Detox programs',
   ```

4. **Tamil text** (`js/i18n.js` → `ta` object):
   ```javascript
   doctor_1_name: 'Dr. பிரியா நடராஜன்',
   doctor_1_role: 'தலைமை இயற்கை மருத்துவர்',
   ```

5. Save both files. The doctor info updates on the site.

### Updating the Leadership profiles (Founder & CEO, Co-Founder & MD)

The "Our Leadership" sub-section sits at the bottom of the About section (`index.html`, inside `<div class="leadership">`). It uses the same `leader-card` layout for both profiles:
- **Photos:** `images/leadership/ceo.png` and `images/leadership/md.jpeg`
- **Text:** `leader_1_name`/`leader_1_role`/`leader_1_bio` and `leader_2_name`/`leader_2_role`/`leader_2_bio` in `js/i18n.js` (both `en` and `ta` objects)

To replace a photo, drop the new image into `images/leadership/` and update the `src` on the matching `<img class="leader-img">`.

---

## 7. How to Add/Edit Services

### Where services live:

1. **Service cards in HTML** (`index.html`): Search for `services-grid` — all 11 service cards are inside this grid.

2. **Service translations** (`js/i18n.js`): Search for `service_1_title` through `service_11_title` (and their `_desc` variants) in both `en` and `ta` objects.

3. **Contact form dropdown** (`index.html`): Search for `contact-service` — the `<select>` element has all 11 services as `<option>` elements.

### To edit an existing service:
1. Update the title and description in `js/i18n.js` (both `en` and `ta`)
2. Update the `value` attribute on the matching `<option>` in the contact form if the service name changed

### To add a new service (e.g., service 12):
1. Copy an existing service card `<div>` in the HTML and change the `data-i18n` keys to `service_12_title` and `service_12_desc`
2. Add `service_12_title` and `service_12_desc` to both `en` and `ta` objects in `i18n.js`
3. Add a new `<option>` to the contact form dropdown
4. Choose an appropriate SVG icon for the new service

---

## 8. How to Deploy to Hostinger

### Current hosting
The owner reports that `owlihealthcare.com` is live on Hostinger Premium Web Hosting, with PHP 8.2–8.5 available, MySQL database provisioning enabled, SSL active, and GitHub deployment targeting `public_html/`. The site is no longer at the purchase/setup stage. For a new environment, configure the domain, hosting, PHP, MySQL and SSL through hPanel before deploying.

### Deployment
The existing site is deployed through the configured GitHub-to-Hostinger flow into `public_html/`. For subsequent updates, push the reviewed changes through the project's normal Git deployment workflow and verify the live site afterward. Do not commit server secrets. Hostinger hPanel also provides File Manager/FTP for server-only tasks such as placing `owli-config.php` outside `public_html`, configuring cron, or importing the database schema.

The owner reports SSL is active and PHP 8.2–8.5 and MySQL are available on the Premium Web Hosting plan. For a fresh domain or hosting setup, configure DNS, SSL, PHP and MySQL in hPanel before deploying.

---

## 9. How to Update the Site After Changes

The owner reports that pushing changes to the configured GitHub branch automatically deploys the project into Hostinger `public_html/`.

1. Review and test changes locally. Use the Docker preview (`http://localhost:8800/`) for the PHP registration flow; opening HTML directly with `file://` does not run PHP endpoints.
2. Keep `owli-config.php`, database credentials, Coaxlure credentials/tokens, and real attendee data outside Git.
3. Commit and push only reviewed project files using the repository's normal workflow.
4. Check the GitHub/Hostinger deployment result and verify the live site. Purge Hostinger/browser cache if static CSS or JavaScript appears stale.

Use Hostinger File Manager or FTP only for server-side setup that must not be committed, such as placing `owli-config.php` outside `public_html`, importing `sql/schema.sql`, or configuring the payment recheck cron job.

### A note on caching (CSS/JS not updating after a change)
If you edit a CSS or JS file and the live site still shows the old version after uploading, it's almost always a **cache** — either the visitor's browser or Hostinger's CDN is still serving the old file. Two things help:
1. **Bump the `?v=` version number** on that file's `<link>`/`<script>` tag in `index.html` (e.g. `styles.css?v=2` → `?v=3`). This forces every browser to treat it as a new file and fetch it fresh. `css/event-banner.css`, `js/event-banner.js`, and `js/event-config.js` already use this pattern — follow the same style for any file you add it to.
2. **Purge the Hostinger CDN cache** in hPanel (Websites → your domain → Cache/CDN section → Purge Cache) after uploading, so the edge servers fetch the new file instead of serving a stale cached copy for up to 7 days.

---

## 10. How to Manage the Arokya Payanam 2026 Event

The event lives in three places, all controlled from one switch:

1. **`js/event-config.js`** — the browser-side switch, `registrationMode: 'preview' | 'open' | 'closed'`. **`open` is the current, committed state** (the go-live flip — see `GO-LIVE-STEPS.md`); the server switch in `owli-config.php` still controls whether registrations are actually accepted. The **server has its own matching switch** — `registration => ['mode' => ...]` in `owli-config.php` — and `api/register.php` refuses with HTTP 503 unless the server switch itself says `'open'`. The form only becomes fully active when BOTH sides say `'open'`, so flipping the JS file alone can never open payments. `registrationUrl` is an optional legacy external-link override and should normally remain empty for the built-in PHP form.
2. **`events/index.html`** — the full event page at `/events/`: agenda, competitions, organisers, contact details.
3. **Homepage popup** (`index.html` → `<div id="event-banner">`, styled by `css/event-banner.css`, behavior in `js/event-banner.js`) — a centered announcement that appears ~800ms after the homepage loads, showing the event logo and a link to `/events/`. It closes via the × button, clicking outside it, or the Escape key, and stays hidden for 24 hours after a visitor closes it. It also stops appearing entirely after the date in `OWLI_EVENT.endDate` (`js/event-config.js`) — update that if the event date ever changes.

### Registration form + online payment (Coaxlure)

The form on `/events/` (`#register-form`) takes the attendee's details, works out the price **on the server**, and sends them to Coaxlure's hosted payment page. Registrations live in a MySQL database.

**Prices (no GST):** Students ₹299 · BNI members ₹399 · Doctors ₹399 · General public ₹449. The dropdown shows these as a price list, highlights the selected category, and shows "Amount to pay". The browser never sends an amount; `api/register.php` looks it up in the `tiers` table.

| File | Role |
|---|---|
| `js/event-register.js` | Form behaviour: category fields, price list, validation, payment redirect, status check on return. Locks the Pay button in `preview`, unlocks only in `open` (client + server must agree) |
| `api/quote.php` | Price display (`?category=all` returns every price; `?category=student&coupon=X` previews a coupon). Reports the server's `mode` — `open` / `preview` / `closed` |
| `api/register.php` | Validates, re-prices, saves a *pending* registration, creates the Coaxlure order + pay link. **Refuses (503 `not_open`) unless the server switch says `'open'`** |
| `api/status.php` | Checks payment with Coaxlure from the server and marks the registration *paid* |
| `api/cron-recheck.php` | Re-checks pending payments (for people who pay and close the browser) |
| `api/_bootstrap.php` | Shared code: config, DB, pricing/coupons, Coaxlure client |
| `api/config.sample.php` | Template for the secrets file (never commit the real one) |
| `sql/schema.sql` | Tables `tiers`, `coupons`, `registrations`, `payment_attempts`, `settings` + the starting prices |
| `admin/index.php` | Staff console (see "Admin console" above): list, statuses, recheck button, filtered CSV export |
| `api/admin-pass.php` | CLI helper that prints a `password_hash()` for the console |
| `scripts/validate.php` | Pre-deploy validation of every component (PHP/DB/API/admin/cron/protection) |

**Changing prices or adding offers** (phpMyAdmin → SQL; no code change). Examples are at the bottom of `sql/schema.sql`: change a tier price, add a date-limited early-bird tier, or add coupon codes (`percent`, `flat`, `fixed_price`; per category; max uses). Amounts are in paise (₹299 = `29900`).

**Admin console (`/admin/`) — view registrations and export them**

`admin/index.php` is a password-protected staff page (not linked from anywhere public, excluded in `robots.txt`) that shows every registration with its status, a summary (total / paid / pending / collected / awaiting), per-registration payment history, an instant **"Recheck payment now"** button for pending rows, filters (status, category, search) and a one-click **CSV export** that respects the current filters (UTF-8 BOM, so Excel opens Tamil text correctly).

Enable it on the server (no Git involved):

```bash
# 1. hash a strong password (hPanel -> Terminal, in public_html)
php api/admin-pass.php 'your-strong-password'
# 2. paste the printed $2y$... line into owli-config.php:
'admin' => [ 'password_hash' => '$2y$10$...' ],
```

The password itself is never stored anywhere — only the hash, inside the git-ignored config. The console locks an IP out for 15 minutes after 5 failed attempts, uses CSRF-protected session login, and stays **disabled** (403) while no hash is set. It is English-only on purpose: it is an internal staff tool, not part of the bilingual public site.

**Validation before going live — `scripts/validate.php`**

One command checks every component, on this machine or on Hostinger, and never prints a credential:

```bash
docker exec owli-web php /srv/public_html/scripts/validate.php   # local Docker
php public_html/scripts/validate.php                             # Hostinger (hPanel Terminal)
```

It verifies: PHP ≥ 8.2 + required extensions, config presence/shape, DB connection, all 5 tables, the 4 price tiers, live endpoint behaviour (`quote.php` prices; `register.php` correctly refusing with 503 while in preview/closed — the probe is skipped when the server is open so it never creates a real order on a live system; `status.php` rejecting unknown refs), admin console reachability + that it leaks no data before login, cron job file + its web-block, internal-file protection, Coaxlure host reachability (empty request, no credentials), and the static pages. Exit code 1 on any failure, so it can gate deploys. **Run it on Hostinger right after the first deploy and before opening registration.** For translation parity: `node scripts/check-i18n.js` (exits 1 on any missing key).

**Go-live steps**

> For the current deployment there is a concrete operator runbook with the exact SQL, switch flips and verification checklist: **`GO-LIVE-STEPS.md`** (steps 5–6 below in expanded, current-state form).

1. hPanel → Databases: create the MySQL database + user. phpMyAdmin → Import `sql/schema.sql`.
2. Copy `api/config.sample.php` to `owli-config.php` in the folder **above** `public_html` and fill in the DB details and the Coaxlure Client ID + Secret (Coaxlure → Business API → Integration). Never put these in Git. Set `registration.mode` to `'preview'` first — the event page then shows ticket prices and a working form demo, while the server still refuses submissions.
   - Also set the admin password hash here (`php api/admin-pass.php 'password'` → paste into the `'admin'` block) so `/admin/` is usable.
   - **Testing:** Coaxlure support says to test on the dev server. Add `'api_base' => 'https://dev.coaxlure.com/merchant/business-api'` under `coaxlure` and use the test credentials. Dev and live tokens are separate, and the token cache is keyed by `api_base`, so switching never reuses the wrong one.
   - A hand-generated token can be set as `'static_token'` to skip `/auth`; however, the current registration readiness check still requires both `client_id` and `secret`, so a static token alone does **not** open registration. The test credentials include a Secret and `/auth` works against the dev server (verified locally on 2026-10-23), so `static_token` is normally left empty.
   - If the dev server returns a payment link on a host other than `app.coaxlure.com`, the redirect is refused and the host is written to the PHP error log. Add that host to `allowed_pay_host` (now a list) for testing only.
   - **Before launch:** remove the `api_base` override (or set it back to the live URL), use live credentials, and remove `static_token`.
3. hPanel → Advanced → Cron Jobs: run `php /home/<user>/domains/owlihealthcare.com/public_html/api/cron-recheck.php` every 5 minutes.
4. Run the validation script from hPanel → Terminal: `php public_html/scripts/validate.php` — everything must pass (it exits 1 if anything fails). Then sign in at `https://owlihealthcare.com/admin/` and confirm the console loads after login and that **Export CSV** downloads a valid file (in preview there may be zero registrations — that is fine).
5. Make a **real ₹10 test payment** first if Coaxlure has no sandbox (temporarily set a tier to `1000` paise, then restore). Confirm the registration turns *paid*. The `paymentStatus` wording is not documented by Coaxlure, so check `payment_attempts.raw_status` in the database (the admin console shows the same "Coaxlure said" value per attempt). If paid is not detected, adjust `coax_fetch_status()` in `api/_bootstrap.php`.
6. After a successful dev-credential test, confirm the production database and live Coaxlure credentials are configured securely, then flip **both switches** to open: `registration.mode => 'open'` in `owli-config.php` (no deploy needed — the file is read on every request) and `registrationMode: 'open'` in `js/event-config.js` (bump its `?v=` cache version). Do not publish test credentials or tokens.

Until step 6, keep `registration.mode` at `'preview'`: the page shows prices and the form as a demo, and the server refuses every submission with 503 `not_open`. Registration is also refused by the server when the Coaxlure credentials are missing, regardless of any client-side flag.

**Local Docker setup and preview**

The current development preview runs in Docker; PHP does not need to be installed on the host computer. The website is available at `http://localhost:8800/`, and the event page is `http://localhost:8800/events/` while the `owli-web` container is running.

The local PHP app connects to MariaDB over Docker's private network. For the current test configuration, use:

| Setting | Local Docker value |
|---|---|
| Database host | `owli-db` |
| Database name | `owli` |
| Database user | `owliuser` (least-privilege: `SELECT`/`INSERT`/`UPDATE` on `owli` only — not root) |
| Database password | In the local-only config file `/home/<you>/work/owli-config.php`; never in Git or docs |
| Database port | Docker's internal default, `3306`; it is not published to the host |

**Where the local secrets live:** `/home/<you>/work/owli-config.php` — outside both repositories, one level above the `owli-landing` folder (the same relative position `_bootstrap.php` probes for on Hostinger). It is git-ignored, and the `owli-web` container mounts it **read-only** as `/srv/owli-config.php`, so editing it reconfigures the local site on the next request. The Coaxlure credential CSVs live in `work/coaxlure/` (also outside Git, permissions `600`). These values are **only for this local Docker setup**: `owli-db` is not reachable from a PHP process on the host, and none of them may ever be copied to Hostinger.

**What the local config currently points at:** the **real Coaxlure DEV server** (`https://dev.coaxlure.com/merchant/business-api`) with the **TEST** Business API credentials. To go back to the offline mock (no network calls), change `api_base` in the config to `http://owli-mock:8081/merchant/business-api`. Never use live credentials on this machine.

**Verified against the real dev API on 2026-10-23 (through the local site):** `/auth` issues a 30-day token, `create-order` returns the correct `totalAmount` (the amount cross-check passes), `payment-link` returns an `https://app.coaxlure.com/...` URL (accepted by both the server host allowlist and the client-side check in `js/event-register.js`), and `payment-status` reports `PENDING` for an unpaid order. The full registration flow was re-tested end-to-end: prices from the DB, validation, honeypot rejection, pending-row reuse with re-pricing (₹299 → ₹449), a new payment attempt per retry, `status.php` verifying with Coaxlure, `cron-recheck.php` via CLI, and direct access to `_bootstrap.php`/`cron-recheck.php` still blocked. **Still not verified:** a real *paid* order — make a ₹10 test payment to confirm Coaxlure's exact `paymentStatus` wording before opening registration.

The local config (`/home/<you>/work/owli-config.php`) currently sets `registration.mode => 'preview'`, matching `js/event-config.js`. The site shows prices and the form, but submissions are refused (503) — the correct state while the payment integration is being verified. To exercise a real submission locally, set `registration.mode` to `'open'` **in that local file only** (never commit an `'open'` JS flag), and put it back to `'preview'` afterwards. Never use real attendee data in tests.

### To remove the event entirely once it's over
1. Delete the `<div id="event-banner">` block from `index.html` (search for `EVENT BANNER`) and its two `<script>` tags (`event-config.js`, `event-banner.js`) and the `<link>` to `css/event-banner.css`.
2. Delete the `events/` folder, `css/events.css`, `images/events/`, and the one footer link to it in `index.html`.
3. Remove the `/events/` entry from `sitemap.xml`.
4. The `ev_*` keys in `js/i18n.js` can stay (unused keys are harmless) or be removed for tidiness.

---

## 11. Complete Placeholder Checklist

**Replace ALL of these before going live:**

| # | Item | File Location | What to Replace | Size/Format |
|---|------|---------------|-----------------|-------------|
| 1 | Hero background image | `images/hero/` | Clinic exterior or nature photo | 1920×800px, JPG |
| 2 | Gallery photos (6) | `images/gallery/` | Real clinic/treatment photos | 800×600px, JPG |
| 3 | Blog post images (3) | `images/blog/` | Relevant health topic images | 600×400px, JPG |
| 4 | Social sharing image | `images/og-image.jpg` | Clinic branding image | 1200×630px, JPG |
| 5 | Patient testimonials (5) | `i18n.js` | Real patient quotes | Text only |
| 6 | Social media URLs (3) | `index.html` footer | Instagram, Facebook, YouTube URLs | Full URLs |

**Already real (no longer placeholder):** hero video/poster, favicon, clinic logo, clinic interior photo, both doctor profiles, both leadership profiles (Founder & CEO, Co-Founder & MD), Google Maps embed.

---

## 12. Browser Support

This website works in all modern browsers:

| Browser | Supported |
|---------|-----------|
| Google Chrome | Yes (latest 2 versions) |
| Mozilla Firefox | Yes (latest 2 versions) |
| Apple Safari | Yes (latest 2 versions) |
| Microsoft Edge | Yes (latest 2 versions) |
| Samsung Internet | Yes |
| Internet Explorer 11 | **No** — not supported |

The site uses modern CSS features (custom properties, grid, flexbox) and JavaScript APIs (IntersectionObserver, localStorage) that are supported in all modern browsers but not in IE11.

---

## 13. Troubleshooting

### "Site not loading after upload"
- Make sure `index.html` is directly inside `public_html/` (not in a subfolder)
- Check that you uploaded all files, not just `index.html`
- Wait up to 48 hours for DNS propagation if using a new domain

### "Styles not appearing / site looks broken"
- Check that the `css/` folder is uploaded to `public_html/`
- Open browser DevTools (F12) → Console tab → look for 404 errors on CSS files
- Verify the file paths in the `<link>` tags in `index.html` match your folder structure
- **If you just edited a CSS/JS file and the change doesn't show up:** it's very likely a cache, not a bug. Hard-refresh the page (Ctrl+Shift+R / Cmd+Shift+R), and if that doesn't help, see "A note on caching" under Section 9 — bump the file's `?v=` number and purge the Hostinger CDN cache. A real symptom of this: an element (like the event popup) rendering as plain unstyled content in the wrong place on the page, because the browser's cached CSS has no rules for a class name that only exists in the newer HTML.

### "Language toggle not working"
- Open browser DevTools → Console tab → check for JavaScript errors
- Make sure `js/i18n.js` is loaded (check the `<script>` tag at the bottom of `index.html`)
- Verify that every `data-i18n` key in the HTML has a matching entry in both `en` and `ta` objects

### "WhatsApp not opening when form is submitted"
- Check that the phone number in `js/contact.js` is `916379610554` (no + sign, include country code)
- Make sure WhatsApp is installed on the device (or WhatsApp Web is accessible)
- Try the link directly: `https://wa.me/916379610554`

### "Images not showing"
- Check that image files are in the correct `images/` subfolder
- Verify filenames match exactly (case-sensitive on most servers)
- Make sure images are in JPG/PNG/SVG format (not HEIC, WebP, or other formats)

### "Carousel not moving"
- Check browser console for JavaScript errors
- Make sure `js/testimonials.js` is loaded
- Verify the testimonial HTML structure has classes: `.testimonial-carousel`, `.testimonial-track`, `.testimonial-slide`
