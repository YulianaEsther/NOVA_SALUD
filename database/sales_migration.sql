-- Ejecutar una sola vez en bases existentes para habilitar ventas y descuentos auditables.
ALTER TABLE detalle_ventas
    ADD COLUMN descuento_unitario DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER precio_unitario;

INSERT IGNORE INTO permisos (codigo, descripcion) VALUES
('ventas.ver', 'Consultar ventas'),
('ventas.crear', 'Registrar ventas'),
('ventas.anular', 'Anular ventas y restaurar existencias'),
('ventas.descuento', 'Autorizar descuentos sobre el precio de venta');

INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT r.id, p.id
FROM roles r CROSS JOIN permisos p
WHERE r.nombre = 'Administrador'
  AND p.codigo IN ('ventas.ver', 'ventas.crear', 'ventas.anular', 'ventas.descuento');
