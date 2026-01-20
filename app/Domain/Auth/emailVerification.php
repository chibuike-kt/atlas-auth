<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Infrastructure\Database\Connection;
use App\Infrastructure\Security\Tokens;
use App\Support\Config;
use App\Infrastructure\Logging\Logger;

final class EmailVerification
{
  public static function issueForUser(int $userId, string $email): void
  {
    $pdo = Connection::pdo();

    // Optional: delete old unused tokens for this user (keeps table tidy)
    $pdo->prepare("DELETE FROM email_verifications WHERE user_id = :uid AND used_at IS NULL")
      ->execute([':uid' => $userId]);

    $token = Tokens::randomToken(32);
    $tokenHash = Tokens::hashToken($token);

    $expires = (new \DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s');

    $pdo->prepare("
      INSERT INTO email_verifications (user_id, token_hash, expires_at)
      VALUES (:uid, :th, :exp)
    ")->execute([
      ':uid' => $userId,
      ':th' => $tokenHash,
      ':exp' => $expires,
    ]);

    $baseUrl = rtrim((string)Config::get('app.url', 'http://localhost:8080'), '/');
    $link = $baseUrl . '/verify-email?token=' . urlencode($token);

    // DEV MODE: log the link (later we’ll swap to SMTP without changing flow)
    Logger::info("VERIFY_EMAIL user_id={$userId} email={$email} link={$link}");
  }
}
