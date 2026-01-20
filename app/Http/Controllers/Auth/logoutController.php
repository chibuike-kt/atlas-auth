<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Support\Response;
use App\Infrastructure\Security\Auth;
use App\Infrastructure\Logging\Audit;
use App\Infrastructure\Session\SessionManager;
use App\Domain\Auth\RememberMe;
use App\Infrastructure\Security\Cookies;

RememberMe::deleteAllForUser($uid);
Cookies::clearRemember();


final class LogoutController
{
  public function __invoke(): array
  {
    $uid = Auth::id();
    if ($uid) Audit::log('logout', $uid);

    Auth::logout();
    SessionManager::destroy();

    return Response::redirect('/');
  }
}
