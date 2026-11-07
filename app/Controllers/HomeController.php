<?php
declare(strict_types=1);

namespace App\Controllers;

final class HomeController
{
    public function index(): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $title = 'Panel de control';
        $viewPath = dirname(__DIR__) . '/Views/dashboard.php';
        require dirname(__DIR__) . '/Views/layout.php';
    }
}
