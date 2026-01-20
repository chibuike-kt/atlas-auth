<?php

declare(strict_types=1);

namespace App\Infrastructure\Session;

use App\Support\Config;

final class SessionManager
{
  public static function start(): void
  {
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $name = (string) Config::get('session.name', 'atlas_session');
    $secure = (bool) Config::get('session.secure', false);
    $sameSite = (string) Config::get('session.samesite', 'Lax');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    // Keep session files outside public
    $savePath = dirname(__DIR__, 4) . '/storage/sessions';
    if (!is_dir($savePath)) @mkdir($savePath, 0775, true);
    session_save_path($savePath);

    session_name($name);

    session_set_cookie_params([
      'lifetime' => 0,
      'path' => '/',
      'domain' => '',
      'secure' => $secure,
      'httponly' => true,
      'samesite' => $sameSite,
    ]);

    session_start();

    // Basic anti-fixation: bind to user agent (lightweight)
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $fingerprint = hash('sha256', $ua);

    if (!isset($_SESSION['_fp'])) {
      $_SESSION['_fp'] = $fingerprint;
    } elseif (!hash_equals((string)$_SESSION['_fp'], $fingerprint)) {
      self::destroy();
      session_start();
      $_SESSION['_fp'] = $fingerprint;
    }
  }

  public static function regenerate(): void
  {
    session_regenerate_id(true);
  }

  public static function destroy(): void
  {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
  }
}
