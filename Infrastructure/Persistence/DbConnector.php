<?php

declare(strict_types=1);

class DbConnector
{
    private static ?DbConnector $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $host   = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $user   = $_ENV['DB_USER'];
        $pass   = $_ENV['DB_PASSWORD'];
        $this->connection = new PDO(
            "mysql:host={$host};dbname={$dbname}",
            $user,
            $pass
        );
    }

    public static function getInstance(): DbConnector
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function prepare(string $query): PDOStatement
    {
        return $this->connection->prepare($query);
    }
}
