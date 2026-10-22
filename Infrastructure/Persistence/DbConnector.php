<?php

declare(strict_types=1);

namespace SocialApi\Infrastructure\Persistence;

use PDO;
use PDOStatement;

class DbConnector
{
    private static ?DbConnector $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port   = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_NAME'] ?? 'social';
        $user   = $_ENV['DB_USER'] ?? '';
        $pass   = $_ENV['DB_PASSWORD'] ?? '';

        $this->connection = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }

    public static function getInstance(): DbConnector
    {
        if(self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function prepare(string $query): PDOStatement
    {
        return $this->connection->prepare($query);
    }
}
