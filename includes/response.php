<?php
/**
 * NovaMart — JSON Response Helper
 * Provides consistent JSON API response formatting.
 */

require_once dirname(__DIR__) . '/config/config.php';

class Response
{
    /**
     * Send a JSON success response
     * @param mixed  $data    Response data
     * @param string $message Optional message
     * @param int    $code    HTTP status code
     */
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): void
    {
        self::send([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Send a JSON error response
     * @param string $message Error message
     * @param int    $code    HTTP status code
     * @param mixed  $errors  Validation errors or additional detail
     */
    public static function error(string $message = 'An error occurred', int $code = 400, mixed $errors = null): void
    {
        $body = [
            'success' => false,
            'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
        ];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        self::send($body, $code);
    }

    /** Send 401 Unauthorized */
    public static function unauthorized(string $message = 'Authentication required'): void
    {
        self::error($message, 401);
    }

    /** Send 403 Forbidden */
    public static function forbidden(string $message = 'Access denied'): void
    {
        self::error($message, 403);
    }

    /** Send 404 Not Found */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /** Send 405 Method Not Allowed */
    public static function methodNotAllowed(string $allowed = 'GET'): void
    {
        if (!headers_sent()) {
            header("Allow: $allowed");
        }
        self::error("Method not allowed. Use: $allowed", 405);
    }

    /** Core sender — sets headers, encodes JSON, exits */
    private static function send(array $body, int $code): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
