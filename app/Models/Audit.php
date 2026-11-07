<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Audit
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    /** @param array{q:string,accion:string,desde:string,hasta:string} $filters */
    public function search(array $filters): array
    {
        $where = []; $params = [];
        if ($filters['q'] !== '') { $where[] = '(a.entidad LIKE :entidad OR a.accion LIKE :accion_q OR CAST(a.entidad_id AS CHAR)=:id OR CONCAT(u.nombres, \' \', u.apellidos) LIKE :usuario)'; $like='%'.$filters['q'].'%'; $params += ['entidad'=>$like,'accion_q'=>$like,'id'=>$filters['q'],'usuario'=>$like]; }
        if ($filters['accion'] !== '') { $where[]='a.accion=:accion'; $params['accion']=$filters['accion']; }
        if ($filters['desde'] !== '') { $where[]='a.creado_en>=:desde'; $params['desde']=$filters['desde'].' 00:00:00'; }
        if ($filters['hasta'] !== '') { $where[]='a.creado_en<:hasta'; $params['hasta']=(new \DateTimeImmutable($filters['hasta']))->modify('+1 day')->format('Y-m-d').' 00:00:00'; }
        $sql='SELECT a.*, CONCAT(u.nombres, \' \', u.apellidos) usuario FROM auditoria a LEFT JOIN usuarios u ON u.id=a.usuario_id'.($where ? ' WHERE '.implode(' AND ',$where) : '').' ORDER BY a.creado_en DESC,a.id DESC LIMIT 500';
        $stmt=$this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll();
    }
}
