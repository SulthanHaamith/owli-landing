Read CLAUDE.md — refer to Sections 10 (SEO), 11 (accessibility), 13 (documentation), and 14 (placeholder checklist).

Build Phase 6: SEO files, README, and full project review.


PART 1: sitemap.xml

Create sitemap.xml with:
- XML declaration and urlset namespace
- Single entry: https://owlihealthcare.com/
- lastmod: today's date (2026-03-08)
- changefreq: weekly
- priority: 1.0


PART 2: robots.txt

Create robots.txt:
- User-agent: *
- Allow: /
- Sitemap: https://owlihealthcare.com/sitemap.xml


PART 3: README.md

Write a comprehensive, beginner-friendly README with these sections:

1. Project Overview
   - What this site is, who it's for, what tech it uses
   - Note: this is a static site, no server or build step needed

2. File Structure
   - List every file and folder with a 1-line description of what it does

3. How to View Locally
   - Just open index.html in a browser — that's it
   - Or use VS Code Live Server extension for auto-reload

4. How to Edit Text Content
   - All display text lives in js/i18n.js inside the translations object
   - Explain the data-i18n system: how to find a key, how to change text, how to add new translatable text
   - Step-by-step example: changing the hero heading

5. How to Replace Placeholder Images
   - Full list of every placeholder from Section 14 of CLAUDE.md
   - For each: file path, recommended dimensions, format
   - Explain: just replace the file with the same filename, or update the src in index.html

6. How to Update Doctor Information
   - Where doctor data lives (index.html + translations in i18n.js)
   - Search for [PLACEHOLDER] comments to find all doctor-related content
   - Step-by-step: replacing a placeholder doctor with real details

7. How to Add/Edit Services
   - Where the service list lives in HTML
   - Where service translations live in i18n.js
   - Where the contact form dropdown is populated in contact.js

8. How to Deploy to Hostinger
   - Step 1: Purchase Hostinger Premium Web Hosting
   - Step 2: Claim free domain (owlihealthcare.com) during checkout
   - Step 3: Open hPanel → Files → File Manager
   - Step 4: Navigate to public_html/
   - Step 5: Delete any default files
   - Step 6: Upload all project files (index.html, css/, js/, images/, sitemap.xml, robots.txt)
   - Step 7: Visit your domain to verify
   - Step 8: Enable free SSL in hPanel → Security → SSL
   - Note about DNS propagation (can take up to 48 hours)

9. How to Update the Site After Changes
   - Edit files locally
   - Re-upload changed files via File Manager or FTP
   - Explain Hostinger FTP credentials location in hPanel

10. Complete Placeholder Checklist
    - Table listing every single item that needs to be replaced before going live
    - Columns: Item, File Location, What to Replace With, Size/Format

11. Browser Support
    - Works in all modern browsers (Chrome, Firefox, Safari, Edge)
    - Note about IE11 not being supported

12. Troubleshooting
    - "Site not loading" → check file paths, ensure index.html is in public_html root
    - "Styles not working" → check css file paths in <head>
    - "Language toggle broken" → check that data-i18n keys match between HTML and i18n.js
    - "WhatsApp not opening" → check phone number format in links
    - "Images not showing" → check file paths and filenames match src attributes


PART 4: Full Project Review and Fixes

Now review the ENTIRE project and fix every issue you find. Check:

1. HTML Validation:
   - All tags properly closed
   - No duplicate IDs
   - All <img> tags have alt attributes
   - All form inputs have associated <label> elements
   - Semantic structure: header > nav > main > sections > footer

2. i18n Completeness:
   - Every element with data-i18n in HTML has a matching key in BOTH en and ta objects in i18n.js
   - No orphaned keys (keys in JS with no matching HTML element)
   - Contact form service dropdown options are included in translations
   - Floating button tooltips are in translations

3. CSS Consistency:
   - No hardcoded color values — all using var(--color-*)
   - All sections have the correct background color per Section 8 table in CLAUDE.md
   - Focus indicators visible on all interactive elements (links, buttons, inputs)
   - .animate-on-scroll elements have initial hidden state (opacity: 0, translateY) and .visible state

4. JavaScript:
   - No console errors — wrap risky operations in try/catch or null checks
   - All event listeners use correct selectors matching the actual HTML
   - Mobile menu opens and closes correctly
   - Carousel auto-rotates, pauses on hover, prev/next work
   - Lightbox opens, navigates, closes via button/overlay/escape
   - Form validation catches all invalid inputs
   - WhatsApp URL generates correctly with proper encoding
   - Language toggle switches ALL text including form labels, placeholders, button text, tooltips

5. Links:
   - All WhatsApp links use correct number: 916379610554
   - Phone links use: tel:+916379610554
   - Email links use: mailto:owli2026@gmail.com
   - All nav anchor links point to correct section IDs
   - No broken href="#" where a real anchor should be

6. Accessibility:
   - Skip-to-content link present and functional
   - ARIA labels on: hamburger button, lightbox, carousel controls, language toggle, floating buttons
   - Tab order logical through the page
   - Lightbox traps focus when open
   - Mobile menu traps focus when open
   - Color contrast: white text on dark green, dark text on cream — both passing WCAG AA

7. SEO:
   - <title> tag matches Section 10
   - Meta description present and correct
   - JSON-LD structured data valid (check syntax, commas, brackets)
   - Open Graph tags all present
   - Canonical URL set
   - All images have descriptive alt text

8. Placeholders:
   - Every placeholder image/content has a <!-- [PLACEHOLDER] --> comment
   - No real patient names or data accidentally included
   - Doctor names are clearly marked as placeholder

Fix every issue you find. Then confirm what was fixed.
