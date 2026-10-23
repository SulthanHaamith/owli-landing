# Product Requirements Document

## Owl I Health Care Centre Website & Arokya Payanam 2026 Registration

**Document status:** Draft / implementation-aligned  
**Assessment date:** 23 October 2026  
**Repository:** `owli-landing`  
**Production domain:** `https://owlihealthcare.com/`

---

## 1. Executive Summary

Owl I Health Care Centre is a bilingual English/Tamil website for a naturopathy and wellness clinic in Tirunelveli, Tamil Nadu. The product has two related experiences:

1. A public clinic website that builds trust, explains the clinic’s services and team, supports SEO/accessibility, and converts visitors into WhatsApp or phone enquiries.
2. A dedicated Arokya Payanam 2026 event page with category-based registration, server-controlled pricing, and Coaxlure-hosted payment processing backed by PHP and MySQL.

The static website experience is substantially implemented. The event registration backend and local integration have also been implemented and tested against the Coaxlure development API, but production launch remains gated on a real paid-payment verification, production server configuration, and final content/asset review.

---

## 2. Product Goals

### Primary goals

- Establish a credible, professional online presence for Owl I Health Care Centre.
- Make the clinic’s naturopathy services, doctors, leadership, location, hours, and contact options easy to understand.
- Serve both English-speaking and Tamil-speaking visitors through an in-place language toggle.
- Convert interested visitors into qualified appointment enquiries through WhatsApp and phone calls.
- Promote Arokya Payanam 2026 through persistent, clearly differentiated calls to action.
- Collect event registrations securely without trusting client-provided prices.
- Verify event payments server-side and retain sufficient records for event operations.

### Secondary goals

- Improve organic search visibility for naturopathy and wellness queries in Tirunelveli.
- Provide a maintainable implementation that clinic staff/developers can update without a frontend build system.
- Keep operational costs and dependency complexity low through static HTML, CSS, vanilla JavaScript, PHP, and MySQL/MariaDB.

### Non-goals

- Online clinical diagnosis, treatment recommendations, or medical consultation.
- A patient portal or electronic medical-record system.
- A CMS or admin dashboard in the current release.
- Native mobile applications.
- Direct browser-side payment processing or storage of payment credentials.
- Publishing treatment fees or durations without written clinic approval.

---

## 3. Target Users and User Stories

### Clinic visitor / prospective patient

- As a visitor, I want to understand what the clinic offers so I can decide whether it is relevant to me.
- As a Tamil-speaking visitor, I want to switch the site to Tamil without reloading the page.
- As a prospective patient, I want to see the doctors, clinic details, opening hours, and location before contacting the clinic.
- As a visitor, I want to ask a question or request an appointment through WhatsApp or phone.
- As a mobile visitor, I want the primary contact actions to remain easy to reach.

### Event attendee

- As an attendee, I want to see the event date, venue, agenda, competitions, audience, and registration requirements.
- As an attendee, I want to register under the correct category and see the current price.
- As an attendee, I want category-specific fields to appear only when relevant.
- As an attendee, I want to apply a valid coupon where applicable.
- As an attendee, I want to be redirected to a trusted hosted payment page.
- As an attendee, I want to know whether my payment is pending or confirmed when I return.

### Clinic/event operator

- As an operator, I want event prices and coupons to be controlled in the database rather than trusted from the browser.
- As an operator, I want pending payments rechecked automatically through a scheduled job.
- As an operator, I want secrets and attendee data kept out of the repository and public client files.
- As an operator, I want to open or close registration through one documented configuration switch after backend readiness is confirmed.

---

## 4. Scope and Functional Requirements

### 4.1 Clinic homepage

The homepage at `/` must provide:

- Sticky responsive navigation with Home, About, Services, Our Team, FAQ, and Contact links.
- Clinic branding and a hero section with a clear natural-healing value proposition.
- Primary CTAs for WhatsApp enquiry, service exploration, and the Arokya Payanam event.
- About section covering philosophy, vision, mission, and clinic imagery.
- Eleven service cards:
  - Massage Therapy
  - Hydrotherapy
  - Fasting & Diet Therapy
  - Mud Therapy
  - Foot Reflexology
  - Aromatherapy
  - Yoga Therapy
  - Acu Therapy
  - Ozone Therapy
  - Shirodhara
  - Kizhi
- Service detail expansion panels with WhatsApp enquiry links and no unapproved fees/durations.
- Why Choose Us section.
- Management/leadership and doctor profiles using the available real profiles and photos.
- Testimonials carousel, gallery/lightbox, blog preview, FAQ, CTA banner, contact/booking section, and footer.
- Floating phone and WhatsApp actions on every page.

### 4.2 Bilingual experience

- English is the default language.
- Tamil is available through a single-click toggle.
- Text is stored in `js/i18n.js` and applied using `data-i18n` attributes.
- The selected language persists through `localStorage`.
- User-visible strings on both the homepage and event page must have English and Tamil translations.
- Language switching must not reload the page.

### 4.3 Appointment enquiry

- Required fields: name and Indian mobile number.
- Optional fields: email, service, and message.
- Client-side validation for required fields, email format, and Indian mobile format.
- Submission opens a pre-filled WhatsApp message.
- A mailto fallback points to `owli2026@gmail.com`.
- The UI must explain that appointments are confirmed only after the clinic responds.

### 4.4 Event information page

The page at `/events/` must present Arokya Payanam 2026 details:

- Date: Sunday, 15 November 2026.
- Time: 9:00 A.M. – 4:00 P.M.
- Venue: AR Group of Institutions, Kadayam, Tirunelveli.
- Theme: Yoga and Naturopathy for a Stronger and Healthier You.
- Six expert sessions and three competitions.
- Full agenda, objectives, target audience, competition information, contact details, and registration area.
- Event structured data using Schema.org `Event` markup.
- Links back to the clinic homepage and the shared language toggle.

### 4.5 Event registration and payment

- Registration categories: student, general public, BNI member, and doctor.
- Category-specific fields:
  - Students: year of study, college, optional competitions.
  - BNI members: chapter and organisation.
  - Doctors: qualification and optional clinic/hospital.
- Current starting prices, controlled by the database:
  - Student: ₹299.
  - BNI: ₹399.
  - Doctor: ₹399.
  - General: ₹449.
- Coupon types: percentage, flat amount, or fixed price, with optional category/date/use limits.
- Browser prices are display-only. `api/register.php` must recalculate the amount from database tiers.
- Registration data is stored as pending before payment.
- The server creates the Coaxlure order/payment link; credentials never reach the browser.
- The browser accepts only an allowed HTTPS payment host.
- Payment status is checked server-side after return and through the scheduled recheck process.
- The API must return no personal details from the public payment status endpoint.
- Registration must reject invalid input, duplicate paid registrations, honeypot submissions, excessive attempts, and closed/unconfigured states.

### 4.6 Operations and deployment

- Static assets deploy to Hostinger `public_html/` through the existing GitHub deployment flow.
- PHP 8.2+ and MySQL/MariaDB are required for event registration.
- `owli-config.php` must remain outside `public_html` and outside Git.
- `sql/schema.sql` is imported once into the production database.
- `api/cron-recheck.php` runs from CLI every five minutes in production.
- API/internal files are protected by `.htaccess` and direct access to helpers/config/cron is denied.

---

## 5. Non-Functional Requirements

### Accessibility

- Semantic HTML landmarks.
- Skip link, ARIA labels, keyboard navigation, visible focus states, and accessible interactive controls.
- Minimum 44px touch targets on mobile.
- Lightbox focus/keyboard behavior must remain usable.
- Images require meaningful alternative text.

### Performance

- No frontend framework, bundler, or heavy JavaScript library.
- Lazy-load below-the-fold images.
- Target total page weight below 1MB excluding placeholder/media-heavy assets where practical.
- Avoid unnecessary third-party requests; icons should remain inline SVG.

### Security and privacy

- All payment amounts are server-determined.
- Use prepared SQL statements and JSON-only POST input for registration.
- Never expose database credentials, Coaxlure secrets/tokens, or attendee data in Git/client files.
- Do not treat a payment redirect as proof of payment.
- Rate-limit registration attempts and protect against honeypot spam.
- Avoid returning personal data from status responses.

### Compatibility

- Support current versions of Chrome, Firefox, Safari, Edge, and Samsung Internet.
- IE11 is explicitly out of scope.
- Validate layouts at approximately 375px, 768px, and 1440px widths.

---

## 6. Current Implementation Status

### Overall assessment: **Feature-complete prototype / pre-launch integration**

| Area | Status | Evidence / notes |
|---|---|---|
| Static homepage structure | Implemented | `index.html` contains the clinic sections and CTAs. |
| Responsive styling | Implemented | `css/styles.css` and `css/responsive.css`. |
| English/Tamil translations | Implemented | Shared `js/i18n.js`; automated checker is present. |
| Clinic contact conversion | Implemented | WhatsApp, phone, email fallback, contact validation. |
| Service detail panels | Implemented | `js/treatment-details.js`, 11 services. |
| Doctors/leadership content | Implemented | Real profiles/photos are present. |
| Event landing page | Implemented | `events/index.html`, event artwork, agenda, competitions, structured data. |
| Homepage event promotion | Implemented | Popup plus persistent header/hero CTAs. |
| Registration UI | Implemented | Category fields, client validation, pricing display, coupon UI, status UI. |
| Registration API | Implemented | `api/register.php`, `api/quote.php`, `api/status.php`. |
| Database schema/pricing | Implemented | `sql/schema.sql` with tiers, coupons, registrations, attempts, settings. |
| Coaxlure dev integration | Verified locally | Auth/order/payment-link/PENDING status exercised on 23 Oct 2026. |
| Paid-payment verification | **Not complete** | A real ₹1 test payment is still required to confirm the provider’s paid-status wording. |
| Production configuration | **Not verified** | Hostinger DB import, production secrets, cron, and live credentials require confirmation. |
| Final content/assets | **Not complete** | Real testimonials, gallery/blog imagery, OG image, and some social links remain placeholders. |
| Automated project test suite | **Not present** | No package/test runner; PHP CLI is unavailable in the current host shell. |

### Repository state at assessment time

- Branch: `main`, tracking `origin/main`.
- Working tree contains modified and untracked project files; changes have not been committed in this session.
- The backend (`api/`, `sql/`, `admin/`, `scripts/`, `js/event-register.js`) is now part of the repository; `.claude/` stays machine-local and is git-ignored.
- `git diff --stat` reports 7 modified tracked files with 523 insertions and 104 deletions.
- The current `js/event-config.js` sets `registrationOpen: true`, while portions of README/implementation notes still describe the default as false. This must be reconciled before deployment.

---

## 7. Launch Blockers and Open Decisions

### P0 — must complete before public registration

1. **Complete a real paid-payment test.** Use the documented ₹1 test procedure, verify the database changes to `paid`, and confirm `paymentStatus` matching in `_bootstrap.php`.
2. **Configure and verify production infrastructure.** Import the schema, create a least-privilege database user, place `owli-config.php` outside `public_html`, configure live Coaxlure credentials, and set the production payment host allowlist.
3. **Configure the cron job.** Run `cron-recheck.php` every five minutes and confirm it can update abandoned-browser payments.
4. **Reconcile the registration switch.** Keep registration closed until production is validated; make the source-of-truth docs and `registrationOpen` value agree.
5. **Perform live smoke tests.** Verify homepage, event page, language switching, CTAs, registration, payment redirect, return status, and API protection on the deployed domain.

### P1 — should complete before broad marketing

1. Replace placeholder testimonials with approved real testimonials.
2. Replace gallery and blog placeholder assets with approved clinic content.
3. Replace the social sharing/OG image and verify favicon references.
4. Confirm Instagram URL and provide approved Facebook/YouTube URLs if required.
5. Update sitemap `lastmod` values after the final production release.
6. Resolve any documentation drift, including service counts and registration-state descriptions.

### Decisions required from product/clinic owner

- Final approval of all event prices, coupon rules, and registration categories.
- Whether student competitions should include the reels competition or remain limited to the two backend-supported values.
- Final event contact email and operational owner for registration support.
- Whether the Site24x7 RUM script remains for production or is removed as originally noted.
- Approval of all patient testimonials, photos, claims, and social links.

---

## 8. Acceptance Criteria

### Clinic website

- A visitor can load the homepage on mobile and desktop without a build step.
- All primary navigation links scroll to the intended section.
- English/Tamil switching updates all covered visible strings without reload and persists after refresh.
- WhatsApp and phone CTAs open the intended destination with the correct clinic number.
- Contact validation prevents malformed submissions and generates the expected WhatsApp message.
- All real images load, have suitable alt text, and below-fold images are lazy-loaded.
- Keyboard users can reach navigation, forms, carousel/lightbox controls, and close controls.

### Event and registration

- Event facts and agenda match the approved event brief.
- Registration remains visibly closed when either the configuration switch or server readiness is false.
- A valid registration receives a server-generated reference and Coaxlure payment URL.
- Changing the browser-supplied amount cannot alter the amount charged.
- Invalid category-specific data is rejected by both client and server validation.
- Duplicate paid registrations are rejected without creating a second charge.
- A pending payment can be rechecked and becomes paid only after server-side provider verification.
- Cron recheck works from CLI and does not expose a web-accessible execution path.
- Secrets, tokens, and personal attendee details do not appear in client responses or repository files.

### Release

- Production smoke tests pass over HTTPS.
- No blocking browser console errors or missing required assets remain.
- Final content owners approve all visible copy and imagery.
- Registration is opened only after the P0 checklist is complete.

---

## 9. Suggested Delivery Plan

### Phase 1 — Content and consistency

- Reconcile docs/config state.
- Obtain approved photos, testimonials, OG image, blog images, and social URLs.
- Confirm event copy, pricing, categories, and support contacts.

### Phase 2 — Production readiness

- Configure Hostinger database and secrets securely.
- Import schema and configure cron.
- Run the real payment verification test.
- Fix any provider status interpretation or redirect-host issues.

### Phase 3 — Release validation

- Deploy through the existing GitHub/Hostinger workflow.
- Run desktop/mobile, bilingual, accessibility, SEO, and registration smoke tests.
- Confirm monitoring decision and cache-busting versions.

### Phase 4 — Controlled launch and operations

- Open registration using the agreed switch.
- Monitor payment attempts, pending registrations, cron output, and support channels.
- Close or archive event registration after 16 November 2026 and remove/retire event promotion according to the post-event procedure.

---

## 10. Success Metrics

- Homepage and event page load successfully over HTTPS on supported mobile and desktop browsers.
- Increase in qualified WhatsApp/phone appointment enquiries after launch.
- Event registration completion rate from event-page visitors.
- Payment success rate and percentage of pending payments resolved by status checks/cron.
- Zero confirmed cases of client-side price tampering affecting charged amounts.
- No exposed secrets or attendee personal data in public responses, browser storage, or Git history.
- No unresolved P0 issues during the first production monitoring period.

---

## 11. Technical Reference

| Concern | Repository location |
|---|---|
| Homepage | `index.html` |
| Event page | `events/index.html` |
| Translations | `js/i18n.js` |
| Event configuration | `js/event-config.js` |
| Registration client | `js/event-register.js` |
| Registration API | `api/register.php` |
| Quote API | `api/quote.php` |
| Payment status API | `api/status.php` |
| Payment recheck | `api/cron-recheck.php` |
| Shared backend helpers | `api/_bootstrap.php` |
| Admin console | `admin/index.php` |
| Pre-deploy validation | `scripts/validate.php` |
| Database schema | `sql/schema.sql` |
| Developer/project notes | `CLAUDE.md` and `README.md` |
