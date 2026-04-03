<?php
/** POST /api/wishlist/toggle.php */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { Response::methodNotAllowed('POST'); }
Auth::requireAuth(); CSRF::validate();
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$pid = Validator::sanitizeInt($input['product_id'] ?? 0);
if (!$pid) { Response::error('product_id required.', 422); }

try {
    $exists = Database::fetchColumn("SELECT id FROM wishlist WHERE user_id=? AND product_id=?", [Auth::id(), $pid]);
    if ($exists) {
        Database::query("DELETE FROM wishlist WHERE user_id=? AND product_id=?", [Auth::id(), $pid]);
        Response::success(['wishlisted' => false], 'Removed from wishlist.');
    } else {
        $prod = Database::fetchColumn("SELECT id FROM products WHERE id=? AND is_active=1", [$pid]);
        if (!$prod) { Response::notFound('Product not found.'); }
        Database::query("INSERT INTO wishlist (user_id, product_id) VALUES (?,?)", [Auth::id(), $pid]);
        Response::success(['wishlisted' => true], 'Added to wishlist!');
    }
} catch (PDOException $e) { error_log("[Wishlist/Toggle] ".$e->getMessage()); Response::error('Failed.',500); }
