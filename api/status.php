<?php
/**
 * STATUS.PHP — GET /api/status.php?ref=AP26-XXXXXX
 *
 * PURPOSE: Lets the attendee (and the /events/ page) check whether a
 *          registration is paid. Payment is verified by asking Coaxlure from
 *          the server. A browser redirect is NOT treated as proof of payment.
 * Reply:   { ok, status: 'pending'|'paid'|'failed', reference, amount }
 *          Deliberately returns no personal details.
 */
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

$ref = strtoupper(trim((string)($_GET['ref'] ?? '')));
if (preg_match('/^AP26-[A-Z0-9]{6}$/', $ref) !== 1) {
    owli_json(['ok' => false, 'error' => 'bad_ref'], 422);
}
if (!owli_db_configured()) {
    owli_json(['ok' => false, 'error' => 'not_open'], 503);
}

try {
    $db = owli_db();
    $st = $db->prepare("SELECT id, reference, status, amount_paise FROM registrations WHERE reference = ?");
    $st->execute([$ref]);
    $reg = $st->fetch();
    if (!$reg) {
        owli_json(['ok' => false, 'error' => 'not_found'], 404);
    }

    $status = (string)$reg['status'];

    if ($status === 'pending' && owli_is_configured()) {
        /* Re-check the newest attempt, at most once every 5 seconds per attempt. */
        $at = $db->prepare(
            "SELECT * FROM payment_attempts WHERE registration_id = ? AND status = 'pending'
              AND (last_checked_at IS NULL OR last_checked_at < (NOW() - INTERVAL 5 SECOND))
              ORDER BY id DESC LIMIT 1"
        );
        $at->execute([$reg['id']]);
        $attempt = $at->fetch();
        if ($attempt) {
            try {
                $status = owli_refresh_attempt($db, $attempt);
            } catch (Throwable $e) {
                error_log('owli status check failed: ' . $e->getMessage());
            }
        }
    }

    owli_json([
        'ok'        => true,
        'status'    => $status,
        'reference' => $reg['reference'],
        'amount'    => owli_rupees((int)$reg['amount_paise']),
    ]);
} catch (Throwable $e) {
    error_log('owli status error: ' . $e->getMessage());
    owli_json(['ok' => false, 'error' => 'server'], 500);
}
