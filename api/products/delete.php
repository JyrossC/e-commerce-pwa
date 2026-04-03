<?php
/**
 * POST /api/products/delete.php  (Admin only)
 * Soft-delete a product by setting is_active = 0.
 *
 * POST param: id
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { Response::methodNotAllowed('POST'); }
Auth::requireAdmin();
CSRF::validate();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id    = Validator::sanitizeInt($input['id'] ?? 0);

if (!$id) { Response::error('Product ID required.', 422); }

try {
    $product = Database::fetchOne("SELECT id, name, image FROM products WHERE id = ?", [$id]);
    if (!$product) { Response::notFound('Product not found.'); }

    // Hard delete (also removes cart/wishlist items via FK)
    Database::query("DELETE FROM products WHERE id = ?", [$id]);

    // Clean up local image (if stored locally)
    if ($product['image'] && str_starts_with($product['image'], UPLOAD_URL)) {
        $localFile = UPLOAD_DIR . basename($product['image']);
        if (is_file($localFile)) @unlink($localFile);
    }

    Response::success(null, "Product '{$product['name']}' deleted successfully.");

} catch (PDOException $e) {
    error_log("[Product/Delete] " . $e->getMessage());
    Response::error('Failed to delete product.', 500);
}
