-- Ejecutar una sola vez en bases existentes para habilitar alertas y auditoría.
-- Las tablas alertas y auditoria ya forman parte de nova_salud.sql.
INSERT IGNORE INTO permisos (codigo, descripcion) VALUES
('alertas.ver', 'Consultar alertas de inventario'),
('alertas.atender', 'Marcar alertas como atendidas'),
('alertas.descartar', 'Descartar alertas de inventario'),
('auditoria.ver', 'Consultar el historial de auditoría');

INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permisos p
WHERE r.nombre = 'Administrador'
  AND p.codigo IN ('alertas.ver', 'alertas.atender', 'alertas.descartar', 'auditoria.ver');
