<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Security\Auth;
use App\Infrastructure\Security\Cookies;
use App\Domain\Auth\RememberMe;

final class RememberMeMiddleware
{
  public function handle(array $request, callable $next): array
  {
    if (!Auth::check()) {
      $token = Cookies::getRemember();
      if ($token) {
        $userId = RememberMe::consume($token);
        if ($userId) {
          Auth::login($userId);
          // reissue rotated token
          Cookies::setRemember(RememberMe::issue($userId));
        } else {
          Cookies::clearRemember();
        }
      }
    }

    return $next($request);
  }
}
