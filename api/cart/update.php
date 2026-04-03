<?php
/**
 * POST /api/cart/update.php
 * Update quantity of a cart item.
 */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { Response::methodNotAllowed('POST'); }
Auth::requireAuth();
CSRF::validate();

$input     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$cartId    = Validator::sanitizeInt($input['cart_id'] ?? 0);
$quantity  = Validator::sanitizeInt($input['quantity'] ?? 0);

if (!$cartId) { Response::error('Cart item ID required.', 422); }

try {
    if ($quantity < 1) {
        // Remove the item
        Database::query("DELETE FROM cart WHERE id = ? AND user_id = ?", [$cartId, Auth::id()]);
        Response::success(null, 'Item removed from cart.');
    }

    // Check stock
    $cartItem = Database::fetchOne(
        "SELECT c.id, p.stock, p.name FROM cart c JOIN products p ON p.id = c.product_id
         WHERE c.id = ? AND c.user_id = ?",
        [$cartId, Auth::id()]
    );
    if (!$cartItem) { Response::notFound('Cart item not found.'); }

    $newQty = min($quantity, $cartItem['stock']);
    Database::query("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", [$newQty, $cartId, Auth::id()]);

    Response::success(['quantity' => $newQty], 'Cart updated.');
} catch (PDOException $e) {
    error_log("[Cart/Update] " . $e->getMessage());
    Response::error('Failed to update cart.', 500);
}
