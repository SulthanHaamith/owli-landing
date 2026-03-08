Read CLAUDE.md for reference. I have 3 specific issues to fix. All changes must be fully responsive across mobile (375px), tablet (768px), and desktop (1440px).


FIX 1: Hero Section — Replace solid background with MP4 video background

The hero section currently has a solid/gradient background behind "Heal Naturally. Live Fully." — replace it with a looping background video.

Implementation:
- Add a <video> element inside the hero section, behind all text content:
  <video autoplay muted loop playsinline class="hero-video">
    <source src="images/hero/hero-video.mp4" type="video/mp4">
  </video>
- Style the video: position absolute, top 0, left 0, width 100%, height 100%, object-fit cover, z-index 1
- Keep the existing dark overlay ON TOP of the video (z-index 2) so white text remains readable
- Hero text content sits above the overlay (z-index 3)
- Add a fallback: if video can't load, show a CSS gradient or background color so the section isn't blank
- Add a poster attribute on the video tag pointing to a placeholder image: poster="images/hero/hero-poster.jpg"
- Mark with <!-- [PLACEHOLDER] Replace hero-video.mp4 with real clinic video (recommended: 15-30 seconds, 1920x1080, under 10MB) -->
- Performance: add preload="metadata" to avoid loading full video on slow connections
- Mobile consideration: some mobile browsers block autoplay video — ensure the poster image and dark overlay still look good as fallback. Keep autoplay muted loop playsinline attributes (playsinline is required for iOS)
- Update responsive.css: on very small screens (under 481px), optionally hide the video and show just the poster image as a background-image instead, to save bandwidth on mobile

Update i18n.js if any new translatable text is added.


FIX 2: Header Logo — Change "Owl I" to "Owl I Healthcare"

The header currently shows only "Owl I" — change it to "Owl I Healthcare".

- Update the logo text in index.html
- Update both en and ta translation keys in i18n.js (Tamil: "Owl I மருத்துவமனை" or the appropriate Tamil term for healthcare — use what makes sense for a naturopathy clinic brand name. If the clinic prefers "Owl I Healthcare" in English even in Tamil mode, keep it as-is in both languages.)
- Check alignment: the longer text should still sit properly next to the nav links on desktop and not overlap or wrap awkwardly
- On mobile: verify the logo text doesn't overflow or push the hamburger button off screen. If it's too long, consider slightly reducing logo font-size on mobile breakpoints in responsive.css
- The leaf/owl SVG icon should still be next to the text, properly vertically aligned


FIX 3: Testimonials Section — Fix "What Our Patients Say" review card alignment

The testimonial/review cards are not properly aligned. Fix the layout:

- Ensure all testimonial slides have consistent height — use min-height or flexbox to prevent layout shift between slides
- Center the testimonial content vertically and horizontally within the carousel container
- Quote text: centered, with comfortable max-width (600-700px) so lines aren't too long on desktop
- Star rating: centered below the quote
- Patient name and treatment type: centered below stars
- Dot indicators: centered below the testimonial card with consistent spacing
- Prev/Next buttons: vertically centered on left and right sides of the carousel, or centered below content on mobile
- On mobile: ensure text doesn't overflow, reduce font size if needed, prev/next buttons below the card (not on sides — not enough room)
- On tablet and desktop: prev/next arrows on left/right sides, vertically centered to the quote
- Check that transitioning between slides doesn't cause content to jump or shift — all slides should occupy the same space

Test mentally at 375px, 768px, and 1440px. Make sure nothing breaks.

After all 3 fixes, review the changes and confirm each fix is working correctly.
