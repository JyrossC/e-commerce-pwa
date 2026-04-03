<?php
/**
 * NovaMart E-Commerce PWA
 * Application Configuration File
 *
 * @author  Jaiross Steven
 * @version 1.0.0
 */

// ─── Database ────────────────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'novamart_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ─── Application ─────────────────────────────────────────────────────────────
define('APP_NAME',    'NovaMart');
define('APP_TAGLINE', 'Your Premium Shopping Destination');
define('APP_URL',     'http://localhost/ecommerce-pwa');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'development'); // Change to 'production' in live

// ─── Session ─────────────────────────────────────────────────────────────────
define('SESSION_LIFETIME', 7200);          // 2 hours
define('SESSION_NAME',     'NM_STORE');

// ─── Security ────────────────────────────────────────────────────────────────
define('CSRF_TOKEN_NAME',    '_csrf_token');
define('CSRF_TOKEN_LENGTH',  40);
define('PASSWORD_BCRYPT_COST', 12);

// ─── File Upload ─────────────────────────────────────────────────────────────
define('UPLOAD_DIR',           dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR);
define('UPLOAD_URL',           APP_URL . '/assets/images/products/');
define('MAX_FILE_SIZE',        5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ─── Pagination ───────────────────────────────────────────────────────────────
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE',   10);
define('USERS_PER_PAGE',    20);

// ─── Security Headers ────────────────────────────────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https://picsum.photos https://fastly.picsum.photos https://images.unsplash.com; connect-src 'self';");

    // Prevent caching of HTML documents to ensure fresh CSRF tokens
    if (php_sapi_name() !== 'cli' && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    }

    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// ─── Session Hardening ───────────────────────────────────────────────────────
ini_set('session.name',          SESSION_NAME);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically to prevent fixation
if (!isset($_SESSION['_last_regen'])) {
    session_regenerate_id(true);
    $_SESSION['_last_regen'] = time();
} elseif (time() - $_SESSION['_last_regen'] > 1800) { // every 30 min
    session_regenerate_id(true);
    $_SESSION['_last_regen'] = time();
}

// ─── Error Handling ──────────────────────────────────────────────────────────
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) { @mkdir($logDir, 0750, true); }
    ini_set('error_log', $logDir . DIRECTORY_SEPARATOR . 'app.log');
}
