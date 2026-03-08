/**
 * I18N.JS — Internationalization (Bilingual Support)
 * Owl I Health and Wellness Center
 *
 * PURPOSE: Handles switching between English and Tamil
 *   on the entire website. Every user-visible text string
 *   is stored here in both languages.
 *
 * HOW IT WORKS:
 *   1. All translatable text in the HTML has a data-i18n="key"
 *      attribute (e.g., data-i18n="hero_heading").
 *   2. This file contains a translations object with 'en'
 *      and 'ta' keys, each holding every translated string.
 *   3. When the user clicks the language toggle button,
 *      the toggleLanguage() function:
 *      - Finds all elements with [data-i18n]
 *      - Replaces their text with the new language version
 *      - Saves the choice to localStorage
 *   4. On page load, the saved language is restored.
 *
 * HOW TO ADD A NEW TRANSLATION:
 *   1. In the HTML, add data-i18n="your_key" to the element
 *   2. In this file, add 'your_key' to both 'en' and 'ta'
 *      objects with the translated text
 *   3. That's it — the toggle system handles the rest
 *
 * DEPENDENCIES: None (vanilla JS only)
 */


/* ──────────────────────────────────────────────
   TRANSLATIONS OBJECT
   Contains every user-visible string in English
   and Tamil. Organized by section for easy editing.

   KEY NAMING CONVENTION:
   - section_element  (e.g., hero_heading, nav_about)
   - section_N_field  (e.g., service_1_title, doctor_2_role)
   ────────────────────────────────────────────── */

const translations = {

  /* ═══════════════════════════════════════════
     ENGLISH TRANSLATIONS
     ═══════════════════════════════════════════ */
  en: {

    /* --- Skip Link (Accessibility) --- */
    skip_to_content: 'Skip to main content',

    /* --- Logo --- */
    logo_text: 'Owl I',

    /* --- Navigation --- */
    nav_home: 'Home',
    nav_about: 'About',
    nav_services: 'Services',
    nav_doctors: 'Doctors',
    nav_gallery: 'Gallery',
    nav_blog: 'Blog',
    nav_contact: 'Contact',
    nav_book_now: 'Book Now',

    /* --- Hero Section --- */
    hero_heading: 'Heal Naturally. Live Fully.',
    hero_subheading: "Tirunelveli's trusted naturopathy and wellness center — holistic healing for body, mind, and soul.",
    hero_cta_book: 'Book Appointment',
    hero_cta_explore: 'Explore Services',

    /* --- About Section --- */
    about_heading: 'About Our Center',
    about_philosophy: "At Owl I Health and Wellness Center, we believe in the body's innate ability to heal itself. Our approach combines time-tested naturopathy traditions with modern wellness practices to deliver drug-free, holistic healing. Every treatment plan is personalized to address the root cause — not just the symptoms — ensuring lasting wellness for body, mind, and soul.",
    about_vision_title: 'Our Vision',
    about_vision: "To be Tirunelveli's leading naturopathy center, making natural and holistic healthcare accessible, affordable, and effective for every individual.",
    about_mission_title: 'Our Mission',
    about_mission: 'To provide personalized, drug-free healing through naturopathy, yoga, and nutrition — empowering our community to live healthier, happier lives through the power of nature.',
    placeholder_clinic_photo: 'Clinic Photo',

    /* --- Services Section --- */
    services_heading: 'Our Services',
    services_subtitle: 'Comprehensive natural healing treatments tailored to your needs',

    service_1_title: 'Hydrotherapy',
    service_1_desc: 'Hip bath, Spinal bath, Steam bath, Enema therapy',
    service_2_title: 'Mud Therapy',
    service_2_desc: 'Mud pack, Mud bath, Clay applications',
    service_3_title: 'Diet Therapy / Nutrition Counseling',
    service_3_desc: 'Therapeutic fasting, Detox diets, Personalized meal plans',
    service_4_title: 'Massage Therapy',
    service_4_desc: 'Full body oil massage, Therapeutic massage, Reflexology',
    service_5_title: 'Yoga Therapy',
    service_5_desc: 'Asanas, Pranayama, Meditation, Therapeutic yoga',
    service_6_title: 'Acupuncture / Acupressure',
    service_6_desc: 'Pain management, Meridian therapy',
    service_7_title: 'Magnetotherapy',
    service_7_desc: 'Magnetic field therapy for chronic pain',
    service_8_title: 'Chromo Therapy',
    service_8_desc: 'Light and color-based healing',
    service_9_title: 'Detox & Panchakarma Programs',
    service_9_desc: '7-day, 14-day, 21-day packages',
    service_10_title: 'Weight Management',
    service_10_desc: 'Diet + Exercise + Therapy combo',
    service_11_title: 'Stress & Anxiety Management',
    service_11_desc: 'Yoga + Counseling + Relaxation therapies',
    service_12_title: 'Chronic Pain Management',
    service_12_desc: 'Back pain, Arthritis, Migraine',
    service_13_title: "Women's Wellness",
    service_13_desc: 'PCOS, Menstrual health, Prenatal care',
    service_14_title: 'Skin & Hair Care',
    service_14_desc: 'Eczema, Psoriasis, Hair fall treatments',
    service_15_title: 'Diabetes & Lifestyle Disease Management',
    service_15_desc: 'Holistic management of lifestyle diseases',

    /* --- Why Choose Us Section --- */
    why_us_heading: 'Why Choose Us',
    why_us_1_title: 'Experienced BNYS Doctors',
    why_us_1_desc: 'Our team of qualified BNYS doctors brings years of clinical expertise in naturopathy and holistic medicine.',
    why_us_2_title: '100% Natural Treatments',
    why_us_2_desc: 'Drug-free healing using water, mud, diet, yoga, and other natural therapies — no side effects, only wellness.',
    why_us_3_title: 'Personalized Care Plans',
    why_us_3_desc: 'Every patient receives a customized treatment plan designed for their unique health needs and goals.',
    why_us_4_title: 'Affordable Wellness',
    why_us_4_desc: 'Quality naturopathy treatments at fair prices — because good health should be accessible to everyone.',

    /* --- Doctors Section --- */
    doctors_heading: 'Meet Our Doctors',
    doctors_subtitle: 'Qualified naturopathy professionals dedicated to your wellness',
    doctor_1_name: 'Dr. [Doctor Name]',
    doctor_1_role: 'Chief Naturopathy Physician',
    doctor_1_qualification: 'BNYS',
    doctor_1_experience: '8+ years experience',
    doctor_1_specialization: 'Chronic disease management, Detox programs',
    doctor_2_name: 'Dr. [Doctor Name]',
    doctor_2_role: 'Yoga & Rehabilitation Specialist',
    doctor_2_qualification: 'BNYS, Certified Yoga Therapist',
    doctor_2_experience: '5+ years experience',
    doctor_2_specialization: 'Musculoskeletal disorders, Stress management',
    doctor_3_name: 'Dr. [Doctor Name]',
    doctor_3_role: 'Nutrition & Diet Therapist',
    doctor_3_qualification: 'BNYS, MSc Nutrition',
    doctor_3_experience: '4+ years experience',
    doctor_3_specialization: 'Weight management, Diabetes reversal',

    /* --- Testimonials Section --- */
    testimonials_heading: 'What Our Patients Say',
    testimonial_1_quote: '"The hydrotherapy sessions have been life-changing for my chronic back pain. After just two weeks, I felt a significant improvement. The doctors here truly care about your well-being."',
    testimonial_1_name: 'Ramesh K.',
    testimonial_1_treatment: 'Hydrotherapy for Back Pain',
    testimonial_2_quote: '"I came here for weight management and the personalized diet plan combined with yoga therapy helped me lose 12 kg in 3 months. Highly recommended!"',
    testimonial_2_name: 'Priya S.',
    testimonial_2_treatment: 'Weight Management Program',
    testimonial_3_quote: '"The mud therapy and naturopathy treatments helped manage my diabetes naturally. My sugar levels have been stable for months now without increasing medication."',
    testimonial_3_name: 'Murugan V.',
    testimonial_3_treatment: 'Diabetes Management',
    testimonial_4_quote: '"I suffered from severe stress and anxiety. The yoga therapy sessions and counseling at Owl I helped me find peace and balance again. Grateful to the entire team."',
    testimonial_4_name: 'Lakshmi R.',
    testimonial_4_treatment: 'Stress & Anxiety Management',
    testimonial_5_quote: '"The detox program was exactly what I needed. 14 days of complete rejuvenation — I left feeling like a new person. The facility is clean and the staff is wonderful."',
    testimonial_5_name: 'Kavitha M.',
    testimonial_5_treatment: '14-Day Detox Program',

    /* --- Gallery Section --- */
    gallery_heading: 'Our Clinic Gallery',
    gallery_subtitle: 'Take a look inside our wellness center',
    gallery_placeholder_1: 'Reception Area',
    gallery_placeholder_2: 'Treatment Room',
    gallery_placeholder_3: 'Yoga Hall',
    gallery_placeholder_4: 'Hydrotherapy Room',
    gallery_placeholder_5: 'Garden Area',
    gallery_placeholder_6: 'Consultation Room',

    /* --- Blog Section --- */
    blog_heading: 'Health & Wellness Blog',
    blog_subtitle: 'Tips and insights for natural living',
    blog_1_title: "5 Benefits of Hydrotherapy You Didn't Know",
    blog_1_excerpt: 'Discover how water-based treatments can relieve pain, improve circulation, and boost your immune system naturally.',
    blog_2_title: 'How Naturopathy Can Help Manage Diabetes Naturally',
    blog_2_excerpt: 'Learn about diet therapy, yoga, and natural treatments that can help stabilize blood sugar levels without increasing medication.',
    blog_3_title: 'Yoga Asanas for Back Pain Relief',
    blog_3_excerpt: 'Simple yoga poses you can practice daily to strengthen your spine, improve posture, and find relief from chronic back pain.',
    blog_read_more: 'Read More →',

    /* --- CTA Banner Section --- */
    cta_heading: 'Ready to Start Your Natural Healing Journey?',
    cta_book: 'Book Appointment',
    cta_call: 'Call Now',

    /* --- Contact Section --- */
    contact_heading: 'Get in Touch',
    contact_subtitle: 'Book an appointment or send us a message',

    /* Form labels */
    form_name_label: 'Your Name *',
    form_phone_label: 'Phone Number *',
    form_email_label: 'Email Address',
    form_service_label: 'Select Service *',
    form_service_default: '-- Choose a Service --',
    form_message_label: 'Your Message',
    form_submit: 'Send via WhatsApp',
    form_fallback_text: 'Or email us at ',

    /* Form error messages */
    form_name_error: 'Please enter your name',
    form_phone_error: 'Please enter a valid 10-digit Indian phone number',
    form_email_error: 'Please enter a valid email address',
    form_service_error: 'Please select a service',

    /* Form placeholders */
    form_name_placeholder: 'Enter your full name',
    form_phone_placeholder: 'e.g., 9876543210',
    form_email_placeholder: 'e.g., you@example.com',
    form_message_placeholder: 'Tell us about your health concern...',

    /* Contact info */
    contact_address_title: 'Address',
    contact_address: '1/120G1, Vishalachi Nagar, Kondanagaram, Suthamalli, Tirunelveli - 627604',
    contact_phone_title: 'Phone',
    contact_whatsapp_title: 'WhatsApp',
    contact_email_title: 'Email',
    contact_hours_title: 'Working Hours',
    contact_hours_weekday: 'Monday – Saturday: 9:00 AM – 9:00 PM',
    contact_hours_sunday: 'Sunday: Closed (or by appointment)',

    /* --- Footer --- */
    footer_about: "Tirunelveli's trusted naturopathy and wellness center. Holistic, drug-free healing for body, mind, and soul.",
    footer_quick_links_title: 'Quick Links',
    footer_services_title: 'Our Services',
    footer_contact_title: 'Contact Us',

    /* --- Floating Buttons --- */
    fab_whatsapp_tooltip: 'Chat on WhatsApp',
    fab_phone_tooltip: 'Call us now'
  },


  /* ═══════════════════════════════════════════
     TAMIL TRANSLATIONS (தமிழ்)
     ═══════════════════════════════════════════ */
  ta: {

    /* --- தொடக்க இணைப்பு --- */
    skip_to_content: 'முதன்மை உள்ளடக்கத்திற்குச் செல்லவும்',

    /* --- சின்னம் --- */
    logo_text: 'Owl I',

    /* --- வழிசெலுத்தல் --- */
    nav_home: 'முகப்பு',
    nav_about: 'எங்களைப் பற்றி',
    nav_services: 'சேவைகள்',
    nav_doctors: 'மருத்துவர்கள்',
    nav_gallery: 'புகைப்படத் தொகுப்பு',
    nav_blog: 'வலைப்பதிவு',
    nav_contact: 'தொடர்பு',
    nav_book_now: 'முன்பதிவு செய்யுங்கள்',

    /* --- ஹீரோ பகுதி --- */
    hero_heading: 'இயற்கையாக குணமாகுங்கள். முழுமையாக வாழுங்கள்.',
    hero_subheading: 'திருநெல்வேலியின் நம்பகமான இயற்கை மருத்துவ மற்றும் ஆரோக்கிய மையம் — உடல், மனம், ஆன்மா ஆகியவற்றிற்கான முழுமையான குணப்படுத்துதல்.',
    hero_cta_book: 'முன்பதிவு செய்யுங்கள்',
    hero_cta_explore: 'சேவைகளை அறியுங்கள்',

    /* --- எங்களைப் பற்றி பகுதி --- */
    about_heading: 'எங்கள் மையத்தைப் பற்றி',
    about_philosophy: 'Owl I ஆரோக்கிய மற்றும் நல்வாழ்வு மையத்தில், உடலின் இயல்பான குணமாகும் திறனை நாங்கள் நம்புகிறோம். காலம் சோதிக்கப்பட்ட இயற்கை மருத்துவ மரபுகளையும் நவீன ஆரோக்கிய நடைமுறைகளையும் இணைத்து, மருந்தில்லா, முழுமையான குணப்படுத்துதலை வழங்குகிறோம். ஒவ்வொரு சிகிச்சைத் திட்டமும் அறிகுறிகளை மட்டுமல்ல — அடிப்படைக் காரணத்தை நிவர்த்தி செய்ய தனிப்பயனாக்கப்பட்டுள்ளது.',
    about_vision_title: 'எங்கள் தொலைநோக்கு',
    about_vision: 'இயற்கை மற்றும் முழுமையான சுகாதாரத்தை அனைவருக்கும் எளிதாகவும், மலிவாகவும், பயனுள்ளதாகவும் மாற்றி, திருநெல்வேலியின் முன்னணி இயற்கை மருத்துவ மையமாக விளங்குவது.',
    about_mission_title: 'எங்கள் நோக்கம்',
    about_mission: 'இயற்கை மருத்துவம், யோகா மற்றும் ஊட்டச்சத்து மூலம் தனிப்பயனாக்கப்பட்ட, மருந்தில்லா குணப்படுத்துதலை வழங்குதல் — இயற்கையின் சக்தி மூலம் ஆரோக்கியமான, மகிழ்ச்சியான வாழ்க்கையை வாழ எங்கள் சமூகத்தை மேம்படுத்துதல்.',
    placeholder_clinic_photo: 'மருத்துவமனை புகைப்படம்',

    /* --- சேவைகள் பகுதி --- */
    services_heading: 'எங்கள் சேவைகள்',
    services_subtitle: 'உங்கள் தேவைகளுக்கேற்ப வடிவமைக்கப்பட்ட இயற்கை குணப்படுத்துதல் சிகிச்சைகள்',

    service_1_title: 'நீர் சிகிச்சை',
    service_1_desc: 'இடுப்பு குளியல், முதுகுத்தண்டு குளியல், நீராவி குளியல், எனிமா சிகிச்சை',
    service_2_title: 'மண் சிகிச்சை',
    service_2_desc: 'மண் பொதி, மண் குளியல், களிமண் பயன்பாடுகள்',
    service_3_title: 'உணவு சிகிச்சை / ஊட்டச்சத்து ஆலோசனை',
    service_3_desc: 'சிகிச்சை உண்ணாவிரதம், நச்சு நீக்க உணவுகள், தனிப்பயன் உணவுத் திட்டங்கள்',
    service_4_title: 'மசாஜ் சிகிச்சை',
    service_4_desc: 'முழு உடல் எண்ணெய் மசாஜ், சிகிச்சை மசாஜ், ரிஃப்ளெக்ஸாலஜி',
    service_5_title: 'யோகா சிகிச்சை',
    service_5_desc: 'ஆசனங்கள், பிராணாயாமம், தியானம், சிகிச்சை யோகா',
    service_6_title: 'குத்தூசி / அக்குபிரஷர்',
    service_6_desc: 'வலி மேலாண்மை, மெரிடியன் சிகிச்சை',
    service_7_title: 'காந்த சிகிச்சை',
    service_7_desc: 'நாள்பட்ட வலிக்கான காந்தப்புல சிகிச்சை',
    service_8_title: 'நிற சிகிச்சை',
    service_8_desc: 'ஒளி மற்றும் நிறம் அடிப்படையிலான குணப்படுத்துதல்',
    service_9_title: 'நச்சு நீக்க & பஞ்சகர்மா திட்டங்கள்',
    service_9_desc: '7 நாள், 14 நாள், 21 நாள் தொகுப்புகள்',
    service_10_title: 'எடை மேலாண்மை',
    service_10_desc: 'உணவு + உடற்பயிற்சி + சிகிச்சை கலவை',
    service_11_title: 'மன அழுத்த & பதட்ட மேலாண்மை',
    service_11_desc: 'யோகா + ஆலோசனை + தளர்வு சிகிச்சைகள்',
    service_12_title: 'நாள்பட்ட வலி மேலாண்மை',
    service_12_desc: 'முதுகு வலி, மூட்டு வலி, ஒற்றைத் தலைவலி',
    service_13_title: 'பெண்கள் நலன்',
    service_13_desc: 'பிசிஓஎஸ், மாதவிடாய் ஆரோக்கியம், பிரசவ முன் பராமரிப்பு',
    service_14_title: 'தோல் & முடி பராமரிப்பு',
    service_14_desc: 'எக்சிமா, சொரியாசிஸ், முடி உதிர்வு சிகிச்சைகள்',
    service_15_title: 'நீரிழிவு & வாழ்க்கைமுறை நோய் மேலாண்மை',
    service_15_desc: 'வாழ்க்கைமுறை நோய்களின் முழுமையான மேலாண்மை',

    /* --- ஏன் எங்களைத் தேர்வு செய்ய வேண்டும் --- */
    why_us_heading: 'ஏன் எங்களைத் தேர்வு செய்ய வேண்டும்',
    why_us_1_title: 'அனுபவமிக்க BNYS மருத்துவர்கள்',
    why_us_1_desc: 'எங்கள் தகுதியான BNYS மருத்துவர்கள் குழு, இயற்கை மருத்துவம் மற்றும் முழுமையான மருத்துவத்தில் பல ஆண்டு மருத்துவ அனுபவத்தை கொண்டுவருகிறது.',
    why_us_2_title: '100% இயற்கை சிகிச்சைகள்',
    why_us_2_desc: 'நீர், மண், உணவு, யோகா மற்றும் பிற இயற்கை சிகிச்சைகளைப் பயன்படுத்தி மருந்தில்லா குணப்படுத்துதல் — பக்கவிளைவுகள் இல்லை, நலம் மட்டுமே.',
    why_us_3_title: 'தனிப்பயன் பராமரிப்பு திட்டங்கள்',
    why_us_3_desc: 'ஒவ்வொரு நோயாளியும் அவர்களின் தனித்துவமான சுகாதார தேவைகள் மற்றும் இலக்குகளுக்கு வடிவமைக்கப்பட்ட தனிப்பயன் சிகிச்சைத் திட்டத்தைப் பெறுகிறார்.',
    why_us_4_title: 'மலிவான ஆரோக்கியம்',
    why_us_4_desc: 'நியாயமான விலையில் தரமான இயற்கை மருத்துவ சிகிச்சைகள் — நல்ல ஆரோக்கியம் அனைவருக்கும் எட்டக்கூடியதாக இருக்க வேண்டும்.',

    /* --- மருத்துவர்கள் பகுதி --- */
    doctors_heading: 'எங்கள் மருத்துவர்களைச் சந்தியுங்கள்',
    doctors_subtitle: 'உங்கள் நலனுக்காக அர்ப்பணிக்கப்பட்ட தகுதிவாய்ந்த இயற்கை மருத்துவ நிபுணர்கள்',
    doctor_1_name: 'Dr. [மருத்துவர் பெயர்]',
    doctor_1_role: 'தலைமை இயற்கை மருத்துவர்',
    doctor_1_qualification: 'BNYS',
    doctor_1_experience: '8+ ஆண்டுகள் அனுபவம்',
    doctor_1_specialization: 'நாள்பட்ட நோய் மேலாண்மை, நச்சு நீக்க திட்டங்கள்',
    doctor_2_name: 'Dr. [மருத்துவர் பெயர்]',
    doctor_2_role: 'யோகா & மறுவாழ்வு நிபுணர்',
    doctor_2_qualification: 'BNYS, சான்றிதழ் பெற்ற யோகா சிகிச்சையாளர்',
    doctor_2_experience: '5+ ஆண்டுகள் அனுபவம்',
    doctor_2_specialization: 'தசை எலும்பு கோளாறுகள், மன அழுத்த மேலாண்மை',
    doctor_3_name: 'Dr. [மருத்துவர் பெயர்]',
    doctor_3_role: 'ஊட்டச்சத்து & உணவு சிகிச்சையாளர்',
    doctor_3_qualification: 'BNYS, MSc ஊட்டச்சத்து',
    doctor_3_experience: '4+ ஆண்டுகள் அனுபவம்',
    doctor_3_specialization: 'எடை மேலாண்மை, நீரிழிவு மாற்றம்',

    /* --- சான்றுகள் பகுதி --- */
    testimonials_heading: 'எங்கள் நோயாளிகள் என்ன சொல்கிறார்கள்',
    testimonial_1_quote: '"நீர் சிகிச்சை அமர்வுகள் எனது நாள்பட்ட முதுகு வலிக்கு வாழ்க்கையை மாற்றியது. வெறும் இரண்டு வாரங்களில், குறிப்பிடத்தக்க முன்னேற்றத்தை உணர்ந்தேன். இங்குள்ள மருத்துவர்கள் உங்கள் நலனில் உண்மையாக அக்கறை கொள்கிறார்கள்."',
    testimonial_1_name: 'ரமேஷ் கே.',
    testimonial_1_treatment: 'முதுகு வலிக்கான நீர் சிகிச்சை',
    testimonial_2_quote: '"எடை மேலாண்மைக்காக இங்கு வந்தேன், தனிப்பயன் உணவுத் திட்டமும் யோகா சிகிச்சையும் இணைந்து 3 மாதங்களில் 12 கிலோ குறைக்க உதவியது. மிகவும் பரிந்துரைக்கிறேன்!"',
    testimonial_2_name: 'பிரியா எஸ்.',
    testimonial_2_treatment: 'எடை மேலாண்மை திட்டம்',
    testimonial_3_quote: '"மண் சிகிச்சை மற்றும் இயற்கை மருத்துவ சிகிச்சைகள் என் நீரிழிவு நோயை இயற்கையாக நிர்வகிக்க உதவியது. மருந்துகளை அதிகரிக்காமல் பல மாதங்களாக என் சர்க்கரை அளவு நிலையாக உள்ளது."',
    testimonial_3_name: 'முருகன் வி.',
    testimonial_3_treatment: 'நீரிழிவு மேலாண்மை',
    testimonial_4_quote: '"கடுமையான மன அழுத்தம் மற்றும் பதட்டத்தால் பாதிக்கப்பட்டேன். Owl I-ல் யோகா சிகிச்சை அமர்வுகளும் ஆலோசனையும் மீண்டும் அமைதியையும் சமநிலையையும் கண்டறிய உதவியது. முழு குழுவிற்கும் நன்றி."',
    testimonial_4_name: 'லட்சுமி ஆர்.',
    testimonial_4_treatment: 'மன அழுத்த & பதட்ட மேலாண்மை',
    testimonial_5_quote: '"நச்சு நீக்க திட்டம் எனக்கு சரியாக தேவையானது. 14 நாட்கள் முழு புத்துணர்ச்சி — ஒரு புதிய நபராக உணர்ந்து வெளியேறினேன். வசதி சுத்தமானது, ஊழியர்கள் அருமை."',
    testimonial_5_name: 'கவிதா எம்.',
    testimonial_5_treatment: '14 நாள் நச்சு நீக்க திட்டம்',

    /* --- புகைப்படத் தொகுப்பு பகுதி --- */
    gallery_heading: 'எங்கள் மருத்துவமனை புகைப்படத் தொகுப்பு',
    gallery_subtitle: 'எங்கள் ஆரோக்கிய மையத்தின் உள்ளே ஒரு பார்வை',
    gallery_placeholder_1: 'வரவேற்புப் பகுதி',
    gallery_placeholder_2: 'சிகிச்சை அறை',
    gallery_placeholder_3: 'யோகா மண்டபம்',
    gallery_placeholder_4: 'நீர் சிகிச்சை அறை',
    gallery_placeholder_5: 'தோட்டப் பகுதி',
    gallery_placeholder_6: 'ஆலோசனை அறை',

    /* --- வலைப்பதிவு பகுதி --- */
    blog_heading: 'ஆரோக்கியம் & நல்வாழ்வு வலைப்பதிவு',
    blog_subtitle: 'இயற்கை வாழ்க்கைக்கான குறிப்புகள் மற்றும் நுண்ணறிவுகள்',
    blog_1_title: 'நீர் சிகிச்சையின் 5 நன்மைகள் — நீங்கள் அறியாதவை',
    blog_1_excerpt: 'நீர் அடிப்படையிலான சிகிச்சைகள் எப்படி வலியைக் குறைக்கும், இரத்த ஓட்டத்தை மேம்படுத்தும், உங்கள் நோய் எதிர்ப்பு சக்தியை இயற்கையாக அதிகரிக்கும் என்பதைக் கண்டறியுங்கள்.',
    blog_2_title: 'இயற்கை மருத்துவம் நீரிழிவு நோயை இயற்கையாக நிர்வகிக்க எப்படி உதவும்',
    blog_2_excerpt: 'மருந்துகளை அதிகரிக்காமல் இரத்த சர்க்கரை அளவை நிலைப்படுத்த உதவும் உணவு சிகிச்சை, யோகா மற்றும் இயற்கை சிகிச்சைகளைப் பற்றி அறியுங்கள்.',
    blog_3_title: 'முதுகு வலி நிவாரணத்திற்கான யோகா ஆசனங்கள்',
    blog_3_excerpt: 'உங்கள் முதுகுத்தண்டை வலுப்படுத்தவும், உடல் நிலையை மேம்படுத்தவும், நாள்பட்ட முதுகு வலியிலிருந்து நிவாரணம் பெறவும் தினமும் பயிற்சி செய்யக்கூடிய எளிய யோகா நிலைகள்.',
    blog_read_more: 'மேலும் படிக்க →',

    /* --- CTA பேனர் பகுதி --- */
    cta_heading: 'உங்கள் இயற்கை குணமாகும் பயணத்தைத் தொடங்க தயாரா?',
    cta_book: 'முன்பதிவு செய்யுங்கள்',
    cta_call: 'இப்போது அழைக்கவும்',

    /* --- தொடர்பு பகுதி --- */
    contact_heading: 'எங்களைத் தொடர்பு கொள்ளுங்கள்',
    contact_subtitle: 'ஒரு முன்பதிவு செய்யுங்கள் அல்லது எங்களுக்கு செய்தி அனுப்புங்கள்',

    /* படிவ லேபிள்கள் */
    form_name_label: 'உங்கள் பெயர் *',
    form_phone_label: 'தொலைபேசி எண் *',
    form_email_label: 'மின்னஞ்சல் முகவரி',
    form_service_label: 'சேவையைத் தேர்ந்தெடுக்கவும் *',
    form_service_default: '-- ஒரு சேவையைத் தேர்ந்தெடுக்கவும் --',
    form_message_label: 'உங்கள் செய்தி',
    form_submit: 'வாட்ஸ்அப் மூலம் அனுப்புங்கள்',
    form_fallback_text: 'அல்லது மின்னஞ்சல் அனுப்புங்கள்: ',

    /* படிவ பிழை செய்திகள் */
    form_name_error: 'உங்கள் பெயரை உள்ளிடவும்',
    form_phone_error: 'சரியான 10 இலக்க இந்திய தொலைபேசி எண்ணை உள்ளிடவும்',
    form_email_error: 'சரியான மின்னஞ்சல் முகவரியை உள்ளிடவும்',
    form_service_error: 'ஒரு சேவையைத் தேர்ந்தெடுக்கவும்',

    /* படிவ ப்ளேஸ்ஹோல்டர்கள் */
    form_name_placeholder: 'உங்கள் முழு பெயரை உள்ளிடவும்',
    form_phone_placeholder: 'எ.கா., 9876543210',
    form_email_placeholder: 'எ.கா., you@example.com',
    form_message_placeholder: 'உங்கள் உடல்நலக் கவலையைப் பற்றி சொல்லுங்கள்...',

    /* தொடர்பு தகவல் */
    contact_address_title: 'முகவரி',
    contact_address: '1/120G1, விசாலாச்சி நகர், கொண்டநாகரம், சுதாமல்லி, திருநெல்வேலி - 627604',
    contact_phone_title: 'தொலைபேசி',
    contact_whatsapp_title: 'வாட்ஸ்அப்',
    contact_email_title: 'மின்னஞ்சல்',
    contact_hours_title: 'வேலை நேரம்',
    contact_hours_weekday: 'திங்கள் – சனி: காலை 9:00 – இரவு 9:00',
    contact_hours_sunday: 'ஞாயிறு: மூடப்பட்டது (அல்லது முன்பதிவு மூலம்)',

    /* --- அடிக்குறிப்பு --- */
    footer_about: 'திருநெல்வேலியின் நம்பகமான இயற்கை மருத்துவ மற்றும் ஆரோக்கிய மையம். உடல், மனம், ஆன்மா ஆகியவற்றிற்கான முழுமையான, மருந்தில்லா குணப்படுத்துதல்.',
    footer_quick_links_title: 'விரைவு இணைப்புகள்',
    footer_services_title: 'எங்கள் சேவைகள்',
    footer_contact_title: 'எங்களைத் தொடர்பு கொள்ளுங்கள்',

    /* --- மிதக்கும் பொத்தான்கள் --- */
    fab_whatsapp_tooltip: 'வாட்ஸ்அப்பில் அரட்டையடிக்கவும்',
    fab_phone_tooltip: 'இப்போது அழைக்கவும்'
  }
};


/* ──────────────────────────────────────────────
   LANGUAGE TOGGLE LOGIC
   Handles switching between English and Tamil,
   saving the preference, and applying translations
   to all elements on the page.
   ────────────────────────────────────────────── */

/**
 * APPLY LANGUAGE
 * PURPOSE: Updates all translatable text on the page
 *   to the specified language.
 *
 * HOW IT WORKS:
 *   1. Finds every element with a [data-i18n] attribute
 *   2. Looks up the translation key in the translations object
 *   3. Replaces the element's text content
 *   4. Also handles placeholders (data-i18n-placeholder)
 *      and tooltips (data-i18n-tooltip)
 *   5. Updates the HTML lang attribute and body class
 *   6. Saves the choice to localStorage
 *
 * @param {string} lang - The language code ('en' or 'ta')
 */
function applyLanguage(lang) {
  var langData = translations[lang];
  if (!langData) return;

  /* --- Update all elements with data-i18n attribute ---
     These are the main translatable text elements. */
  document.querySelectorAll('[data-i18n]').forEach(function (el) {
    var key = el.getAttribute('data-i18n');
    if (langData[key] !== undefined) {
      el.textContent = langData[key];
    }
  });

  /* --- Update placeholder attributes ---
     Form inputs with data-i18n-placeholder get their
     placeholder text translated. */
  document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
    var key = el.getAttribute('data-i18n-placeholder');
    if (langData[key] !== undefined) {
      el.setAttribute('placeholder', langData[key]);
    }
  });

  /* --- Update tooltip text ---
     Floating buttons with data-i18n-tooltip get their
     tooltip <span> text translated via the data-i18n
     on the span. The tooltip attribute itself on the
     parent link can also be updated. */
  document.querySelectorAll('[data-i18n-tooltip]').forEach(function (el) {
    var key = el.getAttribute('data-i18n-tooltip');
    if (langData[key] !== undefined) {
      /* Find the tooltip span inside and update its text */
      var tooltip = el.querySelector('.fab-tooltip');
      if (tooltip) {
        tooltip.textContent = langData[key];
      }
    }
  });

  /* --- Update the HTML lang attribute ---
     This tells browsers and screen readers which
     language the page content is in. */
  document.documentElement.setAttribute('lang', lang === 'ta' ? 'ta' : 'en');

  /* --- Toggle Tamil font class ---
     When Tamil is active, add .lang-ta to body so
     CSS can apply the Noto Sans Tamil font family. */
  if (lang === 'ta') {
    document.body.classList.add('lang-ta');
  } else {
    document.body.classList.remove('lang-ta');
  }

  /* --- Update the language toggle button text ---
     Show the OTHER language as the option to switch to.
     If currently Tamil, show "EN | தமிழ்" to indicate
     you can switch. We keep the button text consistent. */
  var toggleBtn = document.getElementById('lang-toggle');
  if (toggleBtn) {
    /* The button always shows both options; the active
       language could be indicated visually via CSS if needed */
    toggleBtn.querySelector('span').textContent = lang === 'ta' ? 'EN | தமிழ்' : 'EN | தமிழ்';
  }

  /* --- Save language preference to localStorage ---
     Next time the user visits, their choice is remembered. */
  localStorage.setItem('owli-lang', lang);
}


/**
 * TOGGLE LANGUAGE
 * PURPOSE: Switches between English and Tamil.
 *
 * HOW IT WORKS:
 *   - Reads the current language from localStorage
 *   - If it's 'ta', switch to 'en'; otherwise switch to 'ta'
 *   - Calls applyLanguage() with the new language
 */
function toggleLanguage() {
  var currentLang = localStorage.getItem('owli-lang') || 'en';
  var newLang = currentLang === 'en' ? 'ta' : 'en';
  applyLanguage(newLang);
}


/* ──────────────────────────────────────────────
   INITIALIZATION
   When the page loads:
   1. Check if the user previously chose Tamil
   2. If yes, apply Tamil translations immediately
   3. Attach the toggle function to the language button
   ────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

  /* Check for saved language preference */
  var savedLang = localStorage.getItem('owli-lang');

  /* If the user previously chose Tamil, apply it now.
     If no preference or 'en', do nothing — HTML is already in English. */
  if (savedLang === 'ta') {
    applyLanguage('ta');
  }

  /* Attach toggle function to the language button */
  var toggleBtn = document.getElementById('lang-toggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', toggleLanguage);
  }
});
