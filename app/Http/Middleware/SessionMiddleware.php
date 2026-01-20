<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Config;
use App\Support\Response;
use App\Infrastructure\Security\Auth;
use App\Infrastructure\Session\SessionManager;

final class SessionTimeout
{
  public function handle(array $request, callable $next): array
  {
    // Only enforce for authenticated sessions
    if (!Auth::check()) {
      return $next($request);
    }

    $now = time();
    $idle = (int) Config::get('session.idle_timeout', 1800);
    $absolute = (int) Config::get('session.absolute_timeout', 28800);

    // First request after login: set timestamps
    if (!isset($_SESSION['_sess_started_at'])) {
      $_SESSION['_sess_started_at'] = $now;
    }
    if (!isset($_SESSION['_sess_last_activity'])) {
      $_SESSION['_sess_last_activity'] = $now;
    }

    $startedAt = (int) $_SESSION['_sess_started_at'];
    $last = (int) $_SESSION['_sess_last_activity'];

    // Absolute lifetime check
    if ($absolute > 0 && ($now - $startedAt) > $absolute) {
      $this->expire();
      return Response::redirect('/?expired=1');
    }

    // Idle timeout check
    if ($idle > 0 && ($now - $last) > $idle) {
      $this->expire();
      return Response::redirect('/?idle=1');
    }

    // Update last activity for “real” requests (avoid OPTIONS etc.)
    $method = strtoupper($request['method'] ?? 'GET');
    if (in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
      $_SESSION['_sess_last_activity'] = $now;
    }

    return $next($request);
  }

  private function expire(): void
  {
    // If you later add remember-me, clear it here too (safe even if not set)
    if (isset($_COOKIE['remember_me'])) {
      setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'secure' => false, // set true on HTTPS
        'samesite' => 'Lax',
      ]);
    }

    SessionManager::destroy();
  }
}
