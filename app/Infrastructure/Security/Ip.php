<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class Ip
{
  public static function raw(): string
  {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
  }

  public static function packed(): string
  {
    $ip = self::raw();
    $bin = @inet_pton($ip);
    return $bin !== false ? $bin : inet_pton('0.0.0.0');
  }
}
