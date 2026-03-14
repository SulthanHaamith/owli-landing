Scrap the horizontal logo on mobile entirely. Remove .logo-mobile and all related CSS.

On mobile (below 769px), show:
- The favicon.png image at height: 40px, width: 40px
- Immediately next to it, a text span: "Owl I Healthcare Centre"
  - font-family: Poppins
  - font-weight: 700
  - font-size: 16px
  - color: var(--color-primary)
  - margin-left: 8px

Wrap both in a flex container with align-items: center, gap: 8px.

The text span:
- Class: .mobile-brand-text
- Add data-i18n key, update i18n.js for both EN and TA
- Visible ONLY below 769px (display: none on desktop)

On desktop (769px+): keep showing only the favicon as it is now. Hide .mobile-brand-text.

Remove all leftover .logo-mobile img tags, CSS rules, and the white background card on mobile.

Keep it simple. Nothing else changes.
