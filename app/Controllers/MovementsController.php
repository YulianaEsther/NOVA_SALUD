<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\InventoryMovement;

final class MovementsController
{
    private InventoryMovement $movements;
    private const TYPES = ['compra', 'venta', 'ajuste_entrada', 'ajuste_salida', 'anulacion'];
    public function __construct() { $this->movements = new InventoryMovement(); }

    public function index(): void
    {
        $filters = [
            'q' => mb_substr(trim((string)($_GET['q'] ?? '')), 0, 100),
            'tipo' => trim((string)($_GET['tipo'] ?? '')),
            'desde' => trim((string)($_GET['desde'] ?? '')),
            'hasta' => trim((string)($_GET['hasta'] ?? '')),
        ];
        $errors = [];
        if ($filters['tipo'] !== '' && !in_array($filters['tipo'], self::TYPES, true)) { $filters['tipo']=''; $errors[]='El tipo de movimiento no es válido.'; }
        foreach (['desde', 'hasta'] as $key) {
            if ($filters[$key] !== '' && !$this->validDate($filters[$key])) { $filters[$key]=''; $errors[]='Una fecha ingresada no es válida.'; }
        }
        if ($filters['desde'] !== '' && $filters['hasta'] !== '' && $filters['desde'] > $filters['hasta']) { $errors[]='La fecha desde no puede ser posterior a la fecha hasta.'; $filters['desde']=''; $filters['hasta']=''; }
        $result = $this->movements->search($filters);
        $this->render('movements/index.php', [
            'filters' => $filters, 'rows' => $result['rows'], 'summary' => $result['summary'],
            'errors' => $errors, 'success' => \flash('success'), 'error' => \flash('error'),
        ]);
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
    private function render(string $template, array $data): void { $app = require dirname(__DIR__, 2) . '/config/app.php'; $viewPath = dirname(__DIR__) . '/Views/' . $template; $data['title'] ??= 'Movimientos de inventario'; \extract($data, EXTR_SKIP); require dirname(__DIR__) . '/Views/layout.php'; }
}
