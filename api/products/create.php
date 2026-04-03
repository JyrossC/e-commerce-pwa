<?php
/**
 * POST /api/products/create.php  (Admin only)
 * Create a new product. Supports JSON body or multipart form with image upload.
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

// Validate category exists
$cat = Database::fetchColumn("SELECT id FROM categories WHERE id = ?", [(int)$input['category_id']]);
if (!$cat) { Response::error('Invalid category.', 422); }

$imagePath = null;

// Handle image upload
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['image'];
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        Response::error('Invalid image type. Allowed: JPG, PNG, WEBP, GIF.', 422);
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        Response::error('Image too large. Maximum size is 5MB.', 422);
    }

    if (!is_dir(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0755, true); }

    $ext      = match($mimeType) {
        'image/jpeg' => 'jpg', 'image/png' => 'png',
        'image/webp' => 'webp', 'image/gif' => 'gif', default => 'jpg'
    };
    $filename = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destPath = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        Response::error('Failed to save image. Check directory permissions.', 500);
    }
    $imagePath = UPLOAD_URL . $filename;
} elseif (!empty($input['image_url'])) {
    // Accept a URL if no file uploaded (for demo data)
    $imagePath = filter_var($input['image_url'], FILTER_VALIDATE_URL) ? $input['image_url'] : null;
}

$slug     = Validator::slug($input['name']) . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
$price    = Validator::sanitizeFloat($input['price']);
$salePrice = !empty($input['sale_price']) ? Validator::sanitizeFloat($input['sale_price']) : null;

try {
    Database::query(
        "INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, sku, image, is_featured, is_active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            (int)$input['category_id'],
            Validator::sanitizeText($input['name']),
            $slug,
            Validator::sanitizeText($input['description'] ?? ''),
            $price,
            $salePrice,
            max(0, (int)$input['stock']),
            Validator::sanitizeText($input['sku'] ?? ''),
            $imagePath,
            (int)($input['is_featured'] ?? 0),
            (int)($input['is_active'] ?? 1),
        ]
    );

    $id = Database::lastInsertId();
    $product = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$id]);

    Response::success(['product' => $product], 'Product created successfully.', 201);
} catch (PDOException $e) {
    error_log("[Product/Create] " . $e->getMessage());
    Response::error('Failed to create product.', 500);
}
