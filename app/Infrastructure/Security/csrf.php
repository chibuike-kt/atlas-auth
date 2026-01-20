<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Support\Config;

final class Csrf
{
  public static function token(): string
  {
    $ttl = (int) Config::get('csrf.ttl', 7200);

    if (!isset($_SESSION['_csrf_token'], $_SESSION['_csrf_issued'])) {
      $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
      $_SESSION['_csrf_issued'] = time();
      return $_SESSION['_csrf_token'];
    }

    if ((time() - (int)$_SESSION['_csrf_issued']) > $ttl) {
      $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
      $_SESSION['_csrf_issued'] = time();
    }

    return (string) $_SESSION['_csrf_token'];
  }

  public static function verify(?string $provided): bool
  {
    if (!$provided || !isset($_SESSION['_csrf_token'])) return false;
    return hash_equals((string)$_SESSION['_csrf_token'], $provided);
  }
}
