<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Support\Response;
use App\Support\View;
use App\Infrastructure\Security\Csrf;
use App\Infrastructure\Security\RateLimiter;
use App\Infrastructure\Logging\Audit;
use App\Domain\Auth\PasswordReset;

final class ForgotPasswordController
{
  public function show(): array
  {
    return Response::html(View::render('auth/forgot', [
      'csrf' => Csrf::token(),
      'sent' => false,
    ]));
  }

  public function send(array $request): array
  {
    $email = mb_strtolower(trim((string)($request['input']['email'] ?? '')));

    // Throttle: identifier = "reset:{email}"
    $rate = RateLimiter::hit('reset:' . $email, 5, 15);
    if (!$rate['allowed']) {
      return Response::html("Too many requests. Try again later.\n", 429, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    PasswordReset::issue($email);
    Audit::log('password_reset_requested', null, ['email' => $email]);

    // Always show same message
    return Response::html(View::render('auth/forgot', [
      'csrf' => Csrf::token(),
      'sent' => true,
    ]));
  }
}
