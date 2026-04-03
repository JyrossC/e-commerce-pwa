<?php
/**
 * GET /api/auth/check.php
 * Returns current auth status and user info for JS.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::methodNotAllowed('GET');
}

Response::success([
    'authenticated' => Auth::check(),
    'user'          => Auth::check() ? Auth::user() : null,
    'csrf_token'    => CSRF::token(),
]);
