<?php
/** GET /api/reviews/index.php?product_id=N — paginated reviews for a product */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { Response::methodNotAllowed('GET'); }
$productId = Validator::sanitizeInt($_GET['product_id'] ?? 0);
if (!$productId) { Response::error('product_id required.', 422); }
$page  = max(1, Validator::sanitizeInt($_GET['page'] ?? 1));
$limit = min(20, max(1, Validator::sanitizeInt($_GET['limit'] ?? 10)));
$offset = ($page-1)*$limit;

try {
    $total = (int) Database::fetchColumn("SELECT COUNT(*) FROM reviews WHERE product_id=? AND is_approved=1",[$productId]);
    $reviews = Database::fetchAll(
        "SELECT r.id, r.rating, r.title, r.comment, r.created_at, u.name AS reviewer_name
         FROM reviews r JOIN users u ON u.id=r.user_id
         WHERE r.product_id=? AND r.is_approved=1 ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset",
        [$productId]
    );
    $summary = Database::fetchOne(
        "SELECT COALESCE(AVG(rating),0) AS avg, COUNT(*) AS total,
                SUM(rating=5) AS r5, SUM(rating=4) AS r4, SUM(rating=3) AS r3, SUM(rating=2) AS r2, SUM(rating=1) AS r1
         FROM reviews WHERE product_id=? AND is_approved=1",[$productId]
    );
    Response::success(['reviews'=>$reviews,'summary'=>$summary,'pagination'=>['total'=>$total,'page'=>$page,'limit'=>$limit,'total_pages'=>(int)ceil($total/$limit)]]);
} catch (PDOException $e) { error_log("[Reviews/Index] ".$e->getMessage()); Response::error('Failed.',500); }
