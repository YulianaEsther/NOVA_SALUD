-- Ejecutar después de importar nova_salud.sql.
-- Crea el catálogo de permisos y el rol inicial, pero no crea usuarios ni contraseñas.
INSERT IGNORE INTO permisos (codigo, descripcion) VALUES
('dashboard.ver', 'Acceder al inicio del sistema'),
('productos.ver', 'Consultar productos'), ('productos.crear', 'Registrar productos'), ('productos.editar', 'Editar productos'), ('productos.desactivar', 'Desactivar productos'),
('categorias.ver', 'Consultar categorías'), ('categorias.crear', 'Registrar categorías'), ('categorias.editar', 'Editar categorías'), ('categorias.activar', 'Activar categorías'), ('categorias.desactivar', 'Desactivar categorías'),
('clientes.ver', 'Consultar clientes'), ('clientes.crear', 'Registrar clientes'), ('clientes.editar', 'Editar clientes'), ('clientes.activar', 'Activar clientes'), ('clientes.desactivar', 'Desactivar clientes'),
('lotes.ver', 'Consultar lotes'), ('lotes.crear', 'Registrar lotes'), ('lotes.editar', 'Editar lotes'), ('lotes.eliminar', 'Eliminar lotes sin existencias ni historial'),
('marcas.ver', 'Consultar marcas'), ('marcas.crear', 'Registrar marcas'), ('marcas.editar', 'Editar marcas'), ('marcas.activar', 'Activar marcas'), ('marcas.desactivar', 'Desactivar marcas'),
('usuarios.ver', 'Consultar usuarios'), ('usuarios.crear', 'Registrar usuarios'), ('usuarios.editar', 'Editar usuarios'), ('usuarios.activar', 'Activar usuarios'), ('usuarios.desactivar', 'Desactivar usuarios'),
('roles.ver', 'Consultar roles'), ('roles.crear', 'Registrar roles'), ('roles.editar', 'Editar roles'), ('roles.activar', 'Activar roles'), ('roles.desactivar', 'Desactivar roles'), ('roles.asignar_permisos', 'Asignar permisos a roles'),
('proveedores.ver', 'Consultar proveedores'), ('proveedores.crear', 'Registrar proveedores'), ('proveedores.editar', 'Editar proveedores'), ('proveedores.activar', 'Activar proveedores'), ('proveedores.desactivar', 'Desactivar proveedores');

INSERT IGNORE INTO permisos (codigo, descripcion) VALUES
('compras.ver', 'Consultar compras'), ('compras.crear', 'Registrar compras'), ('compras.anular', 'Anular compras y revertir su stock');

INSERT IGNORE INTO roles (nombre, descripcion, activo)
VALUES ('Administrador', 'Rol inicial con acceso completo, ajusta sus permisos según la operación.', 1);

INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permisos p
WHERE r.nombre = 'Administrador';
