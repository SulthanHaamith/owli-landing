<?php
/**
 * CRON-RECHECK.PHP — run by Hostinger Cron Jobs (command line only).
 *
 * PURPOSE: Re-checks pending payments with Coaxlure so people who paid and then
 *          closed the browser are still confirmed, then (pass 2) closes the book
 *          on long-abandoned attempts and (pass 3) sends the every-2-days admin
 *          digest email (owli_maybe_digest in _bootstrap.php).
 * HOW:     hPanel -> Advanced -> Cron Jobs -> Custom, every 5 minutes:
 *            /usr/bin/php /home/<USER>/domains/owlihealthcare.com/public_html/api/cron-recheck.php
 *          (the exact path is shown at the top of hPanel -> Cron Jobs)
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require __DIR__ . '/_bootstrap.php';

if (!owli_is_configured()) {
    echo "Not configured yet.\n";
    exit(0);
}

$db = owli_db();

/* Pass 1 — late payments: attempts from the last 3 days; oldest-checked
   first; max 50 per run. */
$rows = $db->query(
    "SELECT * FROM payment_attempts
      WHERE status = 'pending' AND created_at > (NOW() - INTERVAL 3 DAY)
      ORDER BY COALESCE(last_checked_at, created_at) ASC LIMIT 50"
)->fetchAll();

$paid = 0;
foreach ($rows as $row) {
    try {
        if (owli_refresh_attempt($db, $row) === 'paid') {
            $paid++;
        }
    } catch (Throwable $e) {
        error_log('owli cron: ' . $e->getMessage());
    }
}

/* Pass 2 — abandoned payments (owner request 2026-10-25): registrations that
   have sat pending far longer than any payment link lives get one LAST
   verified look at Coaxlure, then flip to 'failed' so the dashboard stops
   counting them as money that might still arrive. Coaxlure's own answer
   still wins — the sweep can never mark a paid person failed. */
$failed = 0;
try {
    $failed = owli_fail_stale_attempts($db);
} catch (Throwable $e) {
    error_log('owli cron sweep: ' . $e->getMessage());
}

/* Pass 3 — admin digest (owner request 2026-10-26): one email every 2 days
   with EVERY registration, to admin.digest_to (falls back to mail_from).
   Best-effort: the schedule stamp lives in the settings table and is only
   written after a successful send, so failures retry on the next run. */
try {
    owli_maybe_digest($db);
} catch (Throwable $e) {
    error_log('owli cron digest: ' . $e->getMessage());
}

echo date('c')
    . " checked=" . count($rows) . " newly_paid=" . $paid
    . " abandoned_failed=" . $failed . "\n";
