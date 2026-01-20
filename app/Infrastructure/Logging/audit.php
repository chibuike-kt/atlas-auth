<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use App\Infrastructure\Database\Connection;
use App\Infrastructure\Security\Ip;

final class Audit
{
  public static function log(string $event, ?int $userId = null, array $meta = []): void
  {
    $pdo = Connection::pdo();
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    // Store as JSON string if your column is LONGTEXT; if you kept JSON type, this still works.
    $metaJson = $meta ? json_encode($meta, JSON_UNESCAPED_SLASHES) : null;

    $stmt = $pdo->prepare("
      INSERT INTO audit_logs (user_id, event, ip, user_agent, meta)
      VALUES (:uid, :event, :ip, :ua, :meta)
    ");
    $stmt->execute([
      ':uid' => $userId,
      ':event' => $event,
      ':ip' => Ip::packed(),
      ':ua' => $ua,
      ':meta' => $metaJson,
    ]);
  }
}
