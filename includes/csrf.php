<?php
/**
 * NovaMart — CSRF Protection
 * Synchronizer Token Pattern: token stored in session, sent via hidden field or header.
 */

require_once dirname(__DIR__) . '/config/config.php';

class CSRF
{
    /**
     * Generate (or retrieve existing) CSRF token for the current session.
     */
    public static function generateToken(): string
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Return an HTML hidden input field with the CSRF token.
     */
    public static function field(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Return just the token string (useful for JS XHR headers).
     */
    public static function token(): string
    {
        return self::generateToken();
    }

    /**
     * Validate the CSRF token from POST body or X-CSRF-Token header.
     * Terminates with 403 if validation fails.
     */
    public static function validate(): void
    {
        $expected = $_SESSION[CSRF_TOKEN_NAME] ?? '';
        $sid = session_id();
        if (empty($expected)) {
            error_log("[CSRF] Validation failed: Session token missing for SID: $sid");
            self::fail();
        }

        $received = self::getReceivedToken();

        if (empty($received) || !hash_equals($expected, $received)) {
            error_log("[CSRF] Validation failed: Mismatch or empty. SID: $sid, Expected: " . substr($expected, 0, 5) . "..., Received: " . ($received ? substr($received, 0, 5) . "..." : 'none'));
            self::fail();
        }
    }

    /**
     * Validate only; return bool instead of dying.
     */
    public static function check(): bool
    {
        $expected = $_SESSION[CSRF_TOKEN_NAME] ?? '';
        if (empty($expected)) return false;

        $received = self::getReceivedToken();
        return !empty($received) && hash_equals($expected, $received);
    }

    /**
     * Internal helper to extract token from various sources.
     */
    private static function getReceivedToken(): string
    {
        // 1. Check Custom Header (Standard for AJAX/Fetch)
        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        // 2. Check POST body
        if (!empty($_POST[CSRF_TOKEN_NAME])) {
            return $_POST[CSRF_TOKEN_NAME];
        }

        // 3. Check JSON body
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $json = json_decode($input, true);
            if (isset($json[CSRF_TOKEN_NAME])) {
                return (string)$json[CSRF_TOKEN_NAME];
            }
        }

        return '';
    }

    private static function fail(): void
    {
        require_once dirname(__DIR__) . '/includes/response.php';
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token.']);
        exit;
    }
}
