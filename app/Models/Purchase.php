<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\AuditLogger;
use PDO;
use RuntimeException;

final class Purchase
{
    private PDO $db;

    public function __construct() { $this->db = Database::connection(); }

    public function all(string $search = ''): array
    {
        $sql = "SELECT c.id, c.numero_documento, c.fecha, c.estado, c.total, pr.nombre AS proveedor,
                       CONCAT(u.nombres, ' ', u.apellidos) AS usuario,
                       (SELECT COUNT(*) FROM detalle_compras dc WHERE dc.compra_id = c.id) AS lineas
                FROM compras c JOIN proveedores pr ON pr.id = c.proveedor_id
                JOIN usuarios u ON u.id = c.usuario_id
                WHERE (:q = '' OR pr.nombre LIKE :proveedor OR c.numero_documento LIKE :documento OR CAST(c.id AS CHAR) = :id)
                ORDER BY c.fecha DESC, c.id DESC";
        $statement = $this->db->prepare($sql);
        $statement->execute(['q' => $search, 'proveedor' => '%' . $search . '%', 'documento' => '%' . $search . '%', 'id' => $search]);
        return $statement->fetchAll();
    }

    public function providers(): array
    {
        return $this->db->query('SELECT id, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre')->fetchAll();
    }

    public function products(): array
    {
        return $this->db->query(
            'SELECT p.id, p.codigo, p.nombre, p.unidad_medida,
                    COALESCE((SELECT SUM(e.cantidad_disponible)
                              FROM lotes l JOIN existencias_lote e ON e.lote_id=l.id
                              WHERE l.producto_id=p.id), 0) AS stock_actual
             FROM productos p WHERE p.activo=1 ORDER BY p.nombre'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT c.*, pr.nombre AS proveedor, CONCAT(u.nombres, \' \', u.apellidos) AS usuario FROM compras c JOIN proveedores pr ON pr.id=c.proveedor_id JOIN usuarios u ON u.id=c.usuario_id WHERE c.id=:id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public function details(int $id): array
    {
        $statement = $this->db->prepare('SELECT dc.cantidad, dc.costo_unitario, dc.subtotal, l.codigo_lote, l.fecha_vencimiento, p.codigo, p.nombre AS producto, p.unidad_medida FROM detalle_compras dc JOIN lotes l ON l.id=dc.lote_id JOIN productos p ON p.id=l.producto_id WHERE dc.compra_id=:id ORDER BY dc.id');
        $statement->execute(['id' => $id]);
        return $statement->fetchAll();
    }

    /** @param array<string, mixed> $header @param list<array<string, mixed>> $items */
    public function create(array $header, array $items, int $userId): int
    {
        $this->db->beginTransaction();
        try {
            $insert = $this->db->prepare("INSERT INTO compras (proveedor_id, usuario_id, numero_documento, observaciones, total) VALUES (:proveedor, :usuario, :documento, :observaciones, 0)");
            $insert->execute(['proveedor' => $header['proveedor_id'], 'usuario' => $userId, 'documento' => $header['numero_documento'], 'observaciones' => $header['observaciones']]);
            $purchaseId = (int) $this->db->lastInsertId();
            $total = 0.0;
            foreach ($items as $item) {
                $productCheck = $this->db->prepare('SELECT id FROM productos WHERE id=:id AND activo=1');
                $productCheck->execute(['id' => $item['producto_id']]);
                if (!$productCheck->fetchColumn()) { throw new RuntimeException('Uno de los productos ya no está disponible.'); }
                $subtotal = round((float) $item['cantidad'] * (float) $item['costo_unitario'], 2);
                $lot = $this->db->prepare('INSERT INTO lotes (compra_id, producto_id, codigo_lote, fecha_vencimiento, costo_unitario) VALUES (:compra, :producto, :codigo, :vencimiento, :costo)');
                $lot->execute(['compra' => $purchaseId, 'producto' => $item['producto_id'], 'codigo' => $item['codigo_lote'], 'vencimiento' => $item['fecha_vencimiento'], 'costo' => $item['costo_unitario']]);
                $lotId = (int) $this->db->lastInsertId();
                $stock = $this->db->prepare('INSERT INTO existencias_lote (lote_id, cantidad_disponible) VALUES (:lote, :cantidad)');
                $stock->execute(['lote' => $lotId, 'cantidad' => $item['cantidad']]);
                $detail = $this->db->prepare('INSERT INTO detalle_compras (compra_id, lote_id, cantidad, costo_unitario, subtotal) VALUES (:compra, :lote, :cantidad, :costo, :subtotal)');
                $detail->execute(['compra' => $purchaseId, 'lote' => $lotId, 'cantidad' => $item['cantidad'], 'costo' => $item['costo_unitario'], 'subtotal' => number_format($subtotal, 2, '.', '')]);
                $movement = $this->db->prepare("INSERT INTO movimientos_inventario (producto_id, lote_id, usuario_id, compra_id, tipo, cantidad_delta, motivo) VALUES (:producto, :lote, :usuario, :compra, 'compra', :cantidad, 'Ingreso por compra')");
                $movement->execute(['producto' => $item['producto_id'], 'lote' => $lotId, 'usuario' => $userId, 'compra' => $purchaseId, 'cantidad' => $item['cantidad']]);
                $total += $subtotal;
            }
            $update = $this->db->prepare('UPDATE compras SET total=:total WHERE id=:id');
            $update->execute(['total' => number_format($total, 2, '.', ''), 'id' => $purchaseId]);
            AuditLogger::record($this->db, $userId, 'crear', 'compra', $purchaseId, ['total' => number_format($total, 2, '.', ''), 'lineas' => count($items)]);
            $this->db->commit();
            return $purchaseId;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $exception;
        }
    }

    public function cancel(int $id, int $userId): bool
    {
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare("SELECT id FROM compras WHERE id=:id AND estado='registrada' FOR UPDATE");
            $lock->execute(['id' => $id]);
            if (!$lock->fetchColumn()) { $this->db->rollBack(); return false; }
            $lines = $this->db->prepare('SELECT l.id, l.producto_id, e.cantidad_disponible, dc.cantidad FROM lotes l JOIN existencias_lote e ON e.lote_id=l.id JOIN detalle_compras dc ON dc.lote_id=l.id WHERE dc.compra_id=:id FOR UPDATE');
            $lines->execute(['id' => $id]);
            $lots = $lines->fetchAll();
            foreach ($lots as $lot) {
                if (abs((float) $lot['cantidad_disponible'] - (float) $lot['cantidad']) > 0.0005) { throw new RuntimeException('No se puede anular: uno o más lotes ya fueron vendidos o modificados.'); }
            }
            $mark = $this->db->prepare("UPDATE compras SET estado='anulada' WHERE id=:id");
            $mark->execute(['id' => $id]);
            foreach ($lots as $lot) {
                $stock = $this->db->prepare('UPDATE existencias_lote SET cantidad_disponible=0 WHERE lote_id=:lote');
                $stock->execute(['lote' => $lot['id']]);
                $movement = $this->db->prepare("INSERT INTO movimientos_inventario (producto_id, lote_id, usuario_id, compra_id, tipo, cantidad_delta, motivo) SELECT :producto, :lote, :usuario, :compra, 'anulacion', -dc.cantidad, 'Anulación de compra' FROM detalle_compras dc WHERE dc.compra_id=:compra AND dc.lote_id=:lote");
                $movement->execute(['producto' => $lot['producto_id'], 'lote' => $lot['id'], 'usuario' => $userId, 'compra' => $id]);
            }
            AuditLogger::record($this->db, $userId, 'anular', 'compra', $id, ['lotes' => count($lots)]);
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $exception;
        }
    }
}
