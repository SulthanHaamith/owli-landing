/**
 * CONTACT.JS — Contact Form Validation & WhatsApp Submission
 * Owl I Health and Wellness Center
 *
 * PURPOSE: Handles the contact/booking form:
 *   - Validates all form fields with inline error messages
 *   - Builds a WhatsApp URL with the form data
 *   - Opens WhatsApp in a new tab on valid submit
 *   - Provides a mailto fallback link
 *   - Rate-limits submissions to prevent spam
 *
 * HOW IT WORKS:
 *   1. Each field has validation rules (required, format, length)
 *   2. Errors show inline below each field with a red border
 *   3. On valid submit, form data is URL-encoded into a
 *      WhatsApp link: wa.me/916379610554?text=...
 *   4. After submit, the button is disabled for 10 seconds
 *      to prevent accidental duplicate submissions
 *
 * DEPENDENCIES: None (vanilla JS only)
 */

document.addEventListener('DOMContentLoaded', function () {

  /* ──────────────────────────────────────────────
     ELEMENT REFERENCES
     Grab the form and all its fields. If the form
     doesn't exist on the page, bail out gracefully.
     ────────────────────────────────────────────── */

  /** @type {HTMLFormElement|null} The contact/booking form */
  var form = document.getElementById('contact-form');

  /* If form doesn't exist, stop here */
  if (!form) return;

  /** @type {HTMLInputElement} Name input field */
  var nameInput = document.getElementById('contact-name');

  /** @type {HTMLInputElement} Phone input field */
  var phoneInput = document.getElementById('contact-phone');

  /** @type {HTMLInputElement} Email input field */
  var emailInput = document.getElementById('contact-email');

  /** @type {HTMLSelectElement} Service dropdown */
  var serviceSelect = document.getElementById('contact-service');

  /** @type {HTMLTextAreaElement} Message textarea */
  var messageInput = document.getElementById('contact-message');

  /** @type {HTMLButtonElement} Submit button */
  var submitBtn = form.querySelector('.btn-submit');

  /** @type {string} WhatsApp phone number (without +) */
  var WHATSAPP_NUMBER = '916379610554';

  /** @type {string} Fallback email address */
  var FALLBACK_EMAIL = 'owli2026@gmail.com';

  /** @type {number} Rate limit cooldown in seconds */
  var RATE_LIMIT_SECONDS = 10;

  /** @type {boolean} Whether the form is currently rate-limited */
  var isRateLimited = false;


  /* ──────────────────────────────────────────────
     1. VALIDATION RULES
     Each function validates one field and returns
     true (valid) or false (invalid). Error messages
     are shown/hidden by the showError/clearError helpers.
     ────────────────────────────────────────────── */

  /**
   * VALIDATE NAME
   * Rules: Required, minimum 2 characters after trimming whitespace.
   * @returns {boolean} true if valid
   */
  function validateName() {
    var value = nameInput.value.trim();
    if (!value) {
      showError(nameInput);
      return false;
    }
    if (value.length < 2) {
      showError(nameInput);
      return false;
    }
    clearError(nameInput);
    return true;
  }

  /**
   * VALIDATE PHONE
   * Rules: Required, valid Indian mobile number.
   *   - Must be 10 digits after stripping +91 or 0 prefix
   *   - Must start with 6, 7, 8, or 9
   *
   * HOW IT WORKS:
   *   - Strips spaces, dashes, and common prefixes (+91, 0)
   *   - Checks remaining digits against Indian mobile format
   *
   * @returns {boolean} true if valid
   */
  function validatePhone() {
    var value = phoneInput.value.trim();

    /* Remove spaces, dashes, and dots */
    value = value.replace(/[\s\-\.]/g, '');

    /* Strip +91 prefix if present */
    if (value.startsWith('+91')) {
      value = value.substring(3);
    }
    /* Strip leading 0 if present */
    else if (value.startsWith('0')) {
      value = value.substring(1);
    }

    /* Check: must be exactly 10 digits starting with 6-9 */
    var indianPhoneRegex = /^[6-9]\d{9}$/;
    if (!indianPhoneRegex.test(value)) {
      showError(phoneInput);
      return false;
    }
    clearError(phoneInput);
    return true;
  }

  /**
   * VALIDATE EMAIL
   * Rules: Optional field, but if filled, must be a valid email format.
   *   Uses a standard email regex pattern.
   *
   * @returns {boolean} true if valid (or empty)
   */
  function validateEmail() {
    var value = emailInput.value.trim();

    /* Email is optional — empty is valid */
    if (!value) {
      clearError(emailInput);
      return true;
    }

    /* Standard email regex: name@domain.tld */
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      showError(emailInput);
      return false;
    }
    clearError(emailInput);
    return true;
  }

  /**
   * VALIDATE SERVICE
   * Rules: Required, must select a value (not the default empty option).
   *
   * @returns {boolean} true if valid
   */
  function validateService() {
    if (!serviceSelect.value) {
      showError(serviceSelect);
      return false;
    }
    clearError(serviceSelect);
    return true;
  }


  /* ──────────────────────────────────────────────
     2. INLINE ERROR DISPLAY
     Shows/hides error messages below each field.
     Adds/removes a .error class for red border styling.
     ────────────────────────────────────────────── */

  /**
   * SHOW ERROR
   * PURPOSE: Displays the error message below a field
   *   and adds a red border via .error class.
   *
   * @param {HTMLElement} field - The input/select element
   */
  function showError(field) {
    var formGroup = field.closest('.form-group');
    if (!formGroup) return;

    /* Add error styling to the input */
    field.classList.add('error');

    /* Show the error message span */
    var errorSpan = formGroup.querySelector('.form-error');
    if (errorSpan) {
      errorSpan.style.display = 'block';
    }
  }

  /**
   * CLEAR ERROR
   * PURPOSE: Hides the error message and removes the
   *   red border from a field.
   *
   * @param {HTMLElement} field - The input/select element
   */
  function clearError(field) {
    var formGroup = field.closest('.form-group');
    if (!formGroup) return;

    /* Remove error styling */
    field.classList.remove('error');

    /* Hide the error message span */
    var errorSpan = formGroup.querySelector('.form-error');
    if (errorSpan) {
      errorSpan.style.display = 'none';
    }
  }

  /**
   * VALIDATE ALL FIELDS
   * PURPOSE: Runs all validation functions and returns
   *   true only if every field passes.
   *
   * @returns {boolean} true if all fields are valid
   */
  function validateAll() {
    /* Run all validators — use individual variables so
       all errors show at once (not short-circuit) */
    var isNameValid = validateName();
    var isPhoneValid = validatePhone();
    var isEmailValid = validateEmail();
    var isServiceValid = validateService();

    return isNameValid && isPhoneValid && isEmailValid && isServiceValid;
  }


  /* ──────────────────────────────────────────────
     REAL-TIME VALIDATION
     Validate on blur (when user leaves a field) so
     errors appear after they finish typing. Clear
     errors when the user starts typing again (input
     event) for immediate feedback.
     ────────────────────────────────────────────── */

  /* Validate on blur (leaving the field) */
  nameInput.addEventListener('blur', validateName);
  phoneInput.addEventListener('blur', validatePhone);
  emailInput.addEventListener('blur', validateEmail);
  serviceSelect.addEventListener('blur', validateService);
  serviceSelect.addEventListener('change', validateService);

  /* Clear error when user starts typing (instant feedback) */
  nameInput.addEventListener('input', function () { clearError(nameInput); });
  phoneInput.addEventListener('input', function () { clearError(phoneInput); });
  emailInput.addEventListener('input', function () { clearError(emailInput); });


  /* ──────────────────────────────────────────────
     3. FORM SUBMISSION → WHATSAPP
     On valid submit, builds a WhatsApp URL with the
     form data and opens it in a new tab.
     ────────────────────────────────────────────── */

  form.addEventListener('submit', function (e) {
    /* Prevent default form submission (no page reload) */
    e.preventDefault();

    /* Check rate limit */
    if (isRateLimited) return;

    /* Validate all fields */
    if (!validateAll()) {
      /* Scroll to the first error field so the user can see it */
      var firstError = form.querySelector('.error');
      if (firstError) {
        firstError.focus();
      }
      return;
    }

    /* Gather field values */
    var name = nameInput.value.trim();
    var phone = phoneInput.value.trim();
    var email = emailInput.value.trim();
    var service = serviceSelect.value;
    var message = messageInput.value.trim();

    /* Build the WhatsApp message text */
    var waText = 'Name: ' + name
      + '\nPhone: ' + phone
      + '\nEmail: ' + (email || 'Not provided')
      + '\nService: ' + service
      + '\nMessage: ' + (message || 'No message');

    /* Build the WhatsApp URL with encoded text */
    var waUrl = 'https://wa.me/' + WHATSAPP_NUMBER + '?text=' + encodeURIComponent(waText);

    /* Build the mailto fallback URL */
    var mailSubject = encodeURIComponent('Appointment Request - ' + service);
    var mailBody = encodeURIComponent(
      'Name: ' + name
      + '\nPhone: ' + phone
      + '\nEmail: ' + (email || 'Not provided')
      + '\nService: ' + service
      + '\nMessage: ' + (message || 'No message')
    );
    var mailtoUrl = 'mailto:' + FALLBACK_EMAIL + '?subject=' + mailSubject + '&body=' + mailBody;

    /* Open WhatsApp in a new tab */
    window.open(waUrl, '_blank');

    /* Show success message with email fallback */
    showSuccessMessage(mailtoUrl);

    /* Start rate limit countdown */
    startRateLimit();
  });


  /* ──────────────────────────────────────────────
     SUCCESS MESSAGE
     Shows a brief confirmation after form submission
     with a fallback email link.
     ────────────────────────────────────────────── */

  /**
   * SHOW SUCCESS MESSAGE
   * PURPOSE: Displays a confirmation message below the
   *   form with a fallback email link in case WhatsApp
   *   doesn't open.
   *
   * @param {string} mailtoUrl - The mailto: fallback URL
   */
  function showSuccessMessage(mailtoUrl) {
    /* Remove any existing success message */
    var existing = form.querySelector('.form-success');
    if (existing) existing.remove();

    /* Create success message element */
    var successDiv = document.createElement('div');
    successDiv.className = 'form-success';
    successDiv.innerHTML = 'Opening WhatsApp... If it doesn\'t open, '
      + '<a href="' + mailtoUrl + '">click here to email us instead</a>.';

    /* Insert after the submit button */
    submitBtn.parentNode.insertBefore(successDiv, submitBtn.nextSibling);

    /* Auto-remove the message after 15 seconds */
    setTimeout(function () {
      if (successDiv.parentNode) {
        successDiv.remove();
      }
    }, 15000);
  }


  /* ──────────────────────────────────────────────
     5. RATE LIMITING
     After a successful submission, disable the submit
     button for 10 seconds to prevent spam. Shows a
     countdown on the button text.
     ────────────────────────────────────────────── */

  /**
   * START RATE LIMIT
   * PURPOSE: Disables the submit button for RATE_LIMIT_SECONDS
   *   and shows a countdown on the button text.
   */
  function startRateLimit() {
    isRateLimited = true;
    submitBtn.disabled = true;

    var secondsLeft = RATE_LIMIT_SECONDS;

    /* Save original button text to restore later */
    var originalText = submitBtn.textContent;

    /* Update button text with countdown */
    submitBtn.textContent = 'Please wait (' + secondsLeft + 's)...';

    /* Countdown timer — updates every second */
    var countdownTimer = setInterval(function () {
      secondsLeft--;

      if (secondsLeft <= 0) {
        /* Timer complete — re-enable the button */
        clearInterval(countdownTimer);
        isRateLimited = false;
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      } else {
        /* Update countdown text */
        submitBtn.textContent = 'Please wait (' + secondsLeft + 's)...';
      }
    }, 1000);
  }
});
