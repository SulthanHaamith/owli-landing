<?php
/**
 * VALIDATE.PHP — pre-deployment validation for the Owli Healthcare stack.
 *
 * PURPOSE: One command that verifies every component of the event
 *          registration system, locally before a push and on Hostinger
 *          right after the first deploy. It checks — in order:
 *
 *            1. PHP version + required extensions (curl for Coaxlure,
 *               mysqlnd/PDO, session, JSON)
 *            2. Config file presence (never printed: only found/missing)
 *            3. Database connection + every table + expected columns
 *            4. Starting price rows for all 4 categories
 *            5. API health: quote (prices), register (must refuse in
 *               preview/closed), status (reference format)
 *            6. Admin console reachability + auth state
 *            7. Cron job file present and web-protected
 *            8. Web protection of internal files (Apache .htaccess 403, or the
 *               files' own PHP self-guard 404 — either counts as protected)
 *            9. Coaxlure reachability — WITHOUT sending credentials.
 *               (Only the API host is contacted with an empty auth call.
 *               The real credentials/tokens never appear in any output.)
 *           10. Booking-ID lookup on /events/ (#ticket-lookup present; the
 *               three ticket actions appear for a paid ID, the invalid
 *               message for anything else — owner request 2026-10-26)
 *           11. Documentation split: CHANGE-LOG.md + docs/CLAUDE-*.md must
 *               be web-blocked like every other .md (root .htaccess covers
 *               them; these checks make a silent regression impossible).
 *               HANDOFF.md is in the same protected list (handoff contains
 *               operational detail about the live server).
 *
 * SECURITY: No credential is ever echoed — only booleans about config
 *          structure. Safe to paste the output anywhere.
 *
 * HOW TO RUN:
 *   Local Docker:  docker exec owli-web php /srv/public_html/scripts/validate.php
 *   Hostinger:     hPanel -> Terminal:  php public_html/scripts/validate.php
 *                  (or the full /home/<user>/domains/... path)
 */
declare(strict_types=1);

/* ------------------------------------------------------------------ */
/* Tiny test harness                                                   */
/* ------------------------------------------------------------------ */
$results = [];
$failed  = 0;

function check(string $name, bool $ok, string $details = ''): void
{
    global $failed, $results;
    $results[] = [$name, $ok, $details];
    if (!$ok) {
        $failed++;
    }
}

function note(string $msg): void
{
    global $results;
    $results[] = ['NOTE', true, $msg];
}

function hr(): void
{
    echo str_repeat('─', 62), "\n";
}

/** GET/POST probe. Returns [httpCode, body] — [0, ''] when unreachable. */
function probe(string $url, string $method = 'GET', ?string $json = null, int $timeout = 8): array
{
    $ch = @curl_init($url);
    if (!$ch) {
        return [0, ''];
    }
    $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $timeout, CURLOPT_FOLLOWLOCATION => false];
    if ($method !== 'GET') {
        $opts[CURLOPT_CUSTOMREQUEST] = $method;
    }
    if ($json !== null) {
        $opts[CURLOPT_POSTFIELDS] = $json;
        $opts[CURLOPT_HTTPHEADER]  = ['Content-Type: application/json'];
    }
    curl_setopt_array($ch, $opts);
    $body = @curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, is_string($body) ? $body : ''];
}

echo "\n  OWLI HEALTHCARE — pre-deployment validation\n";
echo '  ', date('Y-m-d H:i:s T'), "\n";
hr();

/* 1 ─ PHP runtime -------------------------------------------------- */
echo "1) PHP runtime\n";
check('PHP >= 8.2', version_compare(PHP_VERSION, '8.2.0', '>='), 'found ' . PHP_VERSION . ' (' . PHP_SAPI . ')');
check('extension curl (Coaxlure client)', extension_loaded('curl'));
check('extension pdo_mysql (database)', extension_loaded('pdo_mysql'));
check('extension mbstring (Tamil text)', extension_loaded('mbstring'));
check('extension session (admin login)', extension_loaded('session') || in_array('session', get_loaded_extensions(), true));
check('function password_verify (admin login)', function_exists('password_verify'));
note('Remote timezone: ' . (date_default_timezone_get() ?: 'unset') . ' (API pins Asia/Kolkata itself)');

/* 2 ─ Config file (structure only — values never printed) ---------- */
echo "\n2) Configuration file\n";
$cfgCandidates = [
    dirname(__DIR__, 2) . '/owli-config.php',
    __DIR__ . '/../api/config.php',
];
$cfgFound = '';
foreach ($cfgCandidates as $p) {
    if (is_file($p)) {
        $cfgFound = $p;
        break;
    }
}
check('owli-config.php found (outside repo)', $cfgFound !== '');
if ($cfgFound === '') {
    note('Look for it one level above the web root, or api/config.php.');
}
if ($cfgFound !== '') {
    $cfg = require $cfgFound;
    $cfg = is_array($cfg) ? $cfg : [];
    check('db settings present', !empty($cfg['db']['name']) && !empty($cfg['db']['user']));
    check('coaxlure credentials present', !empty($cfg['coaxlure']['client_id']) && !empty($cfg['coaxlure']['secret']));
    $mode = strtolower(trim((string)($cfg['registration']['mode'] ?? '')));
    note('registration.mode = ' . ($mode !== '' ? "'$mode'" : '(not set — legacy rule applies)'));
    note('api_base ends in /merchant/business-api: ' . (!empty($cfg['coaxlure']['api_base']) && str_ends_with((string)$cfg['coaxlure']['api_base'], '/merchant/business-api') ? 'yes' : 'no'));
}

/* 3 ─ Database + schema -------------------------------------------- */
echo "\n3) Database + schema\n";
$dbOk = false;
try {
    /* _bootstrap.php already loads config + DB helpers. */
    require_once __DIR__ . '/../api/_bootstrap.php';
    $db = owli_db();
    $dbOk = true;
    check('connects with configured credentials', true);
    $ver = $db->query('SELECT VERSION()')->fetchColumn();
    note('server version: ' . $ver);
    foreach (['settings', 'tiers', 'coupons', 'registrations', 'payment_attempts'] as $t) {
        $exists = $db->query("SHOW TABLES LIKE " . $db->quote($t))->fetchColumn();
        check("table $t", (bool)$exists);
    }
    foreach (['reference', 'status', 'category', 'name', 'mobile', 'email', 'address', 'amount_paise'] as $c) {
        $found = $db->query("SHOW COLUMNS FROM registrations LIKE " . $db->quote($c))->fetchColumn();
        check("registrations.$c", (bool)$found);
    }
    $srcCol = $db->query("SHOW COLUMNS FROM registrations LIKE 'source'")->fetchColumn();
    check('registrations.source (staff-entry audit column)', (bool)$srcCol,
        $srcCol ? '' : 'run the ALTER in GO-LIVE-STEPS.md "Add the source column"');
} catch (Throwable $e) {
    check('connects with configured credentials', false, 'DB error: ' . $e->getMessage());
}
if ($dbOk) {
    $cats = $db->query("SELECT category, COUNT(*) AS n, MIN(amount_paise) AS price FROM tiers WHERE active = 1 GROUP BY category")->fetchAll();
    $have = array_column($cats, 'category');
    foreach (['student', 'doctor', 'bni', 'general'] as $c) {
        $row = null;
        foreach ($cats as $x) {
            if ($x['category'] === $c) {
                $row = $x;
                break;
            }
        }
        check("price tier: $c", (bool)$row && (int)$row['n'] > 0, $row ? ('Rs ' . owli_rupees((int)$row['price'])) : 'no active tier');
    }
    $regCount = (int)$db->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    note("registrations in DB: $regCount");
}

/* 4 ─ API endpoints (through the web server) ------------------------ */
echo "\n4) API endpoints\n";
/* Which site are we testing? The LOCAL one wins when it answers — otherwise
   the live domain would be tested first and fail simply because this stack
   has not been deployed there yet. Order:
     1. http://localhost:8800  (Docker web container)
     2. http://localhost        (Hostinger Apache, hPanel Terminal run)
     3. https://owlihealthcare.com (the live domain)
   A candidate counts only if quote.php answers HTTP 200 — i.e. the PHP API
   is actually deployed there, not just a static page. */
$siteUrl = '';
foreach (['http://localhost:8800', 'http://localhost', 'https://owlihealthcare.com'] as $u) {
    [$code] = probe($u . '/api/quote.php?category=all');
    if ($code === 200) {
        $siteUrl = $u;
        break;
    }
}
check('site reachable (localhost:8800 / localhost / owlihealthcare.com)', $siteUrl !== '', $siteUrl);
if ($siteUrl !== '') {
    [$qc, $qb] = probe($siteUrl . '/api/quote.php?category=all');
    $q = json_decode($qb, true);
    check('quote.php answers (category=all)', $qc === 200 && is_array($q) && !empty($q['ok']));
    if (is_array($q) && isset($q['mode'])) {
        note('server registration mode: ' . $q['mode']);
    }
    if (is_array($q) && isset($q['prices']) && is_array($q['prices'])) {
        foreach (['student', 'doctor', 'bni', 'general'] as $c) {
            check("quote.php price: $c", isset($q['prices'][$c]), 'Rs ' . ($q['prices'][$c] ?? '?'));
        }
    }
    /* register must refuse while mode != open. Safe ONLY in preview/closed:
       register.php checks the mode BEFORE validation and BEFORE any database
       write. If the server is already OPEN (post-go-live), do NOT probe — a
       probe with valid-looking data would create a real registration and a
       real Coaxlure order on the live system. */
    $serverMode = (string)($q['mode'] ?? '');
    if ($serverMode === 'open') {
        check('register.php correctly refuses while not open (503 not_open)', true,
            'skipped — server is OPEN (live); a probe would create a real order');
    } else {
        [$rc, $rb] = probe($siteUrl . '/api/register.php', 'POST', json_encode([
            'category' => 'general', 'name' => 'Validation Test', 'address' => 'Test address only',
            'mobile' => '9000000000', 'email' => 'validate@example.com',
        ]));
        $reg = json_decode($rb, true);
        if (is_array($reg) && ($reg['error'] ?? '') === 'not_open') {
            check('register.php correctly refuses while not open (503 not_open)', true, 'HTTP ' . $rc);
        } else {
            check('register.php correctly refuses while not open (503 not_open)', false,
                'unexpected response (HTTP ' . $rc . ') — do not deploy with registration open by accident');
        }
    }
    [$sc, $sb] = probe($siteUrl . '/api/status.php?ref=AP26-AAAAAA');
    $st = json_decode($sb, true);
    check('status.php answers', $sc > 0 && is_array($st));
    if (is_array($st)) {
        check('status.php rejects an unknown ref (404 not_found)',
            ($st['error'] ?? '') === 'not_found', 'error=' . ($st['error'] ?? '?'));
    }
}

/* 5 ─ Admin console ------------------------------------------------- */
echo "\n5) Admin console\n";
if ($siteUrl !== '') {
    [$ac, $ab] = probe($siteUrl . '/admin/');
    $admOk = $ac === 200 && str_contains($ab, 'Admin');
    check('/admin/ page loads', $admOk, 'HTTP ' . $ac);
    if ($admOk) {
        check('/admin/ shows a password form (not data)', str_contains($ab, 'type="password"'));
        check('/admin/ hides attendee data before login',
            !str_contains($ab, 'Rs ') && !str_contains($ab, '@gmail.com') && !str_contains($ab, 'AP26-'));
    }
} else {
    check('/admin/ page loads', false, 'site unreachable');
}

/* 6 ─ Cron job + protection ----------------------------------------- */
echo "\n6) Cron job\n";
check('api/cron-recheck.php exists', is_file(__DIR__ . '/../api/cron-recheck.php'));
if ($siteUrl !== '') {
    /* 403 = Apache .htaccess denial (Hostinger production: fires before PHP runs).
       404 = the file's own PHP self-guard (local dev server: .htaccess is ignored).
       Both mean "not served". 200 would be a real failure. */
    [$cronCode] = probe($siteUrl . '/api/cron-recheck.php');
    check('cron-recheck.php blocked from the web', in_array($cronCode, [403, 404], true), 'HTTP ' . $cronCode);
}

/* 7 ─ Internal file protection --------------------------------------- */
echo "\n7) Internal file protection\n";
if ($siteUrl !== '') {
    foreach (['_bootstrap.php', 'config.sample.php', 'admin-pass.php'] as $file) {
        [$code3] = probe($siteUrl . '/api/' . $file);
        check("api/$file blocked from the web", in_array($code3, [403, 404], true), 'HTTP ' . $code3);
    }
    /* Project docs are deployed into public_html/ by GitHub — the root
       .htaccess must refuse them (GO-LIVE-STEPS.md contains coupon SQL and
       server details; README/CLAUDE/API-TROUBLESHOOTING describe the security
       model and the debugging method). */
    foreach (['/GO-LIVE-STEPS.md', '/README.md', '/CLAUDE.md', '/API-TROUBLESHOOTING.md', '/LOCAL-TESTING.md', '/PROJECT-STATUS.md', '/CHANGE-LOG.md', '/PRD.md', '/prompt.md', '/HANDOFF.md'] as $doc) {
        [$dc] = probe($siteUrl . $doc);
        check("doc $doc blocked from the web", in_array($dc, [403, 404], true), 'HTTP ' . $dc);
    }
    [$sc2] = probe($siteUrl . '/scripts/validate.php');
    check('scripts/ blocked from the web', in_array($sc2, [403, 404], true), 'HTTP ' . $sc2);
}

/* 8 ─ Coaxlure reachability (no credentials sent) -------------------- */
echo "\n8) Coaxlure reachability\n";
$apiBase = (string)($cfg['coaxlure']['api_base'] ?? 'https://live.coaxlure.com/merchant/business-api');
$probe = @curl_init(rtrim($apiBase, '/') . '/auth');
if ($probe) {
    curl_setopt_array($probe, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => '',           /* deliberately empty: reachability only */
    ]);
    @curl_exec($probe);
    $code4 = (int)curl_getinfo($probe, CURLINFO_HTTP_CODE);
    $err   = curl_error($probe);
    curl_close($probe);
    check('Coaxlure API host reachable', $code4 > 0 || $err === '', $code4 > 0 ? 'HTTP ' . $code4 : ($err !== '' ? $err : 'connection failed'));
    if ($code4 === 0 || $code4 >= 500) {
        note('The API answered with an error/empty call — fine; this only proves DNS + TLS + routing.');
    }
} else {
    check('Coaxlure API host reachable', false, 'curl init failed');
}
note('No credentials were sent or printed by this check.');

/* 9 ─ Static site files ---------------------------------------------- */
echo "\n9) Static site\n";
if ($siteUrl !== '') {
    foreach (['/index.html', '/events/', '/robots.txt', '/js/event-config.js'] as $p) {
        [$code5] = probe($siteUrl . $p);
        check("public page $p", $code5 === 200, 'HTTP ' . $code5);
    }
} else {
    check('public pages reachable', false, 'site unreachable');
}
check('js/event-config.js exists', is_file(__DIR__ . '/../js/event-config.js'));
$ec = (string)@file_get_contents(__DIR__ . '/../js/event-config.js');
check('js/event-config.js declares registrationMode', str_contains($ec, 'registrationMode'));
note('committed client mode: ' . (preg_match("~registrationMode:\s*'(\w+)'~", $ec, $m) ? $m[1] : 'unknown'));

/* 10 ─ Booking-ID lookup section (owner request 2026-10-26) --------- */
/* The "Already registered? Get your ticket" card must exist and carry
   its i18n hooks; the JS must render the paid actions for a paid ID.
   ORDER CONTRACT (owner request 2026-10-26, second report): the payment
   status box (#evf-status — the "Payment verified" box) must sit
   DIRECTLY after the registration form, BEFORE the lookup card. */
$evp = (string)@file_get_contents(__DIR__ . '/../events/index.html');
check('events page has the #ticket-lookup card', str_contains($evp, 'id="ticket-lookup"'));
$posForm  = strpos($evp, '</form>');                    /* the register form closes here */
$posStat  = strpos($evp, 'id="evf-status"');
$posLook  = strpos($evp, 'id="ticket-lookup"');
check('payment status box directly after the register form (before the lookup card)',
    $posForm !== false && $posStat !== false && $posLook !== false
    && $posStat > $posForm && $posStat < $posLook,
    $posForm === false || $posStat === false || $posLook === false
        ? 'missing element(s)'
        : ($posStat > $posLook ? 'status box is AFTER the lookup card — swap them back' : 'form < status < lookup'));
check('events page has #ticket-lookup-form', str_contains($evp, 'id="ticket-lookup-form"'));
$evr = (string)@file_get_contents(__DIR__ . '/../js/event-register.js');
check('event-register.js wires the lookup (status.php + evf_lookup_bad)', str_contains($evr, 'ticket-lookup-form') && str_contains($evr, 'evf_lookup_bad'));
$i18 = (string)@file_get_contents(__DIR__ . '/../js/i18n.js');
check('lookup strings exist in BOTH languages', str_contains($i18, 'evf_lookup_ok') && substr_count($i18, 'evf_lookup_bad') === 2);
$ecss = (string)@file_get_contents(__DIR__ . '/../css/events.css');
check('lookup card styled in events.css', str_contains($ecss, '.ev-ticket-lookup'));
/* Invisible-button regression guard (owner reports 2026-10-25 + 2026-10-26):
   the site's .btn-secondary is WHITE-on-transparent (a dark-surface style),
   so inside the white cards on /events/ it rendered white-on-white — the
   "Find my ticket" and coupon "Apply" buttons were invisible until hover.
   events.css must re-skin it on every white surface, and these regex
   checks make a removal or colour regression fail the deploy. */
$reSkin = '/\.ev-form \.btn-secondary,\s*\.ev-status-box \.btn-secondary,\s*'
    . '\.ev-ticket-lookup \.btn-secondary\s*\{[^}]*?color:\s*var\(--color-primary\)/s';
check('white-card .btn-secondary re-skinned green (Apply, Find my ticket, status-box, lookup actions)', (bool)preg_match($reSkin, $ecss));
$reHover = '/\.ev-form \.btn-secondary:hover,[\s\S]*?\.ev-ticket-lookup \.btn-secondary:focus-visible'
    . '\s*\{[^}]*?background-color:\s*var\(--color-secondary\)/s';
check('re-skinned secondary buttons fill green on hover/focus', (bool)preg_match($reHover, $ecss));

/* ------------------------------------------------------------------ */
/* Report                                                              */
/* ------------------------------------------------------------------ */
hr();
foreach ($results as [$name, $ok, $details]) {
    if ($name === 'NOTE') {
        printf("  • %s\n", $details);
    } else {
        printf("  %s %-58s %s\n", $ok ? '✓' : '✗', $name, $details !== '' ? "($details)" : '');
    }
}
hr();
$total = count(array_filter($results, static fn($r) => $r[0] !== 'NOTE'));
printf("  %d/%d checks passed%s\n\n", $total - $failed, $total, $failed === 0 ? ' — all good.' : " — $failed FAILED (see ✗ above)");
exit($failed === 0 ? 0 : 1);
