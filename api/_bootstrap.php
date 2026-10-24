<?php
/**
 * _BOOTSTRAP.PHP — shared helpers for the Arokya Payanam registration API.
 *
 * PURPOSE: Config loading, database connection, JSON responses, price/coupon
 *          calculation, and the Coaxlure Business API client.
 * NOTES:   - Never called directly (api/.htaccess blocks files starting with "_").
 *          - Secrets are NOT in this repo. They come from owli-config.php
 *            (see api/config.sample.php).
 *          - Amounts are stored as integer PAISE (Rs 299 = 29900).
 *          - Coaxlure is only ever called from here (server side).
 */
declare(strict_types=1);

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

date_default_timezone_set('Asia/Kolkata');
mb_internal_encoding('UTF-8');

const OWLI_CATEGORIES    = ['student', 'doctor', 'bni', 'general'];
const OWLI_STUDENT_YEARS = ['part1', 'part2', '2nd', '3rd', '4th', 'internship'];
const OWLI_COMPETITIONS  = ['poster', 'fireless_cooking'];

/* ------------------------------------------------------------------ */
/* Config                                                              */
/* ------------------------------------------------------------------ */

/** Loads owli-config.php from OUTSIDE public_html first, then api/config.php. */
function owli_config(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }
    $candidates = [
        dirname(__DIR__, 2) . '/owli-config.php', // one level above public_html
        __DIR__ . '/config.php',                  // fallback (blocked by .htaccess)
    ];
    foreach ($candidates as $path) {
        if (is_file($path)) {
            $loaded = require $path;
            $cfg = is_array($loaded) ? $loaded : [];
            return $cfg;
        }
    }
    $cfg = [];
    return $cfg;
}

/** True when the database settings exist (enough for price lookups). */
function owli_db_configured(): bool
{
    $c = owli_config()['db'] ?? [];
    return !empty($c['name']) && !empty($c['user']);
}

/** True when DB + Coaxlure credentials are all present (registration can open). */
function owli_is_configured(): bool
{
    $x = owli_config()['coaxlure'] ?? [];
    return owli_db_configured() && !empty($x['client_id']) && !empty($x['secret']);
}

/**
 * The server-side registration switch. THE authority for the API:
 *   'open'    -> register.php accepts registrations
 *   'preview' -> quote.php shows prices; register.php refuses (503 not_open)
 *   'closed'  -> nothing works yet
 * Value lives in owli-config.php -> registration.mode so it can be changed on
 * the server WITHOUT a code deploy (editing the file flips it immediately).
 * If the key is missing, the old behaviour applies: configured = open,
 * not configured = closed.
 */
function owli_registration_mode(): string
{
    $mode = strtolower(trim((string)(owli_config()['registration']['mode'] ?? '')));
    if ($mode === 'preview') {
        return owli_is_configured() ? 'preview' : 'closed';
    }
    if ($mode === 'open') {
        return owli_is_configured() ? 'open' : 'closed';
    }
    if ($mode === 'closed') {
        return 'closed';
    }
    /* No key set (or blank): legacy behaviour — DB present + credentials = open. */
    return owli_is_configured() ? 'open' : 'closed';
}

/* ------------------------------------------------------------------ */
/* HTTP helpers                                                        */
/* ------------------------------------------------------------------ */

function owli_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Reads a JSON POST body. Requires Content-Type: application/json (blocks cross-site form posts). */
function owli_read_json(): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        owli_json(['ok' => false, 'error' => 'method', 'message' => 'POST required.'], 405);
    }
    $type = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
    if (strpos($type, 'application/json') === false) {
        owli_json(['ok' => false, 'error' => 'bad_request', 'message' => 'JSON required.'], 400);
    }
    $raw  = file_get_contents('php://input', false, null, 0, 20000);
    $data = json_decode($raw === false ? '' : $raw, true);
    if (!is_array($data)) {
        owli_json(['ok' => false, 'error' => 'bad_request', 'message' => 'Invalid request.'], 400);
    }
    return $data;
}

function owli_client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/** Returns a clean 10-digit Indian mobile number, or '' if invalid. */
function owli_clean_mobile(string $raw): string
{
    $d = preg_replace('/\D+/', '', $raw) ?? '';
    if (strlen($d) === 12 && strpos($d, '91') === 0) {
        $d = substr($d, 2);
    } elseif (strlen($d) === 11 && $d[0] === '0') {
        $d = substr($d, 1);
    }
    return preg_match('/^[6-9]\d{9}$/', $d) === 1 ? $d : '';
}

/* ------------------------------------------------------------------ */
/* Database                                                            */
/* ------------------------------------------------------------------ */

function owli_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $c = owli_config()['db'] ?? [];
    if (!owli_db_configured()) {
        throw new RuntimeException('db_not_configured');
    }
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $c['host'] ?? 'localhost', $c['name']);
    $pdo = new PDO($dsn, (string)$c['user'], (string)($c['pass'] ?? ''), [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec("SET time_zone = '+05:30'");
    return $pdo;
}

/* ------------------------------------------------------------------ */
/* Pricing: tiers + coupons (all decided on the server)                */
/* ------------------------------------------------------------------ */

/** Shared: uniquely-named AP26-XXXXXX booking references.
 *  Used by BOTH the public form (api/register.php) and the admin console's
 *  "add entry" action, so staff-added rows and website rows share the same
 *  reference format. The alphabet skips I, L, O, 0 and 1 so a reference
 *  read out over the phone is never ambiguous. Uniqueness is checked against
 *  the DB; callers that INSERT keep their own duplicate-key retry. */
function owli_new_reference(PDO $db): string
{
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    for ($try = 0; $try < 8; $try++) {
        $ref = 'AP26-';
        for ($i = 0; $i < 6; $i++) {
            $ref .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $st = $db->prepare("SELECT 1 FROM registrations WHERE reference = ?");
        $st->execute([$ref]);
        if ($st->fetchColumn() === false) {
            return $ref;
        }
    }
    throw new RuntimeException('reference_generation_failed');
}

/**
 * Works out the price for a category (+ optional coupon code).
 * Returns: ok, base_paise, discount_paise, final_paise, coupon (code|null),
 *          coupon_error (null|'invalid'|'used_up').
 */
function owli_quote(PDO $db, string $category, string $couponCode = ''): array
{
    // A tier with a date window beats an open-ended one (e.g. early-bird over regular).
    $st = $db->prepare(
        "SELECT amount_paise FROM tiers
          WHERE category = ? AND active = 1
            AND (valid_from  IS NULL OR valid_from  <= NOW())
            AND (valid_until IS NULL OR valid_until >= NOW())
          ORDER BY (valid_from IS NULL AND valid_until IS NULL) ASC, id DESC
          LIMIT 1"
    );
    $st->execute([$category]);
    $tier = $st->fetch();
    if (!$tier) {
        return ['ok' => false, 'error' => 'no_tier'];
    }

    $base         = (int)$tier['amount_paise'];
    $discount     = 0;
    $couponUsed   = null;
    $couponError  = null;
    $code         = strtoupper(trim($couponCode));

    if ($code !== '') {
        $cs = $db->prepare(
            "SELECT * FROM coupons
              WHERE code = ? AND active = 1
                AND (category    IS NULL OR category = ?)
                AND (valid_from  IS NULL OR valid_from  <= NOW())
                AND (valid_until IS NULL OR valid_until >= NOW())
              LIMIT 1"
        );
        $cs->execute([$code, $category]);
        $row = $cs->fetch();

        if (!$row) {
            $couponError = 'invalid';
        } else {
            if ($row['max_uses'] !== null) {
                $cnt = $db->prepare("SELECT COUNT(*) FROM registrations WHERE coupon_code = ? AND status = 'paid'");
                $cnt->execute([$code]);
                if ((int)$cnt->fetchColumn() >= (int)$row['max_uses']) {
                    $couponError = 'used_up';
                }
            }
            if ($couponError === null) {
                $value = (int)$row['value'];
                switch ($row['discount_type']) {
                    case 'percent':     // value = 1..100
                        $discount = (int)(round($base * min(100, $value) / 10000) * 100); // whole rupees
                        break;
                    case 'flat':        // value = paise off
                        $discount = min($value, $base);
                        break;
                    case 'fixed_price': // value = paise the attendee pays
                        $discount = max(0, $base - $value);
                        break;
                }
                $discount   = max(0, min($discount, $base));
                $couponUsed = $code;
            }
        }
    }

    return [
        'ok'             => true,
        'base_paise'     => $base,
        'discount_paise' => $discount,
        'final_paise'    => $base - $discount,
        'coupon'         => $couponUsed,
        'coupon_error'   => $couponError,
    ];
}

/** Paise -> rupees (int when whole, else 2-decimal float). */
function owli_rupees(int $paise): int|float
{
    return $paise % 100 === 0 ? intdiv($paise, 100) : round($paise / 100, 2);
}

/* ------------------------------------------------------------------ */
/* Coaxlure Business API client                                        */
/* ------------------------------------------------------------------ */

/** Low-level request. Returns [httpCode, decodedJsonArray]. Throws on network failure. */
function coax_http(string $method, string $path, ?array $body, ?string $token): array
{
    $cfg = owli_config()['coaxlure'] ?? [];
    $url = rtrim((string)($cfg['api_base'] ?? 'https://live.coaxlure.com/merchant/business-api'), '/') . $path;

    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_FOLLOWLOCATION => false,
    ];
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;

    $ch = curl_init($url);
    curl_setopt_array($ch, $opts);
    $raw  = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);

    if ($raw === false) {
        throw new RuntimeException('coax_network: ' . $err);
    }
    $json = json_decode((string)$raw, true);
    return [$http, is_array($json) ? $json : []];
}

/** Bearer token (valid ~30 days). Cached in the settings table; refreshed after 25 days. */
function coax_token(PDO $db, bool $force = false): string
{
    $cfg = owli_config()['coaxlure'] ?? [];

    // Optional: a token generated by hand in the Coaxlure dashboard. Skips /auth.
    $static = trim((string)($cfg['static_token'] ?? ''));
    if ($static !== '') {
        return $static;
    }

    // Cache key includes the API base, so a dev token is never reused against live (and vice versa).
    $base = (string)($cfg['api_base'] ?? 'https://live.coaxlure.com/merchant/business-api');
    $key  = 'coax_token_' . substr(sha1($base), 0, 10);

    if (!$force) {
        $st = $db->prepare(
            "SELECT sval FROM settings WHERE skey = ? AND updated_at > (NOW() - INTERVAL 25 DAY)"
        );
        $st->execute([$key]);
        $cached = $st->fetchColumn();
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }
    }

    [$http, $j] = coax_http('POST', '/auth', [
        'clientId' => (string)($cfg['client_id'] ?? ''),
        'secret'   => (string)($cfg['secret'] ?? ''),
    ], null);

    $token = $j['data']['token'] ?? null;
    if (empty($j['status']) || !is_string($token) || $token === '') {
        throw new RuntimeException('coax_auth_failed: ' . ($j['code'] ?? $http));
    }
    $db->prepare(
        "INSERT INTO settings (skey, sval) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE sval = VALUES(sval), updated_at = NOW()"
    )->execute([$key, $token]);
    return $token;
}

/** Authenticated call; re-authenticates once if the token was rejected. */
function coax_call(PDO $db, string $method, string $path, ?array $body = null): array
{
    [$http, $j] = coax_http($method, $path, $body, coax_token($db));
    if ($http === 401 || in_array($j['code'] ?? '', ['BA_1001', 'BA_1002', 'BA_1003'], true)) {
        [$http, $j] = coax_http($method, $path, $body, coax_token($db, true));
    }
    return [$http, $j];
}

/**
 * Creates the order and the hosted payment link.
 * Returns: token, order_id, payment_url.
 */
function coax_create_payment(PDO $db, array $reg, int $amountPaise, string $itemName): array
{
    $customer = ['mobile' => $reg['mobile'], 'fname' => $reg['name']];
    if (!empty($reg['email'])) {
        $customer['email'] = $reg['email'];
    }

    [$http, $j] = coax_call($db, 'POST', '/order/create-order', [
        'customer'       => $customer,
        'gstPercentage'  => 0,   // no GST on this ticket
        'cashbackAmount' => 0,   // never let Coaxlure cashback rules reduce the price
        'items'          => [[
            'itemName'     => $itemName,
            'pricePerUnit' => owli_rupees($amountPaise),
            'quantity'     => 1,
        ]],
        'exOrderId'      => $reg['reference'],
    ]);

    $token = $j['data']['token'] ?? null;
    if (empty($j['status']) || !is_string($token) || $token === '') {
        throw new RuntimeException('coax_create_order_failed: ' . ($j['code'] ?? $http));
    }

    // The payable total must equal our server-side price.
    $total = $j['data']['costDetails']['totalAmount'] ?? null;
    if ($total !== null) {
        if ((int)round(((float)$total) * 100) !== $amountPaise) {
            throw new RuntimeException('coax_amount_mismatch: expected ' . $amountPaise . ' got ' . $total);
        }
    } else {
        error_log('owli: create-order response had no costDetails.totalAmount; amount not cross-checked');
    }

    /* VERIFIED 2026-10-24 (live): the default link Coaxlure returns points at
       /customer/coaxlure-payments/<token> — a route the checkout app does
       NOT serve (blank page, no order lookup, silently lands on /customer/).
       The working route is /customer/coaxlure-payment/<token> (the same URL
       the merchant app and the customer SMS use). merchantDomain is the
       documented parameter for choosing it. */
    $payBase = (string)(owli_config()['coaxlure']['pay_page_base'] ?? 'https://app.coaxlure.com/customer/coaxlure-payment');
    [$http2, $j2] = coax_call($db, 'POST', '/payment-link', [
        'orderTokenOrInvoiceId' => $token,
        'merchantDomain'        => $payBase,
    ]);
    $url = $j2['data']['paymentLinkUrl'] ?? null;
    if (empty($j2['status']) || !is_string($url)) {
        throw new RuntimeException('coax_payment_link_failed: ' . ($j2['code'] ?? $http2));
    }
    /* Belt and braces: if the API ever ignores merchantDomain and returns the
       dead plural route anyway, rewrite it to the working one. */
    $url = str_replace('/coaxlure-payments/', '/coaxlure-payment/', $url);

    // Only ever redirect attendees to the expected Coaxlure host.
    // allowed_pay_host may be a single host or a list (the dev/test server may use a different host).
    $allowed = (array)(owli_config()['coaxlure']['allowed_pay_host'] ?? 'app.coaxlure.com');
    $parts   = parse_url($url);
    $host    = strtolower((string)($parts['host'] ?? ''));
    $okHost  = false;
    foreach ($allowed as $h) {
        if ($host !== '' && $host === strtolower((string)$h)) {
            $okHost = true;
            break;
        }
    }
    if (($parts['scheme'] ?? '') !== 'https' || !$okHost) {
        error_log('owli: unexpected payment host: ' . $host);
        throw new RuntimeException('coax_unexpected_payment_host');
    }

    return [
        'token'       => $token,
        'order_id'    => (string)($j['data']['orderId'] ?? ''),
        'payment_url' => $url,
    ];
}

/**
 * Asks Coaxlure whether an order is paid.
 *
 * VERIFIED 2026-10-24 (live Rs 10 payment): Coaxlure ROTATES the order's
 * payment token the moment the payment is captured, so the create-time token
 * stops resolving afterwards ("Order not found", BA_4002) — a paid attendee
 * would look pending forever. The ORDER ID is stable and survives rotation,
 * and /payment-status accepts it, so it is preferred. The token stays as a
 * fallback for rows that (unusually) have no order id.
 *
 * Only an explicit paid/success counts. Everything else stays pending.
 * Returns: paid (bool), raw (string for logging).
 */
function coax_fetch_status(PDO $db, string $orderToken, string $orderId = ''): array
{
    $ids = [];
    if ($orderId !== '') {
        $ids[] = ['by' => 'order_id', 'value' => $orderId];
    }
    if ($orderToken !== '') {
        $ids[] = ['by' => 'token', 'value' => $orderToken];
    }

    $raw = 'no_identifier';
    foreach ($ids as $id) {
        [$http, $j] = coax_call($db, 'GET', '/payment-status?orderTokenOrPaymentId=' . rawurlencode($id['value']));
        if ($http !== 200 || empty($j['status'])) {
            /* e.g. BA_4002 "Order not found" — a rotated token. Try the next identifier. */
            $raw = 'http=' . $http . ' code=' . ($j['code'] ?? '') . ' by=' . $id['by'];
            continue;
        }
        $d     = is_array($j['data'] ?? null) ? $j['data'] : $j;
        $order = strtolower((string)($d['orderStatus'] ?? ''));
        $pay   = strtolower((string)($d['paymentStatus'] ?? ''));
        /* Verified wording for a completed live payment: orderStatus "PAID",
           paymentStatus "success". "PENDING" orders return paymentStatus null. */
        return [
            'paid' => in_array($pay, ['paid', 'success', 'completed'], true)
                   || in_array($order, ['paid', 'completed'], true),
            'raw'  => 'order=' . $order . ' payment=' . $pay . ' by=' . $id['by'],
        ];
    }
    return ['paid' => false, 'raw' => $raw];
}

/* ------------------------------------------------------------------ */
/* Payment verification (idempotent)                                   */
/* ------------------------------------------------------------------ */

/** Marks a registration paid exactly once; sends the confirmation email the first time. */
function owli_mark_paid(PDO $db, int $regId, ?int $attemptId): void
{
    $first = false;
    $db->beginTransaction();
    try {
        $u = $db->prepare("UPDATE registrations SET status = 'paid', paid_at = NOW() WHERE id = ? AND status = 'pending'");
        $u->execute([$regId]);
        $first = $u->rowCount() === 1;
        if ($attemptId !== null) {
            $db->prepare("UPDATE payment_attempts SET status = 'paid', verified_at = NOW() WHERE id = ?")->execute([$attemptId]);
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
    if ($first) {
        owli_send_confirmation($db, $regId);
    }
}

/** Re-checks one payment attempt with Coaxlure. Returns 'paid' or 'pending'. */
function owli_refresh_attempt(PDO $db, array $attempt): string
{
    $s = coax_fetch_status($db, (string)$attempt['coax_token'], (string)($attempt['coax_order_id'] ?? ''));
    $db->prepare("UPDATE payment_attempts SET last_checked_at = NOW(), raw_status = ? WHERE id = ?")
       ->execute([substr($s['raw'], 0, 120), $attempt['id']]);
    if ($s['paid']) {
        owli_mark_paid($db, (int)$attempt['registration_id'], (int)$attempt['id']);
        return 'paid';
    }
    return 'pending';
}

/* ------------------------------------------------------------------ */
/* Abandoned-payment sweep (pending -> failed)                          */
/* ------------------------------------------------------------------ */

/* How long a payment attempt may stay pending before it counts as ABANDONED.
   Coaxlure's hosted payment links expire around 10 minutes and the cron
   re-checks attempts for 3 days, so anything still pending after 2 days is a
   person who never came back to pay — the order is dead on Coaxlure's side.
   2 days keeps the row a candidate for the cron's late-payment re-checks,
   then closes the book: the attempt's history row stays for the audit trail,
   but the registration stops counting as money that might still arrive. */
const OWLI_PENDING_DAYS = 2;

/* Marks payment attempts that outlived every payment link as 'failed' — and
   the registration too when nothing live is left on it. Rows with no attempt
   (staff entries, free coupon registrations) are never touched, and a live
   Coaxlure "PAID" answer always wins over the sweep (money beats expiry). */
function owli_fail_stale_attempts(PDO $db): int
{
    $n = 0;
    $rows = $db->query(
        "SELECT id, registration_id, coax_token, coax_order_id
           FROM payment_attempts
          WHERE status = 'pending'
            AND created_at < (NOW() - INTERVAL " . OWLI_PENDING_DAYS . " DAY)
          ORDER BY created_at ASC
          LIMIT 200"
    )->fetchAll();
    foreach ($rows as $a) {
        try {
            /* 1) Coaxlure gets the final word on money before we give up on
               the attempt. Pending-attempts older than the window have not
               been checked by anyone in that time (their window is over),
               but a live "PAID" answer always wins over the sweep. */
            $st = coax_fetch_status($db, (string)$a['coax_token'], (string)($a['coax_order_id'] ?? ''));
            if ($st['paid']) {
                owli_mark_paid($db, (int)$a['registration_id'], (int)$a['id']);
                continue;
            }
            /* 2) Coaxlure never saw a payment: close the attempt, and if no
               attempt for the registration is pending/paid any more, the
               registration is failed too. Registrations with no attempt at
               all (staff-added / free coupon rows) are never touched. */
            $db->beginTransaction();
            $db->prepare("UPDATE payment_attempts SET status = 'failed', last_checked_at = NOW(), raw_status = ? WHERE id = ?")
               ->execute(['expired link (unpaid after ' . OWLI_PENDING_DAYS . ' days)', (int)$a['id']]);
            $live = $db->prepare("SELECT COUNT(*) FROM payment_attempts WHERE registration_id = ? AND status IN ('pending','paid')");
            $live->execute([(int)$a['registration_id']]);
            if ((int)$live->fetchColumn() === 0) {
                $db->prepare("UPDATE registrations SET status = 'failed' WHERE id = ? AND status = 'pending'")->execute([(int)$a['registration_id']]);
            }
            $db->commit();
            $n++;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('owli sweep: attempt #' . $a['id'] . ': ' . $e->getMessage());
        }
    }
    return $n;
}

/** Best-effort confirmation email (never blocks or breaks the payment flow). */
function owli_send_confirmation(PDO $db, int $regId): void
{
    try {
        $st = $db->prepare("SELECT reference, name, email, amount_paise FROM registrations WHERE id = ?");
        $st->execute([$regId]);
        $r = $st->fetch();
        if (!$r || !filter_var($r['email'], FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $from = (string)(owli_config()['mail_from'] ?? 'registrations@owlihealthcare.com');
        $amt = (int)$r['amount_paise'];
        $amountLine = $amt === 0
            ? "Amount: FREE (coupon applied - no payment needed)"
            : "Amount paid: Rs " . owli_rupees($amt);
        $body = "Dear " . $r['name'] . ",\r\n\r\n"
              . "Your registration for Arokya Payanam 2026 is confirmed.\r\n\r\n"
              . "Registration ID: " . $r['reference'] . "\r\n"
              . $amountLine . "\r\n"
              /* Owner request 2026-10-26: the ticket travels with the email —
                 a direct link to the same PDF the success box offers, so the
                 attendee has it even if they never return to the site. */
              . "Entry ticket (PDF): https://owlihealthcare.com/api/ticket.php?ref=" . $r['reference'] . "\r\n"
              . "You can re-download the ticket any time from the event page using your ID.\r\n"
              . "Date: Sunday, 15 November 2026, 9:00 A.M. - 4:00 P.M.\r\n"
              . "Venue: AR Group of Institutions, Kadayam, Tirunelveli\r\n\r\n"
              . "Please keep this ID for the event. Questions? WhatsApp +91 6379610554 or arokyapayanam@gmail.com.\r\n\r\n"
              . "Owl I Health Care Centre";
        $headers = "From: Owl I Health Care Centre <" . $from . ">\r\n"
                 . "Reply-To: arokyapayanam@gmail.com\r\n"
                 . "Content-Type: text/plain; charset=UTF-8";
        $subject = '=?UTF-8?B?' . base64_encode('Registration confirmed - Arokya Payanam 2026') . '?=';
        @mail($r['email'], $subject, $body, $headers);
    } catch (Throwable $e) {
        error_log('owli: confirmation email failed: ' . $e->getMessage());
    }
}

/* ------------------------------------------------------------------ */
/* Admin digest — every 2 days, every entry, one email                  */
/* ------------------------------------------------------------------ */

/* Owner request 2026-10-26: "Send a mail containing all the entries to the
   admin mail once every 2 days." Runs as the cron's pass 3; the recipient
   comes from owli-config.php -> admin.digest_to (falls back to mail_from).
   Best-effort by design: a mail failure is logged and retried on the next
   cron run — the schedule stamp below is only written after a successful
   send, and no email ever blocks the payment checks. */

const OWLI_DIGEST_KEY  = 'digest_last_sent';
const OWLI_DIGEST_GAP  = 2 * 86400;   // seconds between digests
const OWLI_DIGEST_ROWS = 1500;         // hard cap on listed rows per mail

/** One registration, one readable line for the digest body. */
function owli_digest_line(array $r): string
{
    $line = $r['reference'] . ' | ' . str_pad(ucfirst((string)$r['status']), 7)
          . ' | ' . ucfirst((string)$r['category'])
          . ' | Rs ' . owli_rupees((int)$r['amount_paise'])
          . ' | ' . (string)$r['name']
          . ' | ' . (string)$r['mobile']
          . ' | ' . (string)$r['email']
          . ' | ' . substr((string)$r['created_at'], 0, 16);
    if ((string)($r['coupon_code'] ?? '') !== '') {
        $line .= ' | coupon ' . (string)$r['coupon_code'];
    }
    return $line;
}

/** Builds the digest as [subject, body]. Split from the sender so the content
 *  can be previewed/tested without a mail server (LOCAL-TESTING.md). */
function owli_build_digest(PDO $db): array
{
    $sum = $db->query(
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(status = 'paid'), 0)    AS paid,
                COALESCE(SUM(status = 'pending'), 0) AS pending,
                COALESCE(SUM(status = 'failed'), 0)  AS failed,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount_paise END), 0)    AS collected,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount_paise END), 0) AS awaiting
           FROM registrations"
    )->fetch();

    $body  = "Arokya Payanam 2026 - registrations digest\r\n"
           . "Generated " . date('d M Y, h:i A') . " (IST) - automatic, every 2 days\r\n\r\n"
           . "Totals: " . (int)$sum['total'] . " registration(s)\r\n"
           . "  Paid:    " . (int)$sum['paid'] . "  (Rs " . owli_rupees((int)$sum['collected']) . " collected)\r\n"
           . "  Pending: " . (int)$sum['pending'] . "  (Rs " . owli_rupees((int)$sum['awaiting']) . " awaiting payment)\r\n"
           . "  Failed:  " . (int)$sum['failed'] . "\r\n";

    foreach (['paid' => 'PAID', 'pending' => 'PENDING (awaiting payment)', 'failed' => 'FAILED / abandoned'] as $st => $title) {
        $stq = $db->prepare(
            "SELECT reference, status, category, name, mobile, email, coupon_code, amount_paise, created_at
               FROM registrations WHERE status = ? ORDER BY created_at ASC LIMIT " . (OWLI_DIGEST_ROWS + 1)
        );
        $stq->execute([$st]);
        $rows = $stq->fetchAll();
        $body .= "\r\n=== " . $title . " (" . count($rows) . ") ===\r\n";
        if (!$rows) {
            $body .= "(none)\r\n";
            continue;
        }
        $more = count($rows) > OWLI_DIGEST_ROWS ? array_pop($rows) : null;
        foreach ($rows as $r) {
            $body .= owli_digest_line($r) . "\r\n";
        }
        if ($more !== null) {
            $body .= "(list capped at " . OWLI_DIGEST_ROWS . " rows - the admin console has the full list)\r\n";
        }
    }

    $body .= "\r\nFull list, filters and CSV export: https://owlihealthcare.com/admin/\r\n"
           . "This is an automated summary - no reply is needed.\r\n"
           . "Owl I Health Care Centre";

    $subject = 'Arokya Payanam 2026 - registrations digest ('
             . (int)$sum['total'] . ' total, ' . (int)$sum['paid'] . ' paid)';
    return [$subject, $body];
}

/** Sends the digest when one is due (>= 2 days since the last successful one). */
function owli_maybe_digest(PDO $db): void
{
    try {
        $to = trim((string)(owli_config()['admin']['digest_to'] ?? ''));
        if ($to === '') {
            $to = trim((string)(owli_config()['mail_from'] ?? ''));
        }
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;   // nowhere to send: skip quietly, no stamp
        }
        $st = $db->prepare("SELECT sval FROM settings WHERE skey = ?");
        $st->execute([OWLI_DIGEST_KEY]);
        $last = $st->fetchColumn();
        if (is_string($last) && (time() - (int)$last) < OWLI_DIGEST_GAP) {
            return;   // not due yet
        }
        [$subject, $body] = owli_build_digest($db);
        $from    = (string)(owli_config()['mail_from'] ?? 'registrations@owlihealthcare.com');
        $headers = "From: Owl I Health Care Centre <" . $from . ">\r\n"
                 . "Content-Type: text/plain; charset=UTF-8";
        if (!@mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers)) {
            error_log('owli digest: mail() failed - will retry on the next cron run');
            return;
        }
        $db->prepare(
            "INSERT INTO settings (skey, sval) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE sval = VALUES(sval), updated_at = NOW()"
        )->execute([OWLI_DIGEST_KEY, (string)time()]);
    } catch (Throwable $e) {
        error_log('owli digest: ' . $e->getMessage());
    }
}
