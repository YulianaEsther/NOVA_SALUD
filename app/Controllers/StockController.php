<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Stock;
use RuntimeException;

final class StockController
{
    private Stock $stock;
    public function __construct() { $this->stock = new Stock(); }

    public function index(): void
    {
        $search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $this->render('stock/index.php', ['balances' => $this->stock->balances($search), 'movements' => $this->stock->movements(), 'search' => $search, 'success' => \flash('success'), 'error' => \flash('error')]);
    }

    public function adjustForm(): void
    {
        $id = $this->requestId($_GET['id'] ?? null);
        $lot = $id ? $this->stock->findLot($id) : null;
        if (!$lot) { $this->notFound(); return; }
        $this->render('stock/adjust.php', ['lot' => $lot, 'errors' => [], 'direction' => 'entrada', 'quantity' => '', 'reason' => '']);
    }

    public function adjust(): void
    {
        if (!\verify_csrf_token($_POST['_csrf'] ?? null)) { http_response_code(419); $this->render('errors/419.php', []); return; }
        $id = $this->requestId($_POST['lote_id'] ?? null);
        $direction = trim((string) ($_POST['direccion'] ?? ''));
        $quantity = trim((string) ($_POST['cantidad'] ?? ''));
        $reason = mb_substr(trim((string) ($_POST['motivo'] ?? '')), 0, 255);
        $lot = $id ? $this->stock->findLot($id) : null;
        if (!$lot) { $this->notFound(); return; }
        $errors = [];
        if (!in_array($direction, ['entrada', 'salida'], true)) { $errors['direccion'] = 'Selecciona entrada o salida.'; }
        if (!$this->validQuantity($quantity)) { $errors['cantidad'] = 'Ingresa una cantidad mayor que cero, con hasta 3 decimales.'; }
        if ($reason === '') { $errors['motivo'] = 'El motivo del ajuste es obligatorio.'; }
        if ($errors !== []) { http_response_code(422); $this->render('stock/adjust.php', ['lot' => $lot, 'errors' => $errors, 'direction' => $direction, 'quantity' => $quantity, 'reason' => $reason]); return; }
        try {
            $this->stock->adjust($id, $direction, $quantity, $reason, (int) $_SESSION['auth_user_id']);
            \flash('success', 'Ajuste de existencias registrado y movimiento guardado.');
            $this->redirect('/existencias');
        } catch (RuntimeException $exception) {
            http_response_code(422);
            $this->render('stock/adjust.php', ['lot' => $lot, 'errors' => ['_form' => $exception->getMessage()], 'direction' => $direction, 'quantity' => $quantity, 'reason' => $reason]);
        }
    }

    private function validQuantity(string $value): bool
    {
        return preg_match('/^(?:0|[1-9]\d{0,8})(?:\.\d{1,3})?$/', $value) === 1 && (float) $value > 0;
    }
    private function requestId(mixed $value): ?int { $id = filter_var($value, FILTER_VALIDATE_INT); return $id !== false && $id > 0 ? $id : null; }
    private function render(string $template, array $data): void { $app = require dirname(__DIR__, 2) . '/config/app.php'; $viewPath = dirname(__DIR__) . '/Views/' . $template; $data['title'] ??= 'Existencias'; \extract($data, EXTR_SKIP); require dirname(__DIR__) . '/Views/layout.php'; }
    private function redirect(string $path): void { $app = require dirname(__DIR__, 2) . '/config/app.php'; header('Location: ' . $app['url'] . $path, true, 303); exit; }
    private function notFound(): void { http_response_code(404); $this->render('errors/404.php', []); }
}
