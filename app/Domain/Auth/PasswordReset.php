<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Infrastructure\Database\Connection;
use App\Infrastructure\Security\Tokens;
use App\Support\Config;
use App\Infrastructure\Logging\Logger;

final class PasswordReset
{
  public static function issue(string $email): void
  {
    $pdo = Connection::pdo();
    $emailNorm = mb_strtolower(trim($email));

    // Find user (but we won't reveal if not found)
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = :e LIMIT 1");
    $stmt->execute([':e' => $emailNorm]);
    $user = $stmt->fetch();

    if (!$user) {
      // Still behave the same
      return;
    }

    $userId = (int)$user['id'];

    // Remove old unused tokens for this user
    $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid AND used_at IS NULL")
      ->execute([':uid' => $userId]);

    $token = Tokens::randomToken(32);
    $tokenHash = Tokens::hashToken($token);
    $expires = (new \DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s');

    $pdo->prepare("
      INSERT INTO password_resets (user_id, token_hash, expires_at)
      VALUES (:uid, :th, :exp)
    ")->execute([
      ':uid' => $userId,
      ':th' => $tokenHash,
      ':exp' => $expires,
    ]);

    $baseUrl = rtrim((string)Config::get('app.url', 'http://localhost:8080'), '/');
    $link = $baseUrl . '/reset-password?token=' . urlencode($token);

    Logger::info("RESET_PASSWORD user_id={$userId} email={$emailNorm} link={$link}");
  }

  public static function consume(string $token): ?int
  {
    $pdo = Connection::pdo();
    $tokenHash = Tokens::hashToken($token);

    $stmt = $pdo->prepare("
      SELECT id, user_id, expires_at, used_at
      FROM password_resets
      WHERE token_hash = :th
      LIMIT 1
    ");
    $stmt->execute([':th' => $tokenHash]);
    $row = $stmt->fetch();

    if (!$row) return null;
    if ($row['used_at'] !== null) return null;

    $expires = new \DateTimeImmutable($row['expires_at']);
    if ($expires < new \DateTimeImmutable('now')) return null;

    // Mark as used
    $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id")
      ->execute([':id' => (int)$row['id']]);

    return (int)$row['user_id'];
  }
}
