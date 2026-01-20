<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Database\Connection;

final class RateLimiter
{
  // Simple, durable rate limiter backed by login_attempts table.
  // identifier can be email (lowercased) for login attempts.
  public static function hit(?string $identifier, int $maxPer15Min = 10, int $lockMinutes = 15): array
  {
    $pdo = Connection::pdo();

    $id = $identifier ? mb_strtolower(trim($identifier)) : null;
    $ip = Ip::packed();

    $stmt = $pdo->prepare("SELECT id, attempts, first_attempt_at, locked_until
                          FROM login_attempts
                          WHERE ip = :ip AND ((identifier IS NULL AND :id IS NULL) OR identifier = :id)
                          LIMIT 1");
    $stmt->execute([':ip' => $ip, ':id' => $id]);
    $row = $stmt->fetch();

    $now = new \DateTimeImmutable('now');

    if ($row) {
      $lockedUntil = $row['locked_until'] ? new \DateTimeImmutable($row['locked_until']) : null;
      if ($lockedUntil && $lockedUntil > $now) {
        return ['allowed' => false, 'retry_after_seconds' => $lockedUntil->getTimestamp() - $now->getTimestamp()];
      }

      $first = new \DateTimeImmutable($row['first_attempt_at']);
      $windowSeconds = 15 * 60;

      // If window expired, reset
      if (($now->getTimestamp() - $first->getTimestamp()) > $windowSeconds) {
        $upd = $pdo->prepare("UPDATE login_attempts
                              SET attempts = 1, first_attempt_at = NOW(), last_attempt_at = NOW(), locked_until = NULL
                              WHERE id = :id");
        $upd->execute([':id' => $row['id']]);
        return ['allowed' => true, 'retry_after_seconds' => 0];
      }

      $attempts = (int)$row['attempts'] + 1;

      $lockedUntilNew = null;
      if ($attempts > $maxPer15Min) {
        $lockedUntilNew = $now->modify("+{$lockMinutes} minutes")->format('Y-m-d H:i:s');
      }

      $upd = $pdo->prepare("UPDATE login_attempts
                            SET attempts = :a, last_attempt_at = NOW(), locked_until = :lu
                            WHERE id = :id");
      $upd->execute([':a' => $attempts, ':lu' => $lockedUntilNew, ':id' => $row['id']]);

      if ($lockedUntilNew) {
        return ['allowed' => false, 'retry_after_seconds' => $lockMinutes * 60];
      }

      return ['allowed' => true, 'retry_after_seconds' => 0];
    }

    $ins = $pdo->prepare("INSERT INTO login_attempts (ip, identifier, attempts) VALUES (:ip, :id, 1)");
    $ins->execute([':ip' => $ip, ':id' => $id]);

    return ['allowed' => true, 'retry_after_seconds' => 0];
  }

  public static function clear(?string $identifier): void
  {
    $pdo = Connection::pdo();
    $id = $identifier ? mb_strtolower(trim($identifier)) : null;
    $ip = Ip::packed();

    $del = $pdo->prepare("DELETE FROM login_attempts
                          WHERE ip = :ip AND ((identifier IS NULL AND :id IS NULL) OR identifier = :id)");
    $del->execute([':ip' => $ip, ':id' => $id]);
  }
}
