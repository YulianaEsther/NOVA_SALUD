<?php
declare(strict_types=1);
$baseUrl = rtrim((string) ($app['url'] ?? ''), '/');
$cssUrl = $baseUrl . '/assets/css/app.css';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Productos') ?> | <?= e($app['name'] ?? 'NOVA SALUD') ?></title>
    <link rel="stylesheet" href="<?= e($cssUrl) ?>">
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a class="text-lg font-bold text-emerald-800" href="<?= e($baseUrl) ?>/">
                <?= e($app['name'] ?? 'NOVA SALUD') ?>
            </a>
            <nav aria-label="Navegación principal">
                <?php foreach (['productos' => 'Productos', 'existencias' => 'Existencias', 'movimientos' => 'Movimientos', 'alertas' => 'Alertas', 'auditoria' => 'Auditoría', 'ventas' => 'Ventas', 'compras' => 'Compras', 'categorias' => 'Categorías', 'clientes' => 'Clientes', 'proveedores' => 'Proveedores', 'marcas' => 'Marcas', 'lotes' => 'Lotes', 'usuarios' => 'Usuarios', 'roles' => 'Roles'] as $module => $label): ?>
                    <?php if (can($module . '.ver')): ?><a class="rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" href="<?= e($baseUrl) ?>/<?= e($module) ?>"><?= e($label) ?></a><?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-slate-600 sm:inline"><?= e($_SESSION['auth_name'] ?? '') ?></span>
                <form method="post" action="<?= e($baseUrl) ?>/logout">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <?php require $viewPath; ?>
    </main>
</body>
</html>
