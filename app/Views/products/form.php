<?php
declare(strict_types=1);
$editing = !empty($product['id']);
$title = $editing ? 'Editar producto' : 'Registrar producto';
$action = $editing ? '/productos/actualizar' : '/productos/crear';
$fieldError = static fn (string $name): string => isset($errors[$name]) ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-emerald-600 focus:ring-emerald-100';
?>
<section class="mx-auto max-w-3xl">
    <a href="<?= e($baseUrl) ?>/productos" class="text-sm font-semibold text-emerald-800 hover:underline">← Volver a productos</a>
    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        <h1 class="text-2xl font-bold text-slate-900"><?= e($title) ?></h1>
        <p class="mt-2 text-sm text-slate-600">Completa los datos del catálogo. Las existencias se registran mediante compras y movimientos de inventario.</p>

        <?php if ($error): ?>
            <div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($categories === []): ?>
            <div role="alert" class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">No hay categorías activas. Registra una categoría en la tabla <code>categorias</code> antes de crear productos.</div>
        <?php endif; ?>

        <form method="post" action="<?= e($baseUrl . $action) ?>" class="mt-6 space-y-5">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($product['id']) ?>"><?php endif; ?>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="codigo" class="mb-1.5 block text-sm font-semibold text-slate-700">Código <span class="text-red-600">*</span></label>
                    <input id="codigo" name="codigo" required maxlength="80" value="<?= e($product['codigo']) ?>" class="min-h-11 w-full rounded-lg border px-3 text-sm <?= $fieldError('codigo') ?> focus:outline-none focus:ring-2">
                    <?php if (isset($errors['codigo'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['codigo']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="nombre" class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre <span class="text-red-600">*</span></label>
                    <input id="nombre" name="nombre" required maxlength="180" value="<?= e($product['nombre']) ?>" class="min-h-11 w-full rounded-lg border px-3 text-sm <?= $fieldError('nombre') ?> focus:outline-none focus:ring-2">
                    <?php if (isset($errors['nombre'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['nombre']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="categoria_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Categoría <span class="text-red-600">*</span></label>
                    <select id="categoria_id" name="categoria_id" required class="min-h-11 w-full rounded-lg border bg-white px-3 text-sm <?= $fieldError('categoria_id') ?> focus:outline-none focus:ring-2">
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['id']) ?>" <?= (string) $product['categoria_id'] === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['categoria_id'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['categoria_id']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="marca_id" class="mb-1.5 block text-sm font-semibold text-slate-700">Marca <span class="font-normal text-slate-500">(opcional)</span></label>
                    <select id="marca_id" name="marca_id" class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Sin marca</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?= e($brand['id']) ?>" <?= (string) $product['marca_id'] === (string) $brand['id'] ? 'selected' : '' ?>><?= e($brand['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['marca_id'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['marca_id']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="unidad_medida" class="mb-1.5 block text-sm font-semibold text-slate-700">Unidad de medida <span class="text-red-600">*</span></label>
                    <input id="unidad_medida" name="unidad_medida" required maxlength="40" value="<?= e($product['unidad_medida']) ?>" class="min-h-11 w-full rounded-lg border px-3 text-sm <?= $fieldError('unidad_medida') ?> focus:outline-none focus:ring-2">
                    <?php if (isset($errors['unidad_medida'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['unidad_medida']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="precio_venta" class="mb-1.5 block text-sm font-semibold text-slate-700">Precio de venta (S/) <span class="text-red-600">*</span></label>
                    <input id="precio_venta" name="precio_venta" type="number" required min="0" step="0.01" value="<?= e($product['precio_venta']) ?>" class="min-h-11 w-full rounded-lg border px-3 text-sm <?= $fieldError('precio_venta') ?> focus:outline-none focus:ring-2">
                    <?php if (isset($errors['precio_venta'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['precio_venta']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label for="stock_minimo" class="mb-1.5 block text-sm font-semibold text-slate-700">Stock mínimo</label>
                    <input id="stock_minimo" name="stock_minimo" type="number" required min="0" step="0.001" value="<?= e($product['stock_minimo']) ?>" class="min-h-11 w-full rounded-lg border px-3 text-sm <?= $fieldError('stock_minimo') ?> focus:outline-none focus:ring-2">
                    <?php if (isset($errors['stock_minimo'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['stock_minimo']) ?></p><?php endif; ?>
                </div>
                <div class="flex items-center pt-7">
                    <input id="requiere_lote" name="requiere_lote" type="checkbox" value="1" <?= (string) $product['requiere_lote'] === '1' ? 'checked' : '' ?> class="size-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                    <label for="requiere_lote" class="ml-2 text-sm font-medium text-slate-700">Controlar por lote y vencimiento</label>
                </div>
            </div>
            <div>
                <label for="descripcion" class="mb-1.5 block text-sm font-semibold text-slate-700">Descripción <span class="font-normal text-slate-500">(opcional)</span></label>
                <textarea id="descripcion" name="descripcion" rows="4" maxlength="5000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100"><?= e($product['descripcion']) ?></textarea>
                <?php if (isset($errors['descripcion'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['descripcion']) ?></p><?php endif; ?>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="<?= e($baseUrl) ?>/productos" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button <?= $categories === [] ? 'disabled' : '' ?> class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50">Guardar producto</button>
            </div>
        </form>
    </div>
</section>
