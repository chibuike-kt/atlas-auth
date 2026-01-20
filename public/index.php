<?php

declare(strict_types=1);

use App\Support\Router;
use App\Support\Response;
use App\Infrastructure\Security\Headers;
use App\Http\Middleware\CsrfMiddleware;
use App\Infrastructure\Security\Csrf;
use App\Support\View;

require __DIR__ . '/../bootstrap/app.php';

Headers::apply();

$request = [
  'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
  'path' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
  'query' => $_GET ?? [],
  'input' => $_POST ?? [],
  'headers' => array_change_key_case(getallheaders() ?: [], CASE_LOWER),
];

$router = new Router();

// Routes (placeholders for now)
$router->get('/', fn() => Response::html(View::render('auth/login', ['csrf' => Csrf::token()])));
$router->post('/login', fn() => Response::html("Login handler coming next.\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']));
$router->get('/register', fn() => Response::html(View::render('auth/register', ['csrf' => Csrf::token()])));
$router->post('/register', fn() => Response::html("Register handler coming next.\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']));
$router->post('/logout', fn() => Response::html("Logout handler coming next.\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']));

// Middleware pipeline
$middleware = [
  new CsrfMiddleware(),
];

$kernel = array_reduce(
  array_reverse($middleware),
  fn($next, $mw) => fn($req) => $mw->handle($req, $next),
  fn($req) => $router->dispatch($req)
);

$response = $kernel($request);

http_response_code((int)($response['status'] ?? 200));
foreach (($response['headers'] ?? []) as $k => $v) {
  header($k . ': ' . $v);
}
echo (string)($response['body'] ?? '');
