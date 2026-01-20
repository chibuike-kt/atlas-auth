<?php

declare(strict_types=1);

namespace App\Support;

final class Config
{
  private static array $data = [];

  public static function init(array $data): void
  {
    self::$data = $data;
  }

  public static function get(string $key, mixed $default = null): mixed
  {
    return self::$data[$key] ?? $default;
  }
}
