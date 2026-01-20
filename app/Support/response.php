<?php

declare(strict_types=1);

namespace App\Support;

final class Response
{
  public static function html(string $html, int $status = 200, array $headers = []): array
  {
    return [
      'status' => $status,
      'headers' => array_merge(['Content-Type' => 'text/html; charset=utf-8'], $headers),
      'body' => $html,
    ];
  }

  public static function redirect(string $to, int $status = 302): array
  {
    return [
      'status' => $status,
      'headers' => ['Location' => $to],
      'body' => '',
    ];
  }
}
