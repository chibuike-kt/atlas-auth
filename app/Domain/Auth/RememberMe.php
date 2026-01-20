<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Infrastructure\Database\Connection;
use App\Infrastructure\Security\Tokens;

final class RememberMe
{
  public static function issue(int $userId): string
  {
    $pdo = Connection::pdo();

    $selector = substr(Tokens::randomToken(16), 0, 24);
    $validator = Tokens::randomToken(32);
    $validatorHash = hash('sha256', $validator);

    $expires = (new \DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

    $pdo->prepare("
      INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at)
      VALUES (:u, :s, :v, :e)
    ")->execute([
      ':u' => $userId,
      ':s' => $selector,
      ':v' => $validatorHash,
      ':e' => $expires,
    ]);

    return $selector . ':' . $validator;
  }

  public static function consume(string $token): ?int
  {
    if (!str_contains($token, ':')) return null;

    [$selector, $validator] = explode(':', $token, 2);
    $pdo = Connection::pdo();

    $stmt = $pdo->prepare("
      SELECT id, user_id, validator_hash, expires_at
      FROM remember_tokens
      WHERE selector = :s
      LIMIT 1
    ");
    $stmt->execute([':s' => $selector]);
    $row = $stmt->fetch();

    if (!$row) return null;
    if (new \DateTimeImmutable($row['expires_at']) < new \DateTimeImmutable('now')) {
      self::deleteById((int)$row['id']);
      return null;
    }

    if (!hash_equals($row['validator_hash'], hash('sha256', $validator))) {
      // possible theft → invalidate all for this selector
      self::deleteById((int)$row['id']);
      return null;
    }

    // Rotate token
    self::deleteById((int)$row['id']);
    return (int)$row['user_id'];
  }

  public static function deleteById(int $id): void
  {
    Connection::pdo()->prepare("DELETE FROM remember_tokens WHERE id = :id")
      ->execute([':id' => $id]);
  }

  public static function deleteAllForUser(int $userId): void
  {
    Connection::pdo()->prepare("DELETE FROM remember_tokens WHERE user_id = :u")
      ->execute([':u' => $userId]);
  }
}
