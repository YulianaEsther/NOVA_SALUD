<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class InventoryMovement
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    /** @param array{q:string,tipo:string,desde:string,hasta:string} $filters
     *  @return array{rows:list<array<string,mixed>>,summary:array<string,mixed>,count:int}
     */
    public function search(array $filters): array
    {
        $where = [];
        $params = [];
        if ($filters['q'] !== '') {
            $where[] = '(p.codigo LIKE :codigo OR p.nombre LIKE :producto OR l.codigo_lote LIKE :lote OR m.motivo LIKE :motivo OR c.numero_documento LIKE :compra_doc OR v.numero_documento LIKE :venta_doc OR CAST(m.compra_id AS CHAR)=:compra_id OR CAST(m.venta_id AS CHAR)=:venta_id)';
            $like = '%' . $filters['q'] . '%';
            foreach (['codigo', 'producto', 'lote', 'motivo', 'compra_doc', 'venta_doc'] as $key) { $params[$key] = $like; }
            $params['compra_id'] = $filters['q'];
            $params['venta_id'] = $filters['q'];
        }
        if ($filters['tipo'] !== '') { $where[] = 'm.tipo = :tipo'; $params['tipo'] = $filters['tipo']; }
        if ($filters['desde'] !== '') { $where[] = 'm.creado_en >= :desde'; $params['desde'] = $filters['desde'] . ' 00:00:00'; }
        if ($filters['hasta'] !== '') {
            $to = (new \DateTimeImmutable($filters['hasta']))->modify('+1 day')->format('Y-m-d');
            $where[] = 'm.creado_en < :hasta'; $params['hasta'] = $to . ' 00:00:00';
        }
        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);
        $joins = ' FROM movimientos_inventario m JOIN productos p ON p.id=m.producto_id LEFT JOIN lotes l ON l.id=m.lote_id LEFT JOIN compras c ON c.id=m.compra_id LEFT JOIN ventas v ON v.id=m.venta_id JOIN usuarios u ON u.id=m.usuario_id';
        $rows = $this->db->prepare(
            'SELECT m.id, m.tipo, m.cantidad_delta, m.motivo, m.creado_en,
                    p.id AS producto_id, p.codigo, p.nombre AS producto, p.unidad_medida,
                    l.id AS lote_id, l.codigo_lote, l.fecha_vencimiento,
                    CONCAT(u.nombres, \' \', u.apellidos) AS usuario,
                    m.compra_id, c.numero_documento AS compra_documento,
                    m.venta_id, v.numero_documento AS venta_documento' . $joins . $whereSql .
            ' ORDER BY m.creado_en DESC, m.id DESC LIMIT 500'
        );
        $rows->execute($params);
        $summary = $this->db->prepare(
            'SELECT COUNT(*) AS movimientos,
                    COALESCE(SUM(CASE WHEN m.cantidad_delta > 0 THEN m.cantidad_delta ELSE 0 END),0) AS entradas,
                    COALESCE(SUM(CASE WHEN m.cantidad_delta < 0 THEN -m.cantidad_delta ELSE 0 END),0) AS salidas' . $joins . $whereSql
        );
        $summary->execute($params);
        $totals = $summary->fetch() ?: ['movimientos' => 0, 'entradas' => 0, 'salidas' => 0];
        return ['rows' => $rows->fetchAll(), 'summary' => $totals, 'count' => (int) $totals['movimientos']];
    }
}
