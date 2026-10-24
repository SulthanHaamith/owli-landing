<?php
/**
 * ADMIN/INDEX.PHP — Arokya Payanam 2026 staff console.
 *
 * PURPOSE: One password-protected page for the organisers to
 *            1. see every registration and its payment status,
 *            2. trigger an instant Coaxlure re-check for one registration,
 *            3. export the list as a clean CSV (Excel-friendly),
 *            4. add an entry for someone who paid OFFLINE (cash / UPI at the
 *               counter) — recorded as source='staff', status 'paid',
 *            5. delete STAFF-added entries only. Rows made by the public
 *               form (source='web') are permanent records and can never be
 *               deleted from this console — the WHERE clauses enforce that
 *               server-side, not just by hiding the button.
 *
 * SECURITY MODEL:
 *   - The password is NEVER stored in this repo. owli-config.php holds only a
 *     one-way password_hash() string under ['admin']['password_hash']
 *     (generate it with  php api/admin-pass.php 'password'). If the key is
 *     empty, the console renders a "disabled" page and never shows a form.
 *   - Login runs over a PHP session with httponly + SameSite=Lax cookies and
 *     5-attempts-per-15-minutes throttling (counter kept in the DB settings
 *     table per IP, plus a 1-second delay on every failure).
 *   - All state-changing POSTs (login, recheck, logout) require a CSRF token
 *     that lives in the session; the session id is regenerated on login.
 *   - Every piece of attendee data is escaped with htmlspecialchars() before
 *     printing, and the CSV is written through fputcsv() (never hand-built).
 *   - Deletion is scoped to rows this console created (source='staff'):
 *     the delete query re-checks the source inside its WHERE clause, so a
 *     crafted POST can never remove a website row. payment_attempts rows go
 *     first (foreign key) and every deletion is written to the PHP error log.
 *   - robots.txt keeps /admin/ out of search engines; nothing here is linked
 *     from the public pages.
 *
 * DEPENDENCIES: api/_bootstrap.php (config, DB, Coaxlure helpers, pricing).
 *   The console needs the database (that is the whole point), so a missing/
 *   broken DB config shows a friendly "not configured" page, not an error.
 *
 * NOTE: English only on purpose — this is an internal staff tool, not part of
 *   the bilingual public site (README §10 documents this decision).
 */
declare(strict_types=1);
require __DIR__ . '/../api/_bootstrap.php';

/* ------------------------------------------------------------------ */
/* Hardening headers                                                   */
/* ------------------------------------------------------------------ */
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate');

/* ------------------------------------------------------------------ */
/* Session (before any output)                                         */
/* ------------------------------------------------------------------ */
session_name('OWLIADMIN');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
if (($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? 'off') !== 'off') {
    ini_set('session.cookie_secure', '1');
}
session_start();

/** Convenience: HTML-escape for printing. */
function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Renders the full page (login form or dashboard) and exits. */
function page(string $title, string $body, int $status = 200): never
{
    http_response_code($status);
    $t = e($title);
    echo "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n<meta charset=\"UTF-8\">\n"
        . "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n"
      . "<meta name=\"theme-color\" content=\"#2D5A3D\">\n"
        . "<meta name=\"robots\" content=\"noindex, nofollow\">\n"
        . "<title>$t — Arokya Payanam Admin</title>\n"
        . "<link rel=\"icon\" href=\"/images/favicon.png\">\n"
        . "<style>\n" . ADMIN_CSS . "\n</style>\n</head>\n<body>\n"
        . $body . "\n</body>\n</html>\n";
    exit;
}

/* Shared stylesheet. The palette (Forest Calm) mirrors css/styles.css so the
   console matches the brand. Kept inline: the console is one self-contained
   file that must work even if the public CSS changes. */
const ADMIN_CSS = '
:root{--color-primary:#2D5A3D;--color-secondary:#5B8C5A;--color-light-green:#A8C5A0;
--color-background:#F4EDE4;--color-accent:#8B6F47;--color-dark-text:#1A1A1A;--color-light-text:#6B7B6B}
*{box-sizing:border-box}
body{margin:0;font:15px/1.5 Poppins,Arial,sans-serif;color:var(--color-dark-text);background:var(--color-background)}
.wrap{max-width:1180px;margin:0 auto;padding:0 16px}
.top{background:var(--color-primary);color:#fff;padding:14px 0}
.top .wrap{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
.top h1{font-size:17px;margin:0;font-weight:600}
.top .right{display:flex;align-items:center;gap:10px}
.badge{background:rgba(255,255,255,.18);border-radius:999px;padding:4px 12px;font-size:12px}
.btn{display:inline-block;border:0;border-radius:8px;padding:8px 16px;font:600 14px/1 Poppins,Arial,sans-serif;
cursor:pointer;text-decoration:none;background:var(--color-secondary);color:#fff}
.btn,.btn.small{min-height:40px} /* touch-target floor — the console is used on phones at the desk */
.btn:hover{filter:brightness(1.07)}
.btn.small{padding:6px 12px;font-size:12px}
.btn.ghost{background:transparent;color:inherit;border:1px solid currentColor}
.card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:18px;margin:16px 0}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin:16px 0}
.stat{background:#fff;border-radius:12px;padding:14px 16px;box-shadow:0 2px 10px rgba(0,0,0,.06)}
.stat b{display:block;font-size:24px;color:var(--color-primary)}
.stat span{font-size:12px;color:var(--color-light-text)}
table{width:100%;border-collapse:collapse;font-size:14px}
th,td{padding:9px 8px;border-bottom:1px solid #eee;text-align:left;vertical-align:top}
th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--color-light-text);white-space:nowrap}
tr.subrow>td{border-bottom:1px solid #eee;background:#faf8f4}
details{width:100%}
summary{cursor:pointer;color:var(--color-accent);font-size:12.5px;font-weight:600;white-space:nowrap}
.pill{display:inline-block;border-radius:999px;padding:2px 10px;font-size:12px;font-weight:600}
.pill.paid{background:#DFF2E4;color:#1E5C33}.pill.pending{background:#FBF0D9;color:#7A5B12}.pill.failed{background:#F8E1E1;color:#8B2E2E}.pill.off{background:#eee;color:#666}
.chips{display:flex;gap:8px;flex-wrap:wrap;margin:14px 0 2px}
.chip{background:#fff;border:1px solid #e5ddcf;border-radius:999px;padding:5px 13px;font-size:12.5px;color:var(--color-dark-text);text-decoration:none;white-space:nowrap}
.chip:hover{border-color:var(--color-secondary)}
.chip b{color:var(--color-primary)}
.chip.active{background:var(--color-primary);border-color:var(--color-primary);color:#fff}
.chip.active b{color:#fff}
form.filters{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
form.filters label{display:flex;flex-direction:column;font-size:12px;color:var(--color-light-text);gap:4px}
select,input[type=text],input[type=password]{border:1px solid #ccc;border-radius:8px;padding:10px 12px;font:16px Poppins,Arial,sans-serif;background:#fff;color:var(--color-dark-text);min-height:44px}
/* 16px font: stops iOS zoom-on-focus; 44px: comfortable thumb target (the console is used on phones at the desk) */
input[type=text]{min-width:220px}
.notice{border-radius:8px;padding:10px 14px;margin:14px 0;font-size:14px}
.notice.ok{background:#DFF2E4;color:#1E5C33}.notice.err{background:#F8E1E1;color:#8B2E2E}
.login{max-width:380px;margin:9vh auto}
.login h1{text-align:center;font-size:18px;color:var(--color-primary)}
.login p{color:var(--color-light-text);font-size:13px;text-align:center}
.pager{display:flex;gap:8px;align-items:center;justify-content:center;padding:8px 0;color:var(--color-light-text);font-size:13px}
.muted{color:var(--color-light-text);font-size:12px}
.kv{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:2px 18px;font-size:13px}
.kv div{display:flex;justify-content:space-between;gap:10px;border-bottom:1px dashed #eee;padding:3px 0}
.kv b{font-weight:400;color:var(--color-light-text)}
.attempts{font-size:12.5px;margin:8px 0 0}
.srcbadge{display:inline-block;border-radius:4px;padding:1px 6px;font-size:10.5px;font-weight:700;vertical-align:middle;margin-left:6px;letter-spacing:.03em}
.srcbadge.staff{background:#EFE3CF;color:#6B4E1F;border:1px solid #D8C39A}
.srcbadge.web{background:#E3EBE5;color:#2D5A3D;border:1px solid #C3D4C7}
.addgrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px 14px;margin:12px 0}
.addgrid label{display:flex;flex-direction:column;font-size:12px;color:var(--color-light-text);gap:4px}
.addgrid input,.addgrid select{border:1px solid #ccc;border-radius:8px;padding:8px 10px;font:14px Poppins,Arial,sans-serif;background:#fff;color:var(--color-dark-text)}
fieldset.addgroup{border:1px solid #e5ddcf;border-radius:10px;padding:10px 12px;margin:10px 0}
fieldset.addgroup legend{font-size:12px;font-weight:600;color:var(--color-accent);padding:0 6px}
.chk{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--color-dark-text);margin-right:14px}
.btn.danger{background:#B3542E}
footer{padding:14px 0 30px;text-align:center}
@media(max-width:640px){td,th{padding:6px 4px}.hide-m{display:none}}
';

/* ------------------------------------------------------------------ */
/* State: config + CSRF                                                */
/* ------------------------------------------------------------------ */
$adminHash = trim((string)(owli_config()['admin']['password_hash'] ?? ''));
$loggedIn  = !empty($_SESSION['admin_ok']);

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/** True when the POST carried the right CSRF token (dies politely if not). */
function need_csrf(string $csrf): void
{
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        page('Not allowed', '<div class="wrap"><div class="notice err">Session expired — please try again.</div></div>', 403);
    }
}

/* ------------------------------------------------------------------ */
/* Login throttling (5 tries / 15 min per IP, stored in settings)      */
/* ------------------------------------------------------------------ */
const ADMIN_FAIL_KEY = 'admin_fail_';          // + IP
const ADMIN_FAIL_MAX = 5;
const ADMIN_FAIL_WINDOW = 15 * 60;             // seconds

/** Remaining lockout seconds for this IP, or 0 when not locked out. */
function admin_lock_left(PDO $db): int
{
    $st = $db->prepare("SELECT sval FROM settings WHERE skey = ?");
    $st->execute([ADMIN_FAIL_KEY . owli_client_ip()]);
    $row = $st->fetchColumn();
    if (!is_string($row)) {
        return 0;
    }
    $d = json_decode($row, true);
    if (!is_array($d)
        || (int)($d['n'] ?? 0) < ADMIN_FAIL_MAX     /* only 5+ failures lock */
        || (time() - (int)($d['t'] ?? 0)) > ADMIN_FAIL_WINDOW) {
        return 0;
    }
    return ((int)($d['t'] ?? 0) + ADMIN_FAIL_WINDOW) - time();
}

function admin_fail(PDO $db): void
{
    $key = ADMIN_FAIL_KEY . owli_client_ip();
    $st = $db->prepare("SELECT sval FROM settings WHERE skey = ?");
    $st->execute([$key]);
    $d = json_decode((string)$st->fetchColumn(), true);
    $n   = (is_array($d) && (time() - (int)($d['t'] ?? 0)) <= ADMIN_FAIL_WINDOW) ? (int)($d['n'] ?? 0) + 1 : 1;
    $db->prepare("INSERT INTO settings (skey, sval) VALUES (?, ?) ON DUPLICATE KEY UPDATE sval = VALUES(sval)")
       ->execute([$key, json_encode(['n' => $n, 't' => time()])]);
}

function admin_clear_fails(PDO $db): void
{
    $db->prepare("DELETE FROM settings WHERE skey = ?")->execute([ADMIN_FAIL_KEY . owli_client_ip()]);
}

/* ------------------------------------------------------------------ */
/* Shared dashboard helpers                                            */
/* ------------------------------------------------------------------ */
/** Builds the WHERE clause (+ params) shared by the list, count and export.
 *  Sorting (admin_speed) and source/coupon/age filters (admin_filters) build
 *  on this. Everything is whitelist-driven — no SQL fragment ever comes
 *  from the request, only bound parameters. */
function admin_where(array $get): array
{
    $where = [];
    $params = [];
    $status = (string)($get['status'] ?? '');
    if (in_array($status, ['paid', 'pending', 'failed'], true)) {
        $where[] = 'status = ?';
        $params[] = $status;
    }
    $cat = (string)($get['category'] ?? '');
    if (in_array($cat, OWLI_CATEGORIES, true)) {
        $where[] = 'category = ?';
        $params[] = $cat;
    }
    $src = (string)($get['source'] ?? '');
    if (in_array($src, ['web', 'staff'], true)) {
        $where[] = 'source = ?';
        $params[] = $src;
    }
    $cpn = strtoupper(trim((string)($get['coupon'] ?? '')));
    if ($cpn !== '' && preg_match('/^[A-Z0-9_-]{2,30}$/', $cpn) === 1) {
        $where[] = 'coupon_code = ?';
        $params[] = $cpn;
    }
    $age = (string)($get['age'] ?? '');
    if (in_array($age, ['today', 'week', 'older'], true)) {
        $where[] = match ($age) {
            'today' => 'created_at >= (NOW() - INTERVAL 1 DAY)',
            'week'  => 'created_at >= (NOW() - INTERVAL 7 DAY)',
            default => 'created_at <  (NOW() - INTERVAL 7 DAY)',
        };
    }
    $q = trim((string)($get['q'] ?? ''));
    if ($q !== '') {
        $where[] = '(reference LIKE ? OR name LIKE ? OR mobile LIKE ? OR email LIKE ?)';
        $like = '%' . addcslashes($q, "%_\\") . '%';
        array_push($params, $like, $like, $like, $like);
    }
    return [$where, $params];
}

/** Whitelisted ORDER BY for the list. Column names are code, not user input;
 *  the only thing that varies is the direction. */
function admin_order(array $get): string
{
    $cols = [
        'newest'      => 'r.created_at DESC, r.id DESC',
        'oldest'      => 'r.created_at ASC, r.id ASC',
        'name'        => 'r.name ASC, r.id DESC',
        'amount_high' => 'r.amount_paise DESC, r.id DESC',
        'amount_low'  => 'r.amount_paise ASC, r.id DESC',
        'status'      => "FIELD(r.status, 'pending', 'paid', 'failed'), r.id DESC",
        'paid_recent' => 'r.paid_at DESC, r.id DESC',
    ];
    $k = (string)($get['sort'] ?? 'newest');
    return $cols[$k] ?? $cols['newest'];
}

/** Quick-filter chip URLs for the dashboard: pre-baked combinations so the
 *  useful questions ("unpaid", "who still owes money", "today's walk-ins")
 *  are one click, and each chip shows its own live count. */
function admin_chip(string $label, int $count, array $params, string $queryArg): string
{
    $qs = http_build_query(array_merge(['sort' => 'newest'], $params));
    $active = ($queryArg === $qs) ? ' active' : '';
    /* $label is a code constant and $count an int — the only user-influenced
       part of the URL ($qs) is escaped. */
    return '<a class="chip' . $active . '" href="./?' . e($qs) . '">' . e($label)
         . ' <b>' . $count . '</b></a>';
}

/** Amount in paise -> "Rs 299" / "Rs 299.50" for the table. */
function rs(int $paise): string
{
    $r = owli_rupees($paise);
    return 'Rs ' . (is_float($r) ? number_format($r, 2) : number_format($r));
}

/** Bootstrap-styled <span> for a status value. */
function status_pill(string $s): string
{
    $s = in_array($s, ['paid', 'pending', 'failed'], true) ? $s : 'off';
    return '<span class="pill ' . $s . '">' . e(ucfirst($s)) . '</span>';
}

/* ------------------------------------------------------------------ */
/* Boot: no DB -> friendly page; no password hash -> console disabled  */
/* ------------------------------------------------------------------ */
try {
    $db = owli_db();
} catch (Throwable $e) {
    page('Not configured', '<div class="wrap"><div class="notice err">Database is not configured yet. '
        . 'See README §10 (Hostinger setup) — the console needs the MySQL database.</div></div>', 503);
}

if ($adminHash === '') {
    /* No password set in owli-config.php: refuse to show a login form at all. */
    page('Console disabled', '<div class="wrap"><div class="notice err">The admin console is disabled. '
        . 'Set admin.password_hash in owli-config.php (generate it with api/admin-pass.php) to enable it.</div></div>', 403);
}

/* ------------------------------------------------------------------ */
/* Staff-entry support: needs the "source" column (sql/schema.sql has  */
/* it for fresh imports; live databases run the one-line migration in  */
/* GO-LIVE-STEPS.md). Until the column exists, add/delete politely     */
/* refuse instead of crashing, and the dashboard hides the staff bits. */
/* ------------------------------------------------------------------ */
$hasSource = false;
try {
    $hasSource = (bool)$db->query("SHOW COLUMNS FROM registrations LIKE 'source'")->fetchColumn();
} catch (Throwable $ignore) {
}

/* ------------------------------------------------------------------ */
/* POST actions: login / logout / recheck / add / delete               */
/* ------------------------------------------------------------------ */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    need_csrf($csrf);
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'login' && !$loggedIn) {
        $left = admin_lock_left($db);
        if ($left > 0) {
            page('Too many attempts', '<div class="wrap"><div class="notice err">Too many failed attempts. Try again in '
                . max(1, (int)ceil($left / 60)) . ' minute(s).</div></div>', 429);
        }
        usleep(1000000); // constant 1s cost per guess, success or failure
        if (password_verify((string)($_POST['password'] ?? ''), $adminHash)) {
            admin_clear_fails($db);
            session_regenerate_id(true);
            $_SESSION['admin_ok'] = true;
            header('Location: ./');
            exit;
        }
        admin_fail($db);
        $leftNow = admin_lock_left($db);
        $loginError = $leftNow > 0
            ? 'Too many failed attempts. Try again in ' . max(1, (int)ceil($leftNow / 60)) . ' minute(s).'
            : 'Wrong password.';
    }

    if ($action === 'logout' && $loggedIn) {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: ./');
        exit;
    }

    if ($action === 'recheck' && $loggedIn) {
        $ref = strtoupper(trim((string)($_POST['ref'] ?? '')));
        $notice = 'Invalid reference.';
        $noticeOk = false;
        if (preg_match('/^AP26-[A-Z0-9]{6}$/', $ref) === 1) {
            try {
                $st = $db->prepare("SELECT id, status FROM registrations WHERE reference = ?");
                $st->execute([$ref]);
                $reg = $st->fetch();
                if ($reg) {
                    if ((string)$reg['status'] === 'paid') {
                        $notice = "$ref is already marked paid — nothing to re-check.";
                        $noticeOk = true;
                    } elseif (!owli_is_configured()) {
                        $notice = "Coaxlure credentials are not configured, so payments cannot be re-checked.";
                    } else {
                        $at = $db->prepare(
                            "SELECT * FROM payment_attempts WHERE registration_id = ? AND status = 'pending'
                              ORDER BY id DESC LIMIT 1"
                        );
                        $at->execute([$reg['id']]);
                        $attempt = $at->fetch();
                        if (!$attempt) {
                            $notice = "$ref has no pending payment attempt.";
                        } else {
                            $res = owli_refresh_attempt($db, $attempt);
                            if ($res === 'paid') {
                                $notice = "$ref is now marked PAID (verified with Coaxlure).";
                                $noticeOk = true;
                            } else {
                                $notice = "$ref: Coaxlure still reports the payment as not received.";
                            }
                        }
                    }
                } else {
                    $notice = "No registration found for $ref.";
                }
            } catch (Throwable $ex) {
                error_log('owli admin recheck: ' . $ex->getMessage());
                $notice = 'The Coaxlure check failed — check the PHP error log.';
            }
        }
    }

    /* ---- Add a STAFF entry: someone who paid OFFLINE (cash / UPI at the
       counter). Stored as source='staff', status='paid', with the price from
       the tiers table (server-side, like every other path) and an optional
       coupon — a coupon that covers the whole price records the entry as
       FREE (Rs 0). No confirmation email is sent for staff entries (the
       organiser is right there when they add it). ---- */
    if ($action === 'add' && $loggedIn) {
        $noticeOk = false;
        if (!$hasSource) {
            $notice = 'Adding entries needs the "source" column — run the one-line migration in GO-LIVE-STEPS.md, then retry.';
        } else {
            $notice = '';
        }
        /* Compact field reader: trim, collapse spaces, enforce length. */
        $v = static function (string $key, int $min, int $max): ?string {
            $x = trim(preg_replace('/\s+/u', ' ', (string)($_POST[$key] ?? '')) ?? '');
            return ($x === '' || mb_strlen($x) < $min || mb_strlen($x) > $max) ? null : $x;
        };

        $cat  = (string)($_POST['category'] ?? '');
        $name = $v('name', 2, 100);
        $addr = $v('address', 5, 300);
        $mob  = owli_clean_mobile((string)($_POST['mobile'] ?? ''));
        $eml  = strtolower(trim((string)($_POST['email'] ?? '')));

        $extra = ['year_of_study' => null, 'college' => null, 'competitions' => null,
                  'chapter' => null, 'organisation' => null, 'qualification' => null, 'clinic' => null];
        $comps = [];
        foreach ((array)($_POST['competitions'] ?? []) as $c) {
            if (in_array($c, OWLI_COMPETITIONS, true) && !in_array($c, $comps, true)) {
                $comps[] = $c;
            }
        }

        $ok = true;
        if (!in_array($cat, OWLI_CATEGORIES, true)) {
            $notice = 'Please choose a category.'; $ok = false;
        } elseif ($name === null) {
            $notice = 'Please enter the full name (2-100 characters).'; $ok = false;
        } elseif ($addr === null) {
            $notice = 'Please enter the address (at least 5 characters).'; $ok = false;
        } elseif ($mob === '') {
            $notice = 'Please enter a valid 10-digit mobile number.'; $ok = false;
        } elseif (!filter_var($eml, FILTER_VALIDATE_EMAIL)) {
            $notice = 'Please enter a valid email address.'; $ok = false;
        }
        if ($ok && $cat === 'student') {
            $year = (string)($_POST['year_of_study'] ?? '');
            $col  = $v('college', 2, 150);
            if (!in_array($year, OWLI_STUDENT_YEARS, true) || $col === null) {
                $notice = 'Students: pick the year of study and enter the college name.'; $ok = false;
            } else {
                $extra['year_of_study'] = $year;
                $extra['college']       = $col;
                $extra['competitions'] = $comps ? implode(',', $comps) : null;
            }
        }
        if ($ok && $cat === 'bni') {
            $ch  = $v('chapter', 2, 100);
            $org = $v('organisation', 2, 150);
            if ($ch === null || $org === null) {
                $notice = 'BNI: enter both the chapter and the organisation.'; $ok = false;
            } else {
                $extra['chapter']      = $ch;
                $extra['organisation'] = $org;
            }
        }
        if ($ok && $cat === 'doctor') {
            $q1 = $v('qualification', 2, 100);
            if ($q1 === null) {
                $notice = 'Doctors: please enter the qualification.'; $ok = false;
            } else {
                $extra['qualification'] = $q1;
                $extra['clinic']        = $v('clinic', 2, 150);   // optional
            }
        }
        if ($ok) {
            $coupon = strtoupper(trim((string)($_POST['coupon'] ?? '')));
            if ($coupon !== '' && preg_match('/^[A-Z0-9_-]{2,30}$/', $coupon) !== 1) {
                $notice = 'That coupon code is not valid.'; $ok = false;
            } else {
                try {
                    /* Same pair rule as the public form: one PAID ticket per
                       mobile + email. A pending web row for the same pair is
                       NOT blocked here (the offline payment is the real one). */
                    $dup = $db->prepare("SELECT reference FROM registrations WHERE mobile = ? AND email = ? AND status = 'paid' LIMIT 1");
                    $dup->execute([$mob, $eml]);
                    $existing = $dup->fetchColumn();
                    if ($existing) {
                        $notice = "This mobile + email is already PAID ($existing) — not added.";
                    } else {
                        $q = owli_quote($db, $cat, $coupon);
                        if (!$q['ok']) {
                            $notice = 'No price tier is active for this category.';
                        } elseif ($q['coupon_error'] !== null) {
                            $notice = $q['coupon_error'] === 'used_up'
                                ? 'That coupon has reached its limit.'
                                : 'That coupon code is not valid.';
                        } else {
                            $final = (int)$q['final_paise'];
                            $regId = 0;
                            $ref   = '';
                            for ($try = 0; $try < 3 && $regId === 0; $try++) {
                                $ref = owli_new_reference($db);
                                try {
                                    $db->prepare(
                                        "INSERT INTO registrations (reference, source, status, category, name, address,
                                            mobile, email, year_of_study, college, competitions, chapter, organisation,
                                            qualification, clinic, coupon_code, base_paise, discount_paise, amount_paise,
                                            ip, paid_at)
                                         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())"
                                    )->execute([
                                        $ref, 'staff', 'paid', $cat, $name, $addr, $mob, $eml,
                                        $extra['year_of_study'], $extra['college'], $extra['competitions'],
                                        $extra['chapter'], $extra['organisation'], $extra['qualification'],
                                        $extra['clinic'], $q['coupon'], $q['base_paise'], $q['discount_paise'],
                                        $final, owli_client_ip(),
                                    ]);
                                    $regId = (int)$db->lastInsertId();
                                } catch (PDOException $pe) {
                                    if (($pe->errorInfo[1] ?? 0) !== 1062) {   // duplicate reference: try a new one
                                        throw $pe;
                                    }
                                }
                            }
                            if ($regId === 0) {
                                throw new RuntimeException('reference_generation_failed');
                            }
                            error_log('owli admin: staff entry ' . $ref . ' added (' . owli_client_ip() . ')');
                            $notice   = 'Added ' . $ref . ' for ' . $name . ' — recorded as PAID (Rs ' . owli_rupees($final) . ').';
                            $noticeOk = true;
                        }
                    }
                } catch (Throwable $ex) {
                    error_log('owli admin add: ' . $ex->getMessage());
                    $notice = 'Could not add the entry — check the PHP error log (is the "source" column migration done?).';
                }
            }
        }
    }

    /* ---- Delete: STAFF entries only. The WHERE clause re-checks the
       source, so even a crafted POST cannot remove a website row.
       payment_attempts go first (foreign key); deletions are logged. ---- */
    if ($action === 'delete' && $loggedIn) {
        $notice = 'Invalid reference.';
        $noticeOk = false;
        $ref = strtoupper(trim((string)($_POST['ref'] ?? '')));
        if (!$hasSource) {
            $notice = 'Deleting entries needs the "source" column — run the one-line migration in GO-LIVE-STEPS.md first.';
        } elseif (preg_match('/^AP26-[A-Z0-9]{6}$/', $ref) === 1) {
            try {
                $st = $db->prepare("SELECT id, source, name FROM registrations WHERE reference = ?");
                $st->execute([$ref]);
                $row = $st->fetch();
                if (!$row) {
                    $notice = "No registration found for $ref.";
                } elseif ((string)$row['source'] !== 'staff') {
                    $notice = "$ref came from the public website — it is a permanent record and cannot be deleted here.";
                } else {
                    $db->beginTransaction();
                    $db->prepare("DELETE FROM payment_attempts WHERE registration_id = ?")->execute([(int)$row['id']]);
                    $db->prepare("DELETE FROM registrations WHERE id = ? AND source = 'staff'")->execute([(int)$row['id']]);
                    $db->commit();
                    error_log('owli admin: staff entry ' . $ref . ' deleted (' . owli_client_ip() . ')');
                    $notice   = 'Deleted staff entry ' . $ref . ' (' . (string)$row['name'] . ').';
                    $noticeOk = true;
                }
            } catch (Throwable $ex) {
                error_log('owli admin delete: ' . $ex->getMessage());
                $notice = 'Could not delete — check the PHP error log.';
            }
        }
    }

    /* ---- Coupon management: generate + toggle. Codes live ONLY in the
       database; the repo deliberately never contains them. The generator
       invents the suffixes so no real code needs to be typed by hand. ---- */
    if ($action === 'coupon_gen' && $loggedIn) {
        $noticeOk = false;
        $notice = '';
        $ctype    = (string)($_POST['ctype'] ?? '');
        $value    = (int)($_POST['value'] ?? 0);
        $count    = max(1, min(100, (int)($_POST['count'] ?? 1)));
        $usesIn   = trim((string)($_POST['uses'] ?? ''));
        $maxUses  = $usesIn === '' ? null : max(1, min(100000, (int)$usesIn));
        $prefix   = strtoupper(trim((string)($_POST['prefix'] ?? '')));
        $category = (string)($_POST['ccategory'] ?? '');

        if (!in_array($ctype, ['percent', 'flat', 'fixed_price'], true)) {
            $notice = 'Choose the coupon type.';
        } elseif ($value <= 0 || ($ctype === 'percent' && $value > 100)) {
            $notice = 'Percent coupons take 1-100; money coupons take paise (Rs 10 = 1000).';
        } elseif ($prefix !== '' && preg_match('/^[A-Z0-9-]{2,12}$/', $prefix) !== 1) {
            $notice = 'Prefix: 2-12 letters/digits/hyphens (A-Z, 0-9, -).';
        } elseif ($category !== '' && !in_array($category, OWLI_CATEGORIES, true)) {
            $notice = 'Invalid category.';
        } else {
            try {
                $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                $made = [];
                $fail = 0;
                while (count($made) < $count && $fail < 200) {
                    $code = ($prefix !== '' ? rtrim($prefix, '-') . '-' : '') . substr(str_shuffle($alphabet), 0, 6);
                    /* str_shuffle uses a non-cryptographic RNG, but a coupon code
                       is not a secret — uniqueness (DB UNIQUE) and value limits
                       live server-side anyway. */
                    try {
                        $db->prepare("INSERT INTO coupons (code, discount_type, value, category, max_uses) VALUES (?,?,?,?,?)")
                           ->execute([$code, $ctype, $value, $category ?: null, $maxUses]);
                        $made[] = $code;
                    } catch (PDOException $pe) {
                        if (($pe->errorInfo[1] ?? 0) !== 1062) {   // duplicate code: try another
                            throw $pe;
                        }
                        $fail++;
                    }
                }
                if ($made) {
                    $couponCodes = $made;   // rendered once below the notice
                    $desc = $ctype === 'percent' ? $value . '% off' : ($ctype === 'flat' ? 'Rs ' . owli_rupees($value) . ' off' : 'pay Rs ' . owli_rupees($value));
                    $notice   = 'Created ' . count($made) . ' coupon code(s): ' . $desc
                              . ($maxUses !== null ? ', max ' . $maxUses . ' use(s)' : ', unlimited uses')
                              . ($category !== '' ? ', category ' . $category : ', all categories') . '.';
                    $noticeOk = true;
                    error_log('owli admin: ' . count($made) . ' coupon(s) generated (' . $ctype . ' ' . $value . ')');
                } else {
                    $notice = 'Could not invent unique codes — try a different prefix.';
                }
            } catch (Throwable $ex) {
                error_log('owli admin coupon_gen: ' . $ex->getMessage());
                $notice = 'Could not generate codes — check the PHP error log.';
            }
        }
    }

    if ($action === 'coupon_toggle' && $loggedIn) {
        $notice = 'Invalid coupon.';
        $noticeOk = false;
        $cid = (int)($_POST['cid'] ?? 0);
        if ($cid > 0) {
            try {
                $st = $db->prepare("SELECT code, active FROM coupons WHERE id = ?");
                $st->execute([$cid]);
                $c = $st->fetch();
                if ($c) {
                    $newActive = ((int)$c['active']) === 1 ? 0 : 1;
                    $db->prepare("UPDATE coupons SET active = ? WHERE id = ?")->execute([$newActive, $cid]);
                    $notice = ($newActive === 1 ? 'Coupon ' . $c['code'] . ' switched ON.' : 'Coupon ' . $c['code'] . ' switched OFF.');
                    $noticeOk = true;
                } else {
                    $notice = 'Coupon not found.';
                }
            } catch (Throwable $ex) {
                error_log('owli admin coupon_toggle: ' . $ex->getMessage());
                $notice = 'Could not update the coupon — check the PHP error log.';
            }
        }
    }
}

/* ------------------------------------------------------------------ */
/* CSV export (GET ?export=1) — same filters as the visible list        */
/* ------------------------------------------------------------------ */
if ($loggedIn && ($_GET['export'] ?? '') === '1') {
    /* Same filters as the visible list. Attempt history comes along as two
       sub-selects so the export is a single query even for big lists. */
    [$where, $params] = admin_where($_GET);
    $sql = "SELECT r.*,
                   (SELECT COUNT(*) FROM payment_attempts pa WHERE pa.registration_id = r.id) AS attempt_count,
                   (SELECT pa2.raw_status FROM payment_attempts pa2
                     WHERE pa2.registration_id = r.id ORDER BY pa2.id DESC LIMIT 1) AS last_attempt_raw
              FROM registrations r"
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . " ORDER BY " . admin_order($_GET);
    $st = $db->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();

    /* Excel opens UTF-8 CSVs correctly when a BOM leads the file. */
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="arokya-payanam-registrations-' . date('Y-m-d') . '.csv"');
    header('Cache-Control: no-store');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, [
        'Reference', 'Status', 'Category', 'Source', 'Full name', 'Mobile', 'Email', 'Address',
        'Year of study', 'College', 'Competitions', 'BNI chapter', 'Organisation',
        'Qualification', 'Clinic', 'Coupon', 'Amount (Rs)', 'Discount (Rs)', 'Base (Rs)',
        'Payment attempts', 'Latest Coaxlure status', 'Registered at', 'Paid at', 'IP',
    ], ',', '"', '\\', "\r\n");
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['reference'], $r['status'], $r['category'], ($r['source'] ?? 'web'), $r['name'], $r['mobile'], $r['email'], $r['address'],
            $r['year_of_study'] ?? '', $r['college'] ?? '', $r['competitions'] ?? '',
            $r['chapter'] ?? '', $r['organisation'] ?? '', $r['qualification'] ?? '', $r['clinic'] ?? '',
            $r['coupon_code'] ?? '',
            (string)owli_rupees((int)$r['amount_paise']),
            (string)owli_rupees((int)$r['discount_paise']),
            (string)owli_rupees((int)$r['base_paise']),
            (string)($r['attempt_count'] ?? 0),
            (string)($r['last_attempt_raw'] ?? ''),
            $r['created_at'], $r['paid_at'] ?? '', $r['ip'] ?? '',
        ], ',', '"', '\\', "\r\n");
    }
    fclose($out);
    exit;
}

/* ------------------------------------------------------------------ */
/* Login page (not logged in yet)                                      */
/* ------------------------------------------------------------------ */
if (!$loggedIn) {
    $err = '';
    if (!empty($loginError)) {
        $err = '<div class="notice err">' . e($loginError) . '</div>';
    }
    page('Sign in', <<<HTML
<div class="wrap login">
  <h1>Arokya Payanam 2026 — Admin</h1>
  <p>Registration console for event staff.</p>
  <div class="card">
    $err
    <form method="post" action="">
      <input type="hidden" name="csrf" value="{$csrf}">
      <input type="hidden" name="action" value="login">
      <label><input type="password" name="password" placeholder="Password" required autofocus
                    autocomplete="current-password" style="width:100%"></label>
      <p><button class="btn" type="submit" style="width:100%">Sign in</button></p>
    </form>
  </div>
</div>
HTML);
}

/* ------------------------------------------------------------------ */
/* Dashboard (logged in)                                               */
/* ------------------------------------------------------------------ */
[$where, $params] = admin_where($_GET);

/* --- summary cards (ignore filters: always the whole event) --- */
$sumSql = "SELECT COUNT(*) AS total,
            COALESCE(SUM(status = 'paid'), 0)    AS paid,
            COALESCE(SUM(status = 'pending'), 0) AS pending,
            COALESCE(SUM(status = 'failed'), 0)  AS failed,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount_paise END), 0)   AS collected,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN amount_paise END), 0) AS awaiting"
    . ($hasSource ? ",\n            COALESCE(SUM(source = 'staff'), 0) AS staffadds" : '')
    . "\n       FROM registrations";
$sum = $db->query($sumSql)->fetch();

/* --- pending-age breakdown + chip counts: the "how long is long" view ---
   Pending is not one bucket: something 5 minutes old is being paid right
   now, something 3 days old never will be. The summary splits pending by
   age so the team can tell at a glance which need attention (and the
   abandoned sweep will turn the stalest into 'failed' on its own). */
$pendAges = $db->query(
    "SELECT
       COALESCE(SUM(created_at >= (NOW() - INTERVAL 1 HOUR)), 0)  AS p1h,
       COALESCE(SUM(created_at <  (NOW() - INTERVAL 1 HOUR)), 0)   AS pStale
       FROM registrations WHERE status = 'pending'"
)->fetch();
$p1h    = (int)($pendAges['p1h'] ?? 0);
$pStale = (int)($pendAges['pStale'] ?? 0);

/* chip counts (computed with the base filter set, not the page's filters,
   so the numbers stay meaningful while a filter is active) */
$chipCount = static function (array $get) use ($db): int {
    [$w, $p] = admin_where($get);
    $st = $db->prepare("SELECT COUNT(*) FROM registrations" . ($w ? ' WHERE ' . implode(' AND ', $w) : ''));
    $st->execute($p);
    return (int)$st->fetchColumn();
};
$chipAll      = $chipCount([]);
$chipPaid     = $chipCount(['status' => 'paid']);
$chipPending  = $chipCount(['status' => 'pending']);
$chipFailed   = $chipCount(['status' => 'failed']);
$chipStale    = $chipCount(['status' => 'pending', 'age' => 'older']);
$chipToday    = $chipCount(['age' => 'today']);
$chipStaff    = $hasSource ? $chipCount(['source' => 'staff']) : 0;

/* --- filtered list, 50 per page --- */
$perPage = 50;
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$cnt = $db->prepare("SELECT COUNT(*) FROM registrations" . ($where ? ' WHERE ' . implode(' AND ', $where) : ''));
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$pageNum = min($pageNum, $pages);
$offset = ($pageNum - 1) * $perPage;

$list = $db->prepare(
    "SELECT * FROM registrations r" . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . " ORDER BY " . admin_order($_GET) . " LIMIT $perPage OFFSET $offset"
);
$list->execute($params);
$rows = $list->fetchAll();

/* --- attempts for the displayed rows (one extra query, not per-row) --- */
$attempts = [];
if ($rows) {
    $in  = implode(',', array_fill(0, count($rows), '?'));
    $ids = array_map(static fn($r) => (int)$r['id'], $rows);
    $at  = $db->prepare("SELECT * FROM payment_attempts WHERE registration_id IN ($in) ORDER BY id ASC");
    $at->execute($ids);
    foreach ($at->fetchAll() as $a) {
        $attempts[(int)$a['registration_id']][] = $a;
    }
}

/* --- filter form keeps its current values --- */
$fStatus   = (string)($_GET['status'] ?? '');
$fCategory = (string)($_GET['category'] ?? '');
$fSource   = (string)($_GET['source'] ?? '');
$fCoupon   = strtoupper(trim((string)($_GET['coupon'] ?? '')));
$fAge      = (string)($_GET['age'] ?? '');
$fSort     = (string)($_GET['sort'] ?? 'newest');
$fQuery    = (string)($_GET['q'] ?? '');
$queryArg  = http_build_query(array_filter([
    'status' => $fStatus, 'category' => $fCategory, 'source' => $fSource,
    'coupon' => $fCoupon, 'age' => $fAge, 'sort' => $fSort, 'q' => $fQuery,
], static fn($v) => $v !== ''));

/* --- the sticky parts the selects need to re-render correctly --- */
$selSrcWeb    = 'web'    === $fSource ? ' selected' : '';
$selSrcStaff  = 'staff'  === $fSource ? ' selected' : '';
$selAgeToday  = 'today'  === $fAge    ? ' selected' : '';
$selAgeWeek   = 'week'   === $fAge    ? ' selected' : '';
$selAgeOlder  = 'older'  === $fAge    ? ' selected' : '';
$sortOptions  = '';
foreach ([
    'newest'      => 'Newest first',
    'oldest'      => 'Oldest first',
    'name'        => 'Name A-Z',
    'amount_high' => 'Amount: high to low',
    'amount_low'  => 'Amount: low to high',
    'status'      => 'Status: pending, paid, failed',
    'paid_recent' => 'Most recently paid',
] as $sk => $sl) {
    $sortOptions .= '<option value="' . e((string)$sk) . '"' . ($sk === $fSort ? ' selected' : '') . '>' . e((string)$sl) . '</option>';
}

$noticeHtml = '';
if (isset($notice)) {
    $noticeHtml = '<div class="notice ' . ($noticeOk ? 'ok' : 'err') . '">' . e($notice) . '</div>';
}

/* --- "Add entry" form (offline payments) --- */
$couponCodesHtml = '';
if (!empty($couponCodes)) {
    $list = e(implode("\n", $couponCodes));
    $couponCodesHtml = <<<HTML
<div class="notice ok">Codes created — copy them now; they are stored in the database and
shown in the Coupons panel below. This box is shown once.</div>
<textarea readonly rows="6" onclick="this.select()" style="width:100%;font:13px/1.6 monospace;padding:10px">{$list}</textarea>
HTML;
}
$staffStatHtml = $hasSource
    ? '<div class="stat"><b>' . (int)($sum['staffadds'] ?? 0) . '</b><span>Added by staff (offline)</span></div>'
    : '';
$sourceMissingHtml = $hasSource
    ? ''
    : '<div class="notice err">The database is missing the <code>source</code> column — run the one-line
       migration in GO-LIVE-STEPS.md ("Add the source column") to enable adding / deleting staff entries.</div>';

$addCatOptions = '';
foreach (OWLI_CATEGORIES as $c) {
    $addCatOptions .= '<option value="' . e($c) . '">' . e(ucfirst($c)) . '</option>';
}
$addYearOptions = '';
foreach (OWLI_STUDENT_YEARS as $y) {
    $addYearOptions .= '<option value="' . e($y) . '">' . e($y) . '</option>';
}
$addComps = '';
foreach (OWLI_COMPETITIONS as $c) {
    $addComps .= '<span class="chk"><input type="checkbox" name="competitions[]" value="' . e($c) . '">'
        . e(ucfirst(str_replace('_', ' ', $c))) . '</span>';
}

$addFormHtml = <<<HTML
<p class="muted">Records a PAID registration for someone who paid offline (cash / UPI at the counter).
The amount comes from the current price tier — the panel below shows what will be recorded — and a coupon
can be applied; if a coupon covers the whole price, the entry is recorded as FREE (Rs 0). Staff entries are
the <b>only</b> rows this console can delete. Website rows are permanent.</p>
{$sourceMissingHtml}
<form method="post" action="">
  <input type="hidden" name="csrf" value="{$csrf}">
  <input type="hidden" name="action" value="add">
  <div class="addgrid">
    <label>Category * <select name="category" id="add-cat" required>{$addCatOptions}</select></label>
    <label>Price that will be recorded <output id="add-price" class="muted">—</output></label>
    <label>Full name * <input type="text" name="name" maxlength="100" required></label>
    <label>Mobile number * <input type="text" name="mobile" maxlength="13" inputmode="numeric" required></label>
    <label>Email * <input type="email" name="email" maxlength="150" required></label>
    <label>Address * <input type="text" name="address" maxlength="300" required></label>
    <label>Coupon code (optional) <input type="text" name="coupon" maxlength="30" autocapitalize="characters"></label>
  </div>
  <fieldset class="addgroup"><legend>Student details (students only)</legend>
    <div class="addgrid">
      <label>Year of study <select name="year_of_study">{$addYearOptions}</select></label>
      <label>College <input type="text" name="college" maxlength="150"></label>
    </div>
    <div>Competitions: {$addComps}</div>
  </fieldset>
  <fieldset class="addgroup"><legend>BNI details (BNI members only)</legend>
    <div class="addgrid">
      <label>BNI chapter <input type="text" name="chapter" maxlength="100"></label>
      <label>Organisation / business <input type="text" name="organisation" maxlength="150"></label>
    </div>
  </fieldset>
  <fieldset class="addgroup"><legend>Doctor details (doctors only)</legend>
    <div class="addgrid">
      <label>Qualification <input type="text" name="qualification" maxlength="100"></label>
      <label>Clinic / hospital <input type="text" name="clinic" maxlength="150"></label>
    </div>
  </fieldset>
  <p><button class="btn" type="submit">Add entry as PAID</button></p>
</form>
<script>
/* Live price preview for the category chosen above — display only, the server
   re-prices on submit exactly like the public form. */
(function () {
  var sel = document.getElementById('add-cat');
  var out = document.getElementById('add-price');
  if (!sel || !out) return;
  function upd() {
    fetch('../api/quote.php?category=' + encodeURIComponent(sel.value))
      .then(function (r) { return r.json(); })
      .then(function (b) {
        if (b.amount === undefined) { out.textContent = 'no active tier'; return; }
        out.textContent = b.discount > 0
          ? 'Rs ' + b.amount + ' (base Rs ' + b.base + ', discount Rs ' + b.discount + ')'
          : 'Rs ' + b.amount;
      })
      .catch(function () { out.textContent = '—'; });
  }
  sel.addEventListener('change', upd);
  upd();
})();
</script>
HTML;

/* --- Coupons panel: live list + generator (codes exist only in the DB) --- */
$couponRows = [];
try {
    $couponRows = $db->query(
        "SELECT c.*, (SELECT COUNT(*) FROM registrations r WHERE r.coupon_code = c.code AND r.status = 'paid') AS used
           FROM coupons c ORDER BY c.active DESC, c.id DESC LIMIT 200"
    )->fetchAll();
} catch (Throwable $ignore) {
    /* coupons table missing = schema not imported; the panel shows a hint */
}
$couponTr = '';
foreach ($couponRows as $c) {
    $desc = (string)$c['discount_type'] === 'percent'
        ? ((int)$c['value'] . '% off')
        : ((string)$c['discount_type'] === 'flat' ? 'Rs ' . owli_rupees((int)$c['value']) . ' off' : 'pay Rs ' . owli_rupees((int)$c['value']));
    $cat   = (string)($c['category'] ?? '') !== '' ? ucfirst((string)$c['category']) : 'All';
    $uses  = $c['max_uses'] === null ? (int)$c['used'] . ' / ∞' : (int)$c['used'] . ' / ' . (int)$c['max_uses'];
    $couponTr .= '<tr>'
        . '<td><b>' . e((string)$c['code']) . '</b></td>'
        . '<td>' . e($desc) . '</td>'
        . '<td>' . e($cat) . '</td>'
        . '<td class="muted">' . e($uses) . '</td>'
        . '<td><span class="pill ' . ((int)$c['active'] === 1 ? 'paid' : 'off') . '">' . ((int)$c['active'] === 1 ? 'Active' : 'Off') . '</span></td>'
        . '<td><form method="post" action="" style="display:inline">'
          . '<input type="hidden" name="csrf" value="' . e($csrf) . '">'
          . '<input type="hidden" name="action" value="coupon_toggle">'
          . '<input type="hidden" name="cid" value="' . (int)$c['id'] . '">'
          . '<button class="btn small ghost" type="submit">' . ((int)$c['active'] === 1 ? 'Switch off' : 'Switch on') . '</button></form></td>'
        . '</tr>';
}
$couponListHtml = $couponTr !== ''
    ? '<table><thead><tr><th>Code</th><th>Discount</th><th>Category</th><th>Uses (paid)</th><th>Status</th><th></th></tr></thead><tbody>' . $couponTr . '</tbody></table>'
    : '<p class="muted">No coupons in the database yet.</p>';

$couponPanelHtml = <<<HTML
<details class="card" id="coupons">
  <summary>Coupons — view, generate &amp; switch codes</summary>
  <p class="muted">Codes live only in this database — never in the Git repository or any public
  page. "Uses" counts PAID registrations only; a FREE ticket (100% coupon) counts as a paid row,
  so a single-use code dies after its one guest registers.</p>
  <form method="post" action="" style="margin:10px 0 16px">
    <input type="hidden" name="csrf" value="{$csrf}">
    <input type="hidden" name="action" value="coupon_gen">
    <div class="addgrid">
      <label>Type
        <select name="ctype">
          <option value="percent">percent — e.g. 10 = 10% off (100 = FREE ticket)</option>
          <option value="flat">flat — paise off, e.g. 10000 = Rs 100 off</option>
          <option value="fixed_price">fixed price — pay exactly e.g. 19900 = Rs 199</option>
        </select>
      </label>
      <label>Value <input type="text" name="value" placeholder="10" required></label>
      <label>How many codes <input type="text" name="count" value="1" required></label>
      <label>Uses per code (blank = unlimited) <input type="text" name="uses" placeholder="blank = unlimited"></label>
      <label>Code prefix (optional) <input type="text" name="prefix" placeholder="FREEAP26-" maxlength="12"></label>
      <label>Category (blank = all) <select name="ccategory"><option value="">All</option>{$addCatOptions}</select></label>
    </div>
    <p><button class="btn" type="submit">Generate codes</button></p>
  </form>
  {$couponCodesHtml}
  {$couponListHtml}
</details>
HTML;

/* --- build the table rows --- */
$tr = '';
$n = $offset;
foreach ($rows as $r) {
    $n++;
    $id  = (int)$r['id'];
    $ref = (string)$r['reference'];
    $atts = $attempts[$id] ?? [];

    /* category-specific extras, shown in the expandable row */
    $extras = '<div><b>Address</b><span>' . e((string)$r['address']) . '</span></div>';
    if ($hasSource) { $extras .= '<div><b>Added via</b><span>' . ((string)$r['source'] === 'staff' ? 'Admin console (staff entry)' : 'Public website') . '</span></div>'; }
    if ($r['paid_at'] ?? '')       { $extras .= '<div><b>Paid at</b><span>' . e((string)$r['paid_at']) . '</span></div>'; }
    if ($r['base_paise'] ?? '')    { $extras .= '<div><b>Base price</b><span>' . rs((int)$r['base_paise']) . '</span></div>'; }
    if ($r['discount_paise'] ?? ''){ $extras .= '<div><b>Discount</b><span>' . rs((int)$r['discount_paise']) . '</span></div>'; }
    if ($r['year_of_study'] ?? '')    { $extras .= '<div><b>Year of study</b><span>' . e((string)$r['year_of_study']) . '</span></div>'; }
    if ($r['college'] ?? '')          { $extras .= '<div><b>College</b><span>' . e((string)$r['college']) . '</span></div>'; }
    if ($r['competitions'] ?? '')     { $extras .= '<div><b>Competitions</b><span>' . e(str_replace(',', ', ', (string)$r['competitions'])) . '</span></div>'; }
    if ($r['chapter'] ?? '')          { $extras .= '<div><b>BNI chapter</b><span>' . e((string)$r['chapter']) . '</span></div>'; }
    if ($r['organisation'] ?? '')     { $extras .= '<div><b>Organisation</b><span>' . e((string)$r['organisation']) . '</span></div>'; }
    if ($r['qualification'] ?? '')    { $extras .= '<div><b>Qualification</b><span>' . e((string)$r['qualification']) . '</span></div>'; }
    if ($r['clinic'] ?? '')           { $extras .= '<div><b>Clinic / hospital</b><span>' . e((string)$r['clinic']) . '</span></div>'; }
    if ($r['coupon_code'] ?? '')      { $extras .= '<div><b>Coupon</b><span>' . e((string)$r['coupon_code']) . '</span></div>'; }
    if ($r['ip'] ?? '')               { $extras .= '<div><b>Registered from IP</b><span>' . e((string)$r['ip']) . '</span></div>'; }

    /* attempt history */
    $attRows = '';
    foreach ($atts as $a) {
        $attRows .= '<tr><td>#' . (int)$a['id'] . '</td>'
            . '<td>' . rs((int)$a['amount_paise']) . '</td>'
            . '<td>' . status_pill((string)$a['status']) . '</td>'
            . '<td class="muted">' . e((string)($a['raw_status'] ?? '—')) . '</td>'
            . '<td class="muted">' . e((string)$a['created_at']) . '</td></tr>';
    }

    $recheckBtn = (string)$r['status'] === 'pending'
        ? '<form method="post" action="" style="display:inline">'
          . '<input type="hidden" name="csrf" value="' . e($csrf) . '">'
          . '<input type="hidden" name="action" value="recheck">'
          . '<input type="hidden" name="ref" value="' . e($ref) . '">'
          . '<button class="btn small" type="submit">Recheck payment now</button></form>'
        : '';

    /* Ticket download for the team (owner request): streams the same PDF the
       attendee gets, so staff can forward or re-print it on WhatsApp / email
       or at the desk. Paid rows only — api/ticket.php itself enforces that
       server-side, so this button simply disappears for other statuses.
       No auth is needed on the URL beyond the unguessable Booking ID: the
       endpoint is public by design for exactly this sharing use case. */
    $ticketBtn = (string)$r['status'] === 'paid'
        ? '<a class="btn small" href="../api/ticket.php?ref=' . e($ref) . '" download>'
          . 'Download ticket (PDF)</a>'
        : '';

    /* Delete exists ONLY for staff-added rows. The reference pattern is
       safe to embed in the confirm() text (letters/digits/hyphen only). */
    $delBtn = ($hasSource && (string)$r['source'] === 'staff')
        ? '<form method="post" action="" style="display:inline" onsubmit="return confirm(\'Delete staff entry ' . e($ref) . '? This cannot be undone.\')">'
          . '<input type="hidden" name="csrf" value="' . e($csrf) . '">'
          . '<input type="hidden" name="action" value="delete">'
          . '<input type="hidden" name="ref" value="' . e($ref) . '">'
          . '<button class="btn small danger" type="submit">Delete this staff entry</button></form>'
        : '';

    $srcBadge = $hasSource
        ? ' <span class="srcbadge ' . e((string)$r['source']) . '">' . ((string)$r['source'] === 'staff' ? 'STAFF' : 'WEB') . '</span>'
        : '';
    $tr .= '<tr>'
        . '<td class="muted">' . $n . '</td>'
        . '<td><b>' . e($ref) . '</b>' . $srcBadge . '</td>'
        . '<td>' . status_pill((string)$r['status']) . '</td>'
        . '<td>' . e(ucfirst((string)$r['category'])) . '</td>'
        . '<td>' . e((string)$r['name']) . '</td>'
        . '<td class="hide-m"><a href="tel:+91' . e((string)$r['mobile']) . '">' . e((string)$r['mobile']) . '</a></td>'
        . '<td class="hide-m">' . e((string)$r['email']) . '</td>'
        . '<td><b>' . rs((int)$r['amount_paise']) . '</b></td>'
        . '<td class="muted">' . e((string)$r['created_at']) . '</td>'
        . '</tr>'
        . '<tr class="subrow"><td colspan="9" style="padding:4px 8px 14px">'
        . '<details><summary>Details &amp; payment history</summary>'
        . '<div class="kv">' . $extras . '</div>'
        . ($attRows
            ? '<table class="attempts"><tr><th>Attempt</th><th>Amount</th><th>Status</th><th>Coaxlure said</th><th>Created</th></tr>' . $attRows . '</table>'
            : '<p class="attempts muted">No payment attempt was ever created for this registration.</p>')
        . '<p class="attempts">' . $recheckBtn . $ticketBtn . $delBtn . '</p>'
        . '</details></td></tr>';
}

$emptyRow = $tr === ''
    ? '<tr><td colspan="9" style="text-align:center;color:var(--color-light-text);padding:30px">'
      . 'No registrations match these filters.</td></tr>'
    : '';

/* Pager URLs are computed BEFORE the heredoc: closures cannot be invoked
   reliably inside {$...} string interpolation, only plain variables can. */
$prevPage = max(1, $pageNum - 1);
$nextPage = min($pages, $pageNum + 1);
$pageUrl  = static function (int $p) use ($queryArg): string {
    $base = './' . ($queryArg ? '?' . $queryArg : '');
    return $base . ($queryArg ? '&amp;' : '?') . 'page=' . $p;
};
$prevUrl = $pageUrl($prevPage);
$nextUrl = $pageUrl($nextPage);
$showing = $total === 0 ? '0' : ($offset + 1) . '–' . ($offset + count($rows));

$mode = owli_registration_mode();

/* --- chips: pre-baked one-click views (counts computed above) --- */
$chipAllRow = admin_chip('All', $chipAll, [], $queryArg)
    . admin_chip('Paid', $chipPaid, ['status' => 'paid'], $queryArg)
    . admin_chip('Pending', $chipPending, ['status' => 'pending'], $queryArg)
    . admin_chip('Failed', $chipFailed, ['status' => 'failed'], $queryArg)
    . admin_chip('Stale pending (7+ days)', $chipStale, ['status' => 'pending', 'age' => 'older'], $queryArg)
    . admin_chip('Last 24h', $chipToday, ['age' => 'today'], $queryArg)
    . ($hasSource ? admin_chip('Staff-added', $chipStaff, ['source' => 'staff'], $queryArg) : '');

$pendAgesHtml = $pStale > 0 ? 'an hour' : 'this whole time';

/* --- values the heredoc below can only print as plain {$var} --- */
$rupeesStr = static function (int $paise): string {
    $r = owli_rupees($paise);
    return 'Rs ' . (is_float($r) ? number_format($r, 2) : number_format($r));
};
$collectedStr = $rupeesStr((int)$sum['collected']);
$awaitingStr  = $rupeesStr((int)$sum['awaiting']);
$selPaid    = 'paid'    === $fStatus ? ' selected' : '';
$selPending = 'pending' === $fStatus ? ' selected' : '';
$selFailed  = 'failed'  === $fStatus ? ' selected' : '';
$categoryOptions = '';
foreach (OWLI_CATEGORIES as $c) {
    $categoryOptions .= '<option value="' . e($c) . '"' . ($c === $fCategory ? ' selected' : '') . '>'
        . e(ucfirst($c)) . '</option>';
}

page('Registrations', <<<HTML
<div class="top">
  <div class="wrap">
    <h1>Arokya Payanam 2026 — Admin</h1>
    <div class="right">
      <span class="badge">Registration mode: {$mode}</span>
      <form method="post" action="" style="display:inline">
        <input type="hidden" name="csrf" value="{$csrf}">
        <input type="hidden" name="action" value="logout">
        <button class="btn ghost small" type="submit">Sign out</button>
      </form>
    </div>
  </div>
</div>
<div class="wrap">
  {$noticeHtml}
  <details class="card" id="add-entry">
    <summary>+ Add entry — record an offline payment (cash / UPI)</summary>
    {$addFormHtml}
  </details>
  {$couponPanelHtml}
  <div class="stats">
    <div class="stat"><b>{$sum['total']}</b><span>Total registrations</span></div>
    <div class="stat"><b>{$sum['paid']}</b><span>Paid</span></div>
    <div class="stat"><b>{$sum['pending']}</b><span>Pending payment</span></div>
    <div class="stat"><b>{$sum['failed']}</b><span>Failed / abandoned</span></div>
    <div class="stat"><b>{$collectedStr}</b><span>Collected</span></div>
    <div class="stat"><b>{$awaitingStr}</b><span>Awaiting payment</span></div>
    {$staffStatHtml}
  </div>

  <p class="muted" style="margin:6px 0 0">Pending for {$pendAgesHtml}: {$p1h} within the last hour, {$pStale} older — the cron sweep moves anything still unpaid after 2 days to Failed automatically.</p>

  <div class="chips">
    {$chipAllRow}
  </div>

  <div class="card">
    <form class="filters" method="get" action="">
      <label>Status
        <select name="status">
          <option value="">All</option>
          <option value="paid"{$selPaid}>Paid</option>
          <option value="pending"{$selPending}>Pending</option>
          <option value="failed"{$selFailed}>Failed / abandoned</option>
        </select>
      </label>
      <label>Category
        <select name="category">
          <option value="">All</option>
          {$categoryOptions}
        </select>
      </label>
      <label>Source
        <select name="source">
          <option value="">All</option>
          <option value="web"{$selSrcWeb}>Public website</option>
          <option value="staff"{$selSrcStaff}>Added by staff</option>
        </select>
      </label>
      <label>Coupon code
        <input type="text" name="coupon" value="{$fCoupon}" placeholder="e.g. EARLY10">
      </label>
      <label>Registered
        <select name="age">
          <option value="">Any time</option>
          <option value="today"{$selAgeToday}>Last 24 hours</option>
          <option value="week"{$selAgeWeek}>Last 7 days</option>
          <option value="older"{$selAgeOlder}>Older than 7 days</option>
        </select>
      </label>
      <label>Sort by
        <select name="sort">{$sortOptions}</select>
      </label>
      <label>Search
        <input type="text" name="q" value="{$fQuery}" placeholder="Reference, name, mobile or email">
      </label>
      <button class="btn" type="submit">Apply</button>
      <a class="btn ghost" href="./?export=1&amp;{$queryArg}">Export CSV</a>
      <a class="btn ghost" href="./">Reset</a>
    </form>
  </div>

  <div class="card" style="overflow-x:auto">
    <table>
      <thead><tr>
        <th>#</th><th>Reference</th><th>Status</th><th>Category</th><th>Name</th>
        <th class="hide-m">Mobile</th><th class="hide-m">Email</th><th>Amount</th><th>Registered</th>
      </tr></thead>
      <tbody>{$tr}{$emptyRow}</tbody>
    </table>
  </div>

  <div class="pager">
    <a class="btn small ghost" href="{$prevUrl}">‹ Prev</a>
    <span>Showing {$showing} of {$total} — page {$pageNum} / {$pages}</span>
    <a class="btn small" href="{$nextUrl}">Next ›</a>
  </div>

  <footer>
    <p class="muted">Pending payments are re-checked automatically every 5 minutes by
    api/cron-recheck.php. “Recheck payment now” asks Coaxlure immediately for one
    registration. Registrations still pending after 2 days are moved to Failed by the
    same cron (the abandoned-payment sweep) — after one final Coaxlure check, so a late
    payment always wins. A failed pair can simply register again: the same row is
    revived at the current price, and the failed history stays in the payment-attempt
    log.</p>
  </footer>
</div>
HTML);
