<?php
/** POST /api/cart/remove.php — Remove a single item */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { Response::methodNotAllowed('POST'); }
Auth::requireAuth(); CSRF::validate();

$input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$cartId = Validator::sanitizeInt($input['cart_id'] ?? 0);
if (!$cartId) { Response::error('Cart ID required.', 422); }

try {
    $rows = Database::query("DELETE FROM cart WHERE id = ? AND user_id = ?", [$cartId, Auth::id()])->rowCount();
    if (!$rows) { Response::notFound('Cart item not found.'); }
    $count = (int) Database::fetchColumn("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?", [Auth::id()]);
    Response::success(['cart_count' => $count], 'Item removed from cart.');
} catch (PDOException $e) {
    error_log("[Cart/Remove] " . $e->getMessage());
    Response::error('Failed to remove item.', 500);
}
