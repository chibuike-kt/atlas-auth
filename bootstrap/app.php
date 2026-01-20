<?php

declare(strict_types=1);

use App\Support\Env;
use App\Support\Config;
use App\Infrastructure\Session\SessionManager;

require __DIR__ . '/../vendor/autoload.php';

Env::load(dirname(__DIR__));

Config::init([
  'app.env' => Env::get('APP_ENV', 'local'),
  'app.debug' => Env::bool('APP_DEBUG', false),
  'app.url' => Env::get('APP_URL', 'http://localhost:8000'),
  'app.key' => Env::get('APP_KEY', ''),

  'db.driver' => Env::get('DB_CONNECTION', 'mysql'),
  'db.host' => Env::get('DB_HOST', '127.0.0.1'),
  'db.port' => Env::get('DB_PORT', '3306'),
  'db.name' => Env::get('DB_DATABASE', ''),
  'db.user' => Env::get('DB_USERNAME', ''),
  'db.pass' => Env::get('DB_PASSWORD', ''),

  'session.name' => Env::get('SESSION_NAME', 'atlas_session'),
  'session.secure' => Env::bool('SESSION_SECURE_COOKIE', false),
  'session.samesite' => Env::get('SESSION_SAMESITE', 'Lax'),

  'csrf.ttl' => (int) Env::get('CSRF_TTL_SECONDS', '7200'),
]);

SessionManager::start();
