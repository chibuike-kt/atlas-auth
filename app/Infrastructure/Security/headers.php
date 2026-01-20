<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

final class Headers
{
  public static function apply(): void
  {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    // Start with a sane CSP; we’ll tighten as UI grows.
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");

    // Only enable HSTS when you are on HTTPS in production.
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
  }
}
