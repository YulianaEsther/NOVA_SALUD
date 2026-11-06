<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\CategoriesController;
use App\Controllers\ProductsController;
use App\Core\Router;

return static function (Router $router): void {
    $home = new HomeController();
    $categories = new CategoriesController();
    $products = new ProductsController();
    $router->get('/', [$home, 'index']);
    $router->get('/categorias', [$categories, 'index']);
    $router->get('/categorias/nueva', [$categories, 'createForm']);
    $router->post('/categorias/crear', [$categories, 'store']);
    $router->get('/categorias/editar', [$categories, 'editForm']);
    $router->post('/categorias/actualizar', [$categories, 'update']);
    $router->post('/categorias/desactivar', [$categories, 'deactivate']);
    $router->post('/categorias/activar', [$categories, 'activate']);
    $router->get('/productos', [$products, 'index']);
    $router->get('/productos/nuevo', [$products, 'createForm']);
    $router->post('/productos/crear', [$products, 'store']);
    $router->get('/productos/editar', [$products, 'editForm']);
    $router->post('/productos/actualizar', [$products, 'update']);
    $router->post('/productos/desactivar', [$products, 'deactivate']);
};
