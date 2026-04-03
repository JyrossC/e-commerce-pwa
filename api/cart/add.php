<?php
/**
 * POST /api/cart/add.php
 * Add a product to the cart (or increment quantity).
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

$input      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$productId  = Validator::sanitizeInt($input['product_id'] ?? 0);
$quantity   = max(1, Validator::sanitizeInt($input['quantity'] ?? 1));

if (!$productId) { Response::error('Product ID required.', 422); }

try {
    $product = Database::fetchOne(
        "SELECT id, name, stock, is_active FROM products WHERE id = ? AND is_active = 1",
        [$productId]
    );
    if (!$product) { Response::notFound('Product not found or unavailable.'); }
    if ($product['stock'] < 1) { Response::error('This product is out of stock.', 422); }

    // Check how much user already has in cart
    $existing = Database::fetchColumn(
        "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?",
        [Auth::id(), $productId]
    );
    $existing = $existing ? (int)$existing : 0;
    $newQty   = min($existing + $quantity, $product['stock']);

    Database::query(
        "INSERT INTO cart (user_id, product_id, quantity)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE quantity = ?",
        [Auth::id(), $productId, $newQty, $newQty]
    );

    // Return updated cart count
    $cartCount = (int) Database::fetchColumn(
        "SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?",
        [Auth::id()]
    );

    Response::success(['cart_count' => $cartCount, 'quantity' => $newQty], 'Added to cart!');

} catch (PDOException $e) {
    error_log("[Cart/Add] " . $e->getMessage());
    Response::error('Failed to add to cart.', 500);
}
