<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Core\Router;

return static function (Router $router): void {
    $home = new HomeController();
    $router->get('/', [$home, 'index']);
};
