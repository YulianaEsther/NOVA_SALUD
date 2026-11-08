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
$renderNavigation = static function () use ($navigation, $baseUrl, $activeModule): void {
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
                    <span class="sidebar-link-mark" aria-hidden="true"></span>
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
            <span class="sidebar-link-mark" aria-hidden="true"></span>
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
                    <a class="sidebar-link<?= $activeModule === '' ? ' is-active' : '' ?>" href="<?= e($baseUrl) ?>/">Panel de control</a>
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
