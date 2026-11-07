<?php
declare(strict_types=1);

use App\Core\Router;
use App\Middleware\AuthMiddleware;

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Helpers/env.php';
require BASE_PATH . '/app/Helpers/view.php';
load_environment(BASE_PATH . '/.env');

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

$app = require BASE_PATH . '/config/app.php';
date_default_timezone_set($app['timezone']);

if ($app['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

set_exception_handler(static function (Throwable $exception) use ($app): void {
    error_log(sprintf("[%s] %s in %s:%d", date(DATE_ATOM), $exception->getMessage(), $exception->getFile(), $exception->getLine()));
    http_response_code(500);
    if ($app['debug']) {
        echo '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
        return;
    }
    echo 'Ocurrió un error interno. Intenta nuevamente más tarde.';
});

$router = new Router();
$registerRoutes = require BASE_PATH . '/routes/web.php';
$registerRoutes($router);
$auth = new AuthMiddleware();
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (!$auth->handle($requestMethod, $requestUri, $router->hasRoute($requestMethod, $requestUri))) {
    exit;
}
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
