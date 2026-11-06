<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\ProductsController;
use App\Core\Router;

return static function (Router $router): void {
    $home = new HomeController();
    $products = new ProductsController();
    $router->get('/', [$home, 'index']);
    $router->get('/productos', [$products, 'index']);
    $router->get('/productos/nuevo', [$products, 'createForm']);
    $router->post('/productos/crear', [$products, 'store']);
    $router->get('/productos/editar', [$products, 'editForm']);
    $router->post('/productos/actualizar', [$products, 'update']);
    $router->post('/productos/desactivar', [$products, 'deactivate']);
};
