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

## 9. SECTION CONTENT DETAILS

### 9.1 Header / Nav
- Text logo: "Owl I" + small leaf/owl inline SVG
- Nav links: Home, About, Services, Our Team, Gallery, Blog, FAQ, Contact
- "Arokya Payanam 2026" button (`.btn-event`, warm brown accent — `--color-accent`) → links to `/events/`. Sits beside "Book Now" in `.nav-actions`, deliberately a different color so it reads as a distinct, time-limited announcement rather than a regular site CTA.
- "Book Now" CTA button (green) → scrolls to `#contact`
- Language toggle: `EN | தமிழ்`
- Mobile: hamburger → slide-in drawer. `.nav-actions` (lang toggle, event button, Book Now) is hidden below the 769px breakpoint, consistent with the pre-existing Book Now behavior — mobile visitors reach the event via the homepage popup, the hero button, or the footer link instead.
- Sticky on scroll with subtle box-shadow

### 9.2 Hero
- Full viewport height (100vh)
- Background: placeholder (gradient or CSS pattern). Mark for replacement.
- Dark overlay for text contrast
- **Heading EN:** "Heal Naturally. Live Fully."
- **Heading TA:** "இயற்கையாக குணமாகுங்கள். முழுமையாக வாழுங்கள்."
- **Subheading EN:** "Tirunelveli's trusted naturopathy and wellness center — holistic healing for body, mind, and soul."
- Three CTAs: "Enquire on WhatsApp" + "Explore Services" (→ `#services`) + "Arokya Payanam 2026" (`.btn-event`, calendar icon, → `/events/`) — same accent color as the nav version, giving visitors a persistent (non-closable) way to reach the event page beyond the dismissable popup
- CSS fade-in animation on load

### 9.3 About
- Two-column: text left, real clinic interior photo right (`images/about/clinic-interior.png`)
- Philosophy: Drug-free healing, holistic approach, personalized plans, traditional + modern
- Brief Vision & Mission statements
- Leadership profiles now live in the combined **Our Team** section (`#team`) — see 9.6

| #  | Name                     | Role                             |
|----|--------------------------|-----------------------------------|
| 1  | Abdul Salim Malik        | Founder & CEO                    |
| 2  | Mrs. Jannath Firthouse    | Co-Founder & Managing Director   |

### 9.4 Services
- Grid: 3 col desktop, 2 tablet, 1 mobile
- 11 cards with icon + name + 1-line description
- Hover: translateY(-5px) + box-shadow
- Sourced from the official "OWL i Health Care Centre" booklet (services the clinic actually offers, as opposed to conditions treated — see `CONDITIONS WE COMMONLY ADDRESS` in the booklet for the latter)

**Full service list:**

| #  | Service               | Description (EN)                                                          |
|----|-----------------------|-----------------------------------------------------------------------------|
| 1  | Massage Therapy       | Swedish, Ayurvedic oil, deep tissue, prenatal and postnatal massage        |
| 2  | Hydrotherapy          | Steam bath, hip bath, and therapeutic water packs                         |
| 3  | Fasting & Diet Therapy | Therapeutic fasting, individualized diet planning, and nutrition guidance |
| 4  | Mud Therapy           | Mud bath, mud application, and mud pack                                   |
| 5  | Foot Reflexology      | Gentle pressure-point therapy for relaxation and well-being               |
| 6  | Aromatherapy          | Aromatherapy massage, essential oil inhalation, and diffusion             |
| 7  | Yoga Therapy          | Therapeutic yoga, yoga nidra, and monthly yoga classes                    |
| 8  | Acu Therapy           | Acupuncture, acupuncture stimulator, and moxa therapy                     |
| 9  | Ozone Therapy         | Controlled oxygen-ozone therapy for individualized care                   |
| 10 | Shirodhara            | Traditional oil-based relaxation therapy for the forehead                 |
| 11 | Kizhi                 | Elakizhi, Njavarakizhi, and Podikizhi herbal poultice therapy             |

#### Treatment details (View Details)
- Every service card has a visible pill button "View Details" (EN) / "விவரங்களைக் காண்க" (TA); label flips to "Show Less" while open
- Expands a panel inside the card showing: what the treatment involves (the card description), a note that the doctor recommends a personalised treatment plan, and a note that an assessment may be required before therapeutic procedures
- No durations or fees are shown anywhere (clinic has not approved them; add only after written confirmation)
- Panel includes a WhatsApp "Enquire on WhatsApp" button with the treatment name pre-filled in the message
- Implemented in `js/treatment-details.js`; builds panels from i18n keys, observes `<html lang>` and re-renders on language switch; ARIA: `aria-expanded` / `aria-controls`

### 9.5 Why Choose Us
- Light green background section
- 4 items (responsive: 4 col → 2x2 → stacked):
  1. Experienced BNYS Doctors
  2. 100% Natural Treatments
  3. Personalized Care Plans
  4. Affordable Wellness

### 9.6 Our Team (`#team`)
- Combined section: **Management & Leadership** sub-grid (2 leader cards) + **Our Doctors** sub-grid (2 doctor cards)
- All 4 members retained — **REAL DATA** (no placeholders)
- Layout: 2 cards side by side on desktop (max-width ~450px each), stacked on mobile
- Each card shows: photo, name, designation, qualification, specialization, experience, and areas of expertise as styled pills/tags

| #  | Name                  | Designation            | Qualification              | Experience | Specialization                                                              |
|----|-----------------------|------------------------|----------------------------|------------|-----------------------------------------------------------------------------|
| 1  | Dr. Jospin Sheela.A   | Chief Medical Officer  | BNYS, M.Sc(Psy), cCGO     | 6+ years   | Naturopathy, Yoga Therapy, Clinical Nutrition and Lifestyle Medicine         |
| 2  | Dr. Jebakumar. A      | Medical Officer        | BNYS                       | 1+ year    | Naturopathy, Pain Management, Acupuncture, Rehabilitation                   |

### 9.7 Testimonials
- Carousel: CSS + vanilla JS (no library)
- 5 placeholder testimonials (quote, first name, treatment type, 5 stars)
- Auto-rotate every 5 seconds, prev/next buttons
- `<!-- [PLACEHOLDER] Replace with real testimonials -->`

### 9.8 Gallery
- Grid: 3 col desktop, 2 mobile
- 6–8 placeholder image slots
- CSS lightbox on click (minimal JS)
- `<!-- [PLACEHOLDER] Replace with real clinic photos -->`

### 9.9 Blog Preview
- 3 cards:
  1. "5 Benefits of Hydrotherapy You Didn't Know"
  2. "How Naturopathy Can Help Manage Diabetes Naturally"
  3. "Yoga Asanas for Back Pain Relief"
- Each: placeholder image, title, 2-line excerpt, "Read More →" (href="#")

### 9.9B FAQ
- 10 questions sourced from the official clinic booklet
- Each rendered as a native `<details>/<summary>` element — accordion expand/collapse with no JS required
- Content: appointments, consultation length, what to bring, continuing existing medicines, number of sessions needed, children/elderly eligibility, yoga without consultation, single-therapy visits, wellness-only visits, treatment suitability

### 9.10 CTA Banner
- Full-width, dark green background
- **EN:** "Ready to Start Your Natural Healing Journey?"
- **TA:** "உங்கள் இயற்கை குணமாகும் பயணத்தைத் தொடங்க தயாரா?"
- Buttons: "Enquire on WhatsApp" → WhatsApp, "Call Now" → `tel:+916379610554`

### 9.11 Contact / Booking
- **Left column — Form:**
  - Fields: Name, Phone (required); Email (optional), Service (dropdown: 11 services + Doctor Consultation / General Wellness Enquiry / Not Sure - Need Guidance), Message (optional)
  - Validation: required fields, email regex, Indian phone format (10 digits, starts 6-9)
  - Submit button label: "Request an Appointment" (EN) / "சந்திப்புக்கு கோரிக்கை அனுப்புங்கள்" (TA)
  - Expectation note under form: "Appointments are confirmed after our team responds."
  - Submit action: open WhatsApp with pre-filled message; success message is bilingual and appends the same confirmation line
  - URL format: `https://wa.me/916379610554?text=Name: ...%0APhone: ...%0AService: ...%0AMessage: ...`
  - Fallback: mailto link to owli2026@gmail.com
- **Right column — Info:**
  - Address, Phone (tel: link), WhatsApp (wa.me link), Email (mailto: link), Hours
  - Google Maps embed (real coordinates: 8.7134811, 77.623205)

### 9.12 Footer
- Dark green background, light text
- 4-column grid:
  1. Logo + brief about + social icon (Instagram only — https://www.instagram.com/owli_healthcare; Facebook/YouTube hidden until the clinic provides URLs)
  2. Quick Links (anchor links)
  3. Top 6 Services
  4. Contact info + Hours
- Bottom bar: `© 2026 Owl I Health Care Centre. All Rights Reserved.`

### 9.13 Floating Action Buttons (WhatsApp + Phone)

Two fixed buttons, stacked vertically in the bottom-right corner of every page.

```
Position: fixed, bottom-right
Layout:   vertical stack — Phone on top, WhatsApp below
Z-index:  high (above all content)
```

**WhatsApp Button (bottom):**
- Green circle (`#25D366` — official WhatsApp green) with WhatsApp SVG icon
- CSS pulse animation to draw attention
- Link: `https://wa.me/916379610554?text=Hi! I'd like to enquire about your services.`
- Tooltip on hover: "Chat on WhatsApp"
- ARIA label: "Chat with us on WhatsApp"

**Phone Button (above WhatsApp):**
- Circle using `--color-secondary` (`#5B8C5A`) with phone SVG icon
- Subtle CSS pulse animation (slower/different rhythm than WhatsApp to avoid visual noise)
- Link: `tel:+916379610554`
- Tooltip on hover: "Call us now"
- ARIA label: "Call Owl I Health Care Centre"

**Styling details:**
- Both buttons: 56px circles on desktop, 48px on mobile
- Gap between buttons: 12px
- Box shadow for depth: `0 4px 12px rgba(0,0,0,0.15)`
- Hover: scale up slightly (`transform: scale(1.1)`) + deeper shadow
- Mobile: position adjusted so they don't overlap content or browser UI (bottom: 20px, right: 16px)
- Both buttons must be bilingual-aware (tooltips swap with language toggle)

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

## 14. PLACEHOLDER CONTENT CHECKLIST

All placeholder content must be marked with `<!-- [PLACEHOLDER] -->` in HTML.
This is the complete list of items the clinic owner needs to replace:

| Item                        | Location              | Recommended Size / Format  | Status    |
|-----------------------------|-----------------------|----------------------------|-----------|
| Hero background image       | `images/hero/`        | 1920×800px, JPG            | Placeholder |
| Gallery photos (6–8)        | `images/gallery/`     | 800×600px, JPG             | Placeholder |
| Blog post images (3)        | `images/blog/`        | 600×400px, JPG             | Placeholder |
| OG/social sharing image     | `images/og-image.jpg` | 1200×630px, JPG            | Placeholder |
| Favicon                     | `images/favicon.ico`  | 32×32px or 64×64px         | Placeholder |
| 5 real patient testimonials | In `i18n.js`          | Text only                  | Placeholder |
| Social media profile URLs   | Footer links          | Full URLs                  | Placeholder |

**Already completed (no longer placeholder):**
- Doctor photos and details (2 doctors with real names, qualifications, and photos)
- Clinic logo (owli-horizontal.png for header, owli-vertical.png for footer)
- Google Maps embed (real coordinates: 8.7134811, 77.623205)
- Clinic interior photo (`images/about/clinic-interior.png`)
- Leadership section — Founder & CEO and Co-Founder & Managing Director profiles with real photos (`images/leadership/`)

---

## 15. DESIGN DIRECTION

- **Tone:** Organic, natural, calming — like walking into a peaceful forest clinic
- **Feel:** Professional but warm and approachable. Not clinical or hospital-sterile.
- **Spatial composition:** Generous whitespace, breathing room between sections. Alternating backgrounds (cream ↔ white ↔ light green) for visual separation.
- **Motion:** Subtle CSS fade-in on scroll via `IntersectionObserver`. Smooth scroll. WhatsApp button pulse. No heavy animation.
- **Icons:** Nature-themed inline SVGs (leaf, water, sun, lotus, etc.)

---

## 16. FILE STRUCTURE

Everything below goes into Hostinger's `public_html/` directory.

```
owli-healthcare/
├── index.html                  # Single-page site with all sections
├── events/
│   └── index.html              # Arokya Payanam 2026 event page (/events/)
├── css/
│   ├── styles.css              # All base styles, layout, components
│   ├── responsive.css          # Media queries for mobile/tablet/desktop
│   ├── events.css              # Styles for the /events/ page only
│   └── event-banner.css        # Homepage "Arokya Payanam 2026" popup
├── js/
│   ├── main.js                 # Nav toggle, sticky header, scroll animations
│   ├── i18n.js                 # Translation object + language toggle logic
│   ├── testimonials.js         # Testimonial carousel (auto-rotate + controls)
│   ├── lightbox.js             # Gallery lightbox (open/close/navigate)
│   ├── contact.js              # Form validation + WhatsApp URL builder
│   ├── treatment-details.js    # Service card "View Details" panels
│   ├── event-config.js         # Event registration state (one place to open/close it)
│   ├── event-banner.js         # Homepage event popup (show/close, 24h dismissal)
│   └── event-register.js       # /events/ registration form (category fields, price list, payment redirect)
├── admin/index.php            # Password-protected staff console: registrations, statuses, recheck, CSV export, staff entries, coupon generator
├── api/                        # PHP backend: register.php, quote.php, status.php, cron-recheck.php, _bootstrap.php, admin-pass.php
├── scripts/                    # validate.php (pre-deploy checks) + check-i18n.js (EN/TA parity); CLI-only, blocked from the web
├── .htaccess                  # Blocks repo-only files from the web (*.md, *.sql, *.log, *.ini → 403)
├── sql/schema.sql              # MySQL tables + starting prices (import once via phpMyAdmin)
├── images/
│   ├── hero/                   # Hero background (1920×800px)
│   ├── services/               # Service icons (SVG preferred)
│   ├── doctors/                # Doctor photos (400×400px, square)
│   ├── leadership/              # Founder/Co-Founder photos
│   ├── about/                  # Clinic interior photo
│   ├── events/                 # Event logo + organiser logo strip
│   ├── gallery/                # Clinic gallery photos (800×600px)
│   ├── blog/                   # Blog thumbnail images (600×400px)
│   ├── logo.svg                # Clinic logo
│   ├── og-image.jpg            # Social sharing image (1200×630px)
│   └── favicon.ico             # Browser tab icon
├── sitemap.xml                 # Sitemap — homepage + /events/
├── robots.txt                  # Allow all crawlers
├── GO-LIVE-STEPS.md            # Operator runbook: Rs 10 payment test + opening registration
├── PROJECT-STATUS.md           # Live status snapshot: verified state, remaining steps, decisions
└── README.md                   # Full project documentation
```

---

## 17. CONTACT LINKS REFERENCE

### WhatsApp Links
| Purpose           | URL                                                                                                  |
|--------------------|------------------------------------------------------------------------------------------------------|
| Floating button    | `https://wa.me/916379610554?text=Hi! I'd like to enquire about your services.`                       |
| Enquire on WhatsApp | `https://wa.me/916379610554?text=Hi! I'd like to enquire about your services.`                        |
| Contact form submit| `https://wa.me/916379610554?text=Name: [name]%0APhone: [phone]%0AEmail: [email]%0AService: [service]%0AMessage: [message]` |

### Phone Link
| Purpose           | URL                          |
|--------------------|------------------------------|
| Floating button    | `tel:+916379610554`          |
| "Call Now" CTAs    | `tel:+916379610554`          |

### Email Link
| Purpose           | URL                              |
|--------------------|----------------------------------|
| Fallback contact   | `mailto:owli2026@gmail.com`      |

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

## Change Log

- **2026-10-24 — `PROJECT-STATUS.md` added; live status verified end-to-end after the ₹10 test.** With the ₹10 payment test passed and all six Hostinger setup steps done, the project gained a one-page status snapshot (`PROJECT-STATUS.md`) — what the project is, the verified live state (deploy/DB/API/admin/cron/protections, all re-probed: assets match the repo, docs 403, `mode:"open"`, ₹10 tier live), the exact remaining owner steps in order (the `source`-column ALTER, the three coupon generations in the console, the registration-window decision with the ₹449 restore SQL, optional test-row cleanup with FK ordering, a `validate.php` re-run), and a decision log capturing every owner request and why it was built the way it was (identity = mobile+email pair, free-coupon same-page completion, hashed coupons deliberately rejected, staff-only deletion enforced in SQL, coupon codes moved out of the repo after the `GO-LIVE-STEPS.md` exposure). Linked from README §2 and the file map in both docs. Web-blocked like all `.md` files.

- **2026-10-24 — Admin: staff entries + coupon generator; repo docs pulled off the web; coupon auto-apply.** Three owner requests shipped together, all verified against the live stack. **(1) Public coupon exposure closed — found by checking, not by luck:** the GitHub deployment publishes the whole repo into `public_html/`, so `https://owlihealthcare.com/GO-LIVE-STEPS.md` was serving HTTP 200 with **all 22 coupon codes** (EARLY10, PAYANAM20, 20 × FREEAP26-*) readable by anyone. Fix: a new root `.htaccess` denies `*.md`, `*.sql`, `*.log`, `*.ini` from the web (verified live: 403 before the fix; docs now 403/404, and the tests confirmed the file is gone from the public URL after deploy), plus `scripts/.htaccess` (CLI-only: `validate.php` / `check-i18n.js` now 403 from a URL while `php scripts/validate.php` keeps working), and `scripts/validate.php` gained checks so the 51-test suite now **fails** the deployment if any project doc or the scripts folder is web-reachable (55/55 target). **Coupon codes are no longer committed at all:** the runbook's INSERT list was replaced by a generator inside the admin console — codes live only in the database, and the repo keeps zero real codes (the schema keeps recipe examples only, already placeholders). **(2) Admin "Add entry" (offline payments) + strictly-scoped deletion:** the console gained a collapsed "+ Add entry — record an offline payment (cash / UPI)" card (category with a live price preview via `quote.php`, all category fields, optional coupon — server-priced like every other path, `owli_clean_mobile`, and the same one-paid-ticket-per-mobile+email rule). Staff rows are stored with a new `source ENUM('web','staff')` column (schema updated for fresh imports; live DBs run the one-line `ALTER` in `GO-LIVE-STEPS.md` — the console degrades politely and hides add/delete until it exists). Deletion is staff-only **enforced in SQL, not just hidden**: the button renders only for `source='staff'`, the handler re-reads the row, and the `DELETE` itself carries `AND source='staff'` so even a forged POST cannot remove a website row; attempts get logged and answered "permanent record". Every deletion (and every add) is written to the PHP error log with the staff IP; `payment_attempts` rows are deleted first inside a transaction. The dashboard shows WEB/STAFF badges, an "Added by staff" stat card, address/paid-at/base/discount in the expandable details (address previously missing from the admin view entirely), and the CSV export gained a Source column. **(3) Coupon auto-apply on the public form:** the Apply click stays, but the price preview now refreshes itself ~0.8s after typing stops (one server call per code, `register.php` re-validates on submit — the free-coupon case needs no second call: `register.php` returns `free:true` and the success box renders in-page without ever opening a payment page, exactly the requested "verify on the same page" behaviour). Note on hashing: a client-side hash table was considered and **rejected deliberately** — it would ship every coupon's verifier to the browser, letting anyone brute-force codes offline with no rate limit; server-side lookup is one cheap call and keeps codes secret. Cache: `event-register.js?v=4` (events page). **Deploy note:** live Hostinger needs the `source` column ALTER before add/delete works there (GO-LIVE-STEPS.md), and the GO-LIVE-STEPS.md protections need the deploy to take effect.

- **2026-10-24 — Payment flow: two live-verified bug fixes (blank checkout page + paid never detected).** The owner ran the first ₹10 live test (Phase B) and the browser landed on a **blank `https://app.coaxlure.com/customer/` with no way to pay** — while the payment page's own "Continue" flow did capture ₹10 on a follow-up link, and the site kept reporting **pending** hours after Coaxlure had the money. Diagnosis against the live API (owner's real paid order, invoice `CLI-26104-114458`, `orderStatus: "PAID"`, `paymentStatus: "success"`) found two root causes, both fixed in `api/_bootstrap.php`:
  **(1) Broken checkout URL — `coax_create_payment()`.** Coaxlure's `POST /payment-link` default returns `https://app.coaxlure.com/customer/coaxlure-payments/<token>` (**plural**) — the checkout SPA does not serve that route at all (verified: `get-order-from-link` answers 404 against the plural URL's token on the wrong backend, and a headless-browser load of the plural URL renders an empty shell with zero API calls). The working route is `/customer/coaxlure-payment/<token>` (**singular**) — the same URL the merchant app and the customer SMS use, verified rendering the real order (correct merchant name, `Order ID`, `Total ₹10.00`, "Automatically payment window will be open", 1432-min expiry). Fix: the server now passes `merchantDomain` (config `pay_page_base`, default `https://app.coaxlure.com/customer/coaxlure-payment`) to `/payment-link`, plus a belt-and-braces rewrite of any plural URL to singular. **(2) Paid payments never detected — `coax_fetch_status()`.** Coaxlure **rotates the payment token at capture time**, so the create-time token (all the DB stored) returns `BA_4002 "Order not found"` afterwards — a genuinely paid attendee would remain pending forever. The stable identifier is the **order id**, which `/payment-status` also accepts (verified: create-time token → BA_4002, order id → `PAID`/`success`, invoice id → `PAID`/`success`, rotated token → `PAID`/`success`). Fix: attempts are now looked up **by order id first, token as fallback**, and the paid matcher accepts Coaxlure's real wording (`paid`/`success`/`completed` on `paymentStatus`, `paid`/`completed` on `orderStatus`; a PENDING order's `paymentStatus: null` correctly stays pending). `coax_refresh_attempt()` passes the order id through. **Verified locally:** full registration through the local site returns the **singular** payment URL; the identifier fallback chain survives a 404 (stays pending, records `http=404 code=BA_4002 by=token`); the matcher flags the genuine live paid envelope YES and PENDING NO; 51/51 validate checks pass; test rows cleaned up. `GO-LIVE-STEPS.md` Phase B gained a "what happened on the first attempt + fixes" preamble and a **B0 deploy step**. **The owner's ₹10 row (`AP26-WC4DV9`) is genuinely captured by Coaxlure and will flip to paid after this deploy + one recheck (cron ≤5 min or the admin "Recheck payment now" button).**

- **2026-10-24 — Free-ticket flow + coupon packs; mobile uniqueness made strict:** Answers to the owner's three questions became three code changes, all live-verified against the dev Coaxlure API. **(1) Mobile/identity rules:** there is no separate "payment phone number" — the only phone number in the flow is the attendee's 10-digit mobile (Coaxlure's create-order uses that same number for its own customer record; the site itself makes no OTP calls). Multiple people CANNOT share one number+email pair: the API refuses a second registration with 409 `already_registered` while the first is paid, and a second submission while PENDING reuses the same row/reference with a fresh payment attempt. Strictness was tightened for release: previously only **paid** rows blocked duplicates, so two people could alternate one number+email to produce unlimited pending rows; now ANY mobile+email pair with a non-failed registration (pending or paid, within the 2-day window) returns the 409 with the existing reference — pending rows are still re-submittable for payment retry by their owner. **(2) Free-ticket coupon flow (new):** a coupon that covers the WHOLE price (a `percent` 100 coupon, or a flat coupon ≥ base — the intended 20 free invites) now works without any payment: `register.php` detects `final = 0` + coupon applied, inserts the registration, and calls `owli_mark_paid()` directly — the row becomes paid instantly (status: paid, amount: 0) with the standard confirmation email ("Amount: FREE (coupon applied)"), no Coaxlure order. The browser shows a paid-style success box (new key `evf_free_success`, EN+TA). Sub-₹1 prices are still refused (`amount_too_low`) when NOT coupon-made; ₹0-by-coupon remains the single free path. `max_uses: 1` makes each free code single-use (counted on PAID rows, which free registrations now are); a second user of a spent code gets 422 `used_up`. **(3) Coupons to create (phpMyAdmin, INSERTs listed in README §10/`GO-LIVE-STEPS.md`):** reusable 10% code `EARLY10` (unlimited uses) and reusable 20% code `PAYANAM20` (unlimited uses) — both `percent` type, all categories, no expiry — plus 20 single-use 100% free codes `FREEAP26-XXXXXX` (`max_uses: 1` each, unique random suffixes; the full INSERT list is in `GO-LIVE-STEPS.md` — codes live ONLY in the database, never in the repo). Verified live: free registration → `{ok:true, free:true, amount:0}` → `status.php` reports **paid**; re-registration with same mobile+email → 409 with reference; a second person reusing the spent free code → `used_up`; 10%/20% previews (₹299→₹269, ₹399→₹319) and reusable unlimited use; pending-row reuse keeps one reference per person. Cache versions bumped: `i18n.js?v=3`, `event-register.js?v=3` (both pages). Cleanup: all test rows and coupons removed from the local DB. **Note: the free flow and strict-duplicate fix require deploy before they exist on Hostinger.**

- **2026-10-24 — Free-ticket flow + coupon packs; mobile-pair rule clarified and verified:** Three owner questions → three changes, live-verified against the dev Coaxlure API. **(1) Identity/mobile rules (no code change needed — verified as-is):** there is no separate "payment phone number"; the attendee's own 10-digit mobile is the only number in the flow (it is also what Coaxlure's create-order receives). Two people CANNOT each get a ticket on one mobile+email pair: the API keeps exactly ONE row per pair while it is pending (verified: person B submitting with A's pending pair gets A's reference back — the SAME row re-priced, `rows_for_pair = 1`), and once that row is PAID any further submission is refused with 409 `already_registered`. So the honest answers: same mobile + same email → one ticket total; same mobile + different email (or vice versa) → registers as a separate person (the pair, not the phone alone, is the identity key). **(2) New free-ticket flow:** a coupon covering the whole price (`percent` 100, or flat ≥ base) now completes registration WITHOUT any payment: `register.php` calls `owli_mark_paid()` directly — the row is instantly `status: paid, amount 0` (visible in the admin console), the confirmation email says "Amount: FREE (coupon applied)", the browser shows a paid-style success box (new key `evf_free_success`, EN+TA). No Coaxlure order is created for ₹0. Verified: `{ok:true, free:true, amount:0}` → `status.php` → paid. `max_uses: 1` makes each free code single-use — a second person reusing a spent code gets `used_up`. **(3) Coupon packs** (the exact INSERT statements are in `GO-LIVE-STEPS.md`; codes live only in the DB, never the repo): reusable `EARLY10` (10%, unlimited) and reusable `PAYANAM20` (20%, unlimited) — both `percent`, all categories — plus 20 single-use 100% codes `FREEAP26-XXXXXX`. Verified previews: ₹299→₹269 (10%), ₹399→₹319 (20%). Cache versions bumped (`i18n.js?v=3`, `event-register.js?v=3`, both pages); local DB test rows/coupons cleaned up; lint + i18n parity PASS. **Requires deploy to Hostinger before the free flow exists there.**

- **2026-10-24 — Client go-live flip executed (Phase A of the runbook):** `js/event-config.js` → `registrationMode: 'open'` — the client half of the go-live pair. Cache versions bumped: `event-config.js?v=5 → ?v=6` (both pages). This is the "A" commit from `GO-LIVE-STEPS.md`: the client UI now unlocks its buttons when the server agrees, but the live server switch (`owli-config.php` → `registration.mode`) **still reads `'preview'`**, so `api/register.php` continues to refuse every submission with 503 `not_open` on Hostinger — verified live right before this change (`quote.php` → `mode:"preview"`, `register.php` → 503). The tier flip (`1000` paise for General) is in place on the live DB (₹10 shows in `quote.php`). Deliberate sequence per the runbook: commit+push+deploy of this client flip happens while payments are still refused; then the owner flips the server switch for the ₹10 test; if the test succeeds, this same client flip IS the go-live commit (nothing further needed client-side). If the test fails, the owner flips the server switch back to `'preview'` (one edit, instant) while this client flip stays harmless. i18n parity re-verified (PASS), `node --check` clean.

- **2026-10-24 — `GO-LIVE-STEPS.md` added: operator runbook for the ₹10 test + go-live:** All remaining next steps (previously only in conversation/README §10) are now a concrete runbook in the repo, tailored to the current deployed state (Hostinger `u810328541_owlievents`, both switches at `'preview'`, patch `f379888` live). It documents the two-switch matrix (server `owli-config.php` — instant, no deploy — vs client `js/event-config.js` — Git deploy), the phased sequence: **A** — ₹10 tier (`1000` paise) + client-flip commit/push/deploy-verify while submissions are still refused; **B** — server switch → immediate ₹10 payment + paid-detection evidence (admin pill, "Coaxlure said" raw wording, `status.php`, cron `newly_paid=1`, confirmation email); **C1** — restore `44900`, registration live (the A commit becomes the go-live commit); **C2** — one-edit server rollback + capture `raw_status` for a `coax_fetch_status()` matcher fix, then retest. Includes safety rules (short ₹10 window, real-money note, credential hygiene, FK-ordered cleanup SQL for the test row) and an explicit correction: the client flip for the *live* test **must** go through Git — a local-only flip does not affect Hostinger (it only works for localhost testing with dev credentials). README §2/§10 and CLAUDE.md §16 now reference the file.

- **2026-10-24 — Live test payment set to Rs 10; online minimum lowered to Rs 1:** The real-money verification step (README §10 go-live step 5) now uses **Rs 10** (`1000` paise) instead of Rs 100. `api/register.php` used to refuse every server price below `10000` paise (Rs 100) with `amount_too_low`, which made a cheap live test impossible; the floor is now **`100` paise (Rs 1)** — Coaxlure's own minimum — so the Rs 10 tier passes and Rs 0 remains impossible (no free-ticket flow; anything below Rs 1 cannot be paid online at all). Real ticket prices stay controlled by the `tiers` table. Verified locally against the real dev Coaxlure API with the server mode temporarily `open`: a `general` tier at `1000` paise completes registration and creates a real Rs 10 dev order (amount cross-check passes, `app.coaxlure.com` payment link returned), while a `50`-paise tier is still refused with `amount_too_low`; tier and mode were restored afterwards and the test rows deleted. README §10, PRD.md and the mode-box comment in `js/event-config.js` now consistently say Rs 10 / `1000` paise. **This change must be deployed to Hostinger before the Rs 10 test** — until the deploy lands, the live `register.php` still refuses with `amount_too_low`.

- **2026-10-24 — Production deployment on Hostinger: COMPLETE (all checklist steps verified).** The Arokya Payanam 2026 backend is now live on `owlihealthcare.com`. Verified state, in order: **(1) DB** — `u810328541_owlievents` (user `u810328541_owli`) created in hPanel, `sql/schema.sql` imported via phpMyAdmin; all 5 tables + 4 price tiers present. **(2) Config** — `owli-config.php` placed one level above `public_html` with the live DB credentials and live Coaxlure credentials; the live `quote.php` flipped from `mode:"closed"` to `mode:"preview"` with all four prices, proving the DB connection and config location on the first request after saving (no deploy needed). **(3) Validation** — `scripts/validate.php` run on the server: 51/51 after the 403/404 fix (see the entry below); independent external probes matched. **(4) Admin console** — `/admin/` login, dashboard and Export CSV verified by the owner (Step 6); password hash lives only in the git-ignored server config. **(5) Cron** — hPanel Cron Jobs job created with `/usr/bin/php /home/u810328541/domains/owlihealthcare.com/public_html/api/cron-recheck.php` every 5 minutes; the command was proven in the hPanel Terminal first (`checked=0 newly_paid=0` — correct for an empty registrations table), and the hPanel cron page itself displays each run's output, so no log redirect is needed; the command was left unchanged. **(6) Public site** — `/`, `/events/` 200 in preview (prices visible, Pay button disabled), `register.php` still refuses with 503 `not_open` (payments safely off), `status.php` rejects unknown refs, internal API files 403, `robots.txt` blocks `/admin/`, http→https 301 works. Remaining before registration can open: the **₹10 real payment test** with live credentials to confirm Coaxlure's paid `paymentStatus` wording (README §10 step 5), then flip both switches to `'open'` (server config first, then `js/event-config.js` with a `?v=` bump). Known open content item: `images/og-image.jpg` is still missing (404) — social shares show no image until the clinic supplies one. Minor SEO note recorded: `www.` serves the same site as the apex domain without a redirect (canonical tags point to the apex, so this is cosmetic).

- **2026-10-24 — First live Hostinger validation (47/51) + validator fix:** Ran `scripts/validate.php` on the real Hostinger server right after the hPanel setup (DB `u810328541_owlievents` imported, `owli-config.php` in place with live Coaxlure credentials, `preview` mode). Everything passed except the four "blocked from the web" checks, which failed **only because of an assertion bug in the validator**: Hostinger's Apache honours `api/.htaccess` and answers `403` (denied before PHP even runs), while the validator asserted `404` — the code path the local PHP dev server takes via the files' own self-guard. The files were in fact protected on both stacks (verified with direct probes: live = 403 with a static Apache error page, local = 404/503 from the self-guard). Fix: the four checks now accept **403 or 404** (documented at the head of the script); anything else — including 200 — still fails. The live run also confirmed the real environment end-to-end: PHP 8.3.35, MariaDB 11.8.9, all 5 tables, all 4 price tiers via `quote.php` (preview mode), `register.php` refusing with 503, `status.php` rejecting unknown refs, `/admin/` showing a password form with zero attendee data pre-login, Coaxlure live host reachable, site pages 200. 51/51 after the fix. Also updated the top-of-script check-list comment.

- **2026-10-23 — Admin console + validation suite + repo cleanup (this push):** Three new pieces ship together as the pre-commit state. **(1) `admin/index.php`** — a password-protected staff console at `/admin/` (unlinked, `robots.txt`-excluded, English-only internal tool): summary cards (total/paid/pending/failed/collected/awaiting), a paginated registration table with status pills, expandable rows showing category details + payment-attempt history with the raw Coaxlure response, filters (status/category/search across reference, name, mobile, email), a **"Recheck payment now"** button per pending registration (calls Coaxlure immediately instead of waiting for the 5-minute cron), and **Export CSV** honouring the active filters (UTF-8 BOM for Excel, built with `fputcsv` — never hand-concatenated). Auth: the password lives ONLY as a `password_hash()` in `owli-config.php` (`'admin' => ['password_hash' => …]`); empty hash = console disabled with a 403 and no login form at all. Sessions are httponly/SameSite=Lax (+secure on HTTPS), the id is regenerated on login, every POST is CSRF-checked, failures cost 1s each and 5 failures/15min lock the IP (counters in the `settings` table, so the throttle survives across processes). The local DB user needed one extra grant: `DELETE ON owli.settings` (throttle cleanup on successful login). **(2) `api/admin-pass.php`** — CLI helper (CLI-only guard + blocked by `.htaccess`) that prints the hash to paste into the config; never stores the password. **(3) `scripts/validate.php`** — pre-deploy validation (local Docker **and** Hostinger): PHP ≥ 8.2 + extensions, config shape (no values printed), DB + all 5 tables + 4 price tiers, endpoint behaviour (quote prices; register refusing 503 in preview/closed — probe skipped when open so it can never create a live order; status rejecting unknown refs), admin console reachable + leaking no data pre-login, cron file present + web-blocked, internal files 404 (this caught `config.sample.php` actually being served — now guarded like `_bootstrap.php`), Coaxlure host reachability with an EMPTY request (no credentials), static pages, exit code 1 on failure. `scripts/check-i18n.js` also moved out of the machine-local `.claude/` folder (now git-ignored wholesale) and gained events-page + runtime-key coverage. `robots.txt` now disallows `/admin/`. Verification (all live): 51/51 validate checks, PHP lint clean, login/logout/recheck/filters/CSV/lockout/CSRF-rejection behaviours confirmed with curl, dashboard shows the 12 test registrations and the correct ₹ summary. Coaxlure credentials: nothing in the repo, `git status` clean of secrets, config sample carries only placeholder values.

- **2026-10-23 — Preview mode: progress demo without opening payments:** Registration now has THREE states instead of an on/off boolean: `'closed'`, `'preview'`, `'open'`. `js/event-config.js` gained `registrationMode: 'preview'` (the committed default), and — more importantly — the **server got its own switch**: `registration => ['mode' => ...]` in `owli-config.php` (mirrored in `api/config.sample.php`), enforced by a new `owli_registration_mode()` in `api/_bootstrap.php`. In `preview`: `api/quote.php` returns real prices (`mode:"preview"`, `open:false`), the form is explorable, the "Proceed to Payment" button is disabled, and `api/register.php` refuses with 503 `not_open` — so flipping the JS flag alone can never open payments. In `open` mode BOTH switches must agree (`clientMode() === 'open' && serverMode === 'open'` in `js/event-register.js`); `closed` keeps the legacy behaviour. New bilingual keys (`evf_preview_note`, `ev_reg_*_preview`, `ev_banner_status_preview`) in EN+TA with i18n parity verified (346/346); cache versions bumped (`event-config.js?v=6`, `i18n.js?v=3`, `event-register.js?v=3`). Stale "Razorpay" comments in both HTML pages were corrected to describe the Coaxlure-era mode system. Live-verified: quote returns prices with `mode:preview`, register returns 503 with the preview message, status.php and cron-recheck.php unaffected. This is the state to commit and deploy so the public site shows real progress (prices, agenda, form) while the ₹10 payment test remains pending. The earlier plan (fixing the blank `https://app.coaxlure.com/customer/...` redirect) is explicitly deferred per the owner: verify later with proper credentials and a real ₹10 test; no Coaxlure credentials are ever committed.

- **2026-10-23 — Registration form: Full name and Mobile number marked as mandatory:** Both fields were already enforced at every layer — HTML `required`, client validation in `js/event-register.js` (name ≥ 2 chars; mobile must be a 10-digit Indian number after stripping `+91`/`0`), `api/register.php` returning HTTP 422 for missing/whitespace/invalid values, and `NOT NULL` columns in `sql/schema.sql` (re-verified live against the dev Coaxlure API: every negative case rejected). What was missing was the visual cue: the events form labelled only the *optional* fields ("(optional)"), so required fields looked no different. The `evf_name`/`evf_mobile` labels now end with `*` — "Full name *", "Mobile number *", and their Tamil equivalents — in `js/i18n.js` and in the `events/index.html` fallback text, matching the contact form's convention. `js/i18n.js` also gained its first cache-buster (`?v=1` in both pages); it previously had none even though repeat visitors hold the pre-event version cached, which is the stale-asset failure README §9 warns about.

- **2026-10-23 — Local credentials set up for pre-commit testing:** Local secrets moved to `/home/<you>/work/owli-config.php` (outside both repos, git-ignored) and the `owli-web` container recreated so it mounts that file **read-only**. The local DB user is now `owliuser` — a new least-privilege MariaDB account (SELECT/INSERT/UPDATE on `owli` only) instead of root. The config targets the real Coaxlure **dev** server with the TEST credentials from `work/coaxlure/test-coaxlure-business-api-credentials.csv` (CSVs chmod 600). The full registration flow was re-verified through the local site against the real dev API: quote/register/status/cron endpoints, pricing, validation, honeypot, pending-row reuse with re-pricing, host allowlist, and guarded files. The dev server's payment links use `https://app.coaxlure.com/...`, matching both the server allowlist and the client-side check. Test rows created during verification were deleted from the local DB.

- **Local registration setup and status:** Docker runs `owli-web` (site at `http://localhost:8800/`, event page `/events/`), `owli-db` (MariaDB, port 3306 internal only) and `owli-mock` (offline Coaxlure fake). Local secrets live in `/home/<you>/work/owli-config.php` — outside both repos, git-ignored, mounted **read-only** into `owli-web` as `/srv/owli-config.php`; the Coaxlure credential CSVs sit in `work/coaxlure/` with `600` permissions. The DB user is now the least-privilege `owliuser` (SELECT/INSERT/UPDATE on `owli` only), not root. The config points at the **real Coaxlure dev server** with the TEST credentials; switch `api_base` to `http://owli-mock:8081/merchant/business-api` for offline mock tests. `static_token` remains empty (test credentials include a Secret and `/auth` works). Registration remains closed by default (`registrationOpen: false`). Never copy local values to Hostinger.

- **Event registration form + Coaxlure payment:** `/events/#register-form` (`js/event-register.js`, bilingual `evf_*` keys) with category-specific fields from `Category.docx`. The attendee picks a category; prices (Student ₹299, BNI ₹399, Doctor ₹399, General ₹449, no GST) are shown as a list from `api/quote.php?category=all` and recalculated by `api/register.php` — the browser never sends an amount. Backend is PHP + MySQL (`api/`, `sql/schema.sql`); tiers and coupons are database rows, so prices/offers change without code edits. Payment is verified server-side via Coaxlure `GET /payment-status` (on return from checkout + `api/cron-recheck.php`); no signed webhook is documented. Secrets live in `owli-config.php` outside `public_html` (git-ignored). **Current committed client state: `registrationMode: 'open'`** — but the form only unlocks when the server switch (`owli-config.php` → `registration.mode`) also says `'open'`; while the server says `'preview'` (current live state), registrations are still refused with 503 `not_open`. Verified locally with Docker (PHP 8.2 + MariaDB). The real Coaxlure **dev** API has now been exercised end-to-end from the local site (2026-10-23): `/auth` issues a token, `create-order` returns the correct `totalAmount`, `payment-link` returns an `https://app.coaxlure.com/...` URL, and `payment-status` reports `PENDING` for an unpaid order. Still pending: a real (₹10) test payment to confirm the paid `paymentStatus` wording that `coax_fetch_status()` matches on `paid` (case-insensitive). See README §10 for go-live steps.

- **Arokya Payanam 2026 event:** `/events/` page (`events/index.html`, `css/events.css`) with event details and bilingual `ev_*` translations, linked from the homepage. Registration is controlled by `js/event-config.js` **plus the independent server switch** (`owli-config.php` → `registration.mode`; see `GO-LIVE-STEPS.md`): the client mode sets the buttons/form UI state, but registrations are only accepted when the server switch itself says `'open'`. The committed client mode is currently `'open'` (the go-live flip); `registrationUrl` is only a legacy external-link override and must stay empty for the built-in PHP/MySQL form.
  - **Homepage announcement:** A centered modal popup (`css/event-banner.css` `.event-modal-overlay`/`.event-modal`, `js/event-banner.js`) appears ~800ms after the homepage loads — dark backdrop, the event logo, supporting text, and a "View event details" button. Closes via the × button, a backdrop click, or Escape; locks background scroll while open; stays hidden for 24h after closing (localStorage) and stops appearing entirely after `OWLI_EVENT.endDate`.
  - **Event artwork:** Official "Arokya Payanam 2026" logo (`images/events/arokya-payanam-logo.webp`) — the centerpiece of both the homepage popup and the events-page hero — and the organiser/association logo strip (INYGMA, AR Group of Institutions, SKT Nature Cure & Yoga Hospital, Owl I) at `images/events/organisers.png`. Both shown in white rounded cards so the white-background artwork doesn't clash with colored section backgrounds. A `.sr-only` utility (`css/styles.css`) keeps the organiser names readable to screen readers/SEO even though they're now conveyed by the logo image.
  - **Cache-busting:** `css/event-banner.css`, `js/event-banner.js`, and `js/event-config.js` are linked with a `?v=2` query string in `index.html`. Bump that number (`v=3`, `v=4`, …) any time one of those three files is edited again — without it, browsers that already cached an older version of the file may keep using the stale copy indefinitely, which previously made the popup render as plain unstyled content at the bottom of the page instead of the centered modal.
  - **Persistent homepage CTAs:** Since the popup is closable (and stays hidden for 24h after that), two always-visible links to `/events/` were added so the event isn't only reachable while the popup happens to be showing: a third hero button ("Arokya Payanam 2026", `.btn-event`, calendar icon) next to "Enquire on WhatsApp" / "Explore Services", and a matching button in the header nav (`.nav-event`) beside "Book Now". Both reuse the `.btn-event` class (`--color-accent` brown background) so the event CTA is visually distinct from the green site CTAs everywhere it appears — nav, hero, and the popup.

- **2026-10-23 — Lucide CDN removed:** `index.html` no longer loads `https://unpkg.com/lucide@latest`. It was a dead dependency — nothing in the codebase ever called `lucide.createIcons()` or any Lucide API (all icons are hand-written inline SVGs). Removing it drops one render-blocking third-party request, eliminates the unpinned `@latest` supply-chain risk, and keeps the page working even if unpkg is unreachable. **Do not re-add an icon CDN** unless a real need appears; add icons as inline SVGs instead.

- **2026-10-10 (B1 + B2):** Treatment "View Details" panels added to all 11 service cards (`js/treatment-details.js`, clinic-approved wording only, no durations/fees). Booking form softened: hero/CTA "Book Appointment" → "Enquire on WhatsApp"; submit → "Request an Appointment"; Email/Message optional; 3 new dropdown options; bilingual confirmation note + success message.
