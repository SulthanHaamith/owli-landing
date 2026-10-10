/**
 * I18N.JS — Internationalization (Bilingual Support)
 * Owl I Health Care Centre
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
    logo_text: 'Owl I Healthcare',
    mobile_brand_text: 'Owl I Health Care Centre',

    /* --- Navigation --- */
    nav_home: 'Home',
    nav_about: 'About',
    nav_services: 'Services',
    nav_team: 'Our Team',
    nav_gallery: 'Gallery',
    nav_blog: 'Blog',
    nav_faq: 'FAQ',
    nav_contact: 'Contact',
    nav_book_now: 'Book Now',

    /* --- Hero Section --- */
    hero_heading: 'Heal Naturally. Live Fully.',
    hero_subheading: "Tirunelveli's trusted naturopathy and wellness center — holistic healing for body, mind, and soul.",
    hero_cta_book: 'Enquire on WhatsApp',
    hero_cta_explore: 'Explore Services',

    /* --- About Section --- */
    about_heading: 'About Our Center',
    about_philosophy: "At Owl I Health Care Centre, we believe in the body's innate ability to heal itself. Our approach combines time-tested naturopathy traditions with modern wellness practices to deliver drug-free, holistic healing. Every treatment plan is personalized to address the root cause — not just the symptoms — supporting your wellness for body, mind, and soul.",
    about_vision_title: 'Our Vision',
    about_vision: "To be Tirunelveli's leading naturopathy center, making natural and holistic healthcare accessible, affordable, and effective for every individual.",
    about_mission_title: 'Our Mission',
    about_mission: 'To provide personalized, drug-free healing through naturopathy, yoga, and nutrition — empowering our community to live healthier, happier lives through the power of nature.',
    placeholder_clinic_photo: 'Clinic Photo',
    leader_1_name: 'Abdul Salim Malik',
    leader_1_role: 'Founder & CEO',
    leader_1_bio: 'Abdul Salim Malik envisions Owl I Health Care Centre as a place where wellness becomes a natural part of everyday life—not something people seek only when they are unwell. He believes true wellbeing comes from caring for the whole person—body, mind, and lifestyle—and is committed to helping people feel understood, supported, and inspired to live healthier, happier lives.',
    leader_2_name: 'Mrs. Jannath Firthouse',
    leader_2_role: 'Co-Founder & Managing Director',
    leader_2_bio: 'For Mrs. Jannath Firthouse, Owl I Health Care Centre is more than a healthcare centre—it is a vision built with care, compassion, and purpose. As Co-Founder and Managing Director, she is passionate about creating a place where every person feels heard, cared for, and truly at home on their journey towards better health and wellbeing.',

    /* --- Services Section --- */
    services_heading: 'Our Services',
    services_subtitle: 'Comprehensive natural healing treatments tailored to your needs',

    service_1_title: 'Massage Therapy',
    service_1_desc: 'Swedish, Ayurvedic oil, deep tissue, prenatal and postnatal massage',
    service_2_title: 'Hydrotherapy',
    service_2_desc: 'Steam bath, hip bath, and therapeutic water packs',
    service_3_title: 'Fasting & Diet Therapy',
    service_3_desc: 'Therapeutic fasting, individualized diet planning, and nutrition guidance',
    service_4_title: 'Mud Therapy',
    service_4_desc: 'Mud bath, mud application, and mud pack',
    service_5_title: 'Foot Reflexology',
    service_5_desc: 'Gentle pressure-point therapy for relaxation and well-being',
    service_6_title: 'Aromatherapy',
    service_6_desc: 'Aromatherapy massage, essential oil inhalation, and diffusion',
    service_7_title: 'Yoga Therapy',
    service_7_desc: 'Therapeutic yoga, yoga nidra, and monthly yoga classes',
    service_8_title: 'Acu Therapy',
    service_8_desc: 'Acupuncture, acupuncture stimulator, and moxa therapy',
    service_9_title: 'Ozone Therapy',
    service_9_desc: 'Controlled oxygen-ozone therapy for individualized care',
    service_10_title: 'Shirodhara',
    service_10_desc: 'Traditional oil-based relaxation therapy for the forehead',
    service_11_title: 'Kizhi',
    service_11_desc: 'Elakizhi, Njavarakizhi, and Podikizhi herbal poultice therapy',

    /* --- Why Choose Us Section --- */
    why_us_heading: 'Why Choose Us',
    why_us_1_title: 'Experienced BNYS Doctors',
    why_us_1_desc: 'Our team of qualified BNYS doctors brings years of clinical expertise in naturopathy and holistic medicine.',
    why_us_2_title: '100% Natural Treatments',
    why_us_2_desc: "Drug-free healing using water, mud, diet, yoga, and other natural therapies. Treatments are selected according to each individual's health needs and suitability.",
    why_us_3_title: 'Personalized Care Plans',
    why_us_3_desc: 'Every patient receives a customized treatment plan designed for their unique health needs and goals.',
    why_us_4_title: 'Affordable Wellness',
    why_us_4_desc: 'Quality naturopathy treatments at fair prices — because good health should be accessible to everyone.',

    /* --- Team Section (Management + Doctors) --- */
    team_heading: 'Our Team',
    team_subtitle: 'The people behind Owl I Health Care Centre',
    team_management_heading: 'Management & Leadership',
    team_doctors_heading: 'Our Doctors',
    doctor_expertise_title: 'Areas of Expertise',
    doctor_1_name: 'Dr. Jospin Sheela.A',
    doctor_1_role: 'Chief Medical Officer',
    doctor_1_qualification: 'BNYS, M.Sc(Psy), cCGO',
    doctor_1_experience: '6+ years experience',
    doctor_1_specialization: 'Naturopathy, Yoga Therapy, Clinical Nutrition and Lifestyle Medicine',
    doctor_1_exp_1: 'Lifestyle Disorders (Diabetes, Hypertension, Obesity)',
    doctor_1_exp_2: 'PCOS and Hormonal Imbalance',
    doctor_1_exp_3: 'Fertility and Preconception Care',
    doctor_1_exp_4: "Women's Health and Postpartum Recovery",
    doctor_1_exp_5: 'Digestive Disorders',
    doctor_1_exp_6: 'Stress Management and Mental Wellness',
    doctor_1_exp_7: 'Therapeutic Yoga and Natural Detox Therapies',
    doctor_1_exp_8: 'Weight Management and Clinical Nutrition',
    doctor_1_exp_9: 'Skin Disorders and Natural Healing Therapies',
    doctor_2_name: 'Dr. Jebakumar. A',
    doctor_2_role: 'Medical Officer',
    doctor_2_qualification: 'BNYS',
    doctor_2_experience: '1+ year experience',
    doctor_2_specialization: 'Naturopathy, Pain Management, Acupuncture, Rehabilitation',
    doctor_2_exp_1: 'Chronic Pain Management (Sciatica, Arthritis, Joint Pain)',
    doctor_2_exp_2: 'Sports & Activity-Related Injury',
    doctor_2_exp_3: 'Rehabilitation (Stroke & Paralysis)',
    doctor_2_exp_4: 'Acupuncture Therapy',
    doctor_2_exp_5: 'Hydrotherapy',

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

    /* --- FAQ Section --- */
    faq_heading: 'Frequently Asked Questions',
    faq_subtitle: 'Answers to common questions about visiting Owl I Health Care Centre',
    faq_q1: 'Do I need an appointment?',
    faq_a1: 'Appointments are recommended for consultation and treatments. Walk-ins may be accommodated depending on availability.',
    faq_q2: 'How long is a consultation?',
    faq_a2: "A consultation usually takes around 20–30 minutes, depending on the patient's condition and requirements.",
    faq_q3: 'What should I bring for my first visit?',
    faq_a3: 'Please bring your previous medical reports, prescriptions, investigation reports, and details of any medicines or treatments you are currently taking.',
    faq_q4: 'Can I continue my existing medicines?',
    faq_a4: 'Yes. Do not stop or change prescribed medicines on your own. Inform our doctor about your current medicines so that your care can be planned appropriately.',
    faq_q5: 'How many treatment sessions will I need?',
    faq_a5: 'The number of sessions varies depending on your condition, treatment plan and progress. The doctor will recommend a suitable schedule after assessment.',
    faq_q6: 'Can children and elderly patients undergo treatment?',
    faq_a6: 'Yes. Treatment can be considered for children and elderly patients after assessing their age, health condition and individual requirements.',
    faq_q7: 'Can I attend yoga without a consultation?',
    faq_a7: 'Yes, for general yoga classes. However, yoga therapy for a specific health condition may require a consultation and assessment first.',
    faq_q8: 'Can I come only for a specific therapy?',
    faq_a8: 'Yes, depending on the therapy. For therapeutic procedures, an assessment may be required to ensure that the treatment is suitable for you.',
    faq_q9: 'Can I visit Owl I Health Care Centre for wellness even if I have no illness?',
    faq_a9: 'Yes. You can visit for preventive health, wellness, lifestyle improvement, yoga, nutrition and healthy living guidance.',
    faq_q10: 'Is every treatment suitable for everyone?',
    faq_a10: "No. Treatments are selected according to the individual's health condition, age, requirements and contraindications.",

    /* --- CTA Banner Section --- */
    cta_heading: 'Ready to Start Your Natural Healing Journey?',
    cta_book: 'Enquire on WhatsApp',
    cta_call: 'Call Now',

    /* --- Contact Section --- */
    contact_heading: 'Get in Touch',
    contact_subtitle: 'Book an appointment or send us a message',

    /* Form labels */
    form_name_label: 'Your Name *',
    form_phone_label: 'Phone Number *',
    form_email_label: 'Email Address (optional)',
    form_service_label: 'Select Service *',
    form_service_default: '-- Choose a Service --',
    form_message_label: 'Your Message (optional)',
    form_submit: 'Request an Appointment',
    form_fallback_text: 'Or email us at ',
    form_privacy_note: 'Your enquiry is sent through WhatsApp and used only to respond to your request.',
    form_confirm_note: 'Appointments are confirmed after our team responds.',
    form_opt_doctor: 'Doctor Consultation',
    form_opt_wellness: 'General Wellness Enquiry',
    form_opt_unsure: 'Not Sure - Need Guidance',
    service_details_toggle: 'View Details',
    service_details_show_less: 'Show Less',
    service_details_involves: 'What it involves: ',
    service_details_plan: 'A personalised treatment plan is recommended by the doctor based on your health needs.',
    service_details_assessment: 'An assessment may be required before therapeutic procedures to ensure the treatment is suitable for you.',
    service_details_enquire: 'Enquire on WhatsApp',
    service_details_wa_msg: 'Hi! I would like to enquire about {t}.',

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
    contact_hours_weekday: 'Monday to Sunday: 9:00 AM – 8:00 PM',
    contact_hours_sunday: 'Open all days.',

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
    logo_text: 'Owl I Healthcare',
    mobile_brand_text: 'Owl I ஆரோக்கிய மையம்',

    /* --- வழிசெலுத்தல் --- */
    nav_home: 'முகப்பு',
    nav_about: 'எங்களைப் பற்றி',
    nav_services: 'சேவைகள்',
    nav_team: 'எங்கள் குழுவினர்',
    nav_gallery: 'புகைப்படத் தொகுப்பு',
    nav_blog: 'வலைப்பதிவு',
    nav_faq: 'கேள்வி பதில்',
    nav_contact: 'தொடர்பு',
    nav_book_now: 'முன்பதிவு செய்யுங்கள்',

    /* --- ஹீரோ பகுதி --- */
    hero_heading: 'இயற்கையாக குணமாகுங்கள். முழுமையாக வாழுங்கள்.',
    hero_subheading: 'திருநெல்வேலியின் நம்பகமான இயற்கை மருத்துவ மற்றும் ஆரோக்கிய மையம் — உடல், மனம், ஆன்மா ஆகியவற்றிற்கான முழுமையான குணப்படுத்துதல்.',
    hero_cta_book: 'வாட்ஸ்அப் மூலம் விசாரிக்கவும்',
    hero_cta_explore: 'சேவைகளை அறியுங்கள்',

    /* --- எங்களைப் பற்றி பகுதி --- */
    about_heading: 'எங்கள் மையத்தைப் பற்றி',
    about_philosophy: 'Owl I ஆரோக்கிய மற்றும் நல்வாழ்வு மையத்தில், உடலின் இயல்பான குணமாகும் திறனை நாங்கள் நம்புகிறோம். காலம் சோதிக்கப்பட்ட இயற்கை மருத்துவ மரபுகளையும் நவீன ஆரோக்கிய நடைமுறைகளையும் இணைத்து, மருந்தில்லா, முழுமையான குணப்படுத்துதலை வழங்குகிறோம். ஒவ்வொரு சிகிச்சைத் திட்டமும் அறிகுறிகளை மட்டுமல்ல — அடிப்படைக் காரணத்தை நிவர்த்தி செய்ய தனிப்பயனாக்கப்பட்டுள்ளது.',
    about_vision_title: 'எங்கள் தொலைநோக்கு',
    about_vision: 'இயற்கை மற்றும் முழுமையான சுகாதாரத்தை அனைவருக்கும் எளிதாகவும், மலிவாகவும், பயனுள்ளதாகவும் மாற்றி, திருநெல்வேலியின் முன்னணி இயற்கை மருத்துவ மையமாக விளங்குவது.',
    about_mission_title: 'எங்கள் நோக்கம்',
    about_mission: 'இயற்கை மருத்துவம், யோகா மற்றும் ஊட்டச்சத்து மூலம் தனிப்பயனாக்கப்பட்ட, மருந்தில்லா குணப்படுத்துதலை வழங்குதல் — இயற்கையின் சக்தி மூலம் ஆரோக்கியமான, மகிழ்ச்சியான வாழ்க்கையை வாழ எங்கள் சமூகத்தை மேம்படுத்துதல்.',
    placeholder_clinic_photo: 'மருத்துவமனை புகைப்படம்',
    leader_1_name: 'அப்துல் சலீம் மாலிக்',
    leader_1_role: 'நிறுவனர் & தலைமை நிர்வாக அதிகாரி',
    leader_1_bio: 'ஆரோக்கியம் என்பது உடல்நிலை சரியில்லாதபோது மட்டும் தேடப்படும் ஒன்றாக இல்லாமல், அன்றாட வாழ்வின் இயல்பான பகுதியாக மாற வேண்டும் என்பது அப்துல் சலீம் மாலிக்கின் பார்வை. உடல், மனம், வாழ்க்கை முறை என முழு மனிதரையும் கவனித்துக்கொள்வதிலேயே உண்மையான நல்வாழ்வு உள்ளது என நம்பும் அவர், மக்கள் புரிந்துகொள்ளப்பட்டதாகவும், ஆதரவு பெற்றதாகவும் உணர்ந்து, ஆரோக்கியமான, மகிழ்ச்சியான வாழ்க்கை வாழ ஊக்கமளிக்க அர்ப்பணிப்புடன் உள்ளார்.',
    leader_2_name: 'திருமதி. ஜன்னத் ஃபிர்தௌஸ்',
    leader_2_role: 'இணை நிறுவனர் & நிர்வாக இயக்குநர்',
    leader_2_bio: 'திருமதி ஜன்னத் ஃபிர்தௌஸைப் பொறுத்தவரை, Owl I ஆரோக்கிய மையம் வெறும் சுகாதார மையம் மட்டுமல்ல — அக்கறை, கருணை, நோக்கத்துடன் கட்டப்பட்ட ஒரு பார்வை. இணை நிறுவனராகவும் நிர்வாக இயக்குநராகவும், ஒவ்வொரு நபரும் கேட்கப்பட்டதாகவும், கவனிக்கப்பட்டதாகவும், சிறந்த ஆரோக்கியம் நோக்கிய தங்கள் பயணத்தில் உண்மையிலேயே வீட்டில் இருப்பதைப் போலவும் உணரும் ஒரு இடத்தை உருவாக்குவதில் அவர் ஆர்வமாக உள்ளார்.',

    /* --- சேவைகள் பகுதி --- */
    services_heading: 'எங்கள் சேவைகள்',
    services_subtitle: 'உங்கள் தேவைகளுக்கேற்ப வடிவமைக்கப்பட்ட இயற்கை குணப்படுத்துதல் சிகிச்சைகள்',

    service_1_title: 'மசாஜ் சிகிச்சை',
    service_1_desc: 'ஸ்வீடிஷ், ஆயுர்வேத எண்ணெய், டீப் டிஷ்யூ, பிரசவ முன் மற்றும் பிரசவத்திற்குப் பின் மசாஜ்',
    service_2_title: 'நீர் சிகிச்சை',
    service_2_desc: 'நீராவி குளியல், இடுப்பு குளியல் மற்றும் சிகிச்சை நீர் பொதிகள்',
    service_3_title: 'உண்ணாவிரதம் & உணவு சிகிச்சை',
    service_3_desc: 'சிகிச்சை உண்ணாவிரதம், தனிப்பயன் உணவுத் திட்டமிடல் மற்றும் ஊட்டச்சத்து வழிகாட்டுதல்',
    service_4_title: 'மண் சிகிச்சை',
    service_4_desc: 'மண் குளியல், மண் பயன்பாடு மற்றும் மண் பொதி',
    service_5_title: 'கால் ரிஃப்ளெக்ஸாலஜி',
    service_5_desc: 'தளர்வு மற்றும் நல்வாழ்விற்கான மென்மையான அழுத்தப் புள்ளி சிகிச்சை',
    service_6_title: 'நறுமண சிகிச்சை',
    service_6_desc: 'நறுமண மசாஜ், எசன்ஷியல் ஆயில் சுவாசம் மற்றும் டிஃப்யூஷன்',
    service_7_title: 'யோகா சிகிச்சை',
    service_7_desc: 'சிகிச்சை யோகா, யோகா நித்ரா மற்றும் மாதாந்திர யோகா வகுப்புகள்',
    service_8_title: 'குத்தூசி சிகிச்சை',
    service_8_desc: 'குத்தூசி, குத்தூசி ஊக்கி மற்றும் மோக்சா சிகிச்சை',
    service_9_title: 'ஓசோன் சிகிச்சை',
    service_9_desc: 'தனிப்பயனாக்கப்பட்ட கவனிப்புக்கான கட்டுப்படுத்தப்பட்ட ஆக்ஸிஜன்-ஓசோன் சிகிச்சை',
    service_10_title: 'சிரோதாரா',
    service_10_desc: 'நெற்றிக்கான பாரம்பரிய எண்ணெய் அடிப்படையிலான தளர்வு சிகிச்சை',
    service_11_title: 'கிழி',
    service_11_desc: 'எலக்கிழி, நவரக்கிழி மற்றும் பொடிக்கிழி மூலிகை பொட்டலம் சிகிச்சை',

    /* --- ஏன் எங்களைத் தேர்வு செய்ய வேண்டும் --- */
    why_us_heading: 'ஏன் எங்களைத் தேர்வு செய்ய வேண்டும்',
    why_us_1_title: 'அனுபவமிக்க BNYS மருத்துவர்கள்',
    why_us_1_desc: 'எங்கள் தகுதியான BNYS மருத்துவர்கள் குழு, இயற்கை மருத்துவம் மற்றும் முழுமையான மருத்துவத்தில் பல ஆண்டு மருத்துவ அனுபவத்தை கொண்டுவருகிறது.',
    why_us_2_title: '100% இயற்கை சிகிச்சைகள்',
    why_us_2_desc: 'நீர், மண், உணவு, யோகா மற்றும் பிற இயற்கை சிகிச்சைகளைப் பயன்படுத்தி மருந்தில்லா குணப்படுத்துதல். ஒவ்வொரு தனிநபரின் உடல்நலத் தேவைகள் மற்றும் பொருத்தத்தைப் பொறுத்து சிகிச்சைகள் தேர்ந்தெடுக்கப்படுகின்றன.',
    why_us_3_title: 'தனிப்பயன் பராமரிப்பு திட்டங்கள்',
    why_us_3_desc: 'ஒவ்வொரு நோயாளியும் அவர்களின் தனித்துவமான சுகாதார தேவைகள் மற்றும் இலக்குகளுக்கு வடிவமைக்கப்பட்ட தனிப்பயன் சிகிச்சைத் திட்டத்தைப் பெறுகிறார்.',
    why_us_4_title: 'மலிவான ஆரோக்கியம்',
    why_us_4_desc: 'நியாயமான விலையில் தரமான இயற்கை மருத்துவ சிகிச்சைகள் — நல்ல ஆரோக்கியம் அனைவருக்கும் எட்டக்கூடியதாக இருக்க வேண்டும்.',

    /* --- குழு பகுதி (நிர்வாகம் + மருத்துவர்கள்) --- */
    team_heading: 'எங்கள் குழு',
    team_subtitle: 'Owl I ஆரோக்கிய மையத்தின் அர்ப்பணிப்புள்ள குழுவினர்',
    team_management_heading: 'நிர்வாகம் மற்றும் தலைமைத்துவம்',
    team_doctors_heading: 'எங்கள் மருத்துவர்கள்',
    doctor_expertise_title: 'நிபுணத்துவப் பகுதிகள்',
    doctor_1_name: 'Dr. Jospin Sheela.A',
    doctor_1_role: 'தலைமை மருத்துவ அதிகாரி',
    doctor_1_qualification: 'BNYS, M.Sc(Psy), cCGO',
    doctor_1_experience: '6+ ஆண்டுகள் அனுபவம்',
    doctor_1_specialization: 'இயற்கை மருத்துவம், யோகா சிகிச்சை, மருத்துவ ஊட்டச்சத்து மற்றும் வாழ்க்கைமுறை மருத்துவம்',
    doctor_1_exp_1: 'வாழ்க்கைமுறை நோய்கள் (நீரிழிவு, உயர் இரத்த அழுத்தம், உடல் பருமன்)',
    doctor_1_exp_2: 'பிசிஓஎஸ் மற்றும் ஹார்மோன் சமநிலையின்மை',
    doctor_1_exp_3: 'கருவுறுதல் மற்றும் கருத்தரிப்பு முன் பராமரிப்பு',
    doctor_1_exp_4: 'பெண்கள் ஆரோக்கியம் மற்றும் பிரசவத்திற்குப் பிறகான மீட்பு',
    doctor_1_exp_5: 'செரிமான கோளாறுகள்',
    doctor_1_exp_6: 'மன அழுத்த மேலாண்மை மற்றும் மன நலம்',
    doctor_1_exp_7: 'சிகிச்சை யோகா மற்றும் இயற்கை நச்சு நீக்க சிகிச்சைகள்',
    doctor_1_exp_8: 'எடை மேலாண்மை மற்றும் மருத்துவ ஊட்டச்சத்து',
    doctor_1_exp_9: 'தோல் நோய்கள் மற்றும் இயற்கை குணப்படுத்தும் சிகிச்சைகள்',
    doctor_2_name: 'Dr. ஜெபகுமார். A',
    doctor_2_role: 'மருத்துவ அதிகாரி',
    doctor_2_qualification: 'BNYS',
    doctor_2_experience: '1+ வருட அனுபவம்',
    doctor_2_specialization: 'இயற்கை மருத்துவம், வலி மேலாண்மை, குத்தூசி மருத்துவம், மறுவாழ்வு சிகிச்சை',
    doctor_2_exp_1: 'நாள்பட்ட வலி மேலாண்மை (சயாட்டிகா, மூட்டுவலி, மூட்டு வீக்கம்)',
    doctor_2_exp_2: 'விளையாட்டு & செயல்பாடு சார்ந்த காயங்கள்',
    doctor_2_exp_3: 'மறுவாழ்வு சிகிச்சை (பக்கவாதம் & முடக்குவாதம்)',
    doctor_2_exp_4: 'குத்தூசி சிகிச்சை',
    doctor_2_exp_5: 'நீர் சிகிச்சை',

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

    /* --- அடிக்கடி கேட்கப்படும் கேள்விகள் --- */
    faq_heading: 'அடிக்கடி கேட்கப்படும் கேள்விகள்',
    faq_subtitle: 'Owl I ஆரோக்கிய மற்றும் நல்வாழ்வு மையத்திற்கு வருகை தருவது குறித்த பொதுவான கேள்விகளுக்கான பதில்கள்',
    faq_q1: 'எனக்கு முன்பதிவு தேவையா?',
    faq_a1: 'ஆலோசனை மற்றும் சிகிச்சைகளுக்கு முன்பதிவு செய்ய பரிந்துரைக்கப்படுகிறது. கிடைக்கும் தன்மையைப் பொறுத்து நேரடியாக வருபவர்களும் பார்க்கப்படலாம்.',
    faq_q2: 'ஆலோசனை எவ்வளவு நேரம் நீடிக்கும்?',
    faq_a2: 'நோயாளியின் நிலை மற்றும் தேவைகளைப் பொறுத்து ஆலோசனை பொதுவாக 20–30 நிமிடங்கள் ஆகும்.',
    faq_q3: 'எனது முதல் வருகைக்கு என்ன கொண்டு வர வேண்டும்?',
    faq_a3: 'தயவுசெய்து உங்கள் முந்தைய மருத்துவ அறிக்கைகள், மருந்துச் சீட்டுகள், பரிசோதனை அறிக்கைகள் மற்றும் நீங்கள் தற்போது எடுத்துக் கொண்டிருக்கும் மருந்துகள் அல்லது சிகிச்சைகளின் விவரங்களைக் கொண்டு வாருங்கள்.',
    faq_q4: 'நான் எனது தற்போதைய மருந்துகளைத் தொடரலாமா?',
    faq_a4: 'ஆம். பரிந்துரைக்கப்பட்ட மருந்துகளை உங்களாகவே நிறுத்தவோ மாற்றவோ வேண்டாம். உங்கள் பராமரிப்பை சரியாகத் திட்டமிட எங்கள் மருத்துவரிடம் உங்கள் தற்போதைய மருந்துகளைப் பற்றி தெரிவிக்கவும்.',
    faq_q5: 'எனக்கு எத்தனை சிகிச்சை அமர்வுகள் தேவைப்படும்?',
    faq_a5: 'அமர்வுகளின் எண்ணிக்கை உங்கள் நிலை, சிகிச்சைத் திட்டம் மற்றும் முன்னேற்றத்தைப் பொறுத்து மாறுபடும். மதிப்பீட்டிற்குப் பிறகு மருத்துவர் பொருத்தமான அட்டவணையை பரிந்துரைப்பார்.',
    faq_q6: 'குழந்தைகள் மற்றும் முதியோர் சிகிச்சை பெறலாமா?',
    faq_a6: 'ஆம். வயது, உடல்நல நிலை மற்றும் தனிப்பட்ட தேவைகளை மதிப்பீடு செய்த பிறகு குழந்தைகள் மற்றும் முதியோருக்கு சிகிச்சை கருதப்படலாம்.',
    faq_q7: 'ஆலோசனை இல்லாமல் நான் யோகாவில் பங்கேற்கலாமா?',
    faq_a7: 'பொதுவான யோகா வகுப்புகளுக்கு ஆம். இருப்பினும், ஒரு குறிப்பிட்ட உடல்நல நிலைக்கான யோகா சிகிச்சைக்கு முதலில் ஆலோசனை மற்றும் மதிப்பீடு தேவைப்படலாம்.',
    faq_q8: 'ஒரு குறிப்பிட்ட சிகிச்சைக்கு மட்டும் நான் வரலாமா?',
    faq_a8: 'சிகிச்சையைப் பொறுத்து ஆம். சிகிச்சை முறைகளுக்கு, அது உங்களுக்குப் பொருத்தமானதா என உறுதி செய்ய மதிப்பீடு தேவைப்படலாம்.',
    faq_q9: 'எனக்கு எந்த நோயும் இல்லாவிட்டாலும் நல்வாழ்விற்காக Owl I ஆரோக்கிய மையத்திற்கு வரலாமா?',
    faq_a9: 'ஆம். தடுப்பு சுகாதாரம், நல்வாழ்வு, வாழ்க்கைமுறை மேம்பாடு, யோகா, ஊட்டச்சத்து மற்றும் ஆரோக்கியமான வாழ்க்கை வழிகாட்டுதலுக்காக நீங்கள் வரலாம்.',
    faq_q10: 'ஒவ்வொரு சிகிச்சையும் அனைவருக்கும் பொருத்தமானதா?',
    faq_a10: 'இல்லை. தனிநபரின் உடல்நல நிலை, வயது, தேவைகள் மற்றும் தடைகளைப் பொறுத்து சிகிச்சைகள் தேர்ந்தெடுக்கப்படுகின்றன.',

    /* --- CTA பேனர் பகுதி --- */
    cta_heading: 'உங்கள் இயற்கை குணமாகும் பயணத்தைத் தொடங்க தயாரா?',
    cta_book: 'வாட்ஸ்அப் மூலம் விசாரிக்கவும்',
    cta_call: 'இப்போது அழைக்கவும்',

    /* --- தொடர்பு பகுதி --- */
    contact_heading: 'எங்களைத் தொடர்பு கொள்ளுங்கள்',
    contact_subtitle: 'ஒரு முன்பதிவு செய்யுங்கள் அல்லது எங்களுக்கு செய்தி அனுப்புங்கள்',

    /* படிவ லேபிள்கள் */
    form_name_label: 'உங்கள் பெயர் *',
    form_phone_label: 'தொலைபேசி எண் *',
    form_email_label: 'மின்னஞ்சல் முகவரி (விருப்பம்)',
    form_service_label: 'சேவையைத் தேர்ந்தெடுக்கவும் *',
    form_service_default: '-- ஒரு சேவையைத் தேர்ந்தெடுக்கவும் --',
    form_message_label: 'உங்கள் செய்தி (விருப்பம்)',
    form_submit: 'சந்திப்புக்கு கோரிக்கை அனுப்புங்கள்',
    form_fallback_text: 'அல்லது மின்னஞ்சல் அனுப்புங்கள்: ',
    form_privacy_note: 'உங்கள் விசாரணை வாட்ஸ்அப் மூலம் அனுப்பப்படுகிறது; உங்கள் கோரிக்கைக்கு பதிலளிக்க மட்டுமே பயன்படுத்தப்படுகிறது.',
    form_confirm_note: 'எங்கள் குழு பதிலளித்த பிறகுதான் சந்திப்புகள் உறுதிசெய்யப்படும்.',
    form_opt_doctor: 'மருத்துவர் ஆலோசனை',
    form_opt_wellness: 'பொது நல்வாழ்வு விசாரணை',
    form_opt_unsure: 'உறுதியில்லை - வழிகாட்டல் வேண்டும்',
    service_details_toggle: 'விவரங்களைக் காண்க',
    service_details_show_less: 'சுருக்கக்கவும்',
    service_details_involves: 'இதில் அடங்குவது: ',
    service_details_plan: 'உங்கள் உடல்நலத் தேவைகளுக்கு ஏற்ப மருத்துவர் தனிப்பயன் சிகிச்சைத் திட்டத்தை பரிந்துரைப்பார்.',
    service_details_assessment: 'சிகிச்சை முறைகளுக்கு முன், அது உங்களுக்குப் பொருத்தமானதா என உறுதி செய்ய மதிப்பீடு தேவைப்படலாம்.',
    service_details_enquire: 'வாட்ஸ்அப் மூலம் விசாரிக்கவும்',
    service_details_wa_msg: 'வணக்கம்! {t} பற்றி விசாரிக்க விரும்புகிறேன்.',

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
    contact_hours_weekday: 'திங்கள் முதல் ஞாயிறு வரை: காலை 9:00 – இரவு 8:00',
    contact_hours_sunday: 'அனைத்து நாட்களும் திறந்திருக்கும்',

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
