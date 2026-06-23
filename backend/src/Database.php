<?php
declare(strict_types=1);
namespace App;
use PDO;
final class Database
{
    private static ?PDO $pdo = null;
    public static function get(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $name = $_ENV['DB_NAME'] ?? 'books_api';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $sslCa = $_ENV['DB_SSL_CA'] ?? null;
        if ($sslCa) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
            $verify = $_ENV['DB_SSL_VERIFY'] ?? 'true';
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = ($verify !== 'false');
        }
        self::$pdo = new PDO($dsn, $user, $pass, $options);
        return self::$pdo;
    }
}