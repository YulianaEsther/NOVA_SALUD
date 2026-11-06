<?php
declare(strict_types=1);
$title = 'Categorías';
?>
<section>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Catálogo</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Categorías</h1>
            <p class="mt-2 text-sm text-slate-600">Organiza los productos por categorías.</p>
        </div>
        <a href="<?= e($baseUrl) ?>/categorias/nueva" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Registrar categoría</a>
    </div>

    <?php if ($success): ?><div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($error) ?></div><?php endif; ?>

    <form method="get" action="<?= e($baseUrl) ?>/categorias" class="mb-5 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:flex-row">
        <label class="sr-only" for="q">Buscar categorías</label>
        <input id="q" name="q" value="<?= e($search) ?>" maxlength="100" placeholder="Buscar por nombre o descripción" class="min-h-11 flex-1 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        <button class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Buscar</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                    <tr>
                        <th scope="col" class="px-4 py-3">Categoría</th>
                        <th scope="col" class="px-4 py-3">Descripción</th>
                        <th scope="col" class="px-4 py-3 text-center">Productos asociados</th>
                        <th scope="col" class="px-4 py-3">Estado</th>
                        <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php if ($categories === []): ?>
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No se encontraron categorías.</td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-900"><?= e($category['nombre']) ?></td>
                            <td class="max-w-sm px-4 py-3 text-slate-600"><?= e($category['descripcion'] ?: '—') ?></td>
                            <td class="px-4 py-3 text-center text-slate-700"><?= e($category['productos_asociados']) ?></td>
                            <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= (int) $category['activo'] === 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' ?>"><?= (int) $category['activo'] === 1 ? 'Activa' : 'Inactiva' ?></span></td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <?php if ((int) $category['activo'] === 1): ?>
                                    <a class="font-semibold text-emerald-800 hover:underline" href="<?= e($baseUrl) ?>/categorias/editar?id=<?= e($category['id']) ?>">Editar</a>
                                    <form class="ml-3 inline" method="post" action="<?= e($baseUrl) ?>/categorias/desactivar" onsubmit="return confirm('¿Desactivar esta categoría? Los productos asociados se conservarán.');">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                        <button class="font-semibold text-red-700 hover:underline">Desactivar</button>
                                    </form>
                                <?php else: ?>
                                    <form class="inline" method="post" action="<?= e($baseUrl) ?>/categorias/activar">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                        <button class="font-semibold text-emerald-800 hover:underline">Activar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
