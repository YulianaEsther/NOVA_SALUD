<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /** @return list<array<string, mixed>> */
    public function all(string $search = '', ?int $categoryId = null): array
    {
        $sql = 'SELECT p.id, p.codigo, p.nombre, p.unidad_medida, p.precio_venta,
                       p.stock_minimo, p.activo, c.nombre AS categoria,
                       m.nombre AS marca, COALESCE(s.stock_actual, 0) AS stock_actual
                FROM productos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                LEFT JOIN marcas m ON m.id = p.marca_id
                LEFT JOIN vista_stock_productos s ON s.producto_id = p.id
                WHERE p.activo = 1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (p.codigo LIKE :search_code OR p.nombre LIKE :search_name)';
            $params['search_code'] = '%' . $search . '%';
            $params['search_name'] = '%' . $search . '%';
        }
        if ($categoryId !== null) {
            $sql .= ' AND p.categoria_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $sql .= ' ORDER BY p.nombre ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function findActive(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, categoria_id, marca_id, codigo, nombre, descripcion,
                    unidad_medida, precio_venta, stock_minimo, requiere_lote
             FROM productos WHERE id = :id AND activo = 1 LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $product = $statement->fetch();
        return $product ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function categories(): array
    {
        return $this->db->query('SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre')->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function brands(): array
    {
        return $this->db->query('SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre')->fetchAll();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO productos
                (categoria_id, marca_id, codigo, nombre, descripcion, unidad_medida,
                 precio_venta, stock_minimo, requiere_lote)
             VALUES
                (:categoria_id, :marca_id, :codigo, :nombre, :descripcion, :unidad_medida,
                 :precio_venta, :stock_minimo, :requiere_lote)'
        );
        $statement->execute($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $statement = $this->db->prepare(
            'UPDATE productos SET
                categoria_id = :categoria_id,
                marca_id = :marca_id,
                codigo = :codigo,
                nombre = :nombre,
                descripcion = :descripcion,
                unidad_medida = :unidad_medida,
                precio_venta = :precio_venta,
                stock_minimo = :stock_minimo,
                requiere_lote = :requiere_lote
             WHERE id = :id AND activo = 1'
        );
        $statement->execute($data);
        return $statement->rowCount() > 0 || $this->findActive($id) !== null;
    }

    public function deactivate(int $id): bool
    {
        $statement = $this->db->prepare('UPDATE productos SET activo = 0 WHERE id = :id AND activo = 1');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() === 1;
    }
}
