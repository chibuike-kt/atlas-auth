<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Security\Auth;
use App\Infrastructure\Database\Connection;
use App\Support\Response;

final class VerifiedEmail
{
  public function handle(array $request, callable $next): array
  {
    $uid = Auth::id();
    if (!$uid) return Response::redirect('/');

    $pdo = Connection::pdo();
    $stmt = $pdo->prepare("SELECT email_verified_at FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $uid]);
    $verifiedAt = $stmt->fetchColumn();

    if (!$verifiedAt) {
      return Response::redirect('/verify-notice');
    }

    return $next($request);
  }
}
