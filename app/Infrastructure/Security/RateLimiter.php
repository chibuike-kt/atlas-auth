<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Database\Connection;

final class RateLimiter
{
  public static function hit(string $identifier, int $maxPer15Min = 10, int $lockMinutes = 15): array
  {
    $pdo = Connection::pdo();

    $id = mb_strtolower(trim($identifier));
    $ip = Ip::packed();
    $now = new \DateTimeImmutable('now');

    // Try to load row
    $stmt = $pdo->prepare("
      SELECT id, attempts, first_attempt_at, locked_until
      FROM login_attempts
      WHERE ip = :ip AND identifier = :identifier
      LIMIT 1
    ");
    $stmt->execute([
      ':ip' => $ip,
      ':identifier' => $id,
    ]);

    $row = $stmt->fetch();

    if ($row) {
      $lockedUntil = $row['locked_until'] ? new \DateTimeImmutable($row['locked_until']) : null;
      if ($lockedUntil && $lockedUntil > $now) {
        return ['allowed' => false, 'retry_after_seconds' => $lockedUntil->getTimestamp() - $now->getTimestamp()];
      }

      $first = new \DateTimeImmutable($row['first_attempt_at']);
      $windowSeconds = 15 * 60;

      // Reset if window expired
      if (($now->getTimestamp() - $first->getTimestamp()) > $windowSeconds) {
        $upd = $pdo->prepare("
          UPDATE login_attempts
          SET attempts = 1, first_attempt_at = NOW(), last_attempt_at = NOW(), locked_until = NULL
          WHERE id = :id
        ");
        $upd->execute([':id' => (int)$row['id']]);
        return ['allowed' => true, 'retry_after_seconds' => 0];
      }

      $attempts = (int)$row['attempts'] + 1;
      $lockedUntilNew = null;

      if ($attempts > $maxPer15Min) {
        $lockedUntilNew = $now->modify("+{$lockMinutes} minutes")->format('Y-m-d H:i:s');
      }

      $upd = $pdo->prepare("
        UPDATE login_attempts
        SET attempts = :a, last_attempt_at = NOW(), locked_until = :lu
        WHERE id = :id
      ");
      $upd->execute([
        ':a' => $attempts,
        ':lu' => $lockedUntilNew,
        ':id' => (int)$row['id'],
      ]);

      if ($lockedUntilNew) {
        return ['allowed' => false, 'retry_after_seconds' => $lockMinutes * 60];
      }

      return ['allowed' => true, 'retry_after_seconds' => 0];
    }

    // Insert new row
    $ins = $pdo->prepare("
      INSERT INTO login_attempts (ip, identifier, attempts)
      VALUES (:ip, :identifier, 1)
    ");
    $ins->execute([
      ':ip' => $ip,
      ':identifier' => $id,
    ]);

    return ['allowed' => true, 'retry_after_seconds' => 0];
  }

  public static function clear(string $identifier): void
  {
    $pdo = Connection::pdo();
    $id = mb_strtolower(trim($identifier));
    $ip = Ip::packed();

    $del = $pdo->prepare("DELETE FROM login_attempts WHERE ip = :ip AND identifier = :identifier");
    $del->execute([
      ':ip' => $ip,
      ':identifier' => $id,
    ]);
  }
}
