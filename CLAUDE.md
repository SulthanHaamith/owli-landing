# CLAUDE.md — Owl I Health Care Centre

> **This file is the single source of truth for building the clinic's landing page.**
> Claude Code should read this before every task. All decisions, content, and structure live here.

---

## 1. PROJECT SUMMARY

**What:** A bilingual (English/Tamil) clinic website with a static homepage and a PHP/MySQL-backed event registration flow for Arokya Payanam 2026.
**Goal:** Maintain the clinic website and provide secure event registration with server-side pricing and Coaxlure payment verification.
**Approach:** The clinic pages are static HTML/CSS/vanilla JavaScript. Event registration uses PHP endpoints and MySQL on Hostinger; local development uses Docker. Payment credentials must remain outside the repository and public web root.

---

## 2. TECH STACK

| Layer       | Choice                | Notes                                          |
|-------------|-----------------------|------------------------------------------------|
| Markup      | Plain HTML5           | Semantic elements, no templating engine         |
| Styling     | Hand-written CSS      | Custom properties for theming, separate responsive file |
| JavaScript  | Vanilla JS (ES6+)     | No libraries, no bundler                       |
| Icons       | Inline SVGs only | Hand-written SVGs; no icon library or CDN |
| Fonts       | Google Fonts (`<link>`)| Poppins (EN), Noto Sans Tamil (TA)             |
| Animations  | CSS-only + IntersectionObserver | Keyframes, transitions — no JS animation libs |
| Build step  | **None**              | Static pages need no compilation; serve locally for API testing |
| Backend     | PHP 8.2+               | Event registration endpoints under `api/`       |
| Database    | MySQL / MariaDB        | Event tiers, coupons, registrations, payments    |
| Hosting     | Hostinger Premium      | PHP/MySQL enabled; site files deployed to `public_html/` |

### Project constraints
- Keep the clinic pages in plain HTML/CSS/vanilla JavaScript; no JS frameworks or front-end build tools.
- The event-registration backend is PHP + MySQL. Do not replace it with a browser-side payment integration.
- Never expose Coaxlure credentials, generated tokens, database passwords, or attendee data in client-side files or Git.
- Keep using plain CSS; no Sass/Less or required CSS build step.

---

## 3. DOMAIN & HOSTING

- **Domain:** `owlihealthcare.com` (reported live by the site owner)
- **Hosting provider:** Hostinger Premium Web Hosting
- **Deployment:** GitHub deployment publishes the site into `public_html/` (the owner reports pushes deploy automatically); File Manager/FTP remain available for server-only configuration.
- **Runtime:** PHP 8.2+ and MySQL are available on the hosting plan. The clinic pages are static; the event form uses the PHP API in `api/` and the MySQL schema in `sql/schema.sql`.
- **SSL:** Active for the domain.
- **Secrets:** Keep `owli-config.php` outside `public_html` and outside Git. On Hostinger, place it in the parent directory described in `api/config.sample.php`.
- **Local preview:** Docker web container serves `http://localhost:8800/`; the event page is `http://localhost:8800/events/`.

### Deployment and server configuration
- The owner reports the domain, Premium hosting, SSL, PHP 8.2–8.5 and MySQL are active.
- The owner reports GitHub pushes deploy the site into `public_html/`. Use the repository's configured deployment workflow for project changes, and verify deployment before considering a change live.
- Use hPanel/File Manager for server-only setup: create the production DB/user, import `sql/schema.sql`, configure PHP/cron, and place `owli-config.php` outside `public_html`.
- Do not remove existing site files or upload a replacement site as part of routine deployment.
- For local PHP/API testing, use the Docker preview URLs and local config details documented in `README.md` §10. Local container hostnames/passwords are not production settings.

---

## 4. CLINIC DETAILS

Use these exact details everywhere on the site.

```
Name:      Owl I Health Care Centre
Address:   1/120G1, Vishalachi Nagar, Kondanagaram, Suthamalli, Tirunelveli - 627604
Phone:     +91 6379610554
WhatsApp:  +91 6379610554
Email:     owli2026@gmail.com
Hours:     Monday to Sunday: 9:00 AM – 8:00 PM
           Open all days.
Domain:    owlihealthcare.com
```

---

## 5. COLOR PALETTE — "Forest Calm"

Define as CSS custom properties in `:root`.

| Token              | Hex       | Usage                          |
|--------------------|-----------|--------------------------------|
| `--color-primary`    | `#2D5A3D` | Headers, nav, footer background |
| `--color-secondary`  | `#5B8C5A` | Buttons, CTAs                   |
| `--color-light-green`| `#A8C5A0` | Highlight sections, accents     |
| `--color-background` | `#F4EDE4` | Page background (cream)         |
| `--color-accent`     | `#8B6F47` | Warm brown details              |
| `--color-white`      | `#FFFFFF` | Cards, light sections           |
| `--color-dark-text`  | `#1A1A1A` | Body text                       |
| `--color-light-text` | `#6B7B6B` | Subtle/secondary text           |

---

## 6. TYPOGRAPHY

- **English:** "Poppins" via Google Fonts — Headings 600/700, Body 300/400
- **Tamil:** "Noto Sans Tamil" via Google Fonts
- Load via `<link>` tags, no npm/build step required
- Tamil font applied automatically when language is switched to Tamil

---

## 7. BILINGUAL SUPPORT (English / Tamil)

| Aspect             | Implementation                                                  |
|--------------------|-----------------------------------------------------------------|
| Toggle location    | Header — `EN \| தமிழ்` button                                    |
| How it works       | JS swaps text content of elements with `data-i18n="key"` attribute |
| Translation store  | Single JS object in `i18n.js` with `en` and `ta` keys            |
| Persistence        | `localStorage` saves selected language                           |
| Default language   | English                                                          |
| Page reload?       | No — swap happens in-place                                       |

**Every user-visible string must be in the i18n system.** No hardcoded display text in HTML.

---

## 8. SITE STRUCTURE

Single-page layout. Navigation uses anchor links to scroll between sections.

| #  | Section           | ID                  | Background        |
|----|-------------------|---------------------|-------------------|
| 1  | Header / Nav      | `#header`           | White → shadow on scroll |
| 2  | Hero              | `#hero`             | Image + dark overlay |
| 3  | About             | `#about`            | Cream (`--color-background`) |
| 4  | Services          | `#services`         | White              |
| 5  | Why Choose Us     | `#why-us`           | Light green (`--color-light-green`) |
| 6  | Our Team          | `#team`             | Cream              |
| 7  | Testimonials      | `#testimonials`     | White              |
| 8  | Gallery           | `#gallery`          | Cream              |
| 9  | Blog Preview      | `#blog`             | White              |
| 9B | FAQ               | `#faq`              | Light green (`--color-light-green`) |
| 10 | CTA Banner        | `#cta-banner`       | Dark green (`--color-primary`) |
| 11 | Contact / Booking | `#contact`          | Cream              |
| 12 | Footer            | `#footer`           | Dark green (`--color-primary`) |
| —  | Floating Buttons  | Fixed bottom-right  | WhatsApp + Phone call, stacked vertically |

---

## 9. SECTION CONTENT DETAILS — moved to `docs/CLAUDE-SECTIONS.md`

> Every section of the homepage (header, hero, about, 11 services, team, testimonials,
> gallery, blog, FAQ, CTA, contact, footer, floating buttons) with exact approved copy
> and structure. **Read that file before editing any homepage section.**

---


## 10. SEO

### Meta tags (in `<head>`)
- `<title>`: Owl I Health Care Centre | Naturopathy Clinic in Tirunelveli
- `<meta name="description">`: Tirunelveli's trusted naturopathy and wellness center. Drug-free natural treatments including Hydrotherapy, Yoga Therapy, Diet Therapy, Mud Therapy, and more.
- `<meta name="keywords">`: naturopathy clinic tirunelveli, natural healing, yoga therapy, hydrotherapy, mud therapy, wellness center tirunelveli, BNYS doctor tirunelveli
- Canonical: `https://owlihealthcare.com/`
- Open Graph: og:title, og:description, og:image, og:url, og:type
- Twitter Card: summary_large_image

### Structured Data (JSON-LD)
- Schema type: `MedicalBusiness`
- Include: name, address, phone, email, opening hours, priceRange (₹₹)

### Files
- `sitemap.xml` — single page entry
- `robots.txt` — allow all, reference sitemap

---

## 11. ACCESSIBILITY

- Semantic HTML: `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>`
- "Skip to main content" link at top
- ARIA labels on nav, buttons, form inputs, interactive elements
- `alt` text on all images (including placeholders)
- Keyboard navigable: proper tab order, visible focus indicators
- Color contrast: verify dark text on cream, white text on dark green
- Touch targets: minimum 44×44px on mobile

---

## 12. PERFORMANCE TARGETS

- No heavy JS libraries — vanilla JS only
- Lazy load images below the fold: `loading="lazy"`
- Total page weight: under 1MB (excluding placeholder images)
- Minimize DOM complexity
- CSS animations only (no JS animation libraries)

---

## 13. DOCUMENTATION STANDARDS

**Every file must be thoroughly commented for a complete beginner.**

### HTML comments
```html
<!-- ============================================
     SECTION: [NAME]
     PURPOSE: [What this section does]
     NOTES: [How to modify it]
     ============================================ -->
```

### CSS comments
```css
/* ──────────────────────────────────────────────
   [SECTION NAME]
   [What these styles do and why]
   ────────────────────────────────────────────── */
```

### JS comments
```javascript
/**
 * [FUNCTION NAME]
 * PURPOSE: [What it does]
 * HOW IT WORKS: [Step-by-step explanation]
 * PARAMS: [If any]
 */
```

---

## 14. PLACEHOLDER CONTENT CHECKLIST — moved to `docs/CLAUDE-CONTENT-CHECKLIST.md`

> What is still placeholder (hero image, gallery, blog images, OG image, testimonials,
> social URLs) vs. already real (doctors, logo, maps, clinic photo, leadership).

---


## 15. DESIGN DIRECTION

- **Tone:** Organic, natural, calming — like walking into a peaceful forest clinic
- **Feel:** Professional but warm and approachable. Not clinical or hospital-sterile.
- **Spatial composition:** Generous whitespace, breathing room between sections. Alternating backgrounds (cream ↔ white ↔ light green) for visual separation.
- **Motion:** Subtle CSS fade-in on scroll via `IntersectionObserver`. Smooth scroll. WhatsApp button pulse. No heavy animation.
- **Icons:** Nature-themed inline SVGs (leaf, water, sun, lotus, etc.)

---

## 16. FILE STRUCTURE — moved to `docs/CLAUDE-FILE-MAP.md`

> The annotated file tree of everything that deploys to `public_html/`.

---


## 17. CONTACT LINKS REFERENCE — moved to `docs/CLAUDE-CONTACT-LINKS.md`

> Canonical WhatsApp / phone / email link formats used everywhere on the site.

---


## 18. RULES FOR CLAUDE CODE

1. **Read this file first** before every task.
2. **Keep the public clinic pages static**, with no front-end build step. The event registration form requires the PHP/MySQL backend when served; direct `file://` opening can preview static content but cannot exercise the API.
3. **All text bilingual** — every display string goes through the i18n system.
4. **Mark every placeholder** with `<!-- [PLACEHOLDER] -->` comments.
5. **Mobile-first** — base styles target mobile, enhance with `min-width` media queries.
6. **WhatsApp + Phone are the primary CTAs** — booking, contact, floating buttons all route to WhatsApp and direct call.
7. **Document thoroughly** — a beginner who has never coded should understand every line.
8. **Test mentally at 375px, 768px, and 1440px** widths.
9. **No hardcoded colors** — use CSS custom properties everywhere.
10. **Semantic HTML** — proper landmark elements, ARIA labels, accessibility.

---

## 19. EVENT REGISTRATION, PAYMENTS & TICKETS — quick reference

| Topic | Where it lives |
|---|---|
| **Complete project handoff (read first)** | `HANDOFF.md` |
| Go-live / ₹10 test / two-switch system | `GO-LIVE-STEPS.md` |
| Live status + owner steps + decision log | `PROJECT-STATUS.md` |
| API debugging runbook | `API-TROUBLESHOOTING.md` |
| Local Docker stack + the three checkers | `LOCAL-TESTING.md` |
| Full dated change log | `CHANGE-LOG.md` |

The essentials, in one paragraph each:

- **Two switches must agree:** the client (`js/event-config.js` → `registrationMode`) and the
  server (`owli-config.php` → `registration.mode`). Flipping the client alone can never open
  payments — `register.php` refuses with 503 `not_open` while the server says `preview`.
- **Identity:** the mobile+email pair is the booking identity. Pending rows are reused (same
  reference), paid pairs are refused with 409, failed rows revive on re-registration.
- **Ticket:** `api/ticket.php?ref=AP26-XXXXXX` streams a paid-only A4 PDF (Booking ID + QR
  that re-opens `/events/?ref=…` as verified-PAID). A **Booking-ID lookup box** sits under the
  registration form (`#ticket-lookup`): paid → the three paid actions (download ticket,
  Get ticket on WhatsApp, Done); anything else → an invalid/unknown ticket message.
- **Delivery paths:** browser success box, ticket link in the confirmation email, WhatsApp
  `wa.me` handoff to the official clinic number, admin per-row download, and a 2-day digest
  mail (`admin => digest_to` in the server config).
- **Coupons:** generated in the admin console, stored only in the DB, verified server-side.
  A 100%-off coupon completes registration on the same page (`free:true`, no payment tab).
- **Admin console** (`admin/index.php`): staff entries, staff-only deletion (SQL-enforced),
  filters/sorts/chips, CSV export, per-row ticket download, recheck button, coupon generator.

---

## 20. DOCUMENTATION MAP

| File | Purpose | Size |
|---|---|---|
| `CLAUDE.md` (this file) | Project rules, stack, palette, structure, constraints — the read-first file | small |
| `CHANGE-LOG.md` | Every dated change, why + how verified (moved out of this file 2026-10-26) | large |
| `docs/CLAUDE-SECTIONS.md` | Homepage section copy + structure (old §9) | medium |
| `docs/CLAUDE-FILE-MAP.md` | Annotated repo tree (old §16) | small |
| `docs/CLAUDE-CONTENT-CHECKLIST.md` | Placeholder vs. real content (old §14) | small |
| `docs/CLAUDE-CONTACT-LINKS.md` | Canonical link formats (old §17) | small |
| `README.md` | Owner-facing guide: editing, deploying, managing the event | large |
| `PRD.md` | Product requirements + acceptance criteria | medium |
| `GO-LIVE-STEPS.md` / `PROJECT-STATUS.md` / `API-TROUBLESHOOTING.md` / `LOCAL-TESTING.md` | Operator runbooks | medium |

`CLAUDE.md` was reduced under the 30k budget on 2026-10-26 by moving §9/§14/§16/§17 into
`docs/` and the change log into `CHANGE-LOG.md` — same content, split for context switching.
