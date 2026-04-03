<?php
/**
 * GET /api/products/show.php?id=N  OR  ?slug=my-product-slug
 * Fetch a single product with category and reviews summary.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { Response::methodNotAllowed('GET'); }

$id   = Validator::sanitizeInt($_GET['id'] ?? 0);
$slug = Validator::sanitizeText($_GET['slug'] ?? '');

if (!$id && !$slug) { Response::error('Product ID or slug required.', 422); }

try {
    $where  = $id ? 'p.id = ?' : 'p.slug = ?';
    $param  = $id ?: $slug;

    $product = Database::fetchOne(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(DISTINCT r.id) AS review_count
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         LEFT JOIN reviews r ON r.product_id = p.id AND r.is_approved = 1
         WHERE $where AND p.is_active = 1
         GROUP BY p.id",
        [$param]
    );

    if (!$product) { Response::notFound('Product not found.'); }

    $product['price']          = (float) $product['price'];
    $product['sale_price']     = $product['sale_price'] !== null ? (float) $product['sale_price'] : null;
    $product['effective_price']= $product['sale_price'] ?? $product['price'];
    $product['avg_rating']     = round((float) $product['avg_rating'], 1);
    $product['review_count']   = (int) $product['review_count'];
    $product['stock']          = (int) $product['stock'];
    $product['in_stock']       = $product['stock'] > 0;

    // Related products (same category)
    $related = Database::fetchAll(
        "SELECT id, name, slug, price, sale_price, image, stock
         FROM products
         WHERE category_id = ? AND id != ? AND is_active = 1
         ORDER BY RAND() LIMIT 4",
        [$product['category_id'], $product['id']]
    );

    Response::success(['product' => $product, 'related' => $related]);

} catch (PDOException $e) {
    error_log("[Product/Show] " . $e->getMessage());
    Response::error('Failed to fetch product.', 500);
}
