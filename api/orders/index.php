<?php
/** GET /api/orders/index.php — list orders (admin: all, customer: own) */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { Response::methodNotAllowed('GET'); }
Auth::requireAuth();

$page   = max(1, Validator::sanitizeInt($_GET['page'] ?? 1));
$limit  = min(50, max(1, Validator::sanitizeInt($_GET['limit'] ?? ORDERS_PER_PAGE)));
$offset = ($page - 1) * $limit;

$conditions = [];
$params     = [];

if (!Auth::isAdmin()) {
    $conditions[] = 'o.user_id = ?';
    $params[] = Auth::id();
}
if (!empty($_GET['status'])) {
    $conditions[] = 'o.status = ?';
    $params[] = Validator::sanitizeText($_GET['status']);
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

try {
    $total  = (int) Database::fetchColumn("SELECT COUNT(*) FROM orders o $where", $params);
    $orders = Database::fetchAll(
        "SELECT o.id, o.order_number, o.total, o.status, o.payment_status,
                o.payment_method, o.shipping_name, o.created_at,
                u.name AS customer_name, u.email AS customer_email,
                COUNT(oi.id) AS item_count
         FROM orders o
         JOIN users u ON u.id = o.user_id
         LEFT JOIN order_items oi ON oi.order_id = o.id
         $where
         GROUP BY o.id
         ORDER BY o.created_at DESC
         LIMIT $limit OFFSET $offset",
        $params
    );

    foreach ($orders as &$o) {
        $o['total'] = (float) $o['total'];
        $o['item_count'] = (int) $o['item_count'];
    }

    Response::success([
        'orders' => $orders,
        'pagination' => ['total' => $total, 'page' => $page, 'limit' => $limit, 'total_pages' => (int)ceil($total/$limit)],
    ]);
} catch (PDOException $e) {
    error_log("[Orders/Index] " . $e->getMessage());
    Response::error('Failed to fetch orders.', 500);
}
