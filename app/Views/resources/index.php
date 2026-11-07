<?php
declare(strict_types=1);
$title = $definition['title'];
?>
<section>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Administración</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900"><?= e($definition['title']) ?></h1>
            <p class="mt-2 text-sm text-slate-600">Consulta y administra los registros.</p>
        </div>
        <a href="<?= e($baseUrl) ?>/<?= e($slug) ?>/nuevo" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Nuevo registro</a>
    </div>

    <?php if ($success): ?><div role="status" class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($error) ?></div><?php endif; ?>

    <form method="get" action="<?= e($baseUrl) ?>/<?= e($slug) ?>" class="mb-5 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:flex-row">
        <label class="sr-only" for="q">Buscar <?= e(mb_strtolower($definition['title'])) ?></label>
        <input id="q" name="q" value="<?= e($search) ?>" maxlength="100" placeholder="Buscar..." class="min-h-11 flex-1 rounded-lg border border-slate-300 px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        <button class="min-h-11 rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Buscar</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                    <tr>
                        <?php foreach ($definition['columns'] as $label): ?><th scope="col" class="px-4 py-3"><?= e($label) ?></th><?php endforeach; ?>
                        <?php if ($definition['active_column'] !== null): ?><th scope="col" class="px-4 py-3">Estado</th><?php endif; ?>
                        <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php if ($rows === []): ?>
                    <tr><td colspan="<?= count($definition['columns']) + ($definition['active_column'] !== null ? 2 : 1) ?>" class="px-4 py-10 text-center text-slate-500">No se encontraron registros.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <?php $active = $definition['active_column'] === null || (int) $row[$definition['active_column']] === 1; ?>
                        <tr class="hover:bg-slate-50">
                            <?php foreach ($definition['columns'] as $key => $label): ?>
                                <td class="px-4 py-3 text-slate-700">
                                    <?php if ($key === 'costo_unitario'): ?>S/ <?= e(number_format((float) $row[$key], 4)) ?>
                                    <?php elseif ($key === 'fecha_vencimiento'): ?><?= e($row[$key] ? date('d/m/Y', strtotime((string) $row[$key])) : 'Sin fecha') ?>
                                    <?php elseif ($key === 'stock'): ?><?= e(number_format((float) $row[$key], 3)) ?>
                                    <?php else: ?><?= e($row[$key] ?? '—') ?><?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if ($definition['active_column'] !== null): ?>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' ?>"><?= $active ? 'Activo' : 'Inactivo' ?></span></td>
                            <?php endif; ?>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <?php if ($active): ?>
                                    <a class="font-semibold text-emerald-800 hover:underline" href="<?= e($baseUrl) ?>/<?= e($slug) ?>/editar?id=<?= e($row['id']) ?>">Editar</a>
                                    <?php if ($definition['active_column'] !== null): ?>
                                        <form class="ml-3 inline" method="post" action="<?= e($baseUrl) ?>/<?= e($slug) ?>/desactivar" onsubmit="return confirm('¿Desactivar este registro?');">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                            <button class="font-semibold text-red-700 hover:underline">Desactivar</button>
                                        </form>
                                    <?php else: ?>
                                        <form class="ml-3 inline" method="post" action="<?= e($baseUrl) ?>/lotes/eliminar" onsubmit="return confirm('Solo se eliminará si el lote no tiene stock ni historial. ¿Continuar?');">
                                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= e($row['id']) ?>">
                                            <button class="font-semibold text-red-700 hover:underline">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                <?php elseif ($definition['active_column'] !== null): ?>
                                    <form class="inline" method="post" action="<?= e($baseUrl) ?>/<?= e($slug) ?>/activar">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= e($row['id']) ?>">
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
