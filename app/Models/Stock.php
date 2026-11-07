<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\AuditLogger;
use PDO;
use RuntimeException;

final class Stock
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function balances(string $search = ''): array
    {
        $statement = $this->db->prepare(
            'SELECT l.id AS lote_id, l.codigo_lote, l.fecha_vencimiento, l.costo_unitario,
                    p.id AS producto_id, p.codigo, p.nombre AS producto, p.unidad_medida, p.stock_minimo, p.activo AS producto_activo,
                    COALESCE(e.cantidad_disponible, 0) AS cantidad_disponible, c.id AS compra_id,
                    CASE WHEN l.fecha_vencimiento IS NOT NULL AND l.fecha_vencimiento < CURRENT_DATE() THEN 1 ELSE 0 END AS vencido
             FROM lotes l JOIN productos p ON p.id=l.producto_id JOIN compras c ON c.id=l.compra_id
             LEFT JOIN existencias_lote e ON e.lote_id=l.id
             WHERE (:q = \'\' OR p.codigo LIKE :codigo OR p.nombre LIKE :nombre OR l.codigo_lote LIKE :lote)
             ORDER BY (l.fecha_vencimiento IS NULL), l.fecha_vencimiento, p.nombre, l.codigo_lote'
        );
        $like = '%' . $search . '%';
        $statement->execute(['q' => $search, 'codigo' => $like, 'nombre' => $like, 'lote' => $like]);
        return $statement->fetchAll();
    }

    public function movements(): array
    {
        return $this->db->query(
            'SELECT m.id, m.tipo, m.cantidad_delta, m.motivo, m.creado_en,
                    p.codigo, p.nombre AS producto, l.codigo_lote,
                    CONCAT(u.nombres, \' \', u.apellidos) AS usuario,
                    m.compra_id, m.venta_id
             FROM movimientos_inventario m JOIN productos p ON p.id=m.producto_id
             LEFT JOIN lotes l ON l.id=m.lote_id JOIN usuarios u ON u.id=m.usuario_id
             ORDER BY m.creado_en DESC, m.id DESC LIMIT 50'
        )->fetchAll();
    }

    public function findLot(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT l.id AS lote_id, l.codigo_lote, l.fecha_vencimiento, p.codigo, p.nombre AS producto,
                    p.unidad_medida, COALESCE(e.cantidad_disponible,0) AS cantidad_disponible
             FROM lotes l JOIN productos p ON p.id=l.producto_id LEFT JOIN existencias_lote e ON e.lote_id=l.id
             WHERE l.id=:id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public function adjust(int $lotId, string $direction, string $quantity, string $reason, int $userId): void
    {
        if (!in_array($direction, ['entrada', 'salida'], true)) { throw new RuntimeException('Selecciona entrada o salida.'); }
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare(
                'SELECT l.id, l.producto_id, COALESCE(e.cantidad_disponible,0) AS stock
                 FROM lotes l LEFT JOIN existencias_lote e ON e.lote_id=l.id WHERE l.id=:id FOR UPDATE'
            );
            $lock->execute(['id' => $lotId]);
            $lot = $lock->fetch();
            if (!$lot) { throw new RuntimeException('No se encontró el lote.'); }
            if ($direction === 'entrada') {
                $balance = $this->db->prepare('INSERT INTO existencias_lote (lote_id, cantidad_disponible) VALUES (:lote, :cantidad) ON DUPLICATE KEY UPDATE cantidad_disponible=cantidad_disponible+:suma');
                $balance->execute(['lote' => $lotId, 'cantidad' => $quantity, 'suma' => $quantity]);
                $delta = $quantity;
                $type = 'ajuste_entrada';
            } else {
                $balance = $this->db->prepare('UPDATE existencias_lote SET cantidad_disponible=cantidad_disponible-:resta WHERE lote_id=:lote AND cantidad_disponible>=:validar');
                $balance->execute(['resta' => $quantity, 'lote' => $lotId, 'validar' => $quantity]);
                if ($balance->rowCount() !== 1) { throw new RuntimeException('La salida supera las existencias disponibles del lote.'); }
                $delta = '-' . $quantity;
                $type = 'ajuste_salida';
            }
            $movement = $this->db->prepare('INSERT INTO movimientos_inventario (producto_id, lote_id, usuario_id, tipo, cantidad_delta, motivo) VALUES (:producto, :lote, :usuario, :tipo, :delta, :motivo)');
            $movement->execute(['producto' => $lot['producto_id'], 'lote' => $lotId, 'usuario' => $userId, 'tipo' => $type, 'delta' => $delta, 'motivo' => $reason]);
            AuditLogger::record($this->db, $userId, $type, 'lote', $lotId, ['cantidad_delta' => $delta, 'motivo' => $reason]);
            $this->db->commit();
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $exception;
        }
    }
}
