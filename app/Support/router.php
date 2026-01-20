<?php

declare(strict_types=1);

namespace App\Support;

final class Router
{
  private array $routes = [];

  public function get(string $path, callable $handler): void
  {
    $this->map('GET', $path, $handler);
  }
  public function post(string $path, callable $handler): void
  {
    $this->map('POST', $path, $handler);
  }

  private function map(string $method, string $path, callable $handler): void
  {
    $this->routes[$method][$path] = $handler;
  }

  public function dispatch(array $request): array
  {
    $method = strtoupper($request['method'] ?? 'GET');
    $path = $request['path'] ?? '/';

    $handler = $this->routes[$method][$path] ?? null;
    if (!$handler) {
      return Response::html('<h1>404</h1>', 404);
    }

    return $handler($request);
  }
}
