Read CLAUDE.md — refer to Sections 9.7 (testimonials), 9.8 (gallery), 9.11 (contact/booking), and 17 (contact links reference).

Build Phase 5: JavaScript — Testimonials (js/testimonials.js), Lightbox (js/lightbox.js), and Contact Form (js/contact.js).


FILE 1: js/testimonials.js — Carousel

1. Core carousel logic:
   - Track current slide index
   - showSlide(index) function: hides all slides, shows the target slide with a CSS transition (opacity or translateX)
   - Wrap around: after last slide → go to first, before first → go to last

2. Auto-rotation:
   - Advance to next slide every 5 seconds using setInterval
   - Pause auto-rotation when user hovers over the testimonial section
   - Resume auto-rotation when hover leaves
   - Reset the timer whenever user manually clicks prev/next

3. Controls:
   - Previous button: go to previous slide
   - Next button: go to next slide
   - Dot indicators: clicking a dot jumps to that slide
   - Update active dot to match current slide (add/remove .active class)

4. Touch/swipe support (mobile):
   - Detect touchstart and touchend events
   - If swipe left (deltaX > 50px): next slide
   - If swipe right (deltaX > 50px): previous slide
   - Prevent accidental swipes during vertical scroll

5. Initialize on DOMContentLoaded. Bail gracefully if testimonial elements don't exist on the page.


FILE 2: js/lightbox.js — Gallery lightbox

1. Opening:
   - Click any gallery image → open fullscreen overlay
   - Overlay: dark semi-transparent background (rgba(0,0,0,0.9))
   - Display clicked image centered and scaled to fit viewport (max-width: 90vw, max-height: 85vh, object-fit: contain)
   - CSS transition: fade in overlay + scale up image
   - Add body class to prevent background scroll (overflow: hidden)

2. Navigation inside lightbox:
   - Previous / Next arrow buttons on left and right sides
   - Navigate through gallery images in order
   - Wrap around at beginning/end
   - Show image counter: "3 / 8"

3. Closing:
   - Click the X close button
   - Click anywhere on the dark overlay background (not on the image itself)
   - Press Escape key
   - Remove body scroll lock on close

4. Keyboard support:
   - ArrowLeft: previous image
   - ArrowRight: next image
   - Escape: close lightbox

5. Accessibility:
   - Trap focus inside lightbox when open (tab cycles between prev, next, close buttons)
   - aria-label on all lightbox buttons
   - role="dialog" and aria-modal="true" on the lightbox overlay
   - Return focus to the gallery image that was clicked when lightbox closes

6. Create lightbox DOM elements dynamically via JS (don't require extra HTML markup — build the overlay, image container, buttons, and counter in JS and append to body). Initialize on DOMContentLoaded.


FILE 3: js/contact.js — Form validation and WhatsApp submission

1. Form field validation rules:
   - Name: required, minimum 2 characters, trim whitespace
   - Phone: required, valid Indian mobile number (10 digits, starts with 6/7/8/9, strip any +91 or 0 prefix the user might add)
   - Email: required, standard email regex validation
   - Service: required, must select a value (not the default "Select a service" option)
   - Message: optional, no validation needed

2. Inline error display:
   - Each field has a corresponding error message element below it
   - On invalid: add .error class to the input (red border) + show error message text
   - On valid: remove .error class + hide error message
   - Validate on form submit AND on blur (when user leaves a field)
   - Clear error when user starts typing in that field (input event)

3. On valid submit:
   - Gather all field values
   - URL-encode each value for WhatsApp
   - Build WhatsApp URL per Section 17:
     https://wa.me/916379610554?text=Name: [name]%0APhone: [phone]%0AEmail: [email]%0AService: [service]%0AMessage: [message]
   - Open the URL in a new tab: window.open(url, '_blank')
   - Show a brief success message: "Opening WhatsApp... If it doesn't open, click here to email us instead."
   - The "click here" links to: mailto:owli2026@gmail.com?subject=Appointment Request&body=Name: [name]...
   - Do NOT actually submit the form (preventDefault)

4. Service dropdown:
   - Populated with all 15 services from CLAUDE.md Section 9.4
   - First option: "Select a service" with empty value (acts as placeholder)
   - The dropdown options must also be bilingual — update options when language toggles
   - In i18n.js, add service names to translations if not already there

5. Rate limiting:
   - After successful submit, disable the submit button for 10 seconds to prevent spam
   - Show countdown text on button: "Please wait (8s)..."
   - Re-enable after timer completes

6. Initialize on DOMContentLoaded. Bail gracefully if form doesn't exist.

All three files: thorough JSDoc comments on every function, explain the logic step by step, follow Section 13 documentation standards. A beginner should understand every line.
