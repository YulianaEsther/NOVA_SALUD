<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Category
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** @return list<array<string, mixed>> */
    public function all(string $search = ''): array
    {
        $sql = 'SELECT c.id, c.nombre, c.descripcion, c.activo,
                       COUNT(p.id) AS productos_asociados
                FROM categorias c
                LEFT JOIN productos p ON p.categoria_id = c.id
                WHERE (:search_empty = 1 OR c.nombre LIKE :search_name OR c.descripcion LIKE :search_description)
                GROUP BY c.id, c.nombre, c.descripcion, c.activo
                ORDER BY c.activo DESC, c.nombre ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'search_empty' => $search === '' ? 1 : 0,
            'search_name' => '%' . $search . '%',
            'search_description' => '%' . $search . '%',
        ]);
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findActive(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, nombre, descripcion FROM categorias WHERE id = :id AND activo = 1 LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();
        return $category ?: null;
    }

    /** @param array{nombre: string, descripcion: ?string} $data */
    public function create(array $data): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)'
        );
        $statement->execute($data);
    }

    /** @param array{nombre: string, descripcion: ?string} $data */
    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $statement = $this->db->prepare(
            'UPDATE categorias SET nombre = :nombre, descripcion = :descripcion
             WHERE id = :id AND activo = 1'
        );
        $statement->execute($data);
        return $statement->rowCount() > 0 || $this->findActive($id) !== null;
    }

    public function setActive(int $id, bool $active): bool
    {
        $statement = $this->db->prepare(
            'UPDATE categorias SET activo = :activo WHERE id = :id AND activo <> :current_state'
        );
        $state = $active ? 1 : 0;
        $statement->execute(['activo' => $state, 'id' => $id, 'current_state' => $state]);
        return $statement->rowCount() === 1;
    }
}
