<?php
/** GET /api/orders/show.php?id=N */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { Response::methodNotAllowed('GET'); }
Auth::requireAuth();

$id = Validator::sanitizeInt($_GET['id'] ?? 0);
if (!$id) { Response::error('Order ID required.', 422); }

try {
    $conditionParam = Auth::isAdmin() ? [$id] : [$id, Auth::id()];
    $extraCond = Auth::isAdmin() ? '' : 'AND o.user_id = ?';

    $order = Database::fetchOne(
        "SELECT o.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
         FROM orders o JOIN users u ON u.id = o.user_id
         WHERE o.id = ? $extraCond",
        $conditionParam
    );
    if (!$order) { Response::notFound('Order not found.'); }
    $order['total'] = (float) $order['total'];
    $order['subtotal'] = (float) $order['subtotal'];
    $order['tax'] = (float) $order['tax'];
    $order['shipping_cost'] = (float) $order['shipping_cost'];

    $items = Database::fetchAll(
        "SELECT oi.*, p.slug AS product_slug FROM order_items oi
         LEFT JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id = ?",
        [$id]
    );
    foreach ($items as &$it) {
        $it['price'] = (float) $it['price'];
        $it['subtotal'] = (float) $it['subtotal'];
        $it['quantity'] = (int) $it['quantity'];
    }

    Response::success(['order' => $order, 'items' => $items]);
} catch (PDOException $e) {
    error_log("[Orders/Show] " . $e->getMessage());
    Response::error('Failed to fetch order.', 500);
}
