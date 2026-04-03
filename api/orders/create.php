<?php
/**
 * POST /api/orders/create.php
 * Place an order from the user's cart.
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

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$v = Validator::make($input, [
    'shipping_name'    => 'required|string|min:2|max:255',
    'shipping_email'   => 'required|email',
    'shipping_phone'   => 'optional|string|max:255',
    'shipping_address' => 'required|string|min:5|max:1000',
    'shipping_city'    => 'required|string|min:2|max:80',
    'shipping_country' => 'required|string|min:2|max:80',
    'payment_method'   => 'optional|string|in:cod,mpesa,card',
    'notes'            => 'optional|string|max:500',
]);

if ($v->fails()) { Response::error('Validation failed.', 422, $v->errors()); }

try {
    // Fetch cart items
    $cartItems = Database::fetchAll(
        "SELECT c.quantity, p.id AS product_id, p.name, p.image,
                p.stock, COALESCE(p.sale_price, p.price) AS unit_price
         FROM cart c
         JOIN products p ON p.id = c.product_id
         WHERE c.user_id = ? AND p.is_active = 1",
        [Auth::id()]
    );

    if (empty($cartItems)) {
        Response::error('Your cart is empty.', 422);
    }

    // Verify stock and compute totals
    $subtotal = 0;
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            Response::error("Insufficient stock for \"{$item['name']}\". Only {$item['stock']} available.", 422);
        }
        $subtotal += $item['unit_price'] * $item['quantity'];
    }

    $shipping  = $subtotal >= 5000 ? 0 : 300;
    $tax       = round($subtotal * 0.16, 2);
    $total     = round($subtotal + $shipping + $tax, 2);
    $orderNum  = 'NVM-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    Database::beginTransaction();

    // Insert order
    Database::query(
        "INSERT INTO orders (user_id, order_number, subtotal, shipping_cost, tax, total,
                             payment_method, shipping_name, shipping_email, shipping_phone,
                             shipping_address, shipping_city, shipping_country, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            Auth::id(), $orderNum, round($subtotal, 2), $shipping, $tax, $total,
            Validator::sanitizeText($input['payment_method'] ?? 'cod'),
            Validator::sanitizeText($input['shipping_name']),
            strtolower(trim($input['shipping_email'])),
            Validator::sanitizeText($input['shipping_phone'] ?? ''),
            Validator::sanitizeText($input['shipping_address']),
            Validator::sanitizeText($input['shipping_city']),
            Validator::sanitizeText($input['shipping_country']),
            Validator::sanitizeText($input['notes'] ?? ''),
        ]
    );

    $orderId = Database::lastInsertId();

    // Insert order items + decrement stock
    foreach ($cartItems as $item) {
        $lineTotal = round($item['unit_price'] * $item['quantity'], 2);
        Database::query(
            "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$orderId, $item['product_id'], $item['name'], $item['image'],
             $item['quantity'], $item['unit_price'], $lineTotal]
        );
        Database::query(
            "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?",
            [$item['quantity'], $item['product_id'], $item['quantity']]
        );
    }

    // Clear cart
    Database::query("DELETE FROM cart WHERE user_id = ?", [Auth::id()]);

    Database::commit();

    Response::success([
        'order_id'     => (int) $orderId,
        'order_number' => $orderNum,
        'total'        => $total,
    ], 'Order placed successfully! Your order number is ' . $orderNum, 201);

} catch (PDOException $e) {
    Database::rollback();
    error_log("[Orders/Create] " . $e->getMessage());
    Response::error('Failed to place order. Please try again.', 500);
}
