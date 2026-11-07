<?php
declare(strict_types=1);
$title = ($editing ? 'Editar ' : 'Registrar ') . mb_strtolower($definition['title']);
?>
<section class="mx-auto max-w-3xl">
    <a href="<?= e($baseUrl) ?>/<?= e($slug) ?>" class="text-sm font-semibold text-emerald-800 hover:underline">← Volver a <?= e(mb_strtolower($definition['title'])) ?></a>
    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
        <h1 class="text-2xl font-bold text-slate-900"><?= e($title) ?></h1>
        <?php if ($slug === 'lotes'): ?><p class="mt-2 text-sm text-slate-600">El lote inicia con saldo cero. Registra las existencias desde una compra para conservar su trazabilidad.</p><?php endif; ?>
        <?php if ($error): ?><div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($error) ?></div><?php endif; ?>
        <?php if (isset($errors['_form'])): ?><div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($errors['_form']) ?></div><?php endif; ?>

        <form method="post" action="<?= e($baseUrl) ?>/<?= e($slug) ?>/<?= $editing ? 'actualizar' : 'crear' ?>" class="mt-6 space-y-5">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($values['id']) ?>"><?php endif; ?>
            <div class="grid gap-5 sm:grid-cols-2">
                <?php foreach ($definition['fields'] as $field): ?>
                    <?php
                    $key = $field['key'];
                    $value = $values[$key] ?? '';
                    $fieldError = isset($errors[$key]);
                    $disabled = $editing && ($field['editable_on_update'] ?? true) === false;
                    $borderClass = $fieldError ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-emerald-600 focus:ring-emerald-100';
                    ?>
                    <div class="<?= in_array($field['type'], ['textarea'], true) ? 'sm:col-span-2' : '' ?>">
                        <label for="<?= e($key) ?>" class="mb-1.5 block text-sm font-semibold text-slate-700"><?= e($field['label']) ?> <?= ($field['required'] && !($editing && $field['type'] === 'password')) ? '<span class="text-red-600">*</span>' : '' ?></label>
                        <?php if ($field['type'] === 'select'): ?>
                            <select id="<?= e($key) ?>" name="<?= e($key) ?>" <?= $field['required'] && !$disabled ? 'required' : '' ?> <?= $disabled ? 'disabled' : '' ?> class="min-h-11 w-full rounded-lg border bg-white px-3 text-sm <?= e($borderClass) ?> focus:outline-none focus:ring-2">
                                <option value="">Selecciona una opción</option>
                                <?php foreach ($field['choices'] ?? [] as $choice): ?>
                                    <option value="<?= e($choice['id']) ?>" <?= (string) $value === (string) $choice['id'] ? 'selected' : '' ?>><?= e($choice['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($field['type'] === 'permissions'): ?>
                            <?php $selectedPermissions = $values['permission_ids'] ?? []; ?>
                            <?php if (($field['choices'] ?? []) === []): ?>
                                <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">No hay permisos cargados. Importa primero <code>database/permissions_seed.sql</code> desde phpMyAdmin.</p>
                            <?php else: ?>
                                <div class="max-h-80 space-y-2 overflow-y-auto rounded-lg border border-slate-200 p-3">
                                    <?php foreach ($field['choices'] as $permission): ?>
                                        <label class="flex cursor-pointer items-start gap-3 rounded-md p-2 hover:bg-slate-50">
                                            <input type="checkbox" name="permission_ids[]" value="<?= e($permission['id']) ?>" <?= in_array((int) $permission['id'], array_map('intval', $selectedPermissions), true) ? 'checked' : '' ?> class="mt-1 size-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                                            <span><span class="block text-sm font-semibold text-slate-800"><?= e($permission['codigo']) ?></span><span class="text-xs text-slate-500"><?= e($permission['descripcion']) ?></span></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($errors['permission_ids'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['permission_ids']) ?></p><?php endif; ?>
                        <?php elseif ($field['type'] === 'textarea'): ?>
                            <textarea id="<?= e($key) ?>" name="<?= e($key) ?>" rows="4" maxlength="<?= e($field['max'] ?? 255) ?>" class="w-full rounded-lg border px-3 py-2 text-sm <?= e($borderClass) ?> focus:outline-none focus:ring-2"><?= e($value) ?></textarea>
                        <?php else: ?>
                            <?php $inputType = $field['type'] === 'decimal' ? 'number' : $field['type']; ?>
                            <input id="<?= e($key) ?>" name="<?= e($key) ?>" type="<?= e($inputType) ?>" <?= $field['required'] && !($editing && $field['type'] === 'password') && !$disabled ? 'required' : '' ?> <?= isset($field['max']) ? 'maxlength="' . e($field['max']) . '"' : '' ?> <?= $field['type'] === 'decimal' ? 'min="0" step="0.' . str_repeat('0', max(0, (int) $field['scale'] - 1)) . '1"' : '' ?> <?= $field['type'] === 'date' ? 'min="1900-01-01"' : '' ?> <?= $disabled ? 'disabled' : '' ?> value="<?= e($field['type'] === 'password' ? '' : $value) ?>" class="min-h-11 w-full rounded-lg border px-3 text-sm <?= e($borderClass) ?> focus:outline-none focus:ring-2">
                            <?php if ($field['type'] === 'password' && $editing): ?><p class="mt-1 text-xs text-slate-500">Déjalo vacío para conservar la contraseña actual.</p><?php endif; ?>
                        <?php endif; ?>
                        <?php if ($fieldError): ?><p class="mt-1 text-sm text-red-700"><?= e($errors[$key]) ?></p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="<?= e($baseUrl) ?>/<?= e($slug) ?>" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Guardar</button>
            </div>
        </form>
    </div>
</section>
