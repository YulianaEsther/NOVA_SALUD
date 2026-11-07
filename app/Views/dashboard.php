<?php
declare(strict_types=1);
$modules = [
    'productos' => 'Productos', 'categorias' => 'Categorías', 'clientes' => 'Clientes',
    'proveedores' => 'Proveedores', 'marcas' => 'Marcas', 'lotes' => 'Lotes',
    'usuarios' => 'Usuarios', 'roles' => 'Roles',
];
?>
<section>
    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">NOVA SALUD</p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Panel de control</h1>
    <p class="mt-2 text-slate-600">Bienvenido, <?= e($_SESSION['auth_name'] ?? '') ?>. Selecciona un módulo para continuar.</p>
    <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($modules as $slug => $label): ?>
            <?php if (can($slug . '.ver')): ?>
                <a href="<?= e($baseUrl) ?>/<?= e($slug) ?>" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow">
                    <h2 class="font-semibold text-slate-900"><?= e($label) ?></h2>
                    <p class="mt-2 text-sm text-slate-600">Abrir módulo</p>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>
