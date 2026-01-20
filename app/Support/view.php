<?php

declare(strict_types=1);

namespace App\Support;

final class View
{
  public static function render(string $view, array $data = []): string
  {
    $path = dirname(__DIR__, 2) . '/resources/views/' . $view . '.php';
    if (!file_exists($path)) {
      return "<h1>View not found</h1>";
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $path;
    return (string) ob_get_clean();
  }
}
