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
├── admin/index.php            # Password-protected staff console: registrations, statuses, recheck, CSV export
├── api/                        # PHP backend: register.php, quote.php, status.php, cron-recheck.php, _bootstrap.php, admin-pass.php
├── scripts/                    # validate.php (pre-deploy checks) + check-i18n.js (EN/TA parity)
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

- **2026-10-24 — Live test payment set to Rs 10; online minimum lowered to Rs 1:** The real-money verification step (README §10 go-live step 5) now uses **Rs 10** (`1000` paise) instead of Rs 100. `api/register.php` used to refuse every server price below `10000` paise (Rs 100) with `amount_too_low`, which made a cheap live test impossible; the floor is now **`100` paise (Rs 1)** — Coaxlure's own minimum — so the Rs 10 tier passes and Rs 0 remains impossible (no free-ticket flow; anything below Rs 1 cannot be paid online at all). Real ticket prices stay controlled by the `tiers` table. Verified locally against the real dev Coaxlure API with the server mode temporarily `open`: a `general` tier at `1000` paise completes registration and creates a real Rs 10 dev order (amount cross-check passes, `app.coaxlure.com` payment link returned), while a `50`-paise tier is still refused with `amount_too_low`; tier and mode were restored afterwards and the test rows deleted. README §10, PRD.md and the mode-box comment in `js/event-config.js` now consistently say Rs 10 / `1000` paise. **This change must be deployed to Hostinger before the Rs 10 test** — until the deploy lands, the live `register.php` still refuses with `amount_too_low`.

- **2026-10-24 — Production deployment on Hostinger: COMPLETE (all checklist steps verified).** The Arokya Payanam 2026 backend is now live on `owlihealthcare.com`. Verified state, in order: **(1) DB** — `u810328541_owlievents` (user `u810328541_owli`) created in hPanel, `sql/schema.sql` imported via phpMyAdmin; all 5 tables + 4 price tiers present. **(2) Config** — `owli-config.php` placed one level above `public_html` with the live DB credentials and live Coaxlure credentials; the live `quote.php` flipped from `mode:"closed"` to `mode:"preview"` with all four prices, proving the DB connection and config location on the first request after saving (no deploy needed). **(3) Validation** — `scripts/validate.php` run on the server: 51/51 after the 403/404 fix (see the entry below); independent external probes matched. **(4) Admin console** — `/admin/` login, dashboard and Export CSV verified by the owner (Step 6); password hash lives only in the git-ignored server config. **(5) Cron** — hPanel Cron Jobs job created with `/usr/bin/php /home/u810328541/domains/owlihealthcare.com/public_html/api/cron-recheck.php` every 5 minutes; the command was proven in the hPanel Terminal first (`checked=0 newly_paid=0` — correct for an empty registrations table), and the hPanel cron page itself displays each run's output, so no log redirect is needed; the command was left unchanged. **(6) Public site** — `/`, `/events/` 200 in preview (prices visible, Pay button disabled), `register.php` still refuses with 503 `not_open` (payments safely off), `status.php` rejects unknown refs, internal API files 403, `robots.txt` blocks `/admin/`, http→https 301 works. Remaining before registration can open: the **₹10 real payment test** with live credentials to confirm Coaxlure's paid `paymentStatus` wording (README §10 step 5), then flip both switches to `'open'` (server config first, then `js/event-config.js` with a `?v=` bump). Known open content item: `images/og-image.jpg` is still missing (404) — social shares show no image until the clinic supplies one. Minor SEO note recorded: `www.` serves the same site as the apex domain without a redirect (canonical tags point to the apex, so this is cosmetic).

- **2026-10-24 — First live Hostinger validation (47/51) + validator fix:** Ran `scripts/validate.php` on the real Hostinger server right after the hPanel setup (DB `u810328541_owlievents` imported, `owli-config.php` in place with live Coaxlure credentials, `preview` mode). Everything passed except the four "blocked from the web" checks, which failed **only because of an assertion bug in the validator**: Hostinger's Apache honours `api/.htaccess` and answers `403` (denied before PHP even runs), while the validator asserted `404` — the code path the local PHP dev server takes via the files' own self-guard. The files were in fact protected on both stacks (verified with direct probes: live = 403 with a static Apache error page, local = 404/503 from the self-guard). Fix: the four checks now accept **403 or 404** (documented at the head of the script); anything else — including 200 — still fails. The live run also confirmed the real environment end-to-end: PHP 8.3.35, MariaDB 11.8.9, all 5 tables, all 4 price tiers via `quote.php` (preview mode), `register.php` refusing with 503, `status.php` rejecting unknown refs, `/admin/` showing a password form with zero attendee data pre-login, Coaxlure live host reachable, site pages 200. 51/51 after the fix. Also updated the top-of-script check-list comment.

- **2026-10-23 — Admin console + validation suite + repo cleanup (this push):** Three new pieces ship together as the pre-commit state. **(1) `admin/index.php`** — a password-protected staff console at `/admin/` (unlinked, `robots.txt`-excluded, English-only internal tool): summary cards (total/paid/pending/failed/collected/awaiting), a paginated registration table with status pills, expandable rows showing category details + payment-attempt history with the raw Coaxlure response, filters (status/category/search across reference, name, mobile, email), a **"Recheck payment now"** button per pending registration (calls Coaxlure immediately instead of waiting for the 5-minute cron), and **Export CSV** honouring the active filters (UTF-8 BOM for Excel, built with `fputcsv` — never hand-concatenated). Auth: the password lives ONLY as a `password_hash()` in `owli-config.php` (`'admin' => ['password_hash' => …]`); empty hash = console disabled with a 403 and no login form at all. Sessions are httponly/SameSite=Lax (+secure on HTTPS), the id is regenerated on login, every POST is CSRF-checked, failures cost 1s each and 5 failures/15min lock the IP (counters in the `settings` table, so the throttle survives across processes). The local DB user needed one extra grant: `DELETE ON owli.settings` (throttle cleanup on successful login). **(2) `api/admin-pass.php`** — CLI helper (CLI-only guard + blocked by `.htaccess`) that prints the hash to paste into the config; never stores the password. **(3) `scripts/validate.php`** — pre-deploy validation (local Docker **and** Hostinger): PHP ≥ 8.2 + extensions, config shape (no values printed), DB + all 5 tables + 4 price tiers, endpoint behaviour (quote prices; register refusing 503 in preview/closed — probe skipped when open so it can never create a live order; status rejecting unknown refs), admin console reachable + leaking no data pre-login, cron file present + web-blocked, internal files 404 (this caught `config.sample.php` actually being served — now guarded like `_bootstrap.php`), Coaxlure host reachability with an EMPTY request (no credentials), static pages, exit code 1 on failure. `scripts/check-i18n.js` also moved out of the machine-local `.claude/` folder (now git-ignored wholesale) and gained events-page + runtime-key coverage. `robots.txt` now disallows `/admin/`. Verification (all live): 51/51 validate checks, PHP lint clean, login/logout/recheck/filters/CSV/lockout/CSRF-rejection behaviours confirmed with curl, dashboard shows the 12 test registrations and the correct ₹ summary. Coaxlure credentials: nothing in the repo, `git status` clean of secrets, config sample carries only placeholder values.

- **2026-10-23 — Preview mode: progress demo without opening payments:** Registration now has THREE states instead of an on/off boolean: `'closed'`, `'preview'`, `'open'`. `js/event-config.js` gained `registrationMode: 'preview'` (the committed default), and — more importantly — the **server got its own switch**: `registration => ['mode' => ...]` in `owli-config.php` (mirrored in `api/config.sample.php`), enforced by a new `owli_registration_mode()` in `api/_bootstrap.php`. In `preview`: `api/quote.php` returns real prices (`mode:"preview"`, `open:false`), the form is explorable, the "Proceed to Payment" button is disabled, and `api/register.php` refuses with 503 `not_open` — so flipping the JS flag alone can never open payments. In `open` mode BOTH switches must agree (`clientMode() === 'open' && serverMode === 'open'` in `js/event-register.js`); `closed` keeps the legacy behaviour. New bilingual keys (`evf_preview_note`, `ev_reg_*_preview`, `ev_banner_status_preview`) in EN+TA with i18n parity verified (346/346); cache versions bumped (`event-config.js?v=5`, `i18n.js?v=2`, `event-register.js?v=2`). Stale "Razorpay" comments in both HTML pages were corrected to describe the Coaxlure-era mode system. Live-verified: quote returns prices with `mode:preview`, register returns 503 with the preview message, status.php and cron-recheck.php unaffected. This is the state to commit and deploy so the public site shows real progress (prices, agenda, form) while the ₹10 payment test remains pending. The earlier plan (fixing the blank `https://app.coaxlure.com/customer/...` redirect) is explicitly deferred per the owner: verify later with proper credentials and a real ₹10 test; no Coaxlure credentials are ever committed.

- **2026-10-23 — Registration form: Full name and Mobile number marked as mandatory:** Both fields were already enforced at every layer — HTML `required`, client validation in `js/event-register.js` (name ≥ 2 chars; mobile must be a 10-digit Indian number after stripping `+91`/`0`), `api/register.php` returning HTTP 422 for missing/whitespace/invalid values, and `NOT NULL` columns in `sql/schema.sql` (re-verified live against the dev Coaxlure API: every negative case rejected). What was missing was the visual cue: the events form labelled only the *optional* fields ("(optional)"), so required fields looked no different. The `evf_name`/`evf_mobile` labels now end with `*` — "Full name *", "Mobile number *", and their Tamil equivalents — in `js/i18n.js` and in the `events/index.html` fallback text, matching the contact form's convention. `js/i18n.js` also gained its first cache-buster (`?v=1` in both pages); it previously had none even though repeat visitors hold the pre-event version cached, which is the stale-asset failure README §9 warns about.

- **2026-10-23 — Local credentials set up for pre-commit testing:** Local secrets moved to `/home/<you>/work/owli-config.php` (outside both repos, git-ignored) and the `owli-web` container recreated so it mounts that file **read-only**. The local DB user is now `owliuser` — a new least-privilege MariaDB account (SELECT/INSERT/UPDATE on `owli` only) instead of root. The config targets the real Coaxlure **dev** server with the TEST credentials from `work/coaxlure/test-coaxlure-business-api-credentials.csv` (CSVs chmod 600). The full registration flow was re-verified through the local site against the real dev API: quote/register/status/cron endpoints, pricing, validation, honeypot, pending-row reuse with re-pricing, host allowlist, and guarded files. The dev server's payment links use `https://app.coaxlure.com/...`, matching both the server allowlist and the client-side check. Test rows created during verification were deleted from the local DB.

- **Local registration setup and status:** Docker runs `owli-web` (site at `http://localhost:8800/`, event page `/events/`), `owli-db` (MariaDB, port 3306 internal only) and `owli-mock` (offline Coaxlure fake). Local secrets live in `/home/<you>/work/owli-config.php` — outside both repos, git-ignored, mounted **read-only** into `owli-web` as `/srv/owli-config.php`; the Coaxlure credential CSVs sit in `work/coaxlure/` with `600` permissions. The DB user is now the least-privilege `owliuser` (SELECT/INSERT/UPDATE on `owli` only), not root. The config points at the **real Coaxlure dev server** with the TEST credentials; switch `api_base` to `http://owli-mock:8081/merchant/business-api` for offline mock tests. `static_token` remains empty (test credentials include a Secret and `/auth` works). Registration remains closed by default (`registrationOpen: false`). Never copy local values to Hostinger.

- **Event registration form + Coaxlure payment:** `/events/#register-form` (`js/event-register.js`, bilingual `evf_*` keys) with category-specific fields from `Category.docx`. The attendee picks a category; prices (Student ₹299, BNI ₹399, Doctor ₹399, General ₹449, no GST) are shown as a list from `api/quote.php?category=all` and recalculated by `api/register.php` — the browser never sends an amount. Backend is PHP + MySQL (`api/`, `sql/schema.sql`); tiers and coupons are database rows, so prices/offers change without code edits. Payment is verified server-side via Coaxlure `GET /payment-status` (on return from checkout + `api/cron-recheck.php`); no signed webhook is documented. Secrets live in `owli-config.php` outside `public_html` (git-ignored). The form stays locked while `registrationOpen` is `false` in `js/event-config.js`. Verified locally with Docker (PHP 8.2 + MariaDB). The real Coaxlure **dev** API has now been exercised end-to-end from the local site (2026-10-23): `/auth` issues a token, `create-order` returns the correct `totalAmount`, `payment-link` returns an `https://app.coaxlure.com/...` URL, and `payment-status` reports `PENDING` for an unpaid order. Still pending: a real (₹10) test payment to confirm the paid `paymentStatus` wording that `coax_fetch_status()` matches on `paid` (case-insensitive). See README §10 for go-live steps.

- **Arokya Payanam 2026 event:** `/events/` page (`events/index.html`, `css/events.css`) with event details and bilingual `ev_*` translations, linked from the homepage. Registration is controlled by `js/event-config.js`: `registrationOpen: false` keeps registration CTAs disabled; when intentionally enabled, CTAs point to the built-in registration form at `/events/#register-form`. `registrationUrl` is only a legacy external-link override; do not use it for the PHP/MySQL form.
  - **Homepage announcement:** A centered modal popup (`css/event-banner.css` `.event-modal-overlay`/`.event-modal`, `js/event-banner.js`) appears ~800ms after the homepage loads — dark backdrop, the event logo, supporting text, and a "View event details" button. Closes via the × button, a backdrop click, or Escape; locks background scroll while open; stays hidden for 24h after closing (localStorage) and stops appearing entirely after `OWLI_EVENT.endDate`.
  - **Event artwork:** Official "Arokya Payanam 2026" logo (`images/events/arokya-payanam-logo.webp`) — the centerpiece of both the homepage popup and the events-page hero — and the organiser/association logo strip (INYGMA, AR Group of Institutions, SKT Nature Cure & Yoga Hospital, Owl I) at `images/events/organisers.png`. Both shown in white rounded cards so the white-background artwork doesn't clash with colored section backgrounds. A `.sr-only` utility (`css/styles.css`) keeps the organiser names readable to screen readers/SEO even though they're now conveyed by the logo image.
  - **Cache-busting:** `css/event-banner.css`, `js/event-banner.js`, and `js/event-config.js` are linked with a `?v=2` query string in `index.html`. Bump that number (`v=3`, `v=4`, …) any time one of those three files is edited again — without it, browsers that already cached an older version of the file may keep using the stale copy indefinitely, which previously made the popup render as plain unstyled content at the bottom of the page instead of the centered modal.
  - **Persistent homepage CTAs:** Since the popup is closable (and stays hidden for 24h after that), two always-visible links to `/events/` were added so the event isn't only reachable while the popup happens to be showing: a third hero button ("Arokya Payanam 2026", `.btn-event`, calendar icon) next to "Enquire on WhatsApp" / "Explore Services", and a matching button in the header nav (`.nav-event`) beside "Book Now". Both reuse the `.btn-event` class (`--color-accent` brown background) so the event CTA is visually distinct from the green site CTAs everywhere it appears — nav, hero, and the popup.

- **2026-10-23 — Lucide CDN removed:** `index.html` no longer loads `https://unpkg.com/lucide@latest`. It was a dead dependency — nothing in the codebase ever called `lucide.createIcons()` or any Lucide API (all icons are hand-written inline SVGs). Removing it drops one render-blocking third-party request, eliminates the unpinned `@latest` supply-chain risk, and keeps the page working even if unpkg is unreachable. **Do not re-add an icon CDN** unless a real need appears; add icons as inline SVGs instead.

- **2026-10-10 (B1 + B2):** Treatment "View Details" panels added to all 11 service cards (`js/treatment-details.js`, clinic-approved wording only, no durations/fees). Booking form softened: hero/CTA "Book Appointment" → "Enquire on WhatsApp"; submit → "Request an Appointment"; Email/Message optional; 3 new dropdown options; bilingual confirmation note + success message.
