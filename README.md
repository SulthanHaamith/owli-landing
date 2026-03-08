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
├── index.html              # The entire website — all 12 sections in one page
├── css/
│   ├── styles.css          # All visual styles, colors, layouts, components
│   └── responsive.css      # Mobile-first media queries for all screen sizes
├── js/
│   ├── i18n.js             # All English + Tamil translations, language toggle logic
│   ├── main.js             # Nav menu, sticky header, scroll animations, active nav
│   ├── testimonials.js     # Testimonial carousel (auto-rotate, swipe, dots)
│   ├── lightbox.js         # Gallery lightbox (open, navigate, close, keyboard)
│   └── contact.js          # Form validation, WhatsApp URL builder, rate limiting
├── images/
│   ├── hero/               # Hero background image (1920×800px)
│   ├── services/           # Service icons (SVG preferred)
│   ├── doctors/            # Doctor photos (400×400px, square)
│   ├── gallery/            # Clinic gallery photos (800×600px)
│   ├── blog/               # Blog thumbnail images (600×400px)
│   ├── logo.svg            # Clinic logo (SVG preferred)
│   ├── og-image.jpg        # Social sharing image (1200×630px)
│   └── favicon.ico         # Browser tab icon (32×32px or 64×64px)
├── sitemap.xml             # SEO sitemap with single page entry
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
| Clinic interior photo | `images/clinic-interior.jpg` | 800 × 600 px | JPG |
| Doctor 1 photo | `images/doctors/doctor-1.jpg` | 400 × 400 px | JPG (square) |
| Doctor 2 photo | `images/doctors/doctor-2.jpg` | 400 × 400 px | JPG (square) |
| Doctor 3 photo | `images/doctors/doctor-3.jpg` | 400 × 400 px | JPG (square) |
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

Doctor data lives in two places:

### In `index.html`:
Search for `[PLACEHOLDER] Replace with real doctor details` to find all three doctor cards. Each card has:
- Photo (replace the `<div class="image-placeholder">` with an `<img>` tag)
- Name, role, qualification, experience, specialization

### In `js/i18n.js`:
Search for `doctor_1_name`, `doctor_2_name`, `doctor_3_name` in both the `en` and `ta` objects.

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

---

## 7. How to Add/Edit Services

### Where services live:

1. **Service cards in HTML** (`index.html`): Search for `services-grid` — all 15 service cards are inside this grid.

2. **Service translations** (`js/i18n.js`): Search for `service_1_title` through `service_15_title` (and their `_desc` variants) in both `en` and `ta` objects.

3. **Contact form dropdown** (`index.html`): Search for `contact-service` — the `<select>` element has all 15 services as `<option>` elements.

### To edit an existing service:
1. Update the title and description in `js/i18n.js` (both `en` and `ta`)
2. Update the `value` attribute on the matching `<option>` in the contact form if the service name changed

### To add a new service (e.g., service 16):
1. Copy an existing service card `<div>` in the HTML and change the `data-i18n` keys to `service_16_title` and `service_16_desc`
2. Add `service_16_title` and `service_16_desc` to both `en` and `ta` objects in `i18n.js`
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

---

## 10. Complete Placeholder Checklist

**Replace ALL of these before going live:**

| # | Item | File Location | What to Replace | Size/Format |
|---|------|---------------|-----------------|-------------|
| 1 | Hero background image | `images/hero/` | Clinic exterior or nature photo | 1920×800px, JPG |
| 2 | Clinic interior photo | `images/` | Real photo of the clinic | 800×600px, JPG |
| 3 | Doctor 1 photo + name | `images/doctors/` + `i18n.js` | Real doctor photo and details | 400×400px, square JPG |
| 4 | Doctor 2 photo + name | `images/doctors/` + `i18n.js` | Real doctor photo and details | 400×400px, square JPG |
| 5 | Doctor 3 photo + name | `images/doctors/` + `i18n.js` | Real doctor photo and details | 400×400px, square JPG |
| 6 | Gallery photos (6) | `images/gallery/` | Real clinic/treatment photos | 800×600px, JPG |
| 7 | Blog post images (3) | `images/blog/` | Relevant health topic images | 600×400px, JPG |
| 8 | Social sharing image | `images/og-image.jpg` | Clinic branding image | 1200×630px, JPG |
| 9 | Favicon | `images/favicon.ico` | Clinic logo icon | 32×32px, ICO |
| 10 | Clinic logo | `images/logo.svg` | Real logo file | SVG preferred |
| 11 | Patient testimonials (5) | `i18n.js` | Real patient quotes | Text only |
| 12 | Doctor names & details (3) | `index.html` + `i18n.js` | Real names, qualifications | Text only |
| 13 | Social media URLs (3) | `index.html` footer | Instagram, Facebook, YouTube URLs | Full URLs |
| 14 | Google Maps embed URL | `index.html` contact section | Real embed URL from Google Maps | Embed iframe URL |

---

## 11. Browser Support

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

## 12. Troubleshooting

### "Site not loading after upload"
- Make sure `index.html` is directly inside `public_html/` (not in a subfolder)
- Check that you uploaded all files, not just `index.html`
- Wait up to 48 hours for DNS propagation if using a new domain

### "Styles not appearing / site looks broken"
- Check that the `css/` folder is uploaded to `public_html/`
- Open browser DevTools (F12) → Console tab → look for 404 errors on CSS files
- Verify the file paths in the `<link>` tags in `index.html` match your folder structure

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
