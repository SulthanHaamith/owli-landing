# docs/CLAUDE-SECTIONS.md — homepage section content (moved out of CLAUDE.md §9)

> Split from CLAUDE.md on 2026-10-26 for context switching. The approved copy and
> structure of every homepage section. Numbers below are the original CLAUDE.md §9.x.

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
  - Fallback: mailto link to support@owlihealthcare.com
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
