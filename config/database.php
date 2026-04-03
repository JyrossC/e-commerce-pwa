<?php
/**
 * NovaMart — Database Connection (PDO Singleton)
 * All queries use prepared statements — immune to SQL injection.
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}
    public function __wakeup(): void { throw new \Exception("Cannot unserialize singleton."); }

    /** Get (or create) the PDO connection instance */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
                );
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    PDO::ATTR_PERSISTENT         => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log("[DB] Connection failed: " . $e->getMessage());
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    http_response_code(500);
                    die('<pre style="color:red">Database Error: ' . htmlspecialchars($e->getMessage()) . '</pre>');
                }
                http_response_code(500);
                die(json_encode([
                    'success' => false,
                    'message' => 'Service temporarily unavailable. Please try again later.'
                ]));
            }
        }
        return self::$instance;
    }

    /** Execute a prepared statement and return the statement object */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch all rows */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Fetch a single row */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    /** Fetch a single column value */
    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    /** Get last inserted ID */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /** Transaction helpers */
    public static function beginTransaction(): void { self::getInstance()->beginTransaction(); }
    public static function commit(): void           { self::getInstance()->commit(); }
    public static function rollback(): void         { self::getInstance()->rollBack(); }
}
