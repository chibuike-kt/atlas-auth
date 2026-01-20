<?php

declare(strict_types=1);

namespace App\Support;

use Dotenv\Dotenv;

final class Env
{
  public static function load(string $rootPath): void
  {
    if (file_exists($rootPath . '/.env')) {
      Dotenv::createImmutable($rootPath)->safeLoad();
    }
  }

  public static function get(string $key, ?string $default = null): ?string
  {
    $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($val === false || $val === null || $val === '') return $default;
    return (string) $val;
  }

  public static function bool(string $key, bool $default = false): bool
  {
    $v = self::get($key);
    if ($v === null) return $default;
    return in_array(strtolower($v), ['1', 'true', 'yes', 'on'], true);
  }
}
