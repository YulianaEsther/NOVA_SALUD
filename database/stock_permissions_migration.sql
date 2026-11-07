-- Ejecutar una vez en bases existentes para habilitar el módulo de existencias.
INSERT IGNORE INTO permisos (codigo, descripcion) VALUES
('existencias.ver', 'Consultar existencias por lote'),
('existencias.ajustar', 'Registrar ajustes de entrada o salida en lotes');

INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permisos p
WHERE r.nombre = 'Administrador'
  AND p.codigo IN ('existencias.ver', 'existencias.ajustar');
