<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuración inicial | <?= e($app['name']) ?></title>
    <link rel="stylesheet" href="<?= e($baseUrl) ?>/assets/css/app.css">
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10 text-slate-800">
    <main class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Configuración inicial</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">Crear administrador</h1>
        <p class="mt-2 text-sm text-slate-600">Este formulario solo está disponible mientras no exista ningún usuario.</p>
        <?php if ($error): ?><div role="alert" class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"><?= e($error) ?></div><?php endif; ?>
        <?php if (isset($errors['_form'])): ?><div role="alert" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($errors['_form']) ?></div><?php endif; ?>
        <form method="post" action="<?= e($baseUrl) ?>/configuracion/inicial" class="mt-6 grid gap-5 sm:grid-cols-2">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <?php foreach (['nombres' => 'Nombres', 'apellidos' => 'Apellidos', 'email' => 'Correo electrónico', 'usuario' => 'Nombre de usuario'] as $field => $label): ?>
                <div>
                    <label for="<?= e($field) ?>" class="mb-1.5 block text-sm font-semibold text-slate-700"><?= e($label) ?></label>
                    <input id="<?= e($field) ?>" name="<?= e($field) ?>" type="<?= $field === 'email' ? 'email' : 'text' ?>" required maxlength="<?= $field === 'email' ? '190' : ($field === 'usuario' ? '80' : '120') ?>" autocomplete="<?= $field === 'email' ? 'email' : 'name' ?>" value="<?= e($values[$field] ?? '') ?>" class="min-h-11 w-full rounded-lg border px-3 text-sm focus:outline-none focus:ring-2 <?= isset($errors[$field]) ? 'border-red-400 focus:border-red-500 focus:ring-red-100' : 'border-slate-300 focus:border-emerald-600 focus:ring-emerald-100' ?>">
                    <?php if (isset($errors[$field])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors[$field]) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Contraseña</label>
                <input id="password" name="password" type="password" required minlength="12" maxlength="128" autocomplete="new-password" class="min-h-11 w-full rounded-lg border px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <?php if (isset($errors['password'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['password']) ?></p><?php endif; ?>
                <p class="mt-1 text-xs text-slate-500">Usa al menos 12 caracteres.</p>
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" maxlength="128" autocomplete="new-password" class="min-h-11 w-full rounded-lg border px-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                <?php if (isset($errors['password_confirmation'])): ?><p class="mt-1 text-sm text-red-700"><?= e($errors['password_confirmation']) ?></p><?php endif; ?>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:col-span-2 sm:flex-row sm:justify-end">
                <a href="<?= e($baseUrl) ?>/login" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Volver</a>
                <button <?= $error ? 'disabled' : '' ?> class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50">Crear administrador</button>
            </div>
        </form>
    </main>
</body>
</html>
