<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Security\Auth;
use App\Support\Response;

final class Authenticate
{
  public function handle(array $request, callable $next): array
  {
    if (!Auth::check()) {
      return Response::redirect('/');
    }
    return $next($request);
  }
}
