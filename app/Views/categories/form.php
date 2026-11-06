<?php
declare(strict_types=1);
$editing = !empty($category['id']);
$title = $editing ? 'Editar categoría' : 'Registrar categoría';
$action = $editing ? '/categorias/actualizar' : '/categorias/crear';
?>
<section class="mx-auto max-w-2xl">
    <a href="<?= e($baseUrl) ?>/categorias" class="text-sm font-semibold text-emerald-800 hover:underline">← Volver a categorías</a>
    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        <h1 class="text-2xl font-bold text-slate-900"><?= e($title) ?></h1>
        <p class="mt-2 text-sm text-slate-600">Completa la información de la categoría.</p>
        <?php if ($error): ?><div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($error) ?></div><?php endif; ?>

        <form method="post" action="<?= e($baseUrl . $action) ?>" class="mt-6 space-y-5">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($category['id']) ?>"><?php endif; ?>
            <div>
                <label for="nombre" class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre <span class="text-red-600">*</span></label>
                <input id="nombre" name="nombre" required maxlength="100" value="<?= e($category['nombre']) ?>" class="min-h-11 w-full rounded-lg border px-3 text-sm focus:outline-none focus:ring-2 <?= isset($errors['nombre']) ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-emerald-600 focus:ring-emerald-100' ?>">
                <?php if (isset($errors['nombre'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['nombre']) ?></p><?php endif; ?>
            </div>
            <div>
                <label for="descripcion" class="mb-1.5 block text-sm font-semibold text-slate-700">Descripción <span class="font-normal text-slate-500">(opcional)</span></label>
                <textarea id="descripcion" name="descripcion" rows="4" maxlength="255" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 <?= isset($errors['descripcion']) ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-emerald-600 focus:ring-emerald-100' ?>"><?= e($category['descripcion']) ?></textarea>
                <?php if (isset($errors['descripcion'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['descripcion']) ?></p><?php endif; ?>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="<?= e($baseUrl) ?>/categorias" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Guardar categoría</button>
            </div>
        </form>
    </div>
</section>
