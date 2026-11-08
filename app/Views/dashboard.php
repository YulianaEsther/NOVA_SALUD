<?php
declare(strict_types=1);
$modules = [
    'productos' => ['Productos', 'Administra el catálogo de productos.'],
    'existencias' => ['Existencias', 'Consulta y ajusta el stock disponible.'],
    'movimientos' => ['Movimientos', 'Revisa el historial de inventario.'],
    'alertas' => ['Alertas', 'Da seguimiento a alertas de inventario.'],
    'auditoria' => ['Auditoría', 'Consulta las acciones registradas.'],
    'ventas' => ['Ventas', 'Gestiona las ventas y sus detalles.'],
    'compras' => ['Compras', 'Registra compras y abastecimiento.'],
    'categorias' => ['Categorías', 'Organiza el catálogo por categorías.'],
    'clientes' => ['Clientes', 'Administra la información de clientes.'],
    'proveedores' => ['Proveedores', 'Mantén tus contactos de suministro.'],
    'marcas' => ['Marcas', 'Administra las marcas de productos.'],
    'lotes' => ['Lotes', 'Controla vencimientos y trazabilidad.'],
    'usuarios' => ['Usuarios', 'Gestiona accesos y cuentas.'],
    'roles' => ['Roles', 'Configura roles y permisos.'],
];
?>
<section class="dashboard">
    <div class="dashboard-welcome">
        <div>
            <p class="dashboard-kicker">RESUMEN GENERAL</p>
            <h2>Todo en orden, <?= e($_SESSION['auth_name'] ?? 'bienvenido') ?>.</h2>
            <p>Accede rápidamente a las herramientas para gestionar tu operación.</p>
        </div>
        <span class="welcome-decoration" aria-hidden="true">
            <svg viewBox="0 0 120 120" fill="none">
                <circle cx="60" cy="60" r="44" stroke="currentColor" stroke-width="2"/>
                <path d="M60 32v56M32 60h56" stroke="currentColor" stroke-width="10" stroke-linecap="round"/>
            </svg>
        </span>
    </div>
    <div class="dashboard-section-heading">
        <div>
            <h2>Tus módulos</h2>
            <p>Selecciona una sección para continuar.</p>
        </div>
        <span class="module-count"><?= count(array_filter($modules, static fn (array $module, string $slug): bool => can($slug . '.ver'), ARRAY_FILTER_USE_BOTH)) ?> disponibles</span>
    </div>
    <div class="dashboard-grid">
        <?php foreach ($modules as $slug => [$label, $description]): ?>
            <?php if (can($slug . '.ver')): ?>
                <a href="<?= e($baseUrl) ?>/<?= e($slug) ?>" class="dashboard-card">
                    <span class="dashboard-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3><?= e($label) ?></h3>
                    <p><?= e($description) ?></p>
                    <span class="dashboard-card-link">Abrir módulo <span aria-hidden="true">→</span></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
