<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Support\Response;
use App\Support\View;
use App\Infrastructure\Security\Csrf;
use App\Infrastructure\Security\Tokens;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Security\Auth;
use App\Domain\Auth\EmailVerification;
use App\Infrastructure\Logging\Audit;
use App\Infrastructure\Security\RateLimiter;

final class VerifyEmailController
{
  public function notice(): array
  {
    return Response::html(View::render('auth/verify_notice', [
      'csrf' => Csrf::token(),
    ]));
  }

  public function verify(array $request): array
  {
    $token = (string)($request['query']['token'] ?? '');
    if ($token === '') {
      return Response::html("Invalid verification link.\n", 400, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $tokenHash = Tokens::hashToken($token);
    $pdo = Connection::pdo();

    $stmt = $pdo->prepare("
      SELECT ev.id AS ev_id, ev.user_id, ev.expires_at, ev.used_at, u.email_verified_at
      FROM email_verifications ev
      JOIN users u ON u.id = ev.user_id
      WHERE ev.token_hash = :th
      LIMIT 1
    ");
    $stmt->execute([':th' => $tokenHash]);
    $row = $stmt->fetch();

    if (!$row) {
      return Response::html("Invalid verification link.\n", 400, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    if ($row['used_at'] !== null) {
      return Response::html("This verification link was already used.\n", 400, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $expires = new \DateTimeImmutable($row['expires_at']);
    if ($expires < new \DateTimeImmutable('now')) {
      return Response::html("This verification link has expired.\n", 400, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    $uid = (int)$row['user_id'];

    // Mark used + set verified (idempotent)
    $pdo->prepare("UPDATE email_verifications SET used_at = NOW() WHERE id = :id")->execute([':id' => (int)$row['ev_id']]);
    $pdo->prepare("UPDATE users SET email_verified_at = COALESCE(email_verified_at, NOW()) WHERE id = :id")->execute([':id' => $uid]);

    Audit::log('email_verified', $uid);

    // If user is logged in, keep them; otherwise send to login
    return Response::redirect(Auth::check() ? '/dashboard' : '/?verified=1');
  }

  public function resend(array $request): array
  {
    if (!Auth::check()) return Response::redirect('/');

    $uid = Auth::id();
    $pdo = Connection::pdo();

    $stmt = $pdo->prepare("SELECT email, email_verified_at FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $uid]);
    $user = $stmt->fetch();

    if (!$user) return Response::redirect('/');

    if (!empty($user['email_verified_at'])) {
      return Response::redirect('/dashboard');
    }

    // reuse RateLimiter table: identifier = "verify:{userId}"
    $rate = RateLimiter::hit('verify:' . $uid, 3, 15);
    if (!$rate['allowed']) {
      return Response::html("Too many requests. Try again later.\n", 429, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    EmailVerification::issueForUser((int)$uid, (string)$user['email']);
    Audit::log('verification_resent', (int)$uid);

    return Response::redirect('/verify-notice?sent=1');
  }
}
