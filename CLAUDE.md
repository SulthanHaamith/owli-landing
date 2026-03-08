# CLAUDE.md — Owl I Health and Wellness Center

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
Name:      Owl I Health and Wellness Center
Address:   1/120G1, Vishalachi Nagar, Kondanagaram, Suthamalli, Tirunelveli - 627604
Phone:     +91 6379610554
WhatsApp:  +91 6379610554
Email:     owli2026@gmail.com
Hours:     Monday–Saturday, 9:00 AM – 9:00 PM
           Sunday: Closed (or by appointment)
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
| 6  | Doctors           | `#doctors`          | Cream              |
| 7  | Testimonials      | `#testimonials`     | White              |
| 8  | Gallery           | `#gallery`          | Cream              |
| 9  | Blog Preview      | `#blog`             | White              |
| 10 | CTA Banner        | `#cta-banner`       | Dark green (`--color-primary`) |
| 11 | Contact / Booking | `#contact`          | Cream              |
| 12 | Footer            | `#footer`           | Dark green (`--color-primary`) |
| —  | Floating Buttons  | Fixed bottom-right  | WhatsApp + Phone call, stacked vertically |

---

## 9. SECTION CONTENT DETAILS

### 9.1 Header / Nav
- Text logo: "Owl I" + small leaf/owl inline SVG
- Nav links: Home, About, Services, Doctors, Gallery, Blog, Contact
- "Book Now" CTA button (green) → scrolls to `#contact`
- Language toggle: `EN | தமிழ்`
- Mobile: hamburger → slide-in drawer
- Sticky on scroll with subtle box-shadow

### 9.2 Hero
- Full viewport height (100vh)
- Background: placeholder (gradient or CSS pattern). Mark for replacement.
- Dark overlay for text contrast
- **Heading EN:** "Heal Naturally. Live Fully."
- **Heading TA:** "இயற்கையாக குணமாகுங்கள். முழுமையாக வாழுங்கள்."
- **Subheading EN:** "Tirunelveli's trusted naturopathy and wellness center — holistic healing for body, mind, and soul."
- Two CTAs: "Book Appointment" (→ WhatsApp) + "Explore Services" (→ `#services`)
- CSS fade-in animation on load

### 9.3 About
- Two-column: text left, image placeholder right
- Philosophy: Drug-free healing, holistic approach, personalized plans, traditional + modern
- Brief Vision & Mission statements
- `<!-- [PLACEHOLDER] Replace with clinic interior photo -->`

### 9.4 Services
- Grid: 3 col desktop, 2 tablet, 1 mobile
- 15 cards with icon + name + 1-line description
- Hover: translateY(-5px) + box-shadow

**Full service list:**

| #  | Service                           | Description (EN)                                      |
|----|-----------------------------------|-------------------------------------------------------|
| 1  | Hydrotherapy                      | Hip bath, Spinal bath, Steam bath, Enema therapy      |
| 2  | Mud Therapy                       | Mud pack, Mud bath, Clay applications                 |
| 3  | Diet Therapy / Nutrition Counseling | Therapeutic fasting, Detox diets, Personalized meal plans |
| 4  | Massage Therapy                   | Full body oil massage, Therapeutic massage, Reflexology |
| 5  | Yoga Therapy                      | Asanas, Pranayama, Meditation, Therapeutic yoga       |
| 6  | Acupuncture / Acupressure        | Pain management, Meridian therapy                     |
| 7  | Magnetotherapy                    | Magnetic field therapy for chronic pain               |
| 8  | Chromo Therapy                    | Light and color-based healing                         |
| 9  | Detox & Panchakarma Programs     | 7-day, 14-day, 21-day packages                       |
| 10 | Weight Management                 | Diet + Exercise + Therapy combo                       |
| 11 | Stress & Anxiety Management       | Yoga + Counseling + Relaxation therapies              |
| 12 | Chronic Pain Management           | Back pain, Arthritis, Migraine                        |
| 13 | Women's Wellness                  | PCOS, Menstrual health, Prenatal care                 |
| 14 | Skin & Hair Care                  | Eczema, Psoriasis, Hair fall treatments               |
| 15 | Diabetes & Lifestyle Disease Mgmt | Holistic management of lifestyle diseases             |

### 9.5 Why Choose Us
- Light green background section
- 4 items (responsive: 4 col → 2x2 → stacked):
  1. Experienced BNYS Doctors
  2. 100% Natural Treatments
  3. Personalized Care Plans
  4. Affordable Wellness

### 9.6 Doctors
- 3 profile cards — **ALL PLACEHOLDER**
- Mark every detail: `<!-- [PLACEHOLDER] Replace with real doctor details -->`

| #  | Role                           | Qualification                     | Experience | Specialization                        |
|----|--------------------------------|-----------------------------------|------------|---------------------------------------|
| 1  | Chief Naturopathy Physician    | BNYS                              | 8+ years   | Chronic disease management, Detox     |
| 2  | Yoga & Rehabilitation Specialist | BNYS, Certified Yoga Therapist  | 5+ years   | Musculoskeletal disorders, Stress mgmt |
| 3  | Nutrition & Diet Therapist     | BNYS, MSc Nutrition               | 4+ years   | Weight management, Diabetes reversal  |

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

### 9.10 CTA Banner
- Full-width, dark green background
- **EN:** "Ready to Start Your Natural Healing Journey?"
- **TA:** "உங்கள் இயற்கை குணமாகும் பயணத்தைத் தொடங்க தயாரா?"
- Buttons: "Book Appointment" → WhatsApp, "Call Now" → `tel:+916379610554`

### 9.11 Contact / Booking
- **Left column — Form:**
  - Fields: Name, Phone, Email, Service (dropdown of 15 services), Message
  - Validation: required fields, email regex, Indian phone format (10 digits, starts 6-9)
  - Submit action: open WhatsApp with pre-filled message
  - URL format: `https://wa.me/916379610554?text=Name: ...%0APhone: ...%0AService: ...%0AMessage: ...`
  - Fallback: mailto link to owli2026@gmail.com
- **Right column — Info:**
  - Address, Phone (tel: link), WhatsApp (wa.me link), Email (mailto: link), Hours
  - Google Maps embed (Tirunelveli coordinates)

### 9.12 Footer
- Dark green background, light text
- 4-column grid:
  1. Logo + brief about + social icons (Instagram, Facebook, YouTube — placeholder `#` links)
  2. Quick Links (anchor links)
  3. Top 6 Services
  4. Contact info + Hours
- Bottom bar: `© 2026 Owl I Health and Wellness Center. All Rights Reserved.`

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
- ARIA label: "Call Owl I Health and Wellness Center"

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
- `<title>`: Owl I Health and Wellness Center | Naturopathy Clinic in Tirunelveli
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

| Item                        | Location              | Recommended Size / Format  |
|-----------------------------|-----------------------|----------------------------|
| Hero background image       | `images/hero/`        | 1920×800px, JPG            |
| Clinic interior photo       | `images/`             | 800×600px, JPG             |
| Doctor 1 photo + real name  | `images/doctors/`     | 400×400px, square          |
| Doctor 2 photo + real name  | `images/doctors/`     | 400×400px, square          |
| Doctor 3 photo + real name  | `images/doctors/`     | 400×400px, square          |
| Gallery photos (6–8)        | `images/gallery/`     | 800×600px, JPG             |
| Blog post images (3)        | `images/blog/`        | 600×400px, JPG             |
| OG/social sharing image     | `images/og-image.jpg` | 1200×630px, JPG            |
| Favicon                     | `images/favicon.ico`  | 32×32px or 64×64px         |
| Clinic logo (if not text)   | `images/logo.svg`     | SVG preferred              |
| 5 real patient testimonials | In `i18n.js`          | Text only                  |
| Real doctor names & details | In HTML + `i18n.js`   | Text only                  |
| Social media profile URLs   | Footer links          | Full URLs                  |
| Google Maps embed URL       | Contact section       | Embed iframe URL           |

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
├── css/
│   ├── styles.css              # All base styles, layout, components
│   └── responsive.css          # Media queries for mobile/tablet/desktop
├── js/
│   ├── main.js                 # Nav toggle, sticky header, scroll animations
│   ├── i18n.js                 # Translation object + language toggle logic
│   ├── testimonials.js         # Testimonial carousel (auto-rotate + controls)
│   ├── lightbox.js             # Gallery lightbox (open/close/navigate)
│   └── contact.js              # Form validation + WhatsApp URL builder
├── images/
│   ├── hero/                   # Hero background (1920×800px)
│   ├── services/               # Service icons (SVG preferred)
│   ├── doctors/                # Doctor photos (400×400px, square)
│   ├── gallery/                # Clinic gallery photos (800×600px)
│   ├── blog/                   # Blog thumbnail images (600×400px)
│   ├── logo.svg                # Clinic logo
│   ├── og-image.jpg            # Social sharing image (1200×630px)
│   └── favicon.ico             # Browser tab icon
├── sitemap.xml                 # Single-page sitemap for SEO
├── robots.txt                  # Allow all crawlers
└── README.md                   # Full project documentation
```

---

## 17. CONTACT LINKS REFERENCE

### WhatsApp Links
| Purpose           | URL                                                                                                  |
|--------------------|------------------------------------------------------------------------------------------------------|
| Floating button    | `https://wa.me/916379610554?text=Hi! I'd like to enquire about your services.`                       |
| Book Appointment   | `https://wa.me/916379610554?text=Hi! I'd like to book an appointment.`                               |
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
