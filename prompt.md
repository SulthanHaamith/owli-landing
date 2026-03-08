Read CLAUDE.md — refer to Sections 5 (colors), 6 (typography), 15 (design direction).

Build Phase 2: Core CSS styling in css/styles.css.

Style every section in index.html. Design direction: organic, natural, calming — like a peaceful forest clinic. Professional but warm. Generous whitespace.

1. CSS reset/normalize at the top
2. :root already has custom properties — use them everywhere, zero hardcoded colors
3. Typography scale using Poppins (headings 600/700 weight, body 300/400)
4. Alternating section backgrounds for visual rhythm: cream → white → light green → cream (per Section 8 table)

Section-by-section:
- Header: sticky, transparent initially, gains white background + subtle box-shadow on scroll (via .scrolled class that JS will add later)
- Hero: 100vh, dark gradient overlay over background placeholder, centered text, CSS fade-in keyframe animation on load
- About: two-column layout (text left, image placeholder right), vertically centered
- Services: 3-column grid, cards with rounded corners + hover effect (translateY -5px + box-shadow transition)
- Why Choose Us: 4-column icon stats row on light green background
- Doctors: profile cards with circular avatar placeholder, centered text
- Testimonials: carousel container with overflow hidden, individual slides, dot indicators styled
- Gallery: 3-column grid with aspect-ratio maintained, hover overlay with subtle zoom
- Blog: 3-column card layout (image top, title, excerpt, read-more link)
- CTA Banner: full-width dark green, centered white text, two side-by-side buttons
- Contact: two-column (form left with styled inputs/focus states, info right)
- Footer: 4-column grid on dark green, light text, bottom copyright bar
- Floating buttons (WhatsApp + Phone): fixed bottom-right, stacked vertically with 12px gap, 56px circles, box-shadow, pulse keyframe animation, hover scale(1.1)

General:
- Buttons: rounded (border-radius 8px), padding 12px 28px, transition on hover (darken background)
- Links: no underline by default, color transition on hover
- Section padding: generous (80px top/bottom desktop)
- Max content width: 1200px centered with auto margins
- Smooth scroll: html { scroll-behavior: smooth }
- Focus indicators: visible outline for keyboard accessibility

Add thorough beginner-friendly comments on every style block per Section 13 documentation standards. Do NOT touch any JS files yet.
