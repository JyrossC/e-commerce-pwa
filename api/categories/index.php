<?php
/** GET /api/categories/index.php */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { Response::methodNotAllowed('GET'); }
try {
    $cats = Database::fetchAll(
        "SELECT c.*, COUNT(p.id) AS product_count
         FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.is_active=1
         WHERE c.is_active=1 GROUP BY c.id ORDER BY c.sort_order, c.name"
    );
    foreach ($cats as &$c) { $c['product_count'] = (int)$c['product_count']; }
    Response::success(['categories' => $cats]);
} catch (PDOException $e) { error_log("[Categories] ".$e->getMessage()); Response::error('Failed.',500); }
