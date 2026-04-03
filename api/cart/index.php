<?php
/**
 * GET  /api/cart/index.php     — fetch cart for logged-in user
 * POST /api/cart/add.php       — add item
 * POST /api/cart/update.php    — update quantity
 * POST /api/cart/remove.php    — remove item
 * POST /api/cart/clear.php     — clear all
 *
 * This file handles GET (cart listing).
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { Response::methodNotAllowed('GET'); }
Auth::requireAuth();

try {
    $items = Database::fetchAll(
        "SELECT c.id AS cart_id, c.quantity,
                p.id, p.name, p.slug, p.price, p.sale_price, p.stock, p.image,
                COALESCE(p.sale_price, p.price) AS effective_price
         FROM cart c
         JOIN products p ON p.id = c.product_id
         WHERE c.user_id = ? AND p.is_active = 1
         ORDER BY c.created_at DESC",
        [Auth::id()]
    );

    $subtotal = 0;
    foreach ($items as &$item) {
        $item['price']           = (float) $item['price'];
        $item['sale_price']      = $item['sale_price'] !== null ? (float) $item['sale_price'] : null;
        $item['effective_price'] = (float) $item['effective_price'];
        $item['stock']           = (int) $item['stock'];
        $item['quantity']        = (int) $item['quantity'];
        $item['line_total']      = round($item['effective_price'] * $item['quantity'], 2);
        $subtotal               += $item['line_total'];
    }

    $shipping = $subtotal >= 5000 ? 0 : 300; // Free shipping over TZS 5000
    $tax      = round($subtotal * 0.16, 2);   // 16% VAT
    $total    = round($subtotal + $shipping + $tax, 2);

    Response::success([
        'items'     => $items,
        'count'     => count($items),
        'subtotal'  => round($subtotal, 2),
        'shipping'  => $shipping,
        'tax'       => $tax,
        'total'     => $total,
        'free_shipping_threshold' => 5000,
    ]);

} catch (PDOException $e) {
    error_log("[Cart/Index] " . $e->getMessage());
    Response::error('Failed to fetch cart.', 500);
}
