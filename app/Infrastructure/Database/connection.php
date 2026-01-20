<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOException;
use App\Support\Config;

final class Connection
{
  private static ?PDO $pdo = null;

  public static function pdo(): PDO
  {
    if (self::$pdo) return self::$pdo;

    $host = Config::get('db.host');
    $port = Config::get('db.port');
    $db = Config::get('db.name');
    $user = Config::get('db.user');
    $pass = Config::get('db.pass');

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    try {
      self::$pdo = new PDO($dsn, (string)$user, (string)$pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]);
    } catch (PDOException $e) {
      throw new \RuntimeException('DB connection failed: ' . $e->getMessage());
    }

    return self::$pdo;
  }
}
