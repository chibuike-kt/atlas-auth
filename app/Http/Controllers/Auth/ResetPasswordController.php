<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Support\Response;
use App\Support\View;
use App\Infrastructure\Security\Csrf;
use App\Domain\Auth\PasswordReset;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Security\Password;
use App\Infrastructure\Logging\Audit;

final class ResetPasswordController
{
  public function show(array $request): array
  {
    $token = (string)($request['query']['token'] ?? '');
    return Response::html(View::render('auth/reset', [
      'csrf' => Csrf::token(),
      'token' => $token,
      'error' => null,
    ]));
  }

  public function update(array $request): array
  {
    $token = (string)($request['input']['token'] ?? '');
    $password = (string)($request['input']['password'] ?? '');

    if ($token === '' || mb_strlen($password) < 10) {
      return Response::html(View::render('auth/reset', [
        'csrf' => Csrf::token(),
        'token' => $token,
        'error' => 'Invalid token or weak password.',
      ]), 422);
    }

    $userId = PasswordReset::consume($token);
    if (!$userId) {
      return Response::html(View::render('auth/reset', [
        'csrf' => Csrf::token(),
        'token' => '',
        'error' => 'This reset link is invalid or expired.',
      ]), 400);
    }

    $pdo = Connection::pdo();
    $hash = Password::hash($password);

    $pdo->prepare("UPDATE users SET password_hash = :p WHERE id = :id")
      ->execute([':p' => $hash, ':id' => $userId]);

    Audit::log('password_reset_completed', $userId);

    return Response::redirect('/?reset=1');
  }
}
