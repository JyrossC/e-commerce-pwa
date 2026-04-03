<?php
/**
 * GET /api/products/index.php
 * List products with optional search, category, pagination, sorting.
 *
 * Query params:
 *   q         - search term
 *   category  - category slug
 *   featured  - 1 for featured only
 *   min_price - minimum price
 *   max_price - maximum price
 *   sort      - price_asc|price_desc|newest|name_asc
 *   page      - page number (default 1)
 *   limit     - items per page (default 12)
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::methodNotAllowed('GET');
}

$q        = Validator::sanitizeText($_GET['q'] ?? '');
$category = Validator::sanitizeText($_GET['category'] ?? '');
$featured = isset($_GET['featured']) ? 1 : null;
$minPrice = isset($_GET['min_price']) ? Validator::sanitizeFloat($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) ? Validator::sanitizeFloat($_GET['max_price']) : null;
$sort     = $_GET['sort'] ?? 'newest';
$page     = max(1, Validator::sanitizeInt($_GET['page'] ?? 1));
$limit    = min(48, max(1, Validator::sanitizeInt($_GET['limit'] ?? PRODUCTS_PER_PAGE)));
$offset   = ($page - 1) * $limit;

$allowedSorts = ['price_asc', 'price_desc', 'newest', 'name_asc', 'popular'];
if (!in_array($sort, $allowedSorts)) $sort = 'newest';

$orderBy = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'name_asc'   => 'p.name ASC',
    'popular'    => '(SELECT COUNT(r.id) FROM reviews r WHERE r.product_id = p.id) DESC',
    default      => 'p.created_at DESC',
};

$conditions = ['p.is_active = 1'];
$params     = [];

if ($q !== '') {
    $conditions[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($category !== '') {
    $conditions[] = 'c.slug = ?';
    $params[] = $category;
}
if ($featured !== null) {
    $conditions[] = 'p.is_featured = 1';
}
if ($minPrice !== null) {
    $conditions[] = 'COALESCE(p.sale_price, p.price) >= ?';
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $conditions[] = 'COALESCE(p.sale_price, p.price) <= ?';
    $params[] = $maxPrice;
}

$where = 'WHERE ' . implode(' AND ', $conditions);

try {
    // Total count
    $total = (int) Database::fetchColumn(
        "SELECT COUNT(*) FROM products p
         LEFT JOIN categories c ON c.id = p.category_id $where",
        $params
    );

    // Paginated results
    $products = Database::fetchAll(
        "SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.stock, p.image,
                p.is_featured, p.created_at,
                c.id AS category_id, c.name AS category_name, c.slug AS category_slug,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(DISTINCT r.id) AS review_count
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         LEFT JOIN reviews r ON r.product_id = p.id AND r.is_approved = 1
         $where
         GROUP BY p.id
         ORDER BY $orderBy
         LIMIT $limit OFFSET $offset",
        $params
    );

    // Format
    foreach ($products as &$p) {
        $p['price']       = (float) $p['price'];
        $p['sale_price']  = $p['sale_price'] !== null ? (float) $p['sale_price'] : null;
        $p['effective_price'] = $p['sale_price'] ?? $p['price'];
        $p['avg_rating']  = round((float) $p['avg_rating'], 1);
        $p['review_count']= (int) $p['review_count'];
        $p['stock']       = (int) $p['stock'];
        $p['in_stock']    = $p['stock'] > 0;
    }

    Response::success([
        'products'    => $products,
        'pagination'  => [
            'total'        => $total,
            'page'         => $page,
            'limit'        => $limit,
            'total_pages'  => (int) ceil($total / $limit),
            'has_more'     => ($page * $limit) < $total,
        ],
        'filters' => [
            'q' => $q, 'category' => $category, 'sort' => $sort,
            'min_price' => $minPrice, 'max_price' => $maxPrice, 'featured' => $featured,
        ],
    ]);

} catch (PDOException $e) {
    error_log("[Products/index] " . $e->getMessage());
    Response::error('Failed to fetch products.', 500);
}
