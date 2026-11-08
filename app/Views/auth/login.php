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
<body class="login-shell min-h-screen text-slate-800 antialiased">
    <main class="login-layout grid min-h-screen w-full lg:grid-cols-2">
        <section class="login-visual relative hidden overflow-hidden text-white lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16" aria-label="NOVA SALUD">
            <div class="relative z-10 flex items-center gap-3">
                <span class="flex size-11 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20" aria-hidden="true">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none">
                        <path d="M10 3h4v7h7v4h-7v7h-4v-7H3v-4h7V3Z" fill="currentColor"/>
                    </svg>
                </span>
                <span class="break-words text-lg font-bold tracking-wide"><?= e($app['name']) ?></span>
            </div>

            <div class="relative z-10 max-w-xl py-12">
                <span class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium tracking-wide text-emerald-50">
                    <span class="size-1.5 rounded-full bg-emerald-300"></span>
                    PLATAFORMA DE GESTIÓN
                </span>
                <h1 class="text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">
                    El control que tu operación de salud necesita.
                </h1>
                <p class="mt-5 max-w-lg text-base leading-7 text-emerald-50/80">
                    Administra productos, existencias, compras y ventas desde un solo lugar.
                </p>
            </div>

            <div class="relative z-10 flex items-center gap-3 border-t border-white/15 pt-6 text-sm text-emerald-50/80">
                <svg class="size-5 shrink-0 text-emerald-200" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3 19 6v5c0 4.7-3 8-7 10-4-2-7-5.3-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Acceso seguro para el equipo autorizado
            </div>
        </section>

        <section class="flex min-w-0 items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
            <div class="login-card w-full max-w-md">
                <div class="mb-9 lg:hidden">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-700 text-white shadow-sm" aria-hidden="true">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none">
                            <path d="M10 3h4v7h7v4h-7v7h-4v-7H3v-4h7V3Z" fill="currentColor"/>
                        </svg>
                    </span>
                </div>

                <div class="mb-8">
                    <p class="mb-2 text-sm font-semibold tracking-wide text-emerald-700">Bienvenido</p>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900">Inicia sesión</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Ingresa tus credenciales para continuar a tu cuenta.</p>
                </div>

                <?php if ($error): ?>
                    <div role="alert" class="mb-6 flex gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm leading-5 text-red-800">
                        <svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 8v4m0 4h.01M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3l-7.5-13.1a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= e($baseUrl) ?>/login" class="space-y-5">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <div>
                        <label for="login" class="mb-2 block text-sm font-medium text-slate-700">Usuario o correo electrónico</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M20 21a8 8 0 0 0-16 0m8-10a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input id="login" name="login" type="text" required maxlength="190" autocomplete="username" autocapitalize="none" spellcheck="false" value="<?= e($login) ?>" placeholder="Tu usuario o correo" class="min-h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Contraseña</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect x="4" y="10" width="16" height="11" rx="2" stroke="currentColor" stroke-width="1.7"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" class="min-h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-base text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 sm:text-sm">
                        </div>
                    </div>
                    <button class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-600/25 active:bg-emerald-900">
                        Iniciar sesión
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>

                <div class="my-7 flex items-center gap-4" aria-hidden="true">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    <span class="text-xs text-slate-400">¿Primera vez aquí?</span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>
                <a class="flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-600/15" href="<?= e($baseUrl) ?>/configuracion/inicial">
                    Crear cuenta de administrador
                </a>
                <p class="mt-8 text-center text-xs text-slate-400">Acceso protegido · <?= e($app['name']) ?></p>
            </div>
        </section>
    </main>
</body>
</html>
