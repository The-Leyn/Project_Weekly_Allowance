<?php

namespace App\Infrastructure\Database;

/**
 * Classe de connexion à la base de données (Singleton)
 */
class Database
{
  private static ?Database $instance = null;
  private \PDO $connection;

  private function __construct()
  {
    $host = getenv('DB_HOST') ?: 'db';
    $port = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_NAME') ?: 'test_db';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: 'root';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    try {
      $this->connection = new \PDO($dsn, $username, $password, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
      ]);
    } catch (\PDOException $e) {
      throw new \RuntimeException("Database connection failed: " . $e->getMessage());
    }
  }

  public static function getInstance(): self
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function getConnection(): \PDO
  {
    return $this->connection;
  }

  // Empêcher le clonage
  private function __clone()
  {
  }

  // Empêcher la désérialisation
  public function __wakeup()
  {
    throw new \Exception("Cannot unserialize singleton");
  }
}
