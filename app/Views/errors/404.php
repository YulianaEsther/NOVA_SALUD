<?php
declare(strict_types=1);
$title = 'No encontrado';
?>
<section class="rounded-xl border border-slate-200 bg-white p-8 text-center">
    <h1 class="text-2xl font-bold text-slate-900">No encontramos ese producto</h1>
    <p class="mt-2 text-slate-600">Puede que haya sido desactivado o que la dirección no sea válida.</p>
    <a href="<?= e($baseUrl) ?>/productos" class="mt-5 inline-flex rounded-lg bg-emerald-700 px-4 py-2 font-semibold text-white">Volver a productos</a>
</section>
