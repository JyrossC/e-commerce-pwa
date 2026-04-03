<?php
/** POST /api/cart/clear.php — Remove all items from user's cart */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { Response::methodNotAllowed('POST'); }
Auth::requireAuth(); CSRF::validate();

try {
    Database::query("DELETE FROM cart WHERE user_id = ?", [Auth::id()]);
    Response::success(['cart_count' => 0], 'Cart cleared.');
} catch (PDOException $e) {
    error_log("[Cart/Clear] " . $e->getMessage());
    Response::error('Failed to clear cart.', 500);
}
