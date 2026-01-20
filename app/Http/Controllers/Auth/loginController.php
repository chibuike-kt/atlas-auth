<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Support\Response;
use App\Support\View;
use App\Infrastructure\Security\Csrf;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Security\Password;
use App\Infrastructure\Security\Auth;
use App\Infrastructure\Security\RateLimiter;
use App\Infrastructure\Logging\Audit;

final class LoginController
{
  public function show(array $request): array
  {
    return Response::html(View::render('auth/login', [
      'csrf' => Csrf::token(),
      'errors' => [],
      'old' => [],
    ]));
  }

  public function store(array $request): array
  {
    $email = mb_strtolower(trim((string)($request['input']['email'] ?? '')));
    $password = (string)($request['input']['password'] ?? '');

    // Rate limit hits regardless of whether account exists (prevents enumeration)
    $rate = RateLimiter::hit($email, 10, 15);
    if (!$rate['allowed']) {
      Audit::log('login_rate_limited', null, ['identifier' => $email]);
      return Response::html("Too many attempts. Try again later.\n", 429, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $pdo = Connection::pdo();
    $stmt = $pdo->prepare("SELECT id, password_hash, email_verified_at, session_version FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // Generic response on failure
    $fail = function () use ($email) {
      Audit::log('login_failed', null, ['identifier' => $email]);
      return Response::html(View::render('auth/login', [
        'csrf' => Csrf::token(),
        'errors' => ['login' => 'Invalid credentials.'],
        'old' => ['email' => $email],
      ]), 422);
    };

    if (!$user) {
      // optional: fake verify delay
      usleep(150000);
      return $fail();
    }

    if (!Password::verify($password, (string)$user['password_hash'])) {
      return $fail();
    }

    // Success: clear limiter and login
    RateLimiter::clear($email);
    Auth::login((int)$user['id']);

    if (!empty($request['input']['remember'])) {
      \App\Infrastructure\Security\Cookies::setRemember(
        \App\Domain\Auth\RememberMe::issue((int)$user['id'])
      );
    }


    // Rehash if needed
    if (Password::needsRehash((string)$user['password_hash'])) {
      $new = Password::hash($password);
      $upd = $pdo->prepare("UPDATE users SET password_hash = :p WHERE id = :id");
      $upd->execute([':p' => $new, ':id' => (int)$user['id']]);
    }

    Audit::log('login_success', (int)$user['id']);

    return Response::redirect('/dashboard');
  }
}
