<?php

declare(strict_types=1);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'");
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$secureCookie = filter_var(getenv('COOKIE_SECURE') ?: 'true', FILTER_VALIDATE_BOOL);
$sameSite = getenv('COOKIE_SAMESITE') ?: ($secureCookie ? 'None' : 'Lax');

ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', $secureCookie ? '1' : '0');
ini_set('session.cookie_samesite', $sameSite);
ini_set('session.gc_maxlifetime', (string) max(300, (int) (getenv('SESSION_TTL') ?: 3600)));

session_name('fixerupper_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => getenv('COOKIE_DOMAIN') ?: '',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => $sameSite,
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
