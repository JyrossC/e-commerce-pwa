<?php
/**
 * NovaMart — Authentication Helpers
 * Session-based auth with role checks.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/response.php';

class Auth
{
    /** Check if the current session has a logged-in user */
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
    }

    /** Check if logged-in user is admin */
    public static function isAdmin(): bool
    {
        return self::check() && $_SESSION['user_role'] === 'admin';
    }

    /** Get the current user's ID */
    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /** Get the current user's role */
    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    /** Get current user's display name */
    public static function name(): string
    {
        return Validator::escape($_SESSION['user_name'] ?? 'Guest');
    }

    /** Get full session user data */
    public static function user(): array
    {
        return [
            'id'    => self::id(),
            'name'  => $_SESSION['user_name']  ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role'  => $_SESSION['user_role']  ?? null,
        ];
    }

    /**
     * Require authentication — for API endpoints.
     * If unauthenticated, returns 401 JSON and exits.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            Response::unauthorized('You must be logged in to access this resource.');
        }
    }

    /**
     * Require admin role — for API endpoints.
     * If not admin, returns 403 JSON and exits.
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            Response::forbidden('Administrator access required.');
        }
    }

    /**
     * Require authentication — for HTML page redirects.
     * Redirects to login page if not authenticated.
     */
    public static function requireAuthPage(string $redirect = '/ecommerce-pwa/pages/login.php'): void
    {
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
            header('Location: ' . APP_URL . '/pages/login.php');
            exit;
        }
    }

    /**
     * Require admin — for HTML pages.
     * Redirects to home if not admin.
     */
    public static function requireAdminPage(): void
    {
        self::requireAuthPage();
        if (!self::isAdmin()) {
            header('Location: ' . APP_URL . '/pages/shop.php');
            exit;
        }
    }

    /**
     * Establish a session for a successfully authenticated user.
     * Regenerates session ID to prevent fixation attacks.
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = (int) $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['_last_regen'] = time();
    }

    /** Destroy the session (logout) */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /** Redirect if already logged in (for login/register pages) */
    public static function redirectIfLoggedIn(string $to = null): void
    {
        if (self::check()) {
            $dest = $to ?? (self::isAdmin() ? APP_URL . '/pages/admin/dashboard.php' : APP_URL . '/pages/shop.php');
            header("Location: $dest");
            exit;
        }
    }
}

// Load Validator if not already loaded (needed for Auth::name())
if (!class_exists('Validator')) {
    require_once dirname(__DIR__) . '/includes/validator.php';
}
