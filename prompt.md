Read CLAUDE.md — refer to Sections 7 (bilingual support), 9.1 (header/nav), 9.13 (floating buttons), and 15 (design direction).

Build Phase 4: JavaScript — Navigation (js/main.js) and Internationalization (js/i18n.js).

FILE 1: js/main.js — Navigation and scroll behavior

1. Mobile hamburger menu:
   - Toggle a class (e.g. .nav-open) on the nav drawer when hamburger is clicked
   - Close drawer when any nav link inside it is clicked
   - Close drawer when clicking outside the drawer (overlay click)
   - Trap focus inside drawer when open (accessibility)
   - Prevent body scroll when drawer is open (overflow: hidden on body)

2. Sticky header:
   - Add .scrolled class to header when window.scrollY > 50
   - Remove it when scrolled back to top
   - Use passive scroll listener for performance

3. Smooth scroll:
   - All anchor links (a[href^="#"]) scroll smoothly to their target section
   - Offset scroll position by header height so content isn't hidden behind sticky nav

4. Active nav highlighting:
   - Use IntersectionObserver to detect which section is in view
   - Add .active class to the corresponding nav link
   - Remove .active from all others
   - Threshold: 0.3 (section 30% visible triggers active state)

5. Scroll-triggered animations:
   - Use IntersectionObserver on elements with class .animate-on-scroll
   - When element enters viewport, add class .visible (CSS handles the animation via opacity/transform transition)
   - Once visible, unobserve the element (animate only once)
   - Add .animate-on-scroll class to: service cards, doctor cards, about section content, why-us items, blog cards

6. DOMContentLoaded:
   - Initialize all of the above when DOM is ready
   - No jQuery, no libraries


FILE 2: js/i18n.js — Bilingual toggle (English / Tamil)

1. Create a complete translations object:
   const translations = { en: { ... }, ta: { ... } }

   Include EVERY key used by data-i18n attributes in index.html. This means:
   - Nav items (home, about, services, doctors, gallery, blog, contact, book_now)
   - Hero heading, subheading, both CTA button texts
   - About section: heading, all paragraphs, vision, mission
   - All 15 service names and their 1-line descriptions
   - Why Choose Us: all 4 item titles and descriptions
   - Doctors section: heading, all 3 doctor names/roles/qualifications/specializations
   - Testimonials: heading, all 5 quotes, patient names, treatment types
   - Gallery: heading
   - Blog: heading, all 3 post titles, excerpts, "Read More" text
   - CTA Banner: heading text, both button texts
   - Contact: heading, all form labels, placeholder texts, submit button, all info labels
   - Footer: about text, "Quick Links" heading, "Our Services" heading, all listed items, copyright text
   - Floating button tooltips: "Chat on WhatsApp", "Call us now"
   - Language toggle label
   
   Tamil translations must be real, proper Tamil — not transliterated English. This is for a clinic in Tamil Nadu.

2. Toggle function:
   - Read current language from localStorage (default: 'en')
   - Switch to the other language
   - Find all elements with [data-i18n] attribute
   - Replace their textContent with translations[newLang][key]
   - For elements with data-i18n-placeholder, update the placeholder attribute instead
   - For elements with data-i18n-aria, update aria-label instead
   - Update the toggle button text (show the OTHER language option)
   - Save new language to localStorage
   - Update <html lang=""> attribute

3. On page load:
   - Check localStorage for saved language
   - If Tamil was saved, apply Tamil translations immediately
   - If no preference saved, default to English (no action needed since HTML is already in English)

4. When Tamil is active, add class .lang-ta to <body> so CSS can apply Noto Sans Tamil font-family

Document every function with thorough JSDoc comments. Explain the i18n system so a beginner can add new translations. Follow Section 13 standards.
