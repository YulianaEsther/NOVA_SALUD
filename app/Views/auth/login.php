<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | <?= e($app['name']) ?></title>
    <link rel="stylesheet" href="<?= e($baseUrl) ?>/assets/css/app.css">
</head>
<body class="login-shell bg-slate-50 text-slate-800">
    <main class="login-card rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="mb-7 text-center">
            <h1 class="break-words text-2xl font-bold text-slate-900"><?= e($app['name']) ?></h1>
            <p class="mt-1 text-sm text-slate-600">Ingresa con tu usuario o correo y contraseña.</p>
        </div>
        <?php if ($error): ?><div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="<?= e($baseUrl) ?>/login" class="space-y-5">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <div>
                <label for="login" class="mb-1.5 block text-sm font-semibold text-slate-700">Usuario o correo electrónico</label>
                <input id="login" name="login" type="text" required maxlength="190" autocomplete="username" autocapitalize="none" spellcheck="false" value="<?= e($login) ?>" class="min-h-11 w-full rounded-lg border border-slate-300 px-3 text-base focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100 sm:text-sm">
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Contraseña</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="min-h-11 w-full rounded-lg border border-slate-300 px-3 text-base focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-100 sm:text-sm">
            </div>
            <button class="min-h-11 w-full rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Iniciar sesión</button>
        </form>
        <p class="mt-6 text-center text-xs text-slate-500">¿Primera instalación? <a class="font-semibold text-emerald-800 hover:underline" href="<?= e($baseUrl) ?>/configuracion/inicial">Crear cuenta de administrador</a></p>
    </main>
</body>
</html>
