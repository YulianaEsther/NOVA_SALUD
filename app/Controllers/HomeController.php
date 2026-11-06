<?php
declare(strict_types=1);

namespace App\Controllers;

final class HomeController
{
    public function index(): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        header('Content-Type: text/html; charset=UTF-8');
        $name = htmlspecialchars($app['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $cssUrl = htmlspecialchars(($app['url'] ?: '') . '/assets/css/app.css', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<link rel="stylesheet" href="' . $cssUrl . '">';
        echo '<title>' . $name . '</title></head><body><main><h1>' . $name . '</h1>';
        echo '<p>Base inicial de la aplicación.</p></main></body></html>';
    }
}
