Read CLAUDE.md — refer to Sections 11 (accessibility) and 15 (design direction).

Build Phase 3: Responsive CSS in css/responsive.css.

Mobile-first approach — base styles in styles.css already target a reasonable default. This file adds min-width media queries to adapt layout at each breakpoint.

Breakpoints:
- Small mobile: up to 480px (base — fix anything that breaks)
- Tablet: min-width 481px
- Small desktop: min-width 769px
- Desktop: min-width 1025px
- Large desktop: min-width 1280px

Key responsive changes at each level:

MOBILE (base / small screens):
- Nav: hide horizontal links, show hamburger button. Nav drawer slides in from right (transform + transition — JS will toggle a class)
- Hero: smaller heading font, stack CTA buttons vertically, reduce padding
- About: single column, image below text
- Services grid: 1 column
- Why Choose Us: single column, stacked
- Doctors: single column, stacked cards
- Gallery: 2 columns
- Blog: single column
- Contact: single column, form on top, info below
- Footer: single column, stacked
- Floating buttons: 48px circles, bottom: 16px, right: 16px
- All font sizes scaled down comfortably for small screens
- All section padding reduced (40px top/bottom)
- Ensure touch targets are minimum 44×44px

TABLET (481px+):
- Services grid: 2 columns
- Why Choose Us: 2×2 grid
- Doctors: 2 columns (third card centered below or full-width)
- Blog: 2 columns (third card centered or hidden)
- Footer: 2 columns

SMALL DESKTOP (769px+):
- Nav: show horizontal links, hide hamburger
- About: two-column layout restored
- Contact: two-column restored

DESKTOP (1025px+):
- Services grid: 3 columns
- Why Choose Us: 4 columns in a row
- Doctors: 3 columns
- Gallery: 3 columns
- Blog: 3 columns
- Footer: 4 columns
- Floating buttons: 56px circles
- Section padding: 80px top/bottom

LARGE DESKTOP (1280px+):
- Max-width container centered, no layout changes — just breathing room

Also handle:
- Images: max-width 100%, height auto at all sizes
- Tables or wide content: horizontal scroll wrapper if needed
- Form inputs: full-width on mobile, comfortable sizing on desktop
- Testimonial carousel: works at all widths, text doesn't overflow

Comment every media query block — explain what changes and why. Follow Section 13 documentation standards.

Do NOT touch JS files yet.
