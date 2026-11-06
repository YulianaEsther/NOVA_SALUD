<?php
declare(strict_types=1);
$title = 'Productos';
?>
<section>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Inventario</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Productos</h1>
            <p class="mt-2 text-sm text-slate-600">Administra el catálogo de productos de NOVA SALUD.</p>
        </div>
        <a href="<?= e($baseUrl) ?>/productos/nuevo" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Registrar producto</a>
    </div>

    <?php if ($success): ?>
        <div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="get" action="<?= e($baseUrl) ?>/productos" class="mb-5 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_14rem_auto]">
        <label class="sr-only" for="q">Buscar por código o nombre</label>
        <input id="q" name="q" value="<?= e($search) ?>" maxlength="100" placeholder="Buscar por código o nombre" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        <label class="sr-only" for="categoria">Filtrar por categoría</label>
        <select id="categoria" name="categoria" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <option value="">Todas las categorías</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['id']) ?>" <?= (int) $selectedCategory === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Buscar</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                    <tr>
                        <th scope="col" class="px-4 py-3">Código / producto</th>
                        <th scope="col" class="px-4 py-3">Categoría</th>
                        <th scope="col" class="px-4 py-3">Marca</th>
                        <th scope="col" class="px-4 py-3 text-right">Precio</th>
                        <th scope="col" class="px-4 py-3 text-right">Stock</th>
                        <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php if ($products === []): ?>
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No se encontraron productos.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php $lowStock = (float) $product['stock_actual'] <= (float) $product['stock_minimo']; ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3"><div class="font-semibold text-slate-900"><?= e($product['nombre']) ?></div><div class="mt-1 text-xs text-slate-500"><?= e($product['codigo']) ?> · <?= e($product['unidad_medida']) ?></div></td>
                            <td class="px-4 py-3 text-slate-700"><?= e($product['categoria']) ?></td>
                            <td class="px-4 py-3 text-slate-700"><?= e($product['marca'] ?: '—') ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">S/ <?= e(number_format((float) $product['precio_venta'], 2)) ?></td>
                            <td class="whitespace-nowrap px-4 py-3 text-right"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $lowStock ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>"><?= e(number_format((float) $product['stock_actual'], 3)) ?> <?= e($product['unidad_medida']) ?></span></td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a class="font-semibold text-emerald-800 hover:underline" href="<?= e($baseUrl) ?>/productos/editar?id=<?= e($product['id']) ?>">Editar</a>
                                <form class="ml-3 inline" method="post" action="<?= e($baseUrl) ?>/productos/desactivar" onsubmit="return confirm('¿Desactivar este producto? Se conservará su historial.');">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= e($product['id']) ?>">
                                    <button class="font-semibold text-red-700 hover:underline">Desactivar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
