<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class Cookies
{
  public static function setRemember(string $value): void
  {
    setcookie('remember_me', $value, [
      'expires' => time() + (60 * 60 * 24 * 30),
      'path' => '/',
      'httponly' => true,
      'secure' => false, // set true on HTTPS
      'samesite' => 'Lax',
    ]);
  }

  public static function getRemember(): ?string
  {
    return $_COOKIE['remember_me'] ?? null;
  }

  public static function clearRemember(): void
  {
    setcookie('remember_me', '', [
      'expires' => time() - 3600,
      'path' => '/',
      'httponly' => true,
      'secure' => false,
      'samesite' => 'Lax',
    ]);
  }
}
