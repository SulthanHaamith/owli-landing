# Owl I Health and Wellness Center — Website

A professional, bilingual (English/Tamil) landing page for a naturopathy clinic in Tirunelveli, Tamil Nadu, India.

---

## 1. Project Overview

**What is this?**
A static, single-page website for Owl I Health and Wellness Center — a naturopathy clinic offering drug-free, holistic healing treatments.

**Who is it for?**
The clinic owner and staff, to establish an online presence and allow patients to learn about services, meet the doctors, and book appointments via WhatsApp or phone.

**Tech stack:**
- **HTML5** — semantic markup, no templating engine
- **CSS3** — hand-written styles with custom properties, no preprocessors
- **Vanilla JavaScript (ES6+)** — no frameworks, no libraries, no build tools
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

**No server or build step needed.** Just open `index.html` in a browser.

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
├── js/
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

### Step 1: Purchase hosting
- Go to [hostinger.com](https://www.hostinger.com)
- Purchase **Premium Web Hosting** (or higher) — includes 1 free domain

### Step 2: Claim your domain
- During checkout, claim the free domain: `owlihealthcare.com`
- If `.com` is taken, try: `owlihealthcare.in`, `owlihealth.com`, or `owlihealthcenter.com`
- Hostinger auto-points the domain to your hosting — no manual DNS setup needed

### Step 3: Open File Manager
- Log into Hostinger **hPanel**
- Go to **Files → File Manager**

### Step 4: Navigate to public_html
- Click into the `public_html/` folder — this is your website's root directory

### Step 5: Delete default files
- Delete any default files Hostinger placed there (like `index.html`, `.htaccess`)

### Step 6: Upload project files
- Click **Upload** and select all project files and folders:
  - `index.html`
  - `css/` (entire folder)
  - `js/` (entire folder)
  - `images/` (entire folder with all real images)
  - `sitemap.xml`
  - `robots.txt`
- Make sure the file structure inside `public_html/` matches the project structure

### Step 7: Verify
- Open your browser and visit `https://owlihealthcare.com`
- The site should be live!

### Step 8: Enable SSL
- In hPanel, go to **Security → SSL**
- Enable the free SSL certificate
- This ensures your site loads over HTTPS (secure connection)

### Note about DNS propagation
After registering a new domain, it can take **up to 48 hours** for the domain to become accessible worldwide. During this time, the site may not load from all locations. This is normal.

---

## 9. How to Update the Site After Changes

1. Make your changes to the files on your computer
2. Test locally by opening `index.html` in a browser
3. Log into Hostinger hPanel → Files → File Manager
4. Navigate to `public_html/`
5. Upload the changed files (they will overwrite the old versions)
6. Refresh your website to verify the changes

### Using FTP (alternative to File Manager):
1. In hPanel, go to **Files → FTP Accounts**
2. Note your FTP hostname, username, and password
3. Use an FTP client like [FileZilla](https://filezilla-project.org/)
4. Connect and upload changed files to `public_html/`

### A note on caching (CSS/JS not updating after a change)
If you edit a CSS or JS file and the live site still shows the old version after uploading, it's almost always a **cache** — either the visitor's browser or Hostinger's CDN is still serving the old file. Two things help:
1. **Bump the `?v=` version number** on that file's `<link>`/`<script>` tag in `index.html` (e.g. `styles.css?v=2` → `?v=3`). This forces every browser to treat it as a new file and fetch it fresh. `css/event-banner.css`, `js/event-banner.js`, and `js/event-config.js` already use this pattern — follow the same style for any file you add it to.
2. **Purge the Hostinger CDN cache** in hPanel (Websites → your domain → Cache/CDN section → Purge Cache) after uploading, so the edge servers fetch the new file instead of serving a stale cached copy for up to 7 days.

---

## 10. How to Manage the Arokya Payanam 2026 Event

The event lives in three places, all controlled from one switch:

1. **`js/event-config.js`** — the single source of truth. While `registrationUrl` is an empty string (`''`), every "Register" button and label on the site (homepage popup + `/events/` page) shows "Registration Opens Soon" and the buttons are disabled. Paste a Razorpay payment link in as `registrationUrl` (must start with `https://`), save, and every one of those labels/buttons switches to "Register Now" automatically — no other file needs to change.
2. **`events/index.html`** — the full event page at `/events/`: agenda, competitions, organisers, contact details.
3. **Homepage popup** (`index.html` → `<div id="event-banner">`, styled by `css/event-banner.css`, behavior in `js/event-banner.js`) — a centered announcement that appears ~800ms after the homepage loads, showing the event logo and a link to `/events/`. It closes via the × button, clicking outside it, or the Escape key, and stays hidden for 24 hours after a visitor closes it. It also stops appearing entirely after the date in `OWLI_EVENT.endDate` (`js/event-config.js`) — update that if the event date ever changes.

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
