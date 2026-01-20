<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class Password
{
  public static function hash(string $password): string
  {
    $options = [];
    if (defined('PASSWORD_ARGON2ID')) {
      return password_hash($password, PASSWORD_ARGON2ID, $options);
    }
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
  }

  public static function verify(string $password, string $hash): bool
  {
    return password_verify($password, $hash);
  }

  public static function needsRehash(string $hash): bool
  {
    if (defined('PASSWORD_ARGON2ID')) {
      return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }
    return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
  }
}
