<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Infrastructure\Security\Auth;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Session\SessionManager;
use App\Support\Response;
use App\Infrastructure\Logging\Audit;

final class LogoutEverywhereController
{
  public function __invoke(): array
  {
    $uid = Auth::id();
    if (!$uid) return Response::redirect('/');

    $pdo = Connection::pdo();

    // Increment version → invalidates all sessions everywhere instantly
    $pdo->prepare("UPDATE users SET session_version = session_version + 1 WHERE id = :id")
      ->execute([':id' => $uid]);

    Audit::log('logout_everywhere', $uid);

    // If you later add remember-me tokens, also delete them here:
    // \App\Domain\Auth\RememberMe::deleteAllForUser($uid);

    // Clear remember cookie if set
    if (isset($_COOKIE['remember_me'])) {
      setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'secure' => false,
        'samesite' => 'Lax',
      ]);
    }

    SessionManager::destroy();
    return Response::redirect('/?signedout=1');
  }
}
