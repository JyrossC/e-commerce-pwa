<?php
/** GET /api/wishlist/index.php — Get logged-in user's wishlist */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { Response::methodNotAllowed('GET'); }
Auth::requireAuth();

try {
    $items = Database::fetchAll(
        "SELECT w.product_id, p.name, p.slug, p.price, p.sale_price, p.image, p.stock, p.is_active
         FROM wishlist w JOIN products p ON p.id = w.product_id
         WHERE w.user_id = ? AND p.is_active = 1
         ORDER BY w.created_at DESC",
        [Auth::id()]
    );
    foreach ($items as &$item) {
        $item['price']          = (float)$item['price'];
        $item['sale_price']     = $item['sale_price'] !== null ? (float)$item['sale_price'] : null;
        $item['effective_price']= $item['sale_price'] ?? $item['price'];
        $item['stock']          = (int)$item['stock'];
        $item['in_stock']       = $item['stock'] > 0;
    }
    Response::success(['items' => $items, 'count' => count($items)]);
} catch (PDOException $e) {
    error_log("[Wishlist/Index] " . $e->getMessage());
    Response::error('Failed to fetch wishlist.', 500);
}
