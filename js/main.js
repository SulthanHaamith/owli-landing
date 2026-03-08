/**
 * MAIN.JS — Owl I Health and Wellness Center
 *
 * PURPOSE: Handles all core site interactions:
 *   1. Mobile hamburger menu (open/close/focus trap)
 *   2. Sticky header with shadow on scroll
 *   3. Smooth scroll with header offset
 *   4. Active nav link highlighting
 *   5. Scroll-triggered fade-in animations
 *
 * HOW IT WORKS:
 *   Everything initializes when the DOM is fully loaded
 *   (DOMContentLoaded event). No external libraries —
 *   pure vanilla JavaScript.
 *
 * DEPENDENCIES: None (vanilla JS only)
 */


/**
 * Wait for the entire HTML document to be parsed before
 * running any JavaScript. This ensures all elements exist
 * in the DOM when we try to select them.
 */
document.addEventListener('DOMContentLoaded', function () {

  /* ──────────────────────────────────────────────
     1. MOBILE HAMBURGER MENU
     Controls the slide-in navigation drawer on mobile.

     HOW IT WORKS:
     - Clicking the hamburger button toggles the .active
       class on the nav drawer, which slides it in/out
       via CSS transition (right: -100% → right: 0).
     - An overlay is created behind the drawer so clicking
       outside closes it.
     - Body scroll is locked when drawer is open.
     - Focus is trapped inside the drawer for accessibility.
     ────────────────────────────────────────────── */

  /** @type {HTMLElement} The hamburger button (three lines) */
  const hamburger = document.getElementById('hamburger');

  /** @type {HTMLElement} The navigation links container (drawer on mobile) */
  const navLinks = document.getElementById('nav-links');

  /** @type {HTMLElement} The header element */
  const header = document.getElementById('header');

  /**
   * Create an overlay element that sits behind the nav drawer.
   * Clicking the overlay closes the drawer. This is added once
   * to the DOM and shown/hidden as needed.
   */
  const overlay = document.createElement('div');
  overlay.className = 'nav-overlay';
  overlay.setAttribute('aria-hidden', 'true');
  document.body.appendChild(overlay);

  /**
   * TOGGLE MOBILE MENU
   * PURPOSE: Opens or closes the mobile navigation drawer.
   * HOW IT WORKS:
   *   - Toggles .active on the nav drawer (slides it in/out)
   *   - Toggles .nav-open on body (prevents scrolling)
   *   - Toggles the overlay visibility
   *   - Updates aria-expanded on the hamburger button
   */
  function toggleMobileMenu() {
    const isOpen = navLinks.classList.toggle('active');
    document.body.classList.toggle('nav-open', isOpen);
    overlay.classList.toggle('active', isOpen);
    hamburger.setAttribute('aria-expanded', String(isOpen));

    if (isOpen) {
      /* When opening, focus the first link in the drawer */
      const firstLink = navLinks.querySelector('a');
      if (firstLink) firstLink.focus();
    } else {
      /* When closing, return focus to the hamburger button */
      hamburger.focus();
    }
  }

  /**
   * CLOSE MOBILE MENU
   * PURPOSE: Closes the mobile navigation drawer.
   * USED BY: Nav link clicks, overlay clicks, Escape key.
   */
  function closeMobileMenu() {
    navLinks.classList.remove('active');
    document.body.classList.remove('nav-open');
    overlay.classList.remove('active');
    hamburger.setAttribute('aria-expanded', 'false');
  }

  /* Hamburger button click → toggle menu */
  hamburger.addEventListener('click', toggleMobileMenu);

  /* Overlay click → close menu */
  overlay.addEventListener('click', closeMobileMenu);

  /* Close menu when any nav link inside the drawer is clicked
     (user tapped a section link, so close the drawer and scroll there) */
  navLinks.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', closeMobileMenu);
  });

  /* Close menu when Escape key is pressed (accessibility) */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && navLinks.classList.contains('active')) {
      closeMobileMenu();
    }
  });

  /**
   * FOCUS TRAP
   * PURPOSE: When the mobile drawer is open, pressing Tab
   *   should cycle focus only through elements inside the
   *   drawer (and the hamburger button). This prevents
   *   keyboard users from tabbing into hidden content behind
   *   the drawer.
   *
   * HOW IT WORKS:
   *   - Get all focusable elements inside the drawer + hamburger
   *   - If Tab would move past the last element, jump to first
   *   - If Shift+Tab would move before the first, jump to last
   */
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Tab' || !navLinks.classList.contains('active')) return;

    /* Collect all focusable elements in the drawer */
    const focusableElements = navLinks.querySelectorAll('a, button');
    const allFocusable = [hamburger, ...focusableElements];
    const firstFocusable = allFocusable[0];
    const lastFocusable = allFocusable[allFocusable.length - 1];

    if (e.shiftKey) {
      /* Shift+Tab: if on first element, wrap to last */
      if (document.activeElement === firstFocusable) {
        e.preventDefault();
        lastFocusable.focus();
      }
    } else {
      /* Tab: if on last element, wrap to first */
      if (document.activeElement === lastFocusable) {
        e.preventDefault();
        firstFocusable.focus();
      }
    }
  });


  /* ──────────────────────────────────────────────
     2. STICKY HEADER
     Adds a .scrolled class to the header when the
     user scrolls down more than 50px. This triggers
     CSS to change the header from transparent to
     white with a box-shadow.

     Uses { passive: true } on the scroll listener
     for better scroll performance — tells the browser
     this handler won't call preventDefault().
     ────────────────────────────────────────────── */

  /**
   * HANDLE HEADER SCROLL
   * PURPOSE: Toggle .scrolled class on header based on scroll position.
   */
  function handleHeaderScroll() {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }

  /* Listen for scroll events with passive flag for performance */
  window.addEventListener('scroll', handleHeaderScroll, { passive: true });

  /* Run once on load in case the page loads already scrolled */
  handleHeaderScroll();


  /* ──────────────────────────────────────────────
     3. SMOOTH SCROLL WITH HEADER OFFSET
     When an anchor link (like "Services") is clicked,
     smoothly scroll to that section. The scroll
     position is offset by the header height so the
     section heading isn't hidden behind the sticky nav.

     NOTE: CSS already has scroll-behavior: smooth on
     <html>, but this JS version adds the header offset
     which CSS can't do by itself.
     ────────────────────────────────────────────── */

  /**
   * Add click handlers to all internal anchor links.
   * When clicked, calculate the target position minus
   * the header height, then smoothly scroll there.
   */
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');

      /* Skip if href is just "#" with no target */
      if (targetId === '#') return;

      const targetElement = document.querySelector(targetId);
      if (!targetElement) return;

      e.preventDefault();

      /* Calculate header height for scroll offset */
      const headerHeight = header.offsetHeight;

      /* Calculate the target position minus header height */
      const targetPosition = targetElement.getBoundingClientRect().top
        + window.pageYOffset
        - headerHeight;

      /* Smoothly scroll to the calculated position */
      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
      });
    });
  });


  /* ──────────────────────────────────────────────
     4. ACTIVE NAV HIGHLIGHTING
     Uses IntersectionObserver to detect which section
     is currently in view, then highlights the matching
     nav link with an .active class.

     HOW IT WORKS:
     - Each section with an id that matches a nav link
       is observed.
     - When a section is 30% visible (threshold: 0.3),
       its corresponding nav link gets the .active class.
     - All other nav links lose the .active class.
     ────────────────────────────────────────────── */

  /** @type {NodeListOf<HTMLElement>} All nav links in the header */
  const allNavLinks = document.querySelectorAll('.nav-links a');

  /**
   * Map section IDs to their nav links for quick lookup.
   * e.g., { '#hero': <a href="#hero">, '#about': <a href="#about">, ... }
   */
  const navLinkMap = {};
  allNavLinks.forEach(function (link) {
    navLinkMap[link.getAttribute('href')] = link;
  });

  /**
   * HIGHLIGHT ACTIVE NAV LINK
   * PURPOSE: Adds .active class to the nav link matching
   *   the given section ID and removes it from all others.
   * @param {string} sectionId - The section's hash ID (e.g., '#about')
   */
  function setActiveNavLink(sectionId) {
    allNavLinks.forEach(function (link) {
      link.classList.remove('active');
    });
    if (navLinkMap[sectionId]) {
      navLinkMap[sectionId].classList.add('active');
    }
  }

  /**
   * IntersectionObserver for section visibility.
   * When a section is 30% visible, mark its nav link active.
   */
  const sectionObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        setActiveNavLink('#' + entry.target.id);
      }
    });
  }, {
    /* rootMargin offsets for the sticky header height.
       Negative top margin means the "viewport" starts below the header.
       Bottom margin of -35% keeps detection in the upper portion of
       the viewport so the highlight changes before you scroll too far. */
    rootMargin: '-80px 0px -35% 0px',
    threshold: 0.05
  });

  /* Observe all sections that have matching nav links */
  var sectionIds = ['hero', 'about', 'services', 'doctors', 'gallery', 'blog', 'contact'];
  sectionIds.forEach(function (id) {
    var section = document.getElementById(id);
    if (section) {
      sectionObserver.observe(section);
    }
  });


  /* ──────────────────────────────────────────────
     5. SCROLL-TRIGGERED ANIMATIONS
     Fades in elements as they scroll into view.

     HOW IT WORKS:
     - Elements with class .animate-on-scroll start
       hidden (opacity: 0, translateY: 20px) via CSS.
     - When they enter the viewport, JS adds class
       .visible which triggers a CSS transition to
       opacity: 1 and translateY: 0.
     - Once visible, the element is unobserved so the
       animation only happens once (doesn't re-trigger
       when scrolling back up).
     ────────────────────────────────────────────── */

  /** @type {NodeListOf<HTMLElement>} All elements that should animate on scroll */
  const animatedElements = document.querySelectorAll('.animate-on-scroll');

  /**
   * IntersectionObserver for scroll animations.
   * When an element is 15% visible, add .visible class
   * and stop observing it (animate only once).
   */
  const animationObserver = new IntersectionObserver(function (entries, observer) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        /* Add .visible class which triggers the CSS fade-in transition */
        entry.target.classList.add('visible');
        /* Stop watching this element — animation should only happen once */
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.15,
    rootMargin: '0px 0px -40px 0px'
  });

  /* Start observing all animated elements */
  animatedElements.forEach(function (el) {
    animationObserver.observe(el);
  });


  /* ──────────────────────────────────────────────
     END OF INITIALIZATION
     All features are now active. The site is ready
     for user interaction.
     ────────────────────────────────────────────── */
});
