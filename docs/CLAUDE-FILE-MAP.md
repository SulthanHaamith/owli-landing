# docs/CLAUDE-FILE-MAP.md — file structure (moved out of CLAUDE.md §16)

> Split from CLAUDE.md on 2026-10-26. The annotated tree of everything in the repo.

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
├── api/                        # PHP backend: register.php, quote.php, status.php, ticket.php (PDF ticket), cron-recheck.php, _bootstrap.php, admin-pass.php
│   └── lib/                    # Vendored single-file libs for the PDF ticket: tfpdf.php, qrcode.php, LICENSE.txt, font/unifont/ (MuktaMalar EN+TA font, ttfonts.php)
├── scripts/                    # validate.php (pre-deploy checks) + check-i18n.js (EN/TA parity) + check-pay-flow.js (payment navigation contract); CLI-only, blocked from the web
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
├── API-TROUBLESHOOTING.md      # API debugging runbook: reproduce → log → cause → verify
├── LOCAL-TESTING.md            # Local test stack: Docker setup, three checkers, manual verification techniques
└── README.md                   # Full project documentation
```

---
