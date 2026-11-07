<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

final class Sale
{
    private PDO $db;

    public function __construct() { $this->db = Database::connection(); }

    public function all(string $search = ''): array
    {
        $statement = $this->db->prepare(
            "SELECT v.id, v.numero_documento, v.fecha, v.estado, v.metodo_pago, v.total,
                    COALESCE(NULLIF(CONCAT_WS(' ', c.nombres, c.apellidos), ''), 'Venta mostrador') AS cliente,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                    (SELECT COUNT(DISTINCT l.producto_id) FROM detalle_ventas dv JOIN lotes l ON l.id=dv.lote_id WHERE dv.venta_id=v.id) AS productos
             FROM ventas v LEFT JOIN clientes c ON c.id=v.cliente_id
             JOIN usuarios u ON u.id=v.usuario_id
             WHERE (:q = '' OR c.nombres LIKE :cliente OR c.apellidos LIKE :apellido OR v.numero_documento LIKE :documento OR CAST(v.id AS CHAR)=:id)
             ORDER BY v.fecha DESC, v.id DESC"
        );
        $statement->execute(['q' => $search, 'cliente' => '%' . $search . '%', 'apellido' => '%' . $search . '%', 'documento' => '%' . $search . '%', 'id' => $search]);
        return $statement->fetchAll();
    }

    public function clients(): array
    {
        return $this->db->query("SELECT id, nombres, apellidos, documento FROM clientes WHERE activo=1 ORDER BY nombres, apellidos")->fetchAll();
    }

    public function products(): array
    {
        return $this->db->query(
            "SELECT p.id, p.codigo, p.nombre, p.unidad_medida, p.precio_venta,
                    COALESCE(SUM(CASE WHEN (l.fecha_vencimiento IS NULL OR l.fecha_vencimiento >= CURRENT_DATE()) THEN e.cantidad_disponible ELSE 0 END), 0) AS stock_vendible
             FROM productos p LEFT JOIN lotes l ON l.producto_id=p.id
             LEFT JOIN existencias_lote e ON e.lote_id=l.id
             WHERE p.activo=1 GROUP BY p.id, p.codigo, p.nombre, p.unidad_medida, p.precio_venta ORDER BY p.nombre"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            "SELECT v.*, COALESCE(NULLIF(CONCAT_WS(' ', c.nombres, c.apellidos), ''), 'Venta mostrador') AS cliente,
                    CONCAT(u.nombres, ' ', u.apellidos) AS usuario
             FROM ventas v LEFT JOIN clientes c ON c.id=v.cliente_id JOIN usuarios u ON u.id=v.usuario_id WHERE v.id=:id"
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public function details(int $id): array
    {
        $statement = $this->db->prepare(
            'SELECT dv.cantidad, dv.precio_unitario, dv.descuento_unitario, dv.subtotal,
                    l.codigo_lote, l.fecha_vencimiento, p.codigo, p.nombre AS producto, p.unidad_medida
             FROM detalle_ventas dv JOIN lotes l ON l.id=dv.lote_id JOIN productos p ON p.id=l.producto_id
             WHERE dv.venta_id=:id ORDER BY p.nombre, l.fecha_vencimiento, dv.id'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetchAll();
    }

    /** @param array<string, mixed> $header @param list<array<string, mixed>> $items */
    public function create(array $header, array $items, int $userId): int
    {
        usort($items, static fn (array $a, array $b): int => $a['producto_id'] <=> $b['producto_id']);
        $this->db->beginTransaction();
        try {
            if ($header['cliente_id'] !== null) {
                $client = $this->db->prepare('SELECT id FROM clientes WHERE id=:id AND activo=1');
                $client->execute(['id' => $header['cliente_id']]);
                if (!$client->fetchColumn()) { throw new RuntimeException('El cliente seleccionado ya no está activo.'); }
            }
            $sale = $this->db->prepare("INSERT INTO ventas (cliente_id, usuario_id, numero_documento, metodo_pago, observaciones, total) VALUES (:cliente, :usuario, :documento, :metodo, :observaciones, 0)");
            $sale->execute(['cliente' => $header['cliente_id'], 'usuario' => $userId, 'documento' => $header['numero_documento'], 'metodo' => $header['metodo_pago'], 'observaciones' => $header['observaciones']]);
            $saleId = (int) $this->db->lastInsertId();
            $total = 0.0;
            foreach ($items as $item) {
                $product = $this->db->prepare('SELECT id, precio_venta FROM productos WHERE id=:id AND activo=1 FOR UPDATE');
                $product->execute(['id' => $item['producto_id']]);
                $catalogPrice = $product->fetchColumn(1);
                if ($catalogPrice === false) { throw new RuntimeException('Uno de los productos ya no está activo.'); }
                if ((float) $item['precio_unitario'] > (float) $catalogPrice) { throw new RuntimeException('El precio de venta cambió. Actualiza la página e intenta nuevamente.'); }
                $lotsQuery = $this->db->prepare(
                    'SELECT l.id, l.producto_id, e.cantidad_disponible
                     FROM lotes l JOIN existencias_lote e ON e.lote_id=l.id
                     WHERE l.producto_id=:producto AND e.cantidad_disponible > 0
                       AND (l.fecha_vencimiento IS NULL OR l.fecha_vencimiento >= CURRENT_DATE())
                     ORDER BY (l.fecha_vencimiento IS NULL), l.fecha_vencimiento, l.id FOR UPDATE'
                );
                $lotsQuery->execute(['producto' => $item['producto_id']]);
                $lots = $lotsQuery->fetchAll();
                $remaining = (float) $item['cantidad'];
                $unitPrice = (float) $item['precio_unitario'];
                $discount = round((float) $catalogPrice - $unitPrice, 2);
                $allocations = [];
                foreach ($lots as $lot) {
                    if ($remaining <= 0.000001) { break; }
                    $available = (float) $lot['cantidad_disponible'];
                    $quantity = min($available, $remaining);
                    if ($quantity <= 0) { continue; }
                    $allocations[] = ['lot' => $lot, 'quantity' => $quantity];
                    $remaining = round($remaining - $quantity, 3);
                }
                if ($remaining > 0.0005) { throw new RuntimeException('Stock vendible insuficiente para uno de los productos. No se registró la venta.'); }
                $lineTotal = round((float) $item['cantidad'] * $unitPrice, 2);
                $allocatedSubtotal = 0.0;
                foreach ($allocations as $allocationIndex => $allocation) {
                    $lot = $allocation['lot'];
                    $quantity = $allocation['quantity'];
                    $quantityValue = number_format($quantity, 3, '.', '');
                    $updateStock = $this->db->prepare('UPDATE existencias_lote SET cantidad_disponible=cantidad_disponible-:cantidad_update WHERE lote_id=:lote AND cantidad_disponible >= :cantidad_check');
                    $updateStock->execute(['cantidad_update' => $quantityValue, 'lote' => $lot['id'], 'cantidad_check' => $quantityValue]);
                    if ($updateStock->rowCount() !== 1) { throw new RuntimeException('Cambió el stock mientras se registraba la venta. Intenta nuevamente.'); }
                    $isLastAllocation = $allocationIndex === array_key_last($allocations);
                    $subtotal = $isLastAllocation ? round($lineTotal - $allocatedSubtotal, 2) : round($quantity * $unitPrice, 2);
                    $allocatedSubtotal += $subtotal;
                    $detail = $this->db->prepare('INSERT INTO detalle_ventas (venta_id, lote_id, cantidad, precio_unitario, descuento_unitario, subtotal) VALUES (:venta, :lote, :cantidad, :precio, :descuento, :subtotal)');
                    $detail->execute(['venta' => $saleId, 'lote' => $lot['id'], 'cantidad' => number_format($quantity, 3, '.', ''), 'precio' => number_format($unitPrice, 2, '.', ''), 'descuento' => number_format($discount, 2, '.', ''), 'subtotal' => number_format($subtotal, 2, '.', '')]);
                    $movement = $this->db->prepare("INSERT INTO movimientos_inventario (producto_id, lote_id, usuario_id, venta_id, tipo, cantidad_delta, motivo) VALUES (:producto, :lote, :usuario, :venta, 'venta', :cantidad, 'Salida por venta')");
                    $movement->execute(['producto' => $item['producto_id'], 'lote' => $lot['id'], 'usuario' => $userId, 'venta' => $saleId, 'cantidad' => number_format(-$quantity, 3, '.', '')]);
                    $total += $subtotal;
                }
            }
            $updateTotal = $this->db->prepare('UPDATE ventas SET total=:total WHERE id=:id');
            $updateTotal->execute(['total' => number_format($total, 2, '.', ''), 'id' => $saleId]);
            $this->db->commit();
            return $saleId;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $exception;
        }
    }

    public function cancel(int $id, int $userId): bool
    {
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare("SELECT id FROM ventas WHERE id=:id AND estado='confirmada' FOR UPDATE");
            $lock->execute(['id' => $id]);
            if (!$lock->fetchColumn()) { $this->db->rollBack(); return false; }
            $details = $this->db->prepare('SELECT dv.lote_id, dv.cantidad, l.producto_id FROM detalle_ventas dv JOIN lotes l ON l.id=dv.lote_id WHERE dv.venta_id=:id ORDER BY dv.lote_id FOR UPDATE');
            $details->execute(['id' => $id]);
            $lines = $details->fetchAll();
            foreach ($lines as $line) {
                $restore = $this->db->prepare('UPDATE existencias_lote SET cantidad_disponible=cantidad_disponible+:cantidad WHERE lote_id=:lote');
                $restore->execute(['cantidad' => $line['cantidad'], 'lote' => $line['lote_id']]);
                if ($restore->rowCount() !== 1) { throw new RuntimeException('No se pudo restaurar el stock de uno de los lotes.'); }
                $movement = $this->db->prepare("INSERT INTO movimientos_inventario (producto_id, lote_id, usuario_id, venta_id, tipo, cantidad_delta, motivo) VALUES (:producto, :lote, :usuario, :venta, 'anulacion', :cantidad, 'Reversión de venta anulada')");
                $movement->execute(['producto' => $line['producto_id'], 'lote' => $line['lote_id'], 'usuario' => $userId, 'venta' => $id, 'cantidad' => $line['cantidad']]);
            }
            $mark = $this->db->prepare("UPDATE ventas SET estado='anulada' WHERE id=:id");
            $mark->execute(['id' => $id]);
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $exception;
        }
    }
}
