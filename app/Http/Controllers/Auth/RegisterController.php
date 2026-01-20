<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Support\Response;
use App\Support\View;
use App\Infrastructure\Security\Csrf;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Security\Password;
use App\Infrastructure\Logging\Audit;
use App\Domain\Auth\EmailVerification;
use App\Infrastructure\Security\Auth;


final class RegisterController
{
  public function show(): array
  {
    return Response::html(View::render('auth/register', [
      'csrf' => Csrf::token(),
      'errors' => [],
      'old' => [],
    ]));
  }

  public function store(array $request): array
  {
    $name = trim((string)($request['input']['name'] ?? ''));
    $email = mb_strtolower(trim((string)($request['input']['email'] ?? '')));
    $password = (string)($request['input']['password'] ?? '');

    $errors = [];
    if ($name === '' || mb_strlen($name) > 120) $errors['name'] = 'Enter a valid name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors['email'] = 'Enter a valid email.';
    if (mb_strlen($password) < 10) $errors['password'] = 'Password must be at least 10 characters.';

    if ($errors) {
      return Response::html(View::render('auth/register', [
        'csrf' => Csrf::token(),
        'errors' => $errors,
        'old' => ['name' => $name, 'email' => $email],
      ]), 422);
    }

    $pdo = Connection::pdo();

    // Prevent duplicates
    $check = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);
    if ($check->fetchColumn()) {
      return Response::html(View::render('auth/register', [
        'csrf' => Csrf::token(),
        'errors' => ['email' => 'That email is already in use.'],
        'old' => ['name' => $name, 'email' => $email],
      ]), 422);
    }

    $hash = Password::hash($password);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (:n, :e, :p)");
    $stmt->execute([':n' => $name, ':e' => $email, ':p' => $hash]);

    $userId = (int)$pdo->lastInsertId();

    EmailVerification::issueForUser($userId, $email);

    // optional: auto-login then send to verify notice
    Auth::login($userId);
    return Response::redirect('/verify-notice');
  }
}
