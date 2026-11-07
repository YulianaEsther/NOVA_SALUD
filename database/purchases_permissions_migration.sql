-- Ejecutar una sola vez en bases existentes para habilitar el módulo de compras.
INSERT IGNORE INTO permisos (codigo, descripcion) VALUES
('compras.ver', 'Consultar compras'),
('compras.crear', 'Registrar compras'),
('compras.anular', 'Anular compras y revertir su stock');

-- Conserva el acceso completo inicial del rol Administrador.
INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permisos p
WHERE r.nombre = 'Administrador' AND p.codigo IN ('compras.ver', 'compras.crear', 'compras.anular');
