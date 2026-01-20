<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class Auth
{
  public static function check(): bool
  {
    return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
  }

  public static function id(): ?int
  {
    return self::check() ? (int)$_SESSION['user_id'] : null;
  }

  public static function login(int $userId): void
  {
    SessionRegeneration::onPrivilegeChange();
    $_SESSION['user_id'] = $userId;

    $_SESSION['_sess_started_at'] = time();
    $_SESSION['_sess_last_activity'] = time();
    $_SESSION['_sv'] = (int)$userId['session_version'];
  }


  public static function logout(): void
  {
    unset($_SESSION['user_id']);
  }
}
