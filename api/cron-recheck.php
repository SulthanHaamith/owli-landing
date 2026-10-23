<?php
/**
 * CRON-RECHECK.PHP — run by Hostinger Cron Jobs (command line only).
 *
 * PURPOSE: Re-checks pending payments with Coaxlure so people who paid and then
 *          closed the browser are still confirmed.
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
/* Attempts from the last 3 days; oldest-checked first; max 50 per run. */
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
echo date('c') . " checked=" . count($rows) . " newly_paid=" . $paid . "\n";
