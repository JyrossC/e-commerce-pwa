<?php
/** POST /api/reviews/create.php */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { Response::methodNotAllowed('POST'); }
Auth::requireAuth(); CSRF::validate();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$v = Validator::make($input, [
    'product_id' => 'required|integer',
    'rating'     => 'required|integer|min:1|max:5',
    'title'      => 'optional|string|max:150',
    'comment'    => 'optional|string|max:2000',
]);
if ($v->fails()) { Response::error('Validation failed.', 422, $v->errors()); }

try {
    $prod = Database::fetchColumn("SELECT id FROM products WHERE id=? AND is_active=1", [(int)$input['product_id']]);
    if (!$prod) { Response::notFound('Product not found.'); }
    $existing = Database::fetchColumn("SELECT id FROM reviews WHERE user_id=? AND product_id=?", [Auth::id(), (int)$input['product_id']]);
    if ($existing) { Response::error('You have already reviewed this product.', 409); }
    Database::query(
        "INSERT INTO reviews (user_id, product_id, rating, title, comment) VALUES (?,?,?,?,?)",
        [Auth::id(), (int)$input['product_id'], (int)$input['rating'],
         Validator::sanitizeText($input['title'] ?? ''), Validator::sanitizeText($input['comment'] ?? '')]
    );
    Response::success(null, 'Review submitted successfully!', 201);
} catch (PDOException $e) { error_log("[Reviews/Create] ".$e->getMessage()); Response::error('Failed.',500); }
