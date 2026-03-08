/**
 * LIGHTBOX.JS — Gallery Image Lightbox
 * Owl I Health and Wellness Center
 *
 * PURPOSE: Opens gallery images in a fullscreen overlay
 *   with navigation. All lightbox DOM elements are created
 *   dynamically by this script — no extra HTML markup needed.
 *
 * HOW IT WORKS:
 *   1. On page load, this script creates the lightbox overlay
 *      (dark background, image container, close/prev/next buttons,
 *      image counter) and appends it to the <body>.
 *   2. Clicking a gallery image opens the lightbox with that image.
 *   3. Users can navigate between images with prev/next buttons,
 *      keyboard arrows, or close with X / Escape / overlay click.
 *   4. Focus is trapped inside the lightbox for accessibility.
 *
 * DEPENDENCIES: None (vanilla JS only)
 */

document.addEventListener('DOMContentLoaded', function () {

  /* ──────────────────────────────────────────────
     GALLERY IMAGE COLLECTION
     Collect all gallery items. If none exist, bail
     out gracefully.
     ────────────────────────────────────────────── */

  /** @type {NodeListOf<HTMLElement>} All gallery item containers */
  var galleryItems = document.querySelectorAll('.gallery-item');

  /* If no gallery items exist, stop here */
  if (!galleryItems.length) return;

  /**
   * @type {Array<{src: string, alt: string}>}
   * Array of gallery image data. For now we use placeholder
   * data since real images aren't uploaded yet. When real
   * images replace placeholders, this will pull from <img> tags.
   */
  var galleryImages = [];

  galleryItems.forEach(function (item, index) {
    /* Check if there's a real <img> inside the gallery item */
    var img = item.querySelector('img');
    if (img) {
      galleryImages.push({
        src: img.src,
        alt: img.alt || 'Gallery image ' + (index + 1)
      });
    } else {
      /* For placeholders, store a reference with placeholder text */
      var label = item.querySelector('[data-i18n]');
      galleryImages.push({
        src: '',
        alt: label ? label.textContent : 'Gallery image ' + (index + 1)
      });
    }
  });


  /* ──────────────────────────────────────────────
     BUILD LIGHTBOX DOM
     Create all lightbox elements dynamically and
     append them to the document body.
     ────────────────────────────────────────────── */

  /** @type {number} Currently displayed image index */
  var currentImageIndex = 0;

  /** @type {HTMLElement|null} The gallery item that triggered the lightbox (for focus return) */
  var triggerElement = null;

  /**
   * CREATE LIGHTBOX ELEMENTS
   * PURPOSE: Builds the entire lightbox UI in JavaScript
   *   and appends it to document.body.
   *
   * Structure created:
   *   .lightbox-overlay (fullscreen dark background)
   *     .lightbox-close (X button, top-right)
   *     .lightbox-prev (left arrow)
   *     .lightbox-container
   *       .lightbox-img (the full-size image)
   *     .lightbox-next (right arrow)
   *     .lightbox-counter ("1 / 6")
   */

  /* Main overlay — fullscreen dark background */
  var overlay = document.createElement('div');
  overlay.className = 'lightbox-overlay';
  overlay.setAttribute('role', 'dialog');
  overlay.setAttribute('aria-modal', 'true');
  overlay.setAttribute('aria-label', 'Image lightbox');
  overlay.setAttribute('aria-hidden', 'true');

  /* Close button (X) — top right */
  var closeBtn = document.createElement('button');
  closeBtn.className = 'lightbox-close';
  closeBtn.setAttribute('aria-label', 'Close lightbox');
  closeBtn.innerHTML = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

  /* Previous button — left side */
  var prevBtn = document.createElement('button');
  prevBtn.className = 'lightbox-prev';
  prevBtn.setAttribute('aria-label', 'Previous image');
  prevBtn.innerHTML = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>';

  /* Next button — right side */
  var nextBtn = document.createElement('button');
  nextBtn.className = 'lightbox-next';
  nextBtn.setAttribute('aria-label', 'Next image');
  nextBtn.innerHTML = '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';

  /* Image container */
  var container = document.createElement('div');
  container.className = 'lightbox-content';

  /* The full-size image */
  var lightboxImg = document.createElement('img');
  lightboxImg.className = 'lightbox-image';
  lightboxImg.alt = '';
  container.appendChild(lightboxImg);

  /* Placeholder display when no real image exists */
  var placeholderDisplay = document.createElement('div');
  placeholderDisplay.className = 'lightbox-placeholder';
  placeholderDisplay.innerHTML = '<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span class="lightbox-placeholder-text"></span>';
  container.appendChild(placeholderDisplay);

  /* Image counter ("1 / 6") */
  var counter = document.createElement('div');
  counter.className = 'lightbox-counter';
  counter.setAttribute('aria-live', 'polite');

  /* Assemble all elements into the overlay */
  overlay.appendChild(closeBtn);
  overlay.appendChild(prevBtn);
  overlay.appendChild(container);
  overlay.appendChild(nextBtn);
  overlay.appendChild(counter);

  /* Add overlay to the page */
  document.body.appendChild(overlay);


  /* ──────────────────────────────────────────────
     LIGHTBOX FUNCTIONS
     Open, close, and navigate the lightbox.
     ────────────────────────────────────────────── */

  /**
   * SHOW IMAGE
   * PURPOSE: Displays the image at the given index
   *   inside the lightbox.
   *
   * @param {number} index - The image index to display (0-based)
   */
  function showImage(index) {
    /* Wrap around */
    if (index >= galleryImages.length) {
      currentImageIndex = 0;
    } else if (index < 0) {
      currentImageIndex = galleryImages.length - 1;
    } else {
      currentImageIndex = index;
    }

    var imageData = galleryImages[currentImageIndex];

    /* If there's a real image URL, show the <img> element */
    if (imageData.src) {
      lightboxImg.src = imageData.src;
      lightboxImg.alt = imageData.alt;
      lightboxImg.style.display = 'block';
      placeholderDisplay.style.display = 'none';
    } else {
      /* No real image — show the placeholder */
      lightboxImg.style.display = 'none';
      placeholderDisplay.style.display = 'flex';
      var textEl = placeholderDisplay.querySelector('.lightbox-placeholder-text');
      if (textEl) textEl.textContent = imageData.alt;
    }

    /* Update counter text: "1 / 6" */
    counter.textContent = (currentImageIndex + 1) + ' / ' + galleryImages.length;
  }

  /**
   * OPEN LIGHTBOX
   * PURPOSE: Opens the lightbox overlay and shows the
   *   image at the given index.
   *
   * @param {number} index - Which gallery image to show
   * @param {HTMLElement} trigger - The element that was clicked (for focus return)
   */
  function openLightbox(index, trigger) {
    triggerElement = trigger;
    currentImageIndex = index;
    showImage(index);

    /* Show the overlay */
    overlay.setAttribute('aria-hidden', 'false');
    overlay.classList.add('active');

    /* Lock body scroll */
    document.body.classList.add('lightbox-open');

    /* Focus the close button for keyboard users */
    setTimeout(function () {
      closeBtn.focus();
    }, 100);
  }

  /**
   * CLOSE LIGHTBOX
   * PURPOSE: Hides the lightbox overlay and cleans up.
   */
  function closeLightbox() {
    overlay.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('active');

    /* Unlock body scroll */
    document.body.classList.remove('lightbox-open');

    /* Clear the image source */
    lightboxImg.src = '';

    /* Return focus to the gallery item that was clicked */
    if (triggerElement) {
      triggerElement.focus();
      triggerElement = null;
    }
  }

  /**
   * NEXT IMAGE
   * PURPOSE: Navigate to the next image in the gallery.
   */
  function nextImage() {
    showImage(currentImageIndex + 1);
  }

  /**
   * PREVIOUS IMAGE
   * PURPOSE: Navigate to the previous image in the gallery.
   */
  function prevImage() {
    showImage(currentImageIndex - 1);
  }


  /* ──────────────────────────────────────────────
     EVENT LISTENERS
     ────────────────────────────────────────────── */

  /* --- Opening: Click gallery items to open lightbox --- */
  galleryItems.forEach(function (item, index) {
    item.addEventListener('click', function () {
      openLightbox(index, item);
    });

    /* Also handle Enter/Space key on gallery items (keyboard accessibility) */
    item.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openLightbox(index, item);
      }
    });
  });

  /* --- Closing: X button --- */
  closeBtn.addEventListener('click', closeLightbox);

  /* --- Closing: Click overlay background (not the image) --- */
  overlay.addEventListener('click', function (e) {
    /* Only close if clicking the dark overlay itself, not buttons or image */
    if (e.target === overlay) {
      closeLightbox();
    }
  });

  /* --- Navigation: Prev/Next buttons --- */
  prevBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    prevImage();
  });

  nextBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    nextImage();
  });

  /* --- Keyboard support --- */
  document.addEventListener('keydown', function (e) {
    /* Only handle keys when lightbox is open */
    if (overlay.getAttribute('aria-hidden') === 'true') return;

    switch (e.key) {
      case 'Escape':
        closeLightbox();
        break;
      case 'ArrowLeft':
        e.preventDefault();
        prevImage();
        break;
      case 'ArrowRight':
        e.preventDefault();
        nextImage();
        break;
      case 'Tab':
        /* Focus trap — keep Tab cycling inside lightbox */
        handleFocusTrap(e);
        break;
    }
  });

  /**
   * FOCUS TRAP
   * PURPOSE: When the lightbox is open, Tab key should
   *   cycle only through the lightbox buttons (close, prev, next).
   *   This prevents keyboard users from tabbing into content
   *   behind the lightbox.
   *
   * @param {KeyboardEvent} e - The keydown event
   */
  function handleFocusTrap(e) {
    var focusableElements = [closeBtn, prevBtn, nextBtn];
    var firstFocusable = focusableElements[0];
    var lastFocusable = focusableElements[focusableElements.length - 1];

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
  }

  /* Prevent image clicks from closing the lightbox */
  container.addEventListener('click', function (e) {
    e.stopPropagation();
  });
});
