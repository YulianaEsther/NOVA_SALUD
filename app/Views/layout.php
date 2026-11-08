<?php
declare(strict_types=1);
$baseUrl = rtrim((string) ($app['url'] ?? ''), '/');
$cssUrl = $baseUrl . '/assets/css/app.css';
$navigation = [
    'Inventario' => [
        'productos' => 'Productos',
        'existencias' => 'Existencias',
        'movimientos' => 'Movimientos',
        'alertas' => 'Alertas',
        'lotes' => 'Lotes',
    ],
    'Operaciones' => [
        'ventas' => 'Ventas',
        'compras' => 'Compras',
        'clientes' => 'Clientes',
        'proveedores' => 'Proveedores',
    ],
    'Catálogos' => [
        'categorias' => 'Categorías',
        'marcas' => 'Marcas',
    ],
    'Administración' => [
        'usuarios' => 'Usuarios',
        'roles' => 'Roles',
        'auditoria' => 'Auditoría',
    ],
];
$moduleLabels = [
    'productos' => 'Productos',
    'existencias' => 'Existencias',
    'movimientos' => 'Movimientos',
    'alertas' => 'Alertas',
    'lotes' => 'Lotes',
    'ventas' => 'Ventas',
    'compras' => 'Compras',
    'clientes' => 'Clientes',
    'proveedores' => 'Proveedores',
    'categorias' => 'Categorías',
    'marcas' => 'Marcas',
    'usuarios' => 'Usuarios',
    'roles' => 'Roles',
    'auditoria' => 'Auditoría',
];
$moduleIcons = [
    'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    'productos' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4.5 7.8 7.5 4.3 7.5-4.3M12 12v9"/>',
    'existencias' => '<path d="M4 19V9m5 10V5m6 14v-7m5 7V8"/><path d="M2 19h20"/>',
    'movimientos' => '<path d="M4 7h15l-3-3m4 13H5l3 3"/><path d="M19 7a7 7 0 0 1 1 9M5 17a7 7 0 0 1-1-9"/>',
    'alertas' => '<path d="M10.3 4.3 2.8 17.2A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.8L13.7 4.3a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 3h.01"/>',
    'lotes' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4.5 7.8 7.5 4.3 7.5-4.3M12 12v9m-3-14 8 4.5"/>',
    'ventas' => '<path d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
    'compras' => '<path d="M4 4h16v16H4z"/><path d="M8 8h8m-8 4h8m-8 4h5"/>',
    'clientes' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.9m-1-11.9a4 4 0 0 1 0 7.8"/>',
    'proveedores' => '<path d="M3 7h11v11H3z"/><path d="M14 11h4l3 3v4h-7"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="17.5" cy="18.5" r="1.5"/>',
    'categorias' => '<path d="M3 5.5A2.5 2.5 0 0 1 5.5 3H11l10 10-8 8L3 11V5.5Z"/><circle cx="7.5" cy="7.5" r="1"/>',
    'marcas' => '<path d="M12 3 14.8 9l6.2.8-4.5 4.3 1.1 6.2L12 17.4l-5.6 2.9 1.1-6.2L3 9.8 9.2 9 12 3Z"/>',
    'usuarios' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 8v6m-3-3h6"/>',
    'roles' => '<path d="M12 3 20 6v5c0 5-3.4 8.3-8 10-4.6-1.7-8-5-8-10V6l8-3Z"/><path d="m9 12 2 2 4-4"/>',
    'auditoria' => '<path d="M3 3v5h5M4 8a8 8 0 1 1-1 6"/><path d="M12 7v5l3 2"/>',
];
$renderModuleIcon = static function (string $slug) use ($moduleIcons): void {
    ?>
    <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <?= $moduleIcons[$slug] ?? $moduleIcons['dashboard'] ?>
    </svg>
    <?php
};
$requestPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$basePath = (string) (parse_url($baseUrl, PHP_URL_PATH) ?: '');
if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath));
}
$pathParts = explode('/', trim($requestPath, '/'));
$activeModule = $pathParts[0] ?? '';
$moduleTitle = $activeModule === ''
    ? 'Panel de control'
    : ($moduleLabels[$activeModule] ?? (string) ($title ?? 'Panel de control'));
$renderNavigation = static function () use ($navigation, $baseUrl, $activeModule, $renderModuleIcon): void {
    foreach ($navigation as $group => $modules) {
        $visibleModules = array_filter($modules, static fn (string $slug): bool => can($slug . '.ver'), ARRAY_FILTER_USE_KEY);
        if ($visibleModules === []) {
            continue;
        }
        ?>
        <div class="sidebar-group">
            <p class="sidebar-group-title"><?= e($group) ?></p>
            <?php foreach ($visibleModules as $slug => $label): ?>
                <a class="sidebar-link<?= $activeModule === $slug ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>/<?= e($slug) ?>"<?= $activeModule === $slug ? ' aria-current="page"' : '' ?>>
                    <?php $renderModuleIcon($slug); ?>
                    <span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
    }
};
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($moduleTitle) ?> | <?= e($app['name'] ?? 'NOVA SALUD') ?></title>
    <link rel="stylesheet" href="<?= e($cssUrl) ?>">
</head>
<body class="app-shell antialiased">
    <aside class="app-sidebar" aria-label="Menú principal">
        <a class="brand-lockup" href="<?= e($baseUrl) ?>/">
            <span class="brand-symbol" aria-hidden="true">
                <svg viewBox="0 0 32 32" fill="none">
                    <path d="M16 5v22M5 16h22" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="16" cy="16" r="13" stroke="currentColor" stroke-width="2"/>
                </svg>
            </span>
            <span class="brand-copy">
                <strong><?= e($app['name'] ?? 'NOVA SALUD') ?></strong>
                <small>Gestión de salud</small>
            </span>
        </a>
        <a class="sidebar-home<?= $activeModule === '' ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>/">
            <?php $renderModuleIcon('dashboard'); ?>
            <span>Panel de control</span>
        </a>
        <nav class="sidebar-navigation" aria-label="Módulos">
            <?php $renderNavigation(); ?>
        </nav>
        <div class="sidebar-profile">
            <span class="profile-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string) ($_SESSION['auth_name'] ?? 'U'), 0, 1))) ?></span>
            <span class="profile-copy">
                <strong><?= e($_SESSION['auth_name'] ?? 'Usuario') ?></strong>
                <small>Sesión activa</small>
            </span>
            <form method="post" action="<?= e($baseUrl) ?>/logout">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <button class="logout-button" type="submit" aria-label="Cerrar sesión" title="Cerrar sesión">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M10 17l5-5-5-5M15 12H3m9-8h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <header class="app-header">
        <div class="header-content">
            <div class="header-title">
                <span class="header-eyebrow">NOVA SALUD / <?= e($moduleTitle) ?></span>
                <h1><?= e($moduleTitle) ?></h1>
            </div>
            <details class="mobile-menu">
                <summary aria-label="Abrir menú de navegación">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Menú</span>
                </summary>
                <nav class="mobile-menu-panel" aria-label="Módulos">
                    <a class="sidebar-link<?= $activeModule === '' ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>/">
                        <?php $renderModuleIcon('dashboard'); ?>
                        <span>Panel de control</span>
                    </a>
                    <?php $renderNavigation(); ?>
                    <form method="post" action="<?= e($baseUrl) ?>/logout">
                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                        <button class="mobile-logout" type="submit">Cerrar sesión</button>
                    </form>
                </nav>
            </details>
            <div class="header-user">
                <span class="header-user-dot" aria-hidden="true"></span>
                <span><?= e($_SESSION['auth_name'] ?? 'Usuario') ?></span>
            </div>
        </div>
    </header>

    <main class="app-main">
        <div class="app-content">
            <?php require $viewPath; ?>
        </div>
    </main>
</body>
</html>
