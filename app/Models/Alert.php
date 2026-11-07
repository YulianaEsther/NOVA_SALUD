<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Alert
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    /** Recalcula alertas vigentes: stock mínimo y vencimiento dentro de 30 días. */
    public function refresh(): void
    {
        $lock = $this->db->query("SELECT GET_LOCK('nova_salud_alertas_refresh', 5)")->fetchColumn();
        if ((int) $lock !== 1) { return; }
        try {
            $this->db->beginTransaction();
            $this->db->exec("UPDATE alertas a SET a.estado='atendida', a.atendida_en=NOW() WHERE a.estado='activa' AND a.lote_id IS NULL AND a.tipo IN ('stock_bajo','sin_stock') AND NOT EXISTS (SELECT 1 FROM (SELECT p.id, COALESCE(SUM(e.cantidad_disponible),0) stock, p.stock_minimo FROM productos p LEFT JOIN lotes l ON l.producto_id=p.id LEFT JOIN existencias_lote e ON e.lote_id=l.id GROUP BY p.id) s WHERE s.id=a.producto_id AND ((a.tipo='sin_stock' AND s.stock<=0) OR (a.tipo='stock_bajo' AND s.stock>0 AND s.stock<=s.stock_minimo)))");
            $this->db->exec("UPDATE alertas a JOIN lotes l ON l.id=a.lote_id LEFT JOIN existencias_lote e ON e.lote_id=l.id SET a.estado='atendida', a.atendida_en=NOW() WHERE a.estado='activa' AND a.tipo IN ('proximo_vencimiento','vencido') AND (COALESCE(e.cantidad_disponible,0)<=0 OR (a.tipo='vencido' AND (l.fecha_vencimiento IS NULL OR l.fecha_vencimiento>=CURRENT_DATE())) OR (a.tipo='proximo_vencimiento' AND (l.fecha_vencimiento IS NULL OR l.fecha_vencimiento<CURRENT_DATE() OR l.fecha_vencimiento>DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY))))");
            $products = $this->db->query("SELECT p.id, p.stock_minimo, COALESCE(SUM(e.cantidad_disponible),0) stock FROM productos p LEFT JOIN lotes l ON l.producto_id=p.id LEFT JOIN existencias_lote e ON e.lote_id=l.id WHERE p.activo=1 GROUP BY p.id, p.stock_minimo HAVING stock<=p.stock_minimo")->fetchAll();
            $find = $this->db->prepare("SELECT id FROM alertas WHERE producto_id=:producto AND lote_id IS NULL AND tipo=:tipo AND estado='activa' LIMIT 1");
            $insert = $this->db->prepare('INSERT INTO alertas (producto_id, tipo, mensaje) VALUES (:producto, :tipo, :mensaje)');
            foreach ($products as $product) {
                $type = (float) $product['stock'] <= 0 ? 'sin_stock' : 'stock_bajo';
                $find = $this->db->prepare("SELECT id FROM alertas WHERE producto_id=:producto AND lote_id IS NULL AND tipo=:tipo AND (estado='activa' OR atendida_por IS NOT NULL) LIMIT 1");
                $find->execute(['producto' => $product['id'], 'tipo' => $type]);
                if (!$find->fetchColumn()) { $insert->execute(['producto' => $product['id'], 'tipo' => $type, 'mensaje' => $type === 'sin_stock' ? 'El producto no tiene existencias.' : 'Las existencias están en el mínimo o por debajo.']); }
            }
            $lots = $this->db->query("SELECT l.id lote_id, l.producto_id, l.fecha_vencimiento, e.cantidad_disponible FROM lotes l JOIN existencias_lote e ON e.lote_id=l.id WHERE e.cantidad_disponible>0 AND l.fecha_vencimiento IS NOT NULL AND l.fecha_vencimiento<=DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)")->fetchAll();
            $findLot = $this->db->prepare("SELECT id FROM alertas WHERE producto_id=:producto AND lote_id=:lote AND tipo=:tipo AND estado='activa' LIMIT 1");
            $insertLot = $this->db->prepare('INSERT INTO alertas (producto_id, lote_id, tipo, mensaje) VALUES (:producto, :lote, :tipo, :mensaje)');
            foreach ($lots as $lot) {
                $expired = $lot['fecha_vencimiento'] < date('Y-m-d');
                $type = $expired ? 'vencido' : 'proximo_vencimiento';
                $findLot = $this->db->prepare("SELECT id FROM alertas WHERE producto_id=:producto AND lote_id=:lote AND tipo=:tipo AND (estado='activa' OR atendida_por IS NOT NULL) LIMIT 1");
                $findLot->execute(['producto' => $lot['producto_id'], 'lote' => $lot['lote_id'], 'tipo' => $type]);
                if (!$findLot->fetchColumn()) { $insertLot->execute(['producto' => $lot['producto_id'], 'lote' => $lot['lote_id'], 'tipo' => $type, 'mensaje' => $expired ? 'El lote está vencido y conserva existencias.' : 'El lote vencerá dentro de los próximos 30 días.']); }
            }
            $this->db->commit();
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $exception;
        } finally { $this->db->query("SELECT RELEASE_LOCK('nova_salud_alertas_refresh')"); }
    }

    public function all(string $status = ''): array
    {
        $statement = $this->db->prepare('SELECT a.*, p.codigo, p.nombre producto, l.codigo_lote, CONCAT(u.nombres, \' \', u.apellidos) atendida_por_nombre FROM alertas a JOIN productos p ON p.id=a.producto_id LEFT JOIN lotes l ON l.id=a.lote_id LEFT JOIN usuarios u ON u.id=a.atendida_por WHERE (:estado_vacio=1 OR a.estado=:estado) ORDER BY FIELD(a.estado, \'activa\', \'atendida\', \'descartada\'), a.creada_en DESC LIMIT 500');
        $statement->execute(['estado_vacio' => $status === '' ? 1 : 0, 'estado' => $status]);
        return $statement->fetchAll();
    }

    public function setStatus(int $id, string $status, int $userId): bool
    {
        $statement = $this->db->prepare("UPDATE alertas SET estado=:estado, atendida_por=:usuario, atendida_en=NOW() WHERE id=:id AND estado='activa'");
        $statement->execute(['estado' => $status, 'usuario' => $userId, 'id' => $id]);
        return $statement->rowCount() === 1;
    }
}
