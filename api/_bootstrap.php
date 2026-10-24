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
