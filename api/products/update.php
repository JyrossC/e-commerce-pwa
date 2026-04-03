<?php
/**
 * POST /api/products/update.php  (Admin only)
 * Update an existing product.
 *
 * POST param: id (product ID)
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

$input = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);
$id    = Validator::sanitizeInt($input['id'] ?? 0);

if (!$id) { Response::error('Product ID is required.', 422); }

$product = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
if (!$product) { Response::notFound('Product not found.'); }

$v = Validator::make($input, [
    'name'        => 'required|string|min:3|max:200',
    'category_id' => 'required|integer',
    'description' => 'optional|string|max:5000',
    'price'       => 'required|positive',
    'sale_price'  => 'optional|positive',
    'stock'       => 'required|integer',
    'sku'         => 'optional|string|max:80',
    'is_featured' => 'optional|integer',
    'is_active'   => 'optional|integer',
]);

if ($v->fails()) { Response::error('Validation failed.', 422, $v->errors()); }

$imagePath = $product['image']; // keep existing by default

// Handle new image upload
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['image'];
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) { Response::error('Invalid image type.', 422); }
    if ($file['size'] > MAX_FILE_SIZE) { Response::error('Image too large. Max 5MB.', 422); }

    if (!is_dir(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0755, true); }

    $ext      = match($mimeType) {
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg'
    };
    $filename = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)) {
        // Delete old local image
        if ($product['image'] && str_starts_with($product['image'], UPLOAD_URL)) {
            $oldFile = UPLOAD_DIR . basename($product['image']);
            if (is_file($oldFile)) @unlink($oldFile);
        }
        $imagePath = UPLOAD_URL . $filename;
    }
} elseif (!empty($input['image_url'])) {
    $imagePath = filter_var($input['image_url'], FILTER_VALIDATE_URL) ? $input['image_url'] : $imagePath;
}

$salePrice = !empty($input['sale_price']) ? Validator::sanitizeFloat($input['sale_price']) : null;

try {
    Database::query(
        "UPDATE products SET
            category_id = ?, name = ?, description = ?, price = ?, sale_price = ?,
            stock = ?, sku = ?, image = ?, is_featured = ?, is_active = ?
         WHERE id = ?",
        [
            (int)$input['category_id'],
            Validator::sanitizeText($input['name']),
            Validator::sanitizeText($input['description'] ?? ''),
            Validator::sanitizeFloat($input['price']),
            $salePrice,
            max(0, (int)$input['stock']),
            Validator::sanitizeText($input['sku'] ?? ''),
            $imagePath,
            (int)($input['is_featured'] ?? 0),
            (int)($input['is_active'] ?? 1),
            $id,
        ]
    );

    $updated = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
    Response::success(['product' => $updated], 'Product updated successfully.');

} catch (PDOException $e) {
    error_log("[Product/Update] " . $e->getMessage());
    Response::error('Failed to update product.', 500);
}
