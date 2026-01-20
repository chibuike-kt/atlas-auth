<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Security\Csrf;

final class CsrfMiddleware
{
  public function handle(array $request, callable $next): array
  {
    $method = strtoupper($request['method'] ?? 'GET');

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
      $token = $request['input']['_csrf'] ?? $request['headers']['x-csrf-token'] ?? null;
      if (!Csrf::verify(is_string($token) ? $token : null)) {
        return [
          'status' => 419,
          'headers' => ['Content-Type' => 'text/plain; charset=utf-8'],
          'body' => "CSRF token mismatch.\n",
        ];
      }
    }

    return $next($request);
  }
}
