<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

final class Logger
{
  public static function info(string $message): void
  {
    $dir = dirname(__DIR__, 3) . '/storage/logs';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($dir . '/app.log', $line, FILE_APPEND);
  }
}
