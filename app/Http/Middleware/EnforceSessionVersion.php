<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Security\Auth;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Session\SessionManager;
use App\Support\Response;

final class EnforceSessionVersion
{
  public function handle(array $request, callable $next): array
  {
    if (!Auth::check()) return $next($request);

    $uid = Auth::id();
    if (!$uid) return $next($request);

    // If missing, treat as invalid (forces re-login once after deploy)
    if (!isset($_SESSION['_sv'])) {
      $this->kill();
      return Response::redirect('/?relogin=1');
    }

    $pdo = Connection::pdo();
    $stmt = $pdo->prepare("SELECT session_version FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $uid]);
    $dbVersion = $stmt->fetchColumn();

    if (!$dbVersion || (int)$dbVersion !== (int)$_SESSION['_sv']) {
      $this->kill();
      return Response::redirect('/?signedout=1');
    }

    return $next($request);
  }

  private function kill(): void
  {
    // Clear remember cookie if present (safe even if you haven't added remember-me yet)
    if (isset($_COOKIE['remember_me'])) {
      setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'secure' => false, // true on HTTPS
        'samesite' => 'Lax',
      ]);
    }

    SessionManager::destroy();
  }
}
