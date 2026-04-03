<?php
/**
 * POST /api/auth/register.php
 * Register a new customer account.
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
    'name'                  => 'required|string|min:2|max:100',
    'email'                 => 'required|email|max:180',
    'password'              => 'required|password',
    'password_confirmation' => 'required|string',
]);

// Check password confirmation manually
if ($input['password'] !== ($input['password_confirmation'] ?? '')) {
    Response::error('Passwords do not match.', 422, ['password_confirmation' => ['Passwords do not match.']]);
}

if ($v->fails()) {
    Response::error('Validation failed.', 422, $v->errors());
}

try {
    // Check email uniqueness
    $existing = Database::fetchColumn("SELECT id FROM users WHERE email = ?", [strtolower($input['email'])]);
    if ($existing) {
        Response::error('This email address is already registered.', 409);
    }

    $hash = password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_BCRYPT_COST]);

    Database::query(
        "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')",
        [
            Validator::sanitizeText($input['name']),
            strtolower(trim($input['email'])),
            $hash,
        ]
    );

    $userId = Database::lastInsertId();
    $user = Database::fetchOne("SELECT id, name, email, role FROM users WHERE id = ?", [$userId]);

    Auth::login($user);

    Response::success(
        ['user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']]],
        'Account created successfully! Welcome to ' . APP_NAME . '.',
        201
    );

} catch (PDOException $e) {
    error_log("[Register] DB Error: " . $e->getMessage());
    Response::error('Registration failed. Please try again.', 500);
}
