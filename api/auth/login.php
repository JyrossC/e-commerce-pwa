<?php
/**
 * POST /api/auth/login.php
 * Authenticate user and start session.
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed('POST');
}

CSRF::validate();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$v = Validator::make($input, [
    'email'    => 'required|email',
    'password' => 'required|string|min:1',
]);

if ($v->fails()) {
    Response::error('Validation failed.', 422, $v->errors());
}

try {
    $user = Database::fetchOne(
        "SELECT id, name, email, password, role, is_active FROM users WHERE email = ?",
        [strtolower(trim($input['email']))]
    );

    // Use timing-safe comparison (password_verify does this internally)
    if (!$user || !password_verify($input['password'], $user['password'])) {
        Response::error('Invalid email or password.', 401);
    }

    if (!$user['is_active']) {
        Response::error('Your account has been suspended. Please contact support.', 403);
    }

    // Rehash if needed (algorithm upgrade)
    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_BCRYPT_COST])) {
        $newHash = password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_BCRYPT_COST]);
        Database::query("UPDATE users SET password = ? WHERE id = ?", [$newHash, $user['id']]);
    }

    Auth::login($user);

    // Merge guest cart (from localStorage items passed in request)
    $guestCart = $input['guest_cart'] ?? [];
    if (!empty($guestCart) && is_array($guestCart)) {
        foreach ($guestCart as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $qty       = max(1, (int)($item['quantity'] ?? 1));
            if ($productId < 1) continue;

            // Check product exists
            $product = Database::fetchOne("SELECT id, stock FROM products WHERE id = ? AND is_active = 1", [$productId]);
            if (!$product) continue;

            $qty = min($qty, $product['stock']);
            Database::query(
                "INSERT INTO cart (user_id, product_id, quantity)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), ?)",
                [$user['id'], $productId, $qty, $product['stock']]
            );
        }
    }

    $redirect = $_SESSION['redirect_after_login'] ?? '';
    unset($_SESSION['redirect_after_login']);

    Response::success([
        'user' => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ],
        'redirect' => $redirect ?: null,
    ], 'Welcome back, ' . $user['name'] . '!');

} catch (PDOException $e) {
    error_log("[Login] DB Error: " . $e->getMessage());
    Response::error('Login failed. Please try again.', 500);
}
