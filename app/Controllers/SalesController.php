<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Sale;
use PDOException;
use RuntimeException;

final class SalesController
{
    private Sale $sales;
    public function __construct() { $this->sales = new Sale(); }

    public function index(): void
    {
        $search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $this->render('sales/index.php', ['sales' => $this->sales->all($search), 'search' => $search, 'success' => \flash('success'), 'error' => \flash('error')]);
    }

    public function createForm(): void { $this->renderForm($this->emptyForm(), []); }

    public function store(): void
    {
        if (!$this->validCsrf()) { return; }
        [$header, $items, $values, $errors] = $this->validateInput();
        if ($errors !== []) { http_response_code(422); $this->renderForm($values, $errors); return; }
        try {
            $id = $this->sales->create($header, $items, (int) $_SESSION['auth_user_id']);
            \flash('success', 'Venta confirmada y existencias descontadas correctamente.');
            $this->redirect('/ventas/detalle?id=' . $id);
        } catch (RuntimeException $exception) {
            http_response_code(422); $errors['items'] = $exception->getMessage(); $this->renderForm($values, $errors);
        } catch (PDOException $exception) {
            throw $exception;
        }
    }

    public function show(): void
    {
        $id = $this->requestId($_GET['id'] ?? null);
        $sale = $id ? $this->sales->find($id) : null;
        if (!$sale) { $this->notFound(); return; }
        $this->render('sales/show.php', ['sale' => $sale, 'items' => $this->sales->details($id), 'success' => \flash('success'), 'error' => \flash('error')]);
    }

    public function cancel(): void
    {
        if (!$this->validCsrf()) { return; }
        $id = $this->requestId($_POST['id'] ?? null);
        if (!$id) { $this->notFound(); return; }
        try {
            if (!$this->sales->cancel($id, (int) $_SESSION['auth_user_id'])) { $this->notFound(); return; }
            \flash('success', 'Venta anulada. Se restauró el stock y quedó registrado el movimiento inverso.');
        } catch (RuntimeException $exception) { \flash('error', $exception->getMessage()); }
        $this->redirect('/ventas/detalle?id=' . $id);
    }

    private function validateInput(): array
    {
        $values = [
            'cliente_id' => trim((string) ($_POST['cliente_id'] ?? '')),
            'numero_documento' => trim((string) ($_POST['numero_documento'] ?? '')),
            'metodo_pago' => trim((string) ($_POST['metodo_pago'] ?? '')),
            'observaciones' => trim((string) ($_POST['observaciones'] ?? '')),
            'items' => [],
        ];
        foreach (array_slice((array) ($_POST['items'] ?? []), 0, 100) as $item) {
            if (!is_array($item)) { continue; }
            $values['items'][] = ['producto_id' => trim((string) ($item['producto_id'] ?? '')), 'cantidad' => trim((string) ($item['cantidad'] ?? '')), 'precio_unitario' => trim((string) ($item['precio_unitario'] ?? ''))];
        }
        $errors = [];
        $clients = array_map('intval', array_column($this->sales->clients(), 'id'));
        if ($values['cliente_id'] !== '' && !in_array((int) $values['cliente_id'], $clients, true)) { $errors['cliente_id'] = 'Selecciona un cliente activo o deja el campo vacío.'; }
        if (mb_strlen($values['numero_documento']) > 80) { $errors['numero_documento'] = 'Máximo 80 caracteres.'; }
        if (!in_array($values['metodo_pago'], ['', 'efectivo', 'tarjeta', 'transferencia', 'otro'], true)) { $errors['metodo_pago'] = 'Selecciona un método de pago válido.'; }
        if (mb_strlen($values['observaciones']) > 500) { $errors['observaciones'] = 'Máximo 500 caracteres.'; }
        if ($values['items'] === []) { $errors['items'] = 'Agrega al menos un producto.'; }
        $products = [];
        foreach ($this->sales->products() as $product) { $products[(int) $product['id']] = $product; }
        $items = [];
        foreach ($values['items'] as $index => $item) {
            $line = $index + 1;
            $productId = filter_var($item['producto_id'], FILTER_VALIDATE_INT);
            if ($productId === false || !isset($products[(int) $productId])) { $errors['items'] = "Selecciona un producto activo en la línea {$line}."; continue; }
            $product = $products[(int) $productId];
            if (!$this->decimal($item['cantidad'], 3, true)) { $errors['items'] = "La cantidad de la línea {$line} debe ser mayor que cero y tener hasta 3 decimales."; }
            if (!$this->decimal($item['precio_unitario'], 2, false)) { $errors['items'] = "El precio de la línea {$line} debe ser válido y no negativo."; }
            elseif ((float) $item['precio_unitario'] > (float) $product['precio_venta']) { $errors['items'] = "El precio de la línea {$line} no puede superar el precio de venta registrado."; }
            elseif ((float) $item['precio_unitario'] < (float) $product['precio_venta'] && !\can('ventas.descuento')) { $errors['items'] = "No tienes permiso para aplicar descuento en la línea {$line}."; }
            $items[] = ['producto_id' => (int) $productId, 'cantidad' => $item['cantidad'], 'precio_unitario' => $item['precio_unitario']];
        }
        return [[
            'cliente_id' => $values['cliente_id'] === '' ? null : (int) $values['cliente_id'],
            'numero_documento' => $values['numero_documento'] === '' ? null : $values['numero_documento'],
            'metodo_pago' => $values['metodo_pago'] === '' ? null : $values['metodo_pago'],
            'observaciones' => $values['observaciones'] === '' ? null : $values['observaciones'],
        ], $items, $values, $errors];
    }

    private function decimal(string $value, int $scale, bool $positive): bool
    {
        $integerDigits = $scale === 3 ? 9 : 10;
        $valid = preg_match('/^(?:0|[1-9]\d{0,' . ($integerDigits - 1) . '})(?:\.\d{1,' . $scale . '})?$/', $value) === 1;
        return $valid && (!$positive || (float) $value > 0);
    }

    private function validCsrf(): bool { if (\verify_csrf_token($_POST['_csrf'] ?? null)) { return true; } http_response_code(419); $this->render('errors/419.php', []); return false; }
    private function requestId(mixed $value): ?int { $id = filter_var($value, FILTER_VALIDATE_INT); return $id !== false && $id > 0 ? $id : null; }
    private function renderForm(array $values, array $errors): void { $this->render('sales/form.php', ['values' => $values, 'errors' => $errors, 'clients' => $this->sales->clients(), 'products' => $this->sales->products(), 'canDiscount' => \can('ventas.descuento')]); }
    private function render(string $template, array $data): void { $app = require dirname(__DIR__, 2) . '/config/app.php'; $viewPath = dirname(__DIR__) . '/Views/' . $template; $data['title'] ??= 'Ventas'; \extract($data, EXTR_SKIP); require dirname(__DIR__) . '/Views/layout.php'; }
    private function redirect(string $path): void { $app = require dirname(__DIR__, 2) . '/config/app.php'; header('Location: ' . $app['url'] . $path, true, 303); exit; }
    private function notFound(): void { http_response_code(404); $this->render('errors/404.php', []); }
    private function emptyForm(): array { return ['cliente_id' => '', 'numero_documento' => '', 'metodo_pago' => '', 'observaciones' => '', 'items' => [['producto_id' => '', 'cantidad' => '1', 'precio_unitario' => '']]]; }
}
