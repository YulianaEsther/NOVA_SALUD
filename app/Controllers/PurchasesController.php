<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Purchase;
use PDOException;
use RuntimeException;

final class PurchasesController
{
    private Purchase $purchases;
    public function __construct() { $this->purchases = new Purchase(); }

    public function index(): void
    {
        $search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $this->render('purchases/index.php', ['purchases' => $this->purchases->all($search), 'search' => $search, 'success' => \flash('success'), 'error' => \flash('error')]);
    }

    public function createForm(): void { $this->renderForm($this->emptyForm(), []); }

    public function store(): void
    {
        if (!$this->validCsrf()) { return; }
        [$header, $items, $values, $errors] = $this->validateInput();
        if ($errors !== []) { http_response_code(422); $this->renderForm($values, $errors); return; }
        try {
            $id = $this->purchases->create($header, $items, (int) $_SESSION['auth_user_id']);
            \flash('success', 'Compra registrada y stock actualizado correctamente.');
            $this->redirect('/compras/detalle?id=' . $id);
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                http_response_code(422);
                $errors['items'] = 'El código de lote ya existe para ese producto. Revisa los lotes ingresados.';
                $this->renderForm($values, $errors);
                return;
            }
            throw $exception;
        } catch (RuntimeException $exception) {
            http_response_code(422); $errors['items'] = $exception->getMessage(); $this->renderForm($values, $errors);
        }
    }

    public function show(): void
    {
        $id = $this->requestId($_GET['id'] ?? null);
        $purchase = $id ? $this->purchases->find($id) : null;
        if (!$purchase) { $this->notFound(); return; }
        $this->render('purchases/show.php', ['purchase' => $purchase, 'items' => $this->purchases->details($id), 'success' => \flash('success'), 'error' => \flash('error')]);
    }

    public function cancel(): void
    {
        if (!$this->validCsrf()) { return; }
        $id = $this->requestId($_POST['id'] ?? null);
        if (!$id) { $this->notFound(); return; }
        try {
            if (!$this->purchases->cancel($id, (int) $_SESSION['auth_user_id'])) { $this->notFound(); return; }
            \flash('success', 'Compra anulada. Se revirtió el stock y se conservó el historial.');
        } catch (RuntimeException $exception) { \flash('error', $exception->getMessage()); }
        $this->redirect('/compras/detalle?id=' . $id);
    }

    private function validateInput(): array
    {
        $providers = array_column($this->purchases->providers(), 'id');
        $values = [
            'proveedor_id' => trim((string) ($_POST['proveedor_id'] ?? '')),
            'numero_documento' => trim((string) ($_POST['numero_documento'] ?? '')),
            'observaciones' => trim((string) ($_POST['observaciones'] ?? '')),
            'items' => [],
        ];
        foreach (array_slice((array) ($_POST['items'] ?? []), 0, 100) as $item) {
            if (!is_array($item)) { continue; }
            $values['items'][] = [
                'producto_id' => trim((string) ($item['producto_id'] ?? '')),
                'codigo_lote' => trim((string) ($item['codigo_lote'] ?? '')),
                'fecha_vencimiento' => trim((string) ($item['fecha_vencimiento'] ?? '')),
                'cantidad' => trim((string) ($item['cantidad'] ?? '')),
                'costo_unitario' => trim((string) ($item['costo_unitario'] ?? '')),
            ];
        }
        $errors = [];
        if (!in_array((int) $values['proveedor_id'], array_map('intval', $providers), true)) { $errors['proveedor_id'] = 'Selecciona un proveedor activo.'; }
        if (mb_strlen($values['numero_documento']) > 80) { $errors['numero_documento'] = 'Máximo 80 caracteres.'; }
        if (mb_strlen($values['observaciones']) > 500) { $errors['observaciones'] = 'Máximo 500 caracteres.'; }
        if ($values['items'] === []) { $errors['items'] = 'Agrega al menos un producto.'; }
        $productIds = array_map('intval', array_column($this->purchases->products(), 'id'));
        $items = [];
        foreach ($values['items'] as $i => $item) {
            $n = $i + 1;
            if (!in_array((int) $item['producto_id'], $productIds, true)) { $errors['items'] = "Selecciona un producto activo en la línea {$n}."; continue; }
            if ($item['codigo_lote'] === '' || mb_strlen($item['codigo_lote']) > 100) { $errors['items'] = "Indica un código de lote válido en la línea {$n} (máximo 100 caracteres)."; }
            if (!$this->decimal($item['cantidad'], 3, true)) { $errors['items'] = "La cantidad de la línea {$n} debe ser mayor que cero y tener hasta 3 decimales."; }
            if (!$this->decimal($item['costo_unitario'], 4, false)) { $errors['items'] = "El costo unitario de la línea {$n} debe ser no negativo y tener hasta 4 decimales."; }
            if ($item['fecha_vencimiento'] !== '' && !$this->validDate($item['fecha_vencimiento'])) { $errors['items'] = "La fecha de vencimiento de la línea {$n} no es válida."; }
            $items[] = ['producto_id' => (int) $item['producto_id'], 'codigo_lote' => $item['codigo_lote'], 'fecha_vencimiento' => $item['fecha_vencimiento'] === '' ? null : $item['fecha_vencimiento'], 'cantidad' => $item['cantidad'], 'costo_unitario' => $item['costo_unitario']];
        }
        $header = ['proveedor_id' => (int) $values['proveedor_id'], 'numero_documento' => $values['numero_documento'] === '' ? null : $values['numero_documento'], 'observaciones' => $values['observaciones'] === '' ? null : $values['observaciones']];
        return [$header, $items, $values, $errors];
    }

    private function decimal(string $value, int $scale, bool $positive): bool
    {
        $integerDigits = $scale === 3 ? 9 : 8;
        $valid = preg_match('/^(?:0|[1-9]\d{0,' . ($integerDigits - 1) . '})(?:\.\d{1,' . $scale . '})?$/', $value) === 1;
        return $valid && (!$positive || (float) $value > 0);
    }
    private function validDate(string $value): bool { $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value); return $date !== false && $date->format('Y-m-d') === $value; }
    private function validCsrf(): bool { if (\verify_csrf_token($_POST['_csrf'] ?? null)) { return true; } http_response_code(419); $this->render('errors/419.php', []); return false; }
    private function requestId(mixed $value): ?int { $id = filter_var($value, FILTER_VALIDATE_INT); return $id !== false && $id > 0 ? $id : null; }
    private function renderForm(array $values, array $errors): void { $this->render('purchases/form.php', ['values' => $values, 'errors' => $errors, 'providers' => $this->purchases->providers(), 'products' => $this->purchases->products(), 'error' => \flash('error')]); }
    private function render(string $template, array $data): void { $app = require dirname(__DIR__, 2) . '/config/app.php'; $viewPath = dirname(__DIR__) . '/Views/' . $template; $data['title'] ??= 'Compras'; \extract($data, EXTR_SKIP); require dirname(__DIR__) . '/Views/layout.php'; }
    private function redirect(string $path): void { $app = require dirname(__DIR__, 2) . '/config/app.php'; header('Location: ' . $app['url'] . $path, true, 303); exit; }
    private function notFound(): void { http_response_code(404); $this->render('errors/404.php', []); }
    private function emptyForm(): array { return ['proveedor_id' => '', 'numero_documento' => '', 'observaciones' => '', 'items' => [['producto_id' => '', 'codigo_lote' => '', 'fecha_vencimiento' => '', 'cantidad' => '', 'costo_unitario' => '']]]; }
}
