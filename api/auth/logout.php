<?php
/**
 * GET /api/auth/logout.php
 * Destroy session and redirect to shop.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

Auth::logout();
header('Location: ' . APP_URL . '/pages/login.php?msg=logged_out');
exit;
