<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class Tokens
{
  public static function randomToken(int $bytes = 32): string
  {
    return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
  }

  public static function hashToken(string $token): string
  {
    return hash('sha256', $token);
  }
}
