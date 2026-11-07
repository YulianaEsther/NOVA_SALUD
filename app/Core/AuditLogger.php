<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class AuditLogger
{
    /** @param array<string, scalar|null> $detail */
    public static function record(PDO $db, ?int $userId, string $action, string $entity, ?int $entityId = null, array $detail = []): void
    {
        $statement = $db->prepare('INSERT INTO auditoria (usuario_id, accion, entidad, entidad_id, detalle, ip) VALUES (:usuario, :accion, :entidad, :entidad_id, :detalle, :ip)');
        $statement->execute([
            'usuario' => $userId,
            'accion' => mb_substr($action, 0, 100),
            'entidad' => mb_substr($entity, 0, 100),
            'entidad_id' => $entityId,
            'detalle' => $detail === [] ? null : json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'ip' => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
        ]);
    }
}
