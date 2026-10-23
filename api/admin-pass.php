<?php
/**
 * ADMIN-PASS.PHP — CLI helper: turn a password into a password_hash() string
 * for the /admin/ console (admin/index.php).
 *
 * PURPOSE: The admin console password must be stored ONLY as a bcrypt/argon
 *          hash inside owli-config.php (the server-only secrets file that is
 *          never committed). This script prints that hash so nobody has to
 *          write a tiny PHP snippet by hand.
 *
 * HOW TO USE (on the server, or locally inside the Docker web container):
 *   php api/admin-pass.php 'your-strong-password'
 *   -> prints something like  $2y$10$e0MYzXyjpJS7Pd0RVvHwHe...
 *   -> paste that whole line into owli-config.php:
 *        'admin' => [ 'password_hash' => '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe...' ],
 *
 * SECURITY:
 *   - Command line only (the web server can never run it: PHP_SAPI guard below
 *     AND api/.htaccess blocks it). On Hostinger use the Terminal button.
 *   - The password itself is never stored anywhere; only the one-way hash is.
 *   - Never commit the hash or the password to Git.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    /* Reached through the web somehow: pretend the file does not exist. */
    http_response_code(404);
    exit;
}

$pass = (string)($argv[1] ?? '');
if (strlen($pass) < 8) {
    fwrite(STDERR, "Usage: php api/admin-pass.php 'your-password'  (8 characters minimum)\n");
    exit(1);
}

echo "\nPaste this into owli-config.php -> 'admin' -> 'password_hash':\n\n";
echo "    '" . password_hash($pass, PASSWORD_DEFAULT) . "',\n\n";
echo "The console is at /admin/ once the hash is saved. Never commit this value.\n";
