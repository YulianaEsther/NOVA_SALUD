<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

final class AdminResource
{
    private PDO $db;
    /** @var array<string, mixed> */
    private array $definition;

    /** @param array<string, mixed> $definition */
    public function __construct(array $definition)
    {
        $this->definition = $definition;
        $this->db = Database::connection();
    }

    /** @return list<array<string, mixed>> */
    public function all(string $search = ''): array
    {
        $table = $this->definition['table'];
        $sql = $this->definition['list_sql'] ?? "SELECT * FROM {$table}";
        $where = [];
        $params = [];
        foreach ($this->definition['search_columns'] ?? [] as $index => $column) {
            $key = 'search_' . $index;
            $where[] = "{$column} LIKE :{$key}";
            $params[$key] = '%' . $search . '%';
        }
        if ($where !== []) {
            $sql .= ' WHERE (' . implode(' OR ', $where) . ')';
        }
        $sql .= isset($this->definition['order_by'])
            ? ' ORDER BY ' . $this->definition['order_by']
            : ' ORDER BY 1 DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM ' . $this->definition['table'] . ' WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return $row ?: null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, array $permissionIds = []): void
    {
        if ($this->definition['table'] === 'roles') {
            $this->db->beginTransaction();
            try {
                $this->insert($data);
                $this->syncRolePermissions((int) $this->db->lastInsertId(), $permissionIds);
                $this->db->commit();
            } catch (\Throwable $exception) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw $exception;
            }
            return;
        }
        if ($this->definition['table'] === 'lotes') {
            $this->db->beginTransaction();
            try {
                $this->insert($data);
                $lotId = (int) $this->db->lastInsertId();
                $stock = $this->db->prepare('INSERT INTO existencias_lote (lote_id, cantidad_disponible) VALUES (:id, 0)');
                $stock->execute(['id' => $lotId]);
                $this->db->commit();
            } catch (\Throwable $exception) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw $exception;
            }
            return;
        }
        $this->insert($data);
    }

    /** @param array<string, mixed> $data */
    private function insert(array $data): void
    {
        $columns = array_keys($data);
        $columnSql = implode(', ', $columns);
        $valueSql = implode(', ', array_map(static fn (string $column): string => ':' . $column, $columns));
        $statement = $this->db->prepare('INSERT INTO ' . $this->definition['table'] . " ({$columnSql}) VALUES ({$valueSql})");
        $statement->execute($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data, ?array $permissionIds = null): bool
    {
        if (in_array($this->definition['table'], ['roles', 'usuarios'], true)) {
            $this->db->beginTransaction();
            try {
                $this->updateRow($id, $data);
                if ($this->definition['table'] === 'roles') {
                    $this->syncRolePermissions($id, $permissionIds ?? []);
                } else {
                    $this->syncUserPermissions($id, $permissionIds ?? []);
                }
                $this->db->commit();
                return true;
            } catch (\Throwable $exception) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw $exception;
            }
        }
        return $this->updateRow($id, $data);
    }

    /** @param array<string, mixed> $data */
    private function updateRow(int $id, array $data): bool
    {
        if ($this->definition['table'] === 'lotes') {
            $editable = [];
            foreach ($this->definition['fields'] as $field) {
                if (($field['editable_on_update'] ?? true) && array_key_exists($field['key'], $data)) {
                    $editable[$field['key']] = $data[$field['key']];
                }
            }
            $data = $editable;
        }
        if ($data === []) {
            return $this->find($id) !== null;
        }
        $assignments = [];
        foreach (array_keys($data) as $column) {
            $assignments[] = $column . ' = :' . $column;
        }
        $data['id'] = $id;
        $statement = $this->db->prepare(
            'UPDATE ' . $this->definition['table'] . ' SET ' . implode(', ', $assignments) . ' WHERE id = :id'
        );
        $statement->execute($data);
        return $statement->rowCount() > 0 || $this->find($id) !== null;
    }

    /** @param list<int> $permissionIds */
    private function syncRolePermissions(int $roleId, array $permissionIds): void
    {
        $remove = $this->db->prepare('DELETE FROM rol_permisos WHERE rol_id = :role_id');
        $remove->execute(['role_id' => $roleId]);
        if ($permissionIds === []) {
            return;
        }
        $insert = $this->db->prepare('INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (:role_id, :permission_id)');
        foreach (array_unique($permissionIds) as $permissionId) {
            $insert->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
    }

    /** @return list<array<string, mixed>> */
    public function permissionOptions(): array
    {
        return $this->db->query('SELECT id, codigo, descripcion FROM permisos ORDER BY codigo')->fetchAll();
    }

    /** @return list<int> */
    public function rolePermissionIds(int $roleId): array
    {
        $statement = $this->db->prepare('SELECT permiso_id FROM rol_permisos WHERE rol_id = :id');
        $statement->execute(['id' => $roleId]);
        return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return list<int> */
    public function effectiveUserPermissionIds(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT p.id, rp.permiso_id AS role_permission_id, up.permitido
             FROM usuarios u
             CROSS JOIN permisos p
             LEFT JOIN rol_permisos rp ON rp.rol_id = u.rol_id AND rp.permiso_id = p.id
             LEFT JOIN usuario_permisos up ON up.usuario_id = u.id AND up.permiso_id = p.id
             WHERE u.id = :id'
        );
        $statement->execute(['id' => $userId]);
        $ids = [];
        foreach ($statement->fetchAll() as $row) {
            $granted = $row['permitido'] !== null ? (int) $row['permitido'] === 1 : $row['role_permission_id'] !== null;
            if ($granted) { $ids[] = (int) $row['id']; }
        }
        return $ids;
    }

    /** Save only deviations from the user's role, so inherited permissions remain dynamic. @param list<int> $permissionIds */
    private function syncUserPermissions(int $userId, array $permissionIds): void
    {
        $selected = array_fill_keys(array_unique($permissionIds), true);
        $role = $this->db->prepare('SELECT rol_id FROM usuarios WHERE id = :id');
        $role->execute(['id' => $userId]);
        $roleId = (int) $role->fetchColumn();
        $roleIds = array_fill_keys($this->rolePermissionIds($roleId), true);
        $remove = $this->db->prepare('DELETE FROM usuario_permisos WHERE usuario_id = :id');
        $remove->execute(['id' => $userId]);
        $insert = $this->db->prepare('INSERT INTO usuario_permisos (usuario_id, permiso_id, permitido) VALUES (:usuario, :permiso, :permitido)');
        foreach (array_unique(array_merge(array_keys($selected), array_keys($roleIds))) as $permissionId) {
            $hasRoleGrant = isset($roleIds[$permissionId]);
            $isSelected = isset($selected[$permissionId]);
            if ($hasRoleGrant !== $isSelected) {
                $insert->execute(['usuario' => $userId, 'permiso' => $permissionId, 'permitido' => $isSelected ? 1 : 0]);
            }
        }
    }

    public function setActive(int $id, bool $active): bool
    {
        $column = $this->definition['active_column'] ?? null;
        if ($column === null) {
            return false;
        }
        $state = $active ? 1 : 0;
        $statement = $this->db->prepare(
            'UPDATE ' . $this->definition['table'] . " SET {$column} = :state WHERE id = :id AND {$column} <> :current"
        );
        $statement->execute(['state' => $state, 'id' => $id, 'current' => $state]);
        return $statement->rowCount() === 1;
    }

    public function roleHasActiveUsers(int $id): bool
    {
        if ($this->definition['table'] !== 'roles') {
            return false;
        }
        $statement = $this->db->prepare('SELECT 1 FROM usuarios WHERE rol_id = :id AND activo = 1 LIMIT 1');
        $statement->execute(['id' => $id]);
        return (bool) $statement->fetchColumn();
    }

    public function isLastActiveAdministrator(int $id): bool
    {
        if ($this->definition['table'] !== 'usuarios') {
            return false;
        }
        $target = $this->db->prepare(
            "SELECT COUNT(*) FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             INNER JOIN permisos p ON p.codigo = 'roles.asignar_permisos'
             LEFT JOIN rol_permisos rp ON rp.rol_id = r.id AND rp.permiso_id = p.id
             LEFT JOIN usuario_permisos up ON up.usuario_id = u.id AND up.permiso_id = p.id
             WHERE u.id = :id AND u.activo = 1 AND r.activo = 1
               AND ((up.usuario_id IS NOT NULL AND up.permitido = 1)
                 OR (up.usuario_id IS NULL AND rp.permiso_id IS NOT NULL))"
        );
        $target->execute(['id' => $id]);
        if ((int) $target->fetchColumn() === 0) {
            return false;
        }
        $administrators = $this->db->query(
            "SELECT COUNT(DISTINCT u.id) FROM usuarios u
             INNER JOIN roles r ON r.id = u.rol_id
             INNER JOIN permisos p ON p.codigo = 'roles.asignar_permisos'
             LEFT JOIN rol_permisos rp ON rp.rol_id = r.id AND rp.permiso_id = p.id
             LEFT JOIN usuario_permisos up ON up.usuario_id = u.id AND up.permiso_id = p.id
             WHERE u.activo = 1 AND r.activo = 1
               AND ((up.usuario_id IS NOT NULL AND up.permitido = 1)
                 OR (up.usuario_id IS NULL AND rp.permiso_id IS NOT NULL))"
        );
        return (int) $administrators->fetchColumn() <= 1;
    }

    public function deleteLotIfUnused(int $id): bool
    {
        if ($this->definition['table'] !== 'lotes') {
            throw new RuntimeException('Eliminación física no permitida para este recurso.');
        }
        $this->db->beginTransaction();
        try {
            $lock = $this->db->prepare('SELECT id FROM lotes WHERE id = :id FOR UPDATE');
            $lock->execute(['id' => $id]);
            if (!$lock->fetch()) {
                $this->db->rollBack();
                return false;
            }
            $stock = $this->db->prepare('SELECT cantidad_disponible FROM existencias_lote WHERE lote_id = :id FOR UPDATE');
            $stock->execute(['id' => $id]);
            $quantity = $stock->fetchColumn();
            if ($quantity === false || (float) $quantity !== 0.0) {
                $this->db->rollBack();
                return false;
            }
            foreach (['detalle_compras', 'detalle_ventas', 'movimientos_inventario'] as $table) {
                $used = $this->db->prepare("SELECT 1 FROM {$table} WHERE lote_id = :id LIMIT 1");
                $used->execute(['id' => $id]);
                if ($used->fetchColumn()) {
                    $this->db->rollBack();
                    return false;
                }
            }
            $removeStock = $this->db->prepare('DELETE FROM existencias_lote WHERE lote_id = :id');
            $removeStock->execute(['id' => $id]);
            $removeLot = $this->db->prepare('DELETE FROM lotes WHERE id = :id');
            $removeLot->execute(['id' => $id]);
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    /** @return list<array{id: int, nombre: string}> */
    public function options(string $source): array
    {
        return match ($source) {
            'roles' => $this->db->query('SELECT id, nombre FROM roles WHERE activo = 1 ORDER BY nombre')->fetchAll(),
            'productos' => $this->db->query('SELECT id, nombre FROM productos WHERE activo = 1 ORDER BY nombre')->fetchAll(),
            'compras' => $this->db->query("SELECT id, CONCAT('Compra #', id, ' · ', DATE_FORMAT(fecha, '%d/%m/%Y')) AS nombre FROM compras WHERE estado = 'registrada' ORDER BY fecha DESC")->fetchAll(),
            default => [],
        };
    }
}
