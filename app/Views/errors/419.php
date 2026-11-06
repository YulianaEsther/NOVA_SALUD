<?php
declare(strict_types=1);
$title = 'Formulario vencido';
?>
<section class="rounded-xl border border-amber-200 bg-white p-8 text-center">
    <h1 class="text-2xl font-bold text-slate-900">El formulario venció</h1>
    <p class="mt-2 text-slate-600">Recarga la página e inténtalo nuevamente.</p>
    <a href="<?= e($baseUrl) ?>/productos" class="mt-5 inline-flex rounded-lg bg-emerald-700 px-4 py-2 font-semibold text-white">Volver a productos</a>
</section>
