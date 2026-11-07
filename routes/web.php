<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\CategoriesController;
use App\Controllers\AdminResourcesController;
use App\Controllers\AuthController;
use App\Controllers\InitialSetupController;
use App\Controllers\ProductsController;
use App\Core\Router;

return static function (Router $router): void {
    $home = new HomeController();
    $categories = new CategoriesController();
    $products = new ProductsController();
    $adminResources = new AdminResourcesController();
    $auth = new AuthController();
    $initialSetup = new InitialSetupController();
    $router->get('/login', [$auth, 'loginForm']);
    $router->post('/login', [$auth, 'login']);
    $router->post('/logout', [$auth, 'logout']);
    $router->get('/configuracion/inicial', [$initialSetup, 'form']);
    $router->post('/configuracion/inicial', [$initialSetup, 'create']);
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

    foreach (['clientes', 'lotes', 'marcas', 'usuarios', 'roles', 'proveedores'] as $slug) {
        $router->get('/' . $slug, static fn () => $adminResources->index($slug));
        $router->get('/' . $slug . '/nuevo', static fn () => $adminResources->createForm($slug));
        $router->post('/' . $slug . '/crear', static fn () => $adminResources->store($slug));
        $router->get('/' . $slug . '/editar', static fn () => $adminResources->editForm($slug));
        $router->post('/' . $slug . '/actualizar', static fn () => $adminResources->update($slug));
        if ($slug !== 'lotes') {
            $router->post('/' . $slug . '/activar', static fn () => $adminResources->activate($slug));
            $router->post('/' . $slug . '/desactivar', static fn () => $adminResources->deactivate($slug));
        }
    }
    $router->post('/lotes/eliminar', [$adminResources, 'deleteLot']);
};
