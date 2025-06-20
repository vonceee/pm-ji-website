<?php
// config/database.php
declare(strict_types=1);

namespace config;

use PDO;
use PDOException;

/**
 * database connection manager.
 */
class database
{
    /** @var PDO|null */
    private static $instance = null;

    /**
     * returns a singleton PDO connection.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = 'localhost';
            $dbName = 'db_pmji';
            $username = 'root';
            $password = '';

            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $host,
                $dbName
            );

            try {
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$instance = $pdo;
            } catch (PDOException $e) {
                // in production, log the error and show a generic message
                exit('database connection failed.');
            }
        }

        return self::$instance;
    }
}
