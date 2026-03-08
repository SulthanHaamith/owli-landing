/**
 * TESTIMONIALS.JS — Testimonial Carousel
 * Owl I Health and Wellness Center
 *
 * PURPOSE: Controls the testimonial carousel in the
 *   Testimonials section. Shows one testimonial at a
 *   time, auto-rotates, and supports manual navigation.
 *
 * HOW IT WORKS:
 *   - All testimonial slides sit in a horizontal row
 *     inside .testimonial-track (a flex container).
 *   - Only one slide is visible at a time because the
 *     parent .testimonial-carousel has overflow: hidden.
 *   - To show a different slide, we shift the track
 *     left/right using CSS transform: translateX().
 *   - Auto-rotation advances to the next slide every
 *     5 seconds. Hovering pauses it.
 *   - Dot indicators and prev/next buttons allow
 *     manual navigation.
 *   - Touch swipe is supported for mobile users.
 *
 * DEPENDENCIES: None (vanilla JS only)
 */

document.addEventListener('DOMContentLoaded', function () {

  /* ──────────────────────────────────────────────
     ELEMENT REFERENCES
     Grab all the DOM elements we need. If the
     carousel doesn't exist on the page, bail out
     gracefully so no errors are thrown.
     ────────────────────────────────────────────── */

  /** @type {HTMLElement|null} The carousel container */
  var carousel = document.querySelector('.testimonial-carousel');

  /* If testimonial elements don't exist, stop here */
  if (!carousel) return;

  /** @type {HTMLElement} The track that holds all slides */
  var track = carousel.querySelector('.testimonial-track');

  /** @type {NodeListOf<HTMLElement>} All individual slides */
  var slides = carousel.querySelectorAll('.testimonial-slide');

  /** @type {HTMLElement} The wrapper around carousel, nav, and dots */
  var wrapper = document.querySelector('.testimonial-wrapper');

  /** @type {HTMLElement} Previous slide button */
  var prevBtn = wrapper.querySelector('.testimonial-prev');

  /** @type {HTMLElement} Next slide button */
  var nextBtn = wrapper.querySelector('.testimonial-next');

  /** @type {NodeListOf<HTMLElement>} All dot indicator buttons */
  var dots = wrapper.querySelectorAll('.dot');

  /** @type {number} Total number of slides */
  var totalSlides = slides.length;

  /** @type {number} Currently visible slide (0-based index) */
  var currentIndex = 0;

  /** @type {number|null} ID of the auto-rotation interval timer */
  var autoRotateTimer = null;

  /** @type {number} Auto-rotation interval in milliseconds (5 seconds) */
  var AUTO_ROTATE_INTERVAL = 5000;


  /* ──────────────────────────────────────────────
     1. CORE CAROUSEL LOGIC
     showSlide() is the heart of the carousel. It
     shifts the track to display the slide at the
     given index, and updates the dot indicators.
     ────────────────────────────────────────────── */

  /**
   * SHOW SLIDE
   * PURPOSE: Displays the slide at the given index.
   *
   * HOW IT WORKS:
   *   - Calculates the translateX percentage to shift
   *     the track so the target slide is visible.
   *   - Updates dot indicators: removes .active from
   *     all dots, adds it to the matching dot.
   *   - Wraps around: if index is past the end, go to
   *     the first slide; if before the start, go to last.
   *
   * @param {number} index - The slide index to show (0-based)
   */
  function showSlide(index) {
    /* Wrap around: go to first slide after last */
    if (index >= totalSlides) {
      currentIndex = 0;
    }
    /* Wrap around: go to last slide before first */
    else if (index < 0) {
      currentIndex = totalSlides - 1;
    }
    else {
      currentIndex = index;
    }

    /* Shift the track left by (currentIndex * 100%) to show the target slide.
       Each slide is 100% wide (min-width: 100%), so slide 2 is at -200%, etc. */
    track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';

    /* Update dot indicators */
    dots.forEach(function (dot, i) {
      if (i === currentIndex) {
        dot.classList.add('active');
        dot.setAttribute('aria-current', 'true');
      } else {
        dot.classList.remove('active');
        dot.removeAttribute('aria-current');
      }
    });
  }


  /* ──────────────────────────────────────────────
     2. AUTO-ROTATION
     Automatically advances to the next slide every
     5 seconds. Pauses when the user hovers over the
     carousel (they might be reading). Resumes when
     the mouse leaves.
     ────────────────────────────────────────────── */

  /**
   * START AUTO-ROTATION
   * PURPOSE: Begins the automatic slide advancement timer.
   */
  function startAutoRotate() {
    /* Clear any existing timer first to avoid duplicates */
    stopAutoRotate();
    autoRotateTimer = setInterval(function () {
      showSlide(currentIndex + 1);
    }, AUTO_ROTATE_INTERVAL);
  }

  /**
   * STOP AUTO-ROTATION
   * PURPOSE: Pauses the automatic slide advancement.
   */
  function stopAutoRotate() {
    if (autoRotateTimer) {
      clearInterval(autoRotateTimer);
      autoRotateTimer = null;
    }
  }

  /**
   * RESET AUTO-ROTATION
   * PURPOSE: Restarts the timer from scratch. Called after
   *   manual navigation so the full 5 seconds elapse before
   *   the next automatic advance.
   */
  function resetAutoRotate() {
    stopAutoRotate();
    startAutoRotate();
  }

  /* Pause auto-rotation on hover (user is reading) */
  wrapper.addEventListener('mouseenter', stopAutoRotate);

  /* Resume auto-rotation when hover leaves */
  wrapper.addEventListener('mouseleave', startAutoRotate);

  /* Start auto-rotation on page load */
  startAutoRotate();


  /* ──────────────────────────────────────────────
     3. CONTROLS
     Previous/Next buttons and dot indicators for
     manual navigation.
     ────────────────────────────────────────────── */

  /* Previous button — go to the previous slide */
  prevBtn.addEventListener('click', function () {
    showSlide(currentIndex - 1);
    resetAutoRotate();
  });

  /* Next button — go to the next slide */
  nextBtn.addEventListener('click', function () {
    showSlide(currentIndex + 1);
    resetAutoRotate();
  });

  /* Dot indicators — clicking a dot jumps to that slide */
  dots.forEach(function (dot, index) {
    dot.addEventListener('click', function () {
      showSlide(index);
      resetAutoRotate();
    });
  });


  /* ──────────────────────────────────────────────
     4. TOUCH / SWIPE SUPPORT (Mobile)
     Detects swipe gestures on touch devices:
     - Swipe left → next slide
     - Swipe right → previous slide
     Uses a minimum distance threshold (50px) to
     avoid triggering on accidental touches.
     ────────────────────────────────────────────── */

  /** @type {number} X position where the touch started */
  var touchStartX = 0;

  /** @type {number} Y position where the touch started (to detect vertical scroll) */
  var touchStartY = 0;

  /** @type {boolean} Whether this touch is primarily horizontal (a swipe) */
  var isSwiping = false;

  /* Record where the touch started */
  carousel.addEventListener('touchstart', function (e) {
    touchStartX = e.changedTouches[0].screenX;
    touchStartY = e.changedTouches[0].screenY;
    isSwiping = false;
  }, { passive: true });

  /* Detect if the gesture is horizontal (swipe) vs vertical (scroll) */
  carousel.addEventListener('touchmove', function (e) {
    var deltaX = Math.abs(e.changedTouches[0].screenX - touchStartX);
    var deltaY = Math.abs(e.changedTouches[0].screenY - touchStartY);

    /* If horizontal movement is greater than vertical, it's a swipe */
    if (deltaX > deltaY && deltaX > 10) {
      isSwiping = true;
    }
  }, { passive: true });

  /* When the touch ends, check if it was a swipe */
  carousel.addEventListener('touchend', function (e) {
    if (!isSwiping) return;

    var touchEndX = e.changedTouches[0].screenX;
    var deltaX = touchStartX - touchEndX;

    /* Minimum swipe distance threshold: 50px */
    if (Math.abs(deltaX) > 50) {
      if (deltaX > 0) {
        /* Swiped left → go to next slide */
        showSlide(currentIndex + 1);
      } else {
        /* Swiped right → go to previous slide */
        showSlide(currentIndex - 1);
      }
      resetAutoRotate();
    }
  }, { passive: true });


  /* ──────────────────────────────────────────────
     5. INITIALIZATION
     Show the first slide on load. The carousel is
     now fully interactive.
     ────────────────────────────────────────────── */
  showSlide(0);
});
