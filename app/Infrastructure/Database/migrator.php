<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

final class Migrator
{
  public static function run(): void
  {
    $pdo = Connection::pdo();

    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      filename VARCHAR(255) NOT NULL UNIQUE,
      ran_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $dir = dirname(__DIR__) . '/Database/Migrations';
    $files = glob($dir . '/*.sql') ?: [];

    foreach ($files as $file) {
      $filename = basename($file);

      $stmt = $pdo->prepare("SELECT 1 FROM migrations WHERE filename = :f LIMIT 1");
      $stmt->execute([':f' => $filename]);
      if ($stmt->fetchColumn()) continue;

      $sql = file_get_contents($file);
      if ($sql === false) throw new \RuntimeException("Failed reading migration: {$filename}");

      $pdo->beginTransaction();

      try {
        $pdo->exec($sql);

        $ins = $pdo->prepare("INSERT INTO migrations (filename) VALUES (:f)");
        $ins->execute([':f' => $filename]);

        $pdo->commit();
      } catch (\Throwable $e) {
        if ($pdo->inTransaction()) {
          $pdo->rollBack();
        }

        $msg = "Migration failed: {$filename}\n" . $e->getMessage() . "\n";
        throw new \RuntimeException($msg, 0, $e);
      }
    }
  }
}
