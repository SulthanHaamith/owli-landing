<?php
/**
 * QUOTE.PHP — GET /api/quote.php?category=student&coupon=CODE
 *
 * PURPOSE: Tells the form what to display as the price (and applies a coupon
 *          preview). Display only: register.php recalculates the price itself.
 * Reply:   { ok, mode, open, amount, base, discount, coupon_error }
 *          mode: 'open'    -> registration accepts payments
 *               'preview'  -> prices shown (demo), submissions refused (503)
 *               'closed'   -> nothing works yet
 *          open is kept for older browsers (true only in 'open' mode).
 */
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$category = (string)($_GET['category'] ?? '');
$mode     = owli_registration_mode();   /* 'open' | 'preview' | 'closed' */

/* category=all -> current price of every category (used to label the dropdown). */
if ($category === 'all') {
    if (!owli_db_configured()) {
        owli_json(['ok' => true, 'mode' => 'closed', 'open' => false, 'prices' => new stdClass()]);
    }
    try {
        $db     = owli_db();
        $prices = [];
        foreach (OWLI_CATEGORIES as $c) {
            $q = owli_quote($db, $c, '');
            if ($q['ok']) {
                $prices[$c] = owli_rupees((int)$q['final_paise']);
            }
        }
        owli_json(['ok' => true, 'mode' => $mode, 'open' => $mode === 'open', 'prices' => (object)$prices]);
    } catch (Throwable $e) {
        error_log('owli quote(all) error: ' . $e->getMessage());
        owli_json(['ok' => true, 'mode' => 'closed', 'open' => false, 'prices' => new stdClass()]);
    }
}

if (!in_array($category, OWLI_CATEGORIES, true)) {
    owli_json(['ok' => false, 'error' => 'category'], 422);
}
if (!owli_db_configured()) {
    owli_json(['ok' => true, 'mode' => 'closed', 'open' => false]);
}

$coupon = strtoupper(trim((string)($_GET['coupon'] ?? '')));
if ($coupon !== '' && preg_match('/^[A-Z0-9_-]{2,30}$/', $coupon) !== 1) {
    owli_json(['ok' => true, 'mode' => $mode, 'open' => $mode === 'open', 'coupon_error' => 'invalid']);
}

try {
    $q = owli_quote(owli_db(), $category, $coupon);
    if (!$q['ok']) {
        owli_json(['ok' => true, 'mode' => 'closed', 'open' => false]);
    }
    owli_json([
        'ok'           => true,
        'mode'         => $mode,
        'open'         => $mode === 'open',
        'amount'       => owli_rupees((int)$q['final_paise']),
        'base'         => owli_rupees((int)$q['base_paise']),
        'discount'     => owli_rupees((int)$q['discount_paise']),
        'coupon_error' => $q['coupon_error'],
    ]);
} catch (Throwable $e) {
    error_log('owli quote error: ' . $e->getMessage());
    owli_json(['ok' => true, 'open' => false]);
}
