-- Ejecutar una vez en bases existentes para habilitar la consulta de movimientos.
INSERT IGNORE INTO permisos (codigo, descripcion)
VALUES ('movimientos.ver', 'Consultar el historial de movimientos de inventario');

INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permisos p
WHERE r.nombre = 'Administrador' AND p.codigo = 'movimientos.ver';
