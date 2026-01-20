<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Session\SessionManager;

final class SessionRegeneration
{
  public static function onPrivilegeChange(): void
  {
    SessionManager::regenerate();
  }
}
