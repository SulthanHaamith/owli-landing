# CLAUDE.md — Owl I Health Care Centre

> **This file is the single source of truth for building the clinic's landing page.**
> Claude Code should read this before every task. All decisions, content, and structure live here.

---

## 1. PROJECT SUMMARY

**What:** A static landing page for a naturopathy clinic in Tirunelveli, Tamil Nadu, India.
**Goal:** Get the clinic online quickly with a professional, fast-loading, bilingual (English/Tamil) website.
**Approach:** Pure static site — no backend, no database, no authentication. Upload-and-go.

---

## 2. TECH STACK

| Layer       | Choice                | Notes                                          |
|-------------|-----------------------|------------------------------------------------|
| Markup      | Plain HTML5           | Semantic elements, no templating engine         |
| Styling     | Hand-written CSS      | Custom properties for theming, separate responsive file |
| JavaScript  | Vanilla JS (ES6+)     | No libraries, no bundler                       |
| Icons       | Inline SVGs + Lucide CDN | `https://unpkg.com/lucide@latest` as fallback |
| Fonts       | Google Fonts (`<link>`)| Poppins (EN), Noto Sans Tamil (TA)             |
| Animations  | CSS-only + IntersectionObserver | Keyframes, transitions — no JS animation libs |
| Build step  | **None**              | Open `index.html` in browser = done            |
| Hosting     | Hostinger (shared)    | Upload to `public_html/` via File Manager or FTP |

### What is NOT allowed
- No JS frameworks (React, Vue, Angular, Astro, Next.js)
- No build tools (npm, Webpack, Vite, Parcel)
- No server-side runtime (Node.js, PM2, Nginx config)
- No database (PostgreSQL, Prisma, SQLite)
- No authentication system
- No CSS preprocessors (Sass, Less) — plain CSS only
- No Tailwind build step (CDN usage would be acceptable but not planned)

---

## 3. DOMAIN & HOSTING

- **Domain:** owlihealthcare.com (not yet registered — register through Hostinger for free domain with hosting plan)
- **Hosting provider:** Hostinger — Premium Web Hosting plan (includes 1 free domain)
- **Deployment method:** Upload all files to `public_html/` via Hostinger File Manager or FTP
- **SSL:** Free SSL certificate — enable in Hostinger hPanel after domain is active
- **No server-side setup needed:** No Node.js, no PM2, no Nginx config. Just upload static files.

### Domain Registration Steps
1. Purchase Hostinger Premium Web Hosting (or higher) — includes 1 free domain for the first year
2. During checkout, claim free domain: `owlihealthcare.com`
3. If `.com` is taken, fallback options: `owlihealthcare.in`, `owlihealth.com`, `owlihealthcenter.com`
4. After purchase: Hostinger auto-points the domain to hosting — no manual DNS setup needed
5. Enable free SSL in hPanel → Security → SSL

### Deployment Steps
1. Open Hostinger hPanel → Files → File Manager
2. Navigate to `public_html/`
3. Delete default files (index.html, .htaccess if present)
4. Upload entire project folder contents (index.html, css/, js/, images/, etc.) directly into `public_html/`
5. Verify: visit `https://owlihealthcare.com` — site should be live

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
│   └── event-banner.js         # Homepage event popup (show/close, 24h dismissal)
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
2. **Pure static output** — the final site must work by opening `index.html` in a browser. No build step required in production.
3. **All text bilingual** — every display string goes through the i18n system.
4. **Mark every placeholder** with `<!-- [PLACEHOLDER] -->` comments.
5. **Mobile-first** — base styles target mobile, enhance with `min-width` media queries.
6. **WhatsApp + Phone are the primary CTAs** — booking, contact, floating buttons all route to WhatsApp and direct call.
7. **Document thoroughly** — a beginner who has never coded should understand every line.
8. **Test mentally at 375px, 768px, and 1440px** widths.
9. **No hardcoded colors** — use CSS custom properties everywhere.
10. **Semantic HTML** — proper landmark elements, ARIA labels, accessibility.

## Change Log

- **Arokya Payanam 2026 event:** New `/events/` page (`events/index.html`, `css/events.css`) with full event details, bilingual (`ev_*` keys in `i18n.js`), plus one footer link on the homepage. Registration state is controlled from one place, `js/event-config.js`: while `registrationUrl` is empty, every registration label/button site-wide says "Registration Opens Soon"; paste the Razorpay link there and push to switch everything to "Register Now".
  - **Homepage announcement:** A centered modal popup (`css/event-banner.css` `.event-modal-overlay`/`.event-modal`, `js/event-banner.js`) appears ~800ms after the homepage loads — dark backdrop, the event logo, supporting text, and a "View event details" button. Closes via the × button, a backdrop click, or Escape; locks background scroll while open; stays hidden for 24h after closing (localStorage) and stops appearing entirely after `OWLI_EVENT.endDate`.
  - **Event artwork:** Official "Arokya Payanam 2026" logo (`images/events/arokya-payanam-logo.webp`) — the centerpiece of both the homepage popup and the events-page hero — and the organiser/association logo strip (INYGMA, AR Group of Institutions, SKT Nature Cure & Yoga Hospital, Owl I) at `images/events/organisers.png`. Both shown in white rounded cards so the white-background artwork doesn't clash with colored section backgrounds. A `.sr-only` utility (`css/styles.css`) keeps the organiser names readable to screen readers/SEO even though they're now conveyed by the logo image.
  - **Cache-busting:** `css/event-banner.css`, `js/event-banner.js`, and `js/event-config.js` are linked with a `?v=2` query string in `index.html`. Bump that number (`v=3`, `v=4`, …) any time one of those three files is edited again — without it, browsers that already cached an older version of the file may keep using the stale copy indefinitely, which previously made the popup render as plain unstyled content at the bottom of the page instead of the centered modal.
  - **Persistent homepage CTAs:** Since the popup is closable (and stays hidden for 24h after that), two always-visible links to `/events/` were added so the event isn't only reachable while the popup happens to be showing: a third hero button ("Arokya Payanam 2026", `.btn-event`, calendar icon) next to "Enquire on WhatsApp" / "Explore Services", and a matching button in the header nav (`.nav-event`) beside "Book Now". Both reuse the `.btn-event` class (`--color-accent` brown background) so the event CTA is visually distinct from the green site CTAs everywhere it appears — nav, hero, and the popup.

- **2026-10-10 (B1 + B2):** Treatment "View Details" panels added to all 11 service cards (`js/treatment-details.js`, clinic-approved wording only, no durations/fees). Booking form softened: hero/CTA "Book Appointment" → "Enquire on WhatsApp"; submit → "Request an Appointment"; Email/Message optional; 3 new dropdown options; bilingual confirmation note + success message.
