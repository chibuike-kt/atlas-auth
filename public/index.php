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
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Middleware\SessionTimeout;
use App\Http\Middleware\EnforceSessionVersion;
use App\Http\Controllers\Auth\LogoutEverywhereController;





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
$forgot = new ForgotPasswordController();
$reset = new ResetPasswordController();
$logoutEverywhere = new LogoutEverywhereController();




// Routes
$router->get('/', fn($req) => $login->show($req));
$router->post('/login', fn($req) => $login->store($req));
$router->get('/verify-notice', fn() => $verify->notice());
$router->post('/resend-verification', fn($req) => $verify->resend($req));
$router->get('/verify-email', fn($req) => $verify->verify($req));
$router->get('/forgot-password', fn() => $forgot->show());
$router->post('/forgot-password', fn($req) => $forgot->send($req));

$router->get('/reset-password', fn($req) => $reset->show($req));
$router->post('/reset-password', fn($req) => $reset->update($req));
$router->post('/logout-everywhere', fn() => $logoutEverywhere());




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
  new \App\Http\Middleware\SessionTimeout(),      // if you have it
  new EnforceSessionVersion(),
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
