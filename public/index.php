<?php

declare(strict_types=1);

use App\Support\Router;
use App\Infrastructure\Security\Headers;
use App\Http\Middleware\CsrfMiddleware;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Middleware\Authenticate;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\VerifiedEmail;


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

// Controllers
$register = new RegisterController();
$login = new LoginController();
$logout = new LogoutController();
$verify = new VerifyEmailController();


// Routes
$router->get('/', fn($req) => $login->show($req));
$router->post('/login', fn($req) => $login->store($req));
$router->get('/verify-notice', fn() => $verify->notice());
$router->post('/resend-verification', fn($req) => $verify->resend($req));
$router->get('/verify-email', fn($req) => $verify->verify($req));


$router->get('/register', fn() => $register->show());
$router->post('/register', fn($req) => $register->store($req));

$router->post('/logout', fn() => $logout());

// Protected route
$authMw = new Authenticate();
$router->get('/dashboard', function ($req) use ($authMw) {
  return $authMw->handle($req, function () {
    return \App\Support\Response::html(\App\Support\View::render('dashboard', [
      'csrf' => \App\Infrastructure\Security\Csrf::token(),
    ]));
  });
});

// Middleware pipeline (CSRF already protects POSTs)
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
