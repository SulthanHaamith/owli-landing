# Prompt History — Owl I Health and Wellness Center

This file documents every prompt used during the development of the Owl I Healthcare landing page, along with what was accomplished in each phase.

---

## Phase 1: Project Scaffolding & HTML Structure

**Prompt:**
> Read CLAUDE.md. Do as that file say.

**What was done:**
- Set up the full file structure: `css/`, `js/`, `images/` subdirectories, `sitemap.xml`, `robots.txt`, `README.md`
- Built `index.html` with all 12 sections: header/nav, hero, about, services (15 cards), why-us (4 items), doctors (3 cards), testimonials (5 slides), gallery (6 items), blog (3 cards), CTA banner, contact (form + info), footer
- Added floating action buttons (WhatsApp + Phone)
- SEO meta tags, Open Graph tags, JSON-LD structured data (MedicalBusiness schema)
- 177 `data-i18n` attributes for bilingual support
- 37 `[PLACEHOLDER]` markers for content to be replaced
- 56 ARIA labels for accessibility
- CSS file contains only `:root` custom properties — no styling yet
- JS files are empty stubs — no logic yet

**Commit:** `bdc2e16` — "Add Phase 1: project scaffolding and complete HTML structure"

---

## Phase 2: CSS Styling

**Prompt:**
> Read prompt.md and implement Phase 2 as mentioned.

**What was done:**
- Built `css/styles.css` (1642 lines) with full visual design for all 12 sections following the "Forest Calm" color palette
- CSS reset/normalize, Poppins typography scale
- Sticky transparent-to-white header with box-shadow on scroll
- 100vh hero section with dark overlay and `fadeInUp` animation
- Service cards with hover lift (`translateY(-5px)`)
- Testimonial carousel layout (flex track, overflow hidden)
- Gallery grid with zoom hover effect
- Styled contact form with focus glow states
- Dark green footer with 4-column grid
- Floating WhatsApp/Phone buttons with pulse keyframe animations
- All colors use CSS custom properties — zero hardcoded values
- Built `css/responsive.css` (327 lines) with three breakpoints (1024px, 768px, 480px): hamburger nav drawer, grid column collapses, stacked CTAs, smaller floating buttons for mobile

**Commit:** `cef2c68` — "Add Phase 2: complete CSS styling for all sections"

---

## Phase 3: Mobile-First Responsive CSS

**Prompt:**
> Read prompt.md and implement Phase 3 as mentioned.

**What was done:**
- Rewrote `css/responsive.css` (840 lines) from desktop-first (max-width) to mobile-first (min-width) approach
- Five breakpoints: base (≤480px), 481px (tablet), 769px (small desktop), 1025px (desktop), 1280px (large desktop)
- Mobile base overrides: single-column grids, hamburger slide-in drawer nav, stacked CTAs, smaller typography, tighter section padding
- Each `min-width` query progressively restores multi-column layouts: 2-col at tablet, horizontal nav at 769px, full 3/4-col grids at 1025px
- `prefers-reduced-motion` support for accessibility (disables all animations/transitions)
- WCAG 44px minimum touch targets on all interactive elements

**Commit:** `40be07b` — "Add Phase 3: mobile-first responsive CSS with min-width queries"

---

## Phase 4: JavaScript & i18n System

**Prompt:**
> Read prompt.md and implement Phase 4 as mentioned.

**What was done:**
- Built `js/main.js` (347 lines):
  - Mobile hamburger menu with overlay, focus trap, body scroll lock, Escape key close
  - Sticky header (`.scrolled` class at `scrollY > 50`)
  - Smooth scroll with header offset for anchor links
  - Active nav link highlighting via IntersectionObserver
  - Scroll-triggered fade-in animations on 27 elements (`.animate-on-scroll` → `.visible`)
- Built `js/i18n.js` (562 lines):
  - Complete English/Tamil translations for all 127 unique keys
  - Handles `data-i18n` (textContent), `data-i18n-placeholder` (form inputs), `data-i18n-tooltip` (floating buttons)
  - Persists language choice in `localStorage` (`owli-lang` key)
  - Updates `html lang` attribute and toggles `.lang-ta` body class for Noto Sans Tamil font
- Added supporting CSS: nav overlay, body scroll lock, active nav link style, `.lang-ta` font override, `.animate-on-scroll` with staggered transition delays
- Added `.animate-on-scroll` class to service/doctor/why-us/blog cards and about section in HTML

**Commit:** `4852d31` — "Add Phase 4: navigation JS and bilingual i18n system"

---

## Phase 5: Interactive Components (Carousel, Lightbox, Contact Form)

**Prompt:**
> Read prompt.md and implement Phase 5 as mentioned. Push it to git.

**What was done:**
- Built `js/testimonials.js` (261 lines):
  - Carousel with `translateX` slide transitions
  - 5-second auto-rotation with pause-on-hover
  - Prev/next buttons and dot indicators
  - Touch/swipe support for mobile (50px threshold with vertical scroll detection)
- Built `js/lightbox.js` (351 lines):
  - Dynamically creates all lightbox DOM elements (removed static HTML from Phase 1)
  - Fullscreen overlay with fade transition
  - Prev/next navigation with wrap-around, image counter ("1 / 6")
  - Keyboard support (arrow keys + Escape)
  - Focus trap and returns focus to trigger element on close
  - Handles both real images and placeholder display
- Built `js/contact.js` (400 lines):
  - Validates name (2+ chars), phone (Indian 10-digit starting 6-9 with +91/0 prefix stripping), email (regex), service (required select)
  - Inline error display on blur with clear-on-input
  - Builds WhatsApp URL with encoded form data, opens in new tab
  - Shows success message with mailto fallback link
  - 10-second rate limit with button countdown to prevent spam

**Commit:** `49d4469` — "Add Phase 5: testimonial carousel, gallery lightbox, and contact form"

---

## Phase 6: SEO, README & Project Review

**Prompt:**
> Read prompt.md and implement Phase 6 as mentioned. Push it to git.

**What was done:**
- Rewrote `README.md` (360 lines) with 12 beginner-friendly sections:
  - Project overview, file structure, local viewing instructions
  - How to edit text (i18n system walkthrough with step-by-step examples)
  - How to replace placeholder images (full table with dimensions/formats)
  - How to update doctor info, how to add/edit services
  - Hostinger deployment guide (8 steps)
  - Site update instructions (File Manager + FTP)
  - Complete placeholder checklist (14 items)
  - Browser support table, troubleshooting guide
- Updated `sitemap.xml` changefreq from monthly to weekly
- Full project review completed — verified:
  - All 177 i18n keys match between HTML and JS (both en/ta)
  - No hardcoded colors outside `:root`
  - All section backgrounds correct per CLAUDE.md spec
  - JSON-LD structured data valid
  - All links correct (WhatsApp/phone/email)
  - ARIA labels on all interactive elements
  - Form labels associated, no duplicate IDs
  - All placeholders properly marked
  - No issues found

**Commit:** `242d6c5` — "Add Phase 6: comprehensive README, SEO updates, and project review"

---

## Fix Round 1: Hero Video, Logo Text, Testimonial Alignment

**Prompt:**
> Read prompt.md and implement the fixes. Push it to git.

The prompt.md at that time contained 3 specific fixes:

### FIX 1: Hero Section — Replace solid background with MP4 video background
- Added `<video autoplay muted loop playsinline preload="metadata">` with `poster="images/hero/hero-poster.jpg"`
- CSS z-index layering: video (z-1) → dark overlay (z-2) → text content (z-3)
- CSS gradient fallback if video can't load
- Mobile (<481px): video hidden via `display: none`, poster shown as `background-image` to save bandwidth
- Video restored at 481px+ tablet breakpoint
- Placeholder comment added for video replacement

### FIX 2: Header Logo — "Owl I" → "Owl I Healthcare"
- Updated logo text in both header and footer in `index.html`
- Updated `logo_text` key in both `en` and `ta` objects in `js/i18n.js` (kept as "Owl I Healthcare" in both languages — brand name stays English)
- Responsive font-size: 1.15rem (mobile) → 1.35rem (tablet) → 1.5rem (desktop) to prevent overflow on small screens

### FIX 3: Testimonials — Card alignment
- Added `min-height` and flexbox centering (`flex-direction: column`, `align-items: center`, `justify-content: center`) to `.testimonial-slide`
- Added `max-width: 650px` on blockquote for comfortable reading width
- Moved prev/next buttons outside `.testimonial-carousel` overflow container into `.testimonial-nav` wrapper
- Added `.testimonial-wrapper` as positioning context
- Mobile: buttons centered below the card
- Tablet/desktop (769px+): buttons absolutely positioned on left/right sides
- Updated `js/testimonials.js` selectors to find buttons/dots from the wrapper element

**Commit:** `cdc9c45` — "Fix hero video background, logo text, and testimonial alignment"

---

## Fix: Hero Video & Poster — File Placement and Compression

**Prompt:**
> Both files are dropped there (user placed video and poster in images/hero/)

**What was done:**
- Renamed `165771-833149605_small.mp4` → `hero-video.mp4`
- Renamed `Naturopathy-1.jpg` → `hero-poster.jpg`

**Prompt:**
> Compress the video

**What was done:**
- Compressed video using ffmpeg: CRF 28, preset slow, stripped audio, faststart for web streaming
- Result: **12MB → 3.4MB** (72% reduction)
- Same 1920x1080 resolution, 20 seconds duration, no audio (muted background video)

**Commit:** `b8b99f1` — "Media files added"

---

## Fix: Nav Bar Skipping Services on Scroll

**Prompt:**
> Issue: When I scroll the nav bar animation skips Services

**What was done:**
- Root cause: IntersectionObserver had `threshold: 0.3` with `rootMargin: '-80px 0px -50% 0px'`. The Services section (15 cards) is very tall, and on some viewport sizes the effective observation area (~304px on 768px viewport) was smaller than 30% of the section height (~360px), making it mathematically impossible for the threshold to trigger.
- Fix: Lowered threshold from `0.3` to `0.05` and reduced bottom rootMargin from `-50%` to `-35%`
- This gives a ~417px observation area on a 768px viewport, easily fitting 5% of even the tallest section

**Commit:** `35892e7` — "nav bar fix"

---

## Fix: Testimonial Carousel Alignment (Round 2)

**Prompt:**
> Issue: What Our Patients Say - This part is still having alignment issue in both Desktop and mobile view

**What was done:**
- **Slide sizing**: Changed from `min-width: 100%` to `flex: 0 0 100%; width: 100%; box-sizing: border-box` for reliable full-width sizing without padding overflow
- **Blockquote width**: Added `width: 100%` so text stretches properly within the flex column instead of shrinking to content width
- **Carousel cleanup**: Removed duplicate `max-width` and `position: relative` from `.testimonial-carousel` — wrapper is the single sizing/positioning source
- **Desktop nav collapse**: `.testimonial-nav` on desktop now collapses with `height: 0; margin: 0; overflow: visible` so absolute-positioned buttons don't push dots down with phantom spacing
- **Button centering**: Changed from `top: 45%` to `top: 50%` for true vertical centering
- **Dot spacing**: Reduced `margin-top` from 24px to 20px for tighter grouping
- **Responsive star ratings**: Scaled properly across breakpoints (1.2rem mobile → 1.3rem tablet → 1.4rem desktop)
- Added subtle border on nav buttons for better visibility

**Commit:** `d4e9462` — "Fix testimonial carousel alignment on mobile and desktop"
