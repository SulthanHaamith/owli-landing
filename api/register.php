<?php
/**
 * REGISTER.PHP — POST /api/register.php
 *
 * PURPOSE: Validates the registration form, works out the price ON THE SERVER,
 *          saves a pending registration, creates the Coaxlure order + payment
 *          link and returns the link for the browser to open.
 *
 * Body (JSON): category, name, address, mobile, email, coupon?,
 *              student: year_of_study, college, competitions[]
 *              bni:     chapter, organisation
 *              doctor:  qualification, clinic?
 * Reply: { ok:true, reference, payment_url, amount }  or  { ok:false, error, message?, field? }
 *
 * The amount is NEVER read from the browser.
 */
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$in = owli_read_json();

/* Bots fill hidden fields; humans don't. Pretend success, do nothing. */
if (trim((string)($in['website'] ?? '')) !== '') {
    owli_json(['ok' => false, 'error' => 'rejected'], 400);
}

if (owli_registration_mode() !== 'open') {
    /* 'preview' or 'closed': the API must refuse, even if the browser-side
       flag was flipped. quote.php still shows prices in 'preview'. */
    owli_json([
        'ok'      => false,
        'error'   => 'not_open',
        'message' => owli_registration_mode() === 'preview'
            ? 'Online payment is not enabled yet (preview mode). Please register via WhatsApp or email.'
            : 'Registration is not open yet.',
    ], 503);
}

/* Registration window ends with the event. */
$endTs = strtotime('2026-11-15T16:00:00+05:30');
if (time() > $endTs) {
    owli_json(['ok' => false, 'error' => 'closed', 'message' => 'Registration is closed.'], 410);
}

/* ---------------- Validation ---------------- */

/** Trims, collapses whitespace, enforces length. Returns string or null if invalid. */
function field(array $in, string $key, int $min, int $max, bool $required = true): ?string
{
    $v = trim(preg_replace('/\s+/u', ' ', (string)($in[$key] ?? '')) ?? '');
    if ($v === '') {
        return $required ? null : '';
    }
    $len = mb_strlen($v);
    return ($len < $min || $len > $max) ? null : $v;
}

function fail(string $field, string $msg): never
{
    owli_json(['ok' => false, 'error' => 'validation', 'field' => $field, 'message' => $msg], 422);
}

$category = (string)($in['category'] ?? '');
if (!in_array($category, OWLI_CATEGORIES, true)) {
    fail('category', 'Please choose a category.');
}

$name = field($in, 'name', 2, 100);
if ($name === null) {
    fail('name', 'Please enter your full name.');
}
$address = field($in, 'address', 5, 300);
if ($address === null) {
    fail('address', 'Please enter your address.');
}
$mobile = owli_clean_mobile((string)($in['mobile'] ?? ''));
if ($mobile === '') {
    fail('mobile', 'Please enter a valid 10-digit mobile number.');
}
$email = strtolower(trim((string)($in['email'] ?? '')));
if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('email', 'Please enter a valid email address.');
}

$extra = [
    'year_of_study' => null, 'college' => null, 'competitions' => null,
    'chapter' => null, 'organisation' => null, 'qualification' => null, 'clinic' => null,
];

if ($category === 'student') {
    $year = (string)($in['year_of_study'] ?? '');
    if (!in_array($year, OWLI_STUDENT_YEARS, true)) {
        fail('year_of_study', 'Please select your year of study.');
    }
    $college = field($in, 'college', 2, 150);
    if ($college === null) {
        fail('college', 'Please enter your college name.');
    }
    $comps = [];
    if (is_array($in['competitions'] ?? null)) {
        foreach ($in['competitions'] as $c) {
            if (in_array($c, OWLI_COMPETITIONS, true) && !in_array($c, $comps, true)) {
                $comps[] = $c;
            }
        }
    }
    $extra['year_of_study'] = $year;
    $extra['college']       = $college;
    $extra['competitions']  = $comps ? implode(',', $comps) : null;
} elseif ($category === 'bni') {
    $chapter = field($in, 'chapter', 2, 100);
    if ($chapter === null) {
        fail('chapter', 'Please enter your BNI chapter.');
    }
    $org = field($in, 'organisation', 2, 150);
    if ($org === null) {
        fail('organisation', 'Please enter your organisation / business name.');
    }
    $extra['chapter']      = $chapter;
    $extra['organisation'] = $org;
} elseif ($category === 'doctor') {
    $qual = field($in, 'qualification', 2, 100);
    if ($qual === null) {
        fail('qualification', 'Please enter your qualification.');
    }
    $clinic = field($in, 'clinic', 2, 150, false);
    if ($clinic === null) {
        fail('clinic', 'Clinic / hospital name is too long.');
    }
    $extra['qualification'] = $qual;
    $extra['clinic']        = $clinic !== '' ? $clinic : null;
}

$couponInput = strtoupper(trim((string)($in['coupon'] ?? '')));
if ($couponInput !== '' && preg_match('/^[A-Z0-9_-]{2,30}$/', $couponInput) !== 1) {
    fail('coupon', 'That coupon code is not valid.');
}

/* ---------------- Save + create payment ---------------- */

try {
    $db = owli_db();

    /* Simple abuse limit: 8 new registrations per IP per 10 minutes. */
    $rl = $db->prepare("SELECT COUNT(*) FROM registrations WHERE ip = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)");
    $rl->execute([owli_client_ip()]);
    if ((int)$rl->fetchColumn() >= 8) {
        owli_json(['ok' => false, 'error' => 'rate_limited', 'message' => 'Too many attempts. Please try again in a few minutes.'], 429);
    }

    /* One ticket per mobile + email pair:
       - PAID   -> this pair is locked: refuse a second registration outright.
       - PENDING -> NOT a duplicate: the retry block below REUSES that same row
         (same reference, fresh payment attempt), so two people sharing one
         number+email can never create two rows — every submission before
         payment updates the single row for the pair, and only that row can
         ever become paid. */
    $dup = $db->prepare("SELECT reference FROM registrations WHERE mobile = ? AND email = ? AND status = 'paid' LIMIT 1");
    $dup->execute([$mobile, $email]);
    $existingPaid = $dup->fetchColumn();
    if ($existingPaid) {
        owli_json([
            'ok' => false, 'error' => 'already_registered', 'reference' => $existingPaid,
            'message' => 'This mobile number and email are already registered.',
        ], 409);
    }

    /* Price from the server (tier + optional coupon). */
    $q = owli_quote($db, $category, $couponInput);
    if (!$q['ok']) {
        owli_json(['ok' => false, 'error' => 'no_price', 'message' => 'Pricing is not available for this category.'], 500);
    }
    if ($q['coupon_error'] !== null) {
        fail('coupon', $q['coupon_error'] === 'used_up'
            ? 'This coupon has reached its limit.'
            : 'That coupon code is not valid.');
    }
    $final = (int)$q['final_paise'];
    /* Free-ticket flow: a coupon that covers the WHOLE price (100% off, or a flat
       coupon >= base) makes the final amount Rs 0. That is confirmed instantly —
       no Coaxlure order is created (Rs 0 cannot be charged online). Any other
       price below Rs 1 (100 paise, Coaxlure's minimum) is still refused. The old
       Rs 100 policy floor was removed so a small real-money TEST payment
       (Rs 10 = 1000 paise; README §10 go-live step 5) is possible before launch. */
    $isFree = $final === 0 && $q['coupon'] !== null;
    if ($final < 100 && !$isFree) {
        owli_json(['ok' => false, 'error' => 'amount_too_low', 'message' => 'Please contact the organisers for this registration.'], 422);
    }

    /* Reuse a pending registration for the same person (retry) instead of piling up rows. */
    $reg = null;
    $pend = $db->prepare(
        "SELECT * FROM registrations WHERE mobile = ? AND email = ? AND status = 'pending'
          AND created_at > (NOW() - INTERVAL 2 DAY) ORDER BY id DESC LIMIT 1"
    );
    $pend->execute([$mobile, $email]);
    $reg = $pend->fetch() ?: null;

    if ($reg) {
        $db->prepare(
            "UPDATE registrations SET category=?, name=?, address=?, year_of_study=?, college=?, competitions=?,
                    chapter=?, organisation=?, qualification=?, clinic=?, coupon_code=?,
                    base_paise=?, discount_paise=?, amount_paise=?, ip=? WHERE id=?"
        )->execute([
            $category, $name, $address, $extra['year_of_study'], $extra['college'], $extra['competitions'],
            $extra['chapter'], $extra['organisation'], $extra['qualification'], $extra['clinic'], $q['coupon'],
            $q['base_paise'], $q['discount_paise'], $final, owli_client_ip(), $reg['id'],
        ]);
        $regId     = (int)$reg['id'];
        $reference = (string)$reg['reference'];
    } else {
        /* Shared generator (also used by the admin console's add-entry form). */
        $regId = 0;
        for ($try = 0; $try < 5 && $regId === 0; $try++) {
            $reference = owli_new_reference($db);
            try {
                $db->prepare(
                    "INSERT INTO registrations (reference, category, name, address, mobile, email,
                        year_of_study, college, competitions, chapter, organisation, qualification, clinic,
                        coupon_code, base_paise, discount_paise, amount_paise, ip)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
                )->execute([
                    $reference, $category, $name, $address, $mobile, $email,
                    $extra['year_of_study'], $extra['college'], $extra['competitions'],
                    $extra['chapter'], $extra['organisation'], $extra['qualification'], $extra['clinic'],
                    $q['coupon'], $q['base_paise'], $q['discount_paise'], $final, owli_client_ip(),
                ]);
                $regId = (int)$db->lastInsertId();
            } catch (PDOException $e) {
                if (($e->errorInfo[1] ?? 0) !== 1062) { // 1062 = duplicate reference, retry
                    throw $e;
                }
            }
        }
        if ($regId === 0) {
            throw new RuntimeException('reference_generation_failed');
        }
    }

    /* Coupon covered the whole price: confirm instantly, no payment page.
       owli_mark_paid() flips the fresh pending row to paid (once) and sends
       the standard confirmation email — exactly like a paid registration. */
    if ($isFree) {
        owli_mark_paid($db, $regId, null);
        owli_json(['ok' => true, 'reference' => $reference, 'amount' => 0, 'free' => true]);
    }

    $labels = ['student' => 'Student', 'doctor' => 'Doctor', 'bni' => 'BNI Member', 'general' => 'General'];
    $pay = coax_create_payment(
        $db,
        ['name' => $name, 'mobile' => $mobile, 'email' => $email, 'reference' => $reference],
        $final,
        'Arokya Payanam 2026 - ' . $labels[$category] . ' Registration'
    );

    $db->prepare(
        "INSERT INTO payment_attempts (registration_id, coax_token, coax_order_id, amount_paise) VALUES (?,?,?,?)"
    )->execute([$regId, $pay['token'], $pay['order_id'], $final]);

    owli_json([
        'ok'          => true,
        'reference'   => $reference,
        'amount'      => owli_rupees($final),
        'payment_url' => $pay['payment_url'],
    ]);
} catch (Throwable $e) {
    error_log('owli register error: ' . $e->getMessage());
    owli_json(['ok' => false, 'error' => 'server', 'message' => 'Something went wrong. Please try again, or contact us on WhatsApp.'], 500);
}
