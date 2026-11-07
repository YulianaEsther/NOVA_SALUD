-- NOVA SALUD: esquema inicial para MySQL 8.x
-- Importar en una base vacía desde phpMyAdmin.
-- Los movimientos de inventario deben crearse en la misma transacción
-- que la compra o venta que los origina.

SET NAMES utf8mb4;
SET time_zone = '-05:00';

CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(60) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permisos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permisos_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rol_permisos (
    rol_id BIGINT UNSIGNED NOT NULL,
    permiso_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rol_permisos_rol FOREIGN KEY (rol_id) REFERENCES roles (id),
    CONSTRAINT fk_rol_permisos_permiso FOREIGN KEY (permiso_id) REFERENCES permisos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    rol_id BIGINT UNSIGNED NOT NULL,
    nombres VARCHAR(120) NOT NULL,
    apellidos VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    usuario VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL COMMENT 'Guardar hash generado con password_hash() de PHP',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_acceso DATETIME NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_usuario (usuario),
    KEY idx_usuarios_rol_activo (rol_id, activo),
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuario_permisos (
    usuario_id BIGINT UNSIGNED NOT NULL,
    permiso_id BIGINT UNSIGNED NOT NULL,
    permitido TINYINT(1) NOT NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, permiso_id),
    KEY idx_usuario_permisos_permiso (permiso_id),
    CONSTRAINT fk_usuario_permisos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_usuario_permisos_permiso FOREIGN KEY (permiso_id) REFERENCES permisos (id) ON DELETE CASCADE,
    CONSTRAINT chk_usuario_permisos_permitido CHECK (permitido IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marca es opcional mientras NOVA SALUD confirma si necesita gestionar laboratorios.
CREATE TABLE IF NOT EXISTS marcas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(120) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_marcas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proveedores (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(160) NOT NULL,
    documento VARCHAR(30) NULL,
    telefono VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    direccion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_proveedores_documento (documento),
    KEY idx_proveedores_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombres VARCHAR(120) NOT NULL,
    apellidos VARCHAR(120) NULL,
    documento VARCHAR(30) NULL,
    telefono VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_documento (documento),
    KEY idx_clientes_nombre (nombres, apellidos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id BIGINT UNSIGNED NOT NULL,
    marca_id BIGINT UNSIGNED NULL,
    codigo VARCHAR(80) NOT NULL,
    nombre VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    unidad_medida VARCHAR(40) NOT NULL DEFAULT 'unidad',
    precio_venta DECIMAL(12,2) NOT NULL,
    stock_minimo DECIMAL(12,3) NOT NULL DEFAULT 0,
    requiere_lote TINYINT(1) NOT NULL DEFAULT 1,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_productos_codigo (codigo),
    KEY idx_productos_nombre (nombre),
    KEY idx_productos_categoria_activo (categoria_id, activo),
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id),
    CONSTRAINT fk_productos_marca FOREIGN KEY (marca_id) REFERENCES marcas (id),
    CONSTRAINT chk_productos_precio CHECK (precio_venta >= 0),
    CONSTRAINT chk_productos_stock_minimo CHECK (stock_minimo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS compras (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    proveedor_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    numero_documento VARCHAR(80) NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('registrada', 'anulada') NOT NULL DEFAULT 'registrada',
    observaciones VARCHAR(500) NULL,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_compras_fecha (fecha),
    KEY idx_compras_proveedor (proveedor_id, fecha),
    CONSTRAINT fk_compras_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores (id),
    CONSTRAINT fk_compras_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
    CONSTRAINT chk_compras_total CHECK (total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cada lote pertenece al producto y a la compra en la que se recibió.
CREATE TABLE IF NOT EXISTS lotes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    compra_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,
    codigo_lote VARCHAR(100) NOT NULL,
    fecha_vencimiento DATE NULL,
    costo_unitario DECIMAL(12,4) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lotes_producto_codigo (producto_id, codigo_lote),
    KEY idx_lotes_vencimiento (fecha_vencimiento),
    KEY idx_lotes_compra (compra_id),
    CONSTRAINT fk_lotes_compra FOREIGN KEY (compra_id) REFERENCES compras (id),
    CONSTRAINT fk_lotes_producto FOREIGN KEY (producto_id) REFERENCES productos (id),
    CONSTRAINT chk_lotes_costo CHECK (costo_unitario >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Saldo actual por lote. Las ventas deben descontar con una actualización
-- condicional (cantidad_disponible >= cantidad solicitada) dentro de una transacción.
CREATE TABLE IF NOT EXISTS existencias_lote (
    lote_id BIGINT UNSIGNED NOT NULL,
    cantidad_disponible DECIMAL(12,3) NOT NULL DEFAULT 0,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (lote_id),
    CONSTRAINT fk_existencias_lote_lote FOREIGN KEY (lote_id) REFERENCES lotes (id),
    CONSTRAINT chk_existencias_lote_no_negativa CHECK (cantidad_disponible >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detalle_compras (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    compra_id BIGINT UNSIGNED NOT NULL,
    lote_id BIGINT UNSIGNED NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL,
    costo_unitario DECIMAL(12,4) NOT NULL,
    subtotal DECIMAL(14,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_detalle_compras_compra (compra_id),
    KEY idx_detalle_compras_lote (lote_id),
    CONSTRAINT fk_detalle_compras_compra FOREIGN KEY (compra_id) REFERENCES compras (id),
    CONSTRAINT fk_detalle_compras_lote FOREIGN KEY (lote_id) REFERENCES lotes (id),
    CONSTRAINT chk_detalle_compras_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_detalle_compras_costo CHECK (costo_unitario >= 0),
    CONSTRAINT chk_detalle_compras_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ventas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cliente_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    numero_documento VARCHAR(80) NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('confirmada', 'anulada') NOT NULL DEFAULT 'confirmada',
    metodo_pago VARCHAR(40) NULL,
    observaciones VARCHAR(500) NULL,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ventas_fecha (fecha),
    KEY idx_ventas_cliente (cliente_id, fecha),
    CONSTRAINT fk_ventas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id),
    CONSTRAINT fk_ventas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
    CONSTRAINT chk_ventas_total CHECK (total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detalle_ventas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    venta_id BIGINT UNSIGNED NOT NULL,
    lote_id BIGINT UNSIGNED NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(14,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_detalle_ventas_venta (venta_id),
    KEY idx_detalle_ventas_lote (lote_id),
    CONSTRAINT fk_detalle_ventas_venta FOREIGN KEY (venta_id) REFERENCES ventas (id),
    CONSTRAINT fk_detalle_ventas_lote FOREIGN KEY (lote_id) REFERENCES lotes (id),
    CONSTRAINT chk_detalle_ventas_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_detalle_ventas_precio CHECK (precio_unitario >= 0),
    CONSTRAINT chk_detalle_ventas_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Libro de movimientos. Entradas positivas y salidas negativas.
-- Historial inmutable de entradas y salidas. Actualizar también existencias_lote
-- en la misma transacción que este registro.
CREATE TABLE IF NOT EXISTS movimientos_inventario (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    producto_id BIGINT UNSIGNED NOT NULL,
    lote_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    compra_id BIGINT UNSIGNED NULL,
    venta_id BIGINT UNSIGNED NULL,
    tipo ENUM('compra', 'venta', 'ajuste_entrada', 'ajuste_salida', 'anulacion') NOT NULL,
    cantidad_delta DECIMAL(12,3) NOT NULL,
    motivo VARCHAR(255) NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_movimientos_producto_fecha (producto_id, creado_en),
    KEY idx_movimientos_lote_fecha (lote_id, creado_en),
    KEY idx_movimientos_compra (compra_id),
    KEY idx_movimientos_venta (venta_id),
    CONSTRAINT fk_movimientos_producto FOREIGN KEY (producto_id) REFERENCES productos (id),
    CONSTRAINT fk_movimientos_lote FOREIGN KEY (lote_id) REFERENCES lotes (id),
    CONSTRAINT fk_movimientos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
    CONSTRAINT fk_movimientos_compra FOREIGN KEY (compra_id) REFERENCES compras (id),
    CONSTRAINT fk_movimientos_venta FOREIGN KEY (venta_id) REFERENCES ventas (id),
    CONSTRAINT chk_movimientos_cantidad CHECK (cantidad_delta <> 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS alertas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    producto_id BIGINT UNSIGNED NOT NULL,
    lote_id BIGINT UNSIGNED NULL,
    tipo ENUM('stock_bajo', 'sin_stock', 'proximo_vencimiento', 'vencido') NOT NULL,
    mensaje VARCHAR(255) NOT NULL,
    estado ENUM('activa', 'atendida', 'descartada') NOT NULL DEFAULT 'activa',
    creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atendida_por BIGINT UNSIGNED NULL,
    atendida_en DATETIME NULL,
    PRIMARY KEY (id),
    KEY idx_alertas_estado_tipo (estado, tipo),
    KEY idx_alertas_producto (producto_id, creada_en),
    CONSTRAINT fk_alertas_producto FOREIGN KEY (producto_id) REFERENCES productos (id),
    CONSTRAINT fk_alertas_lote FOREIGN KEY (lote_id) REFERENCES lotes (id),
    CONSTRAINT fk_alertas_usuario FOREIGN KEY (atendida_por) REFERENCES usuarios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auditoria (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id BIGINT UNSIGNED NULL,
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(100) NOT NULL,
    entidad_id BIGINT UNSIGNED NULL,
    detalle JSON NULL,
    ip VARCHAR(45) NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_auditoria_entidad (entidad, entidad_id, creado_en),
    KEY idx_auditoria_usuario_fecha (usuario_id, creado_en),
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intentos_acceso (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(190) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    intentos TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_intentos_email_ip (email, ip),
    KEY idx_intentos_bloqueo (bloqueado_hasta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vista de existencias calculadas desde el libro de movimientos.
CREATE OR REPLACE VIEW vista_stock_productos AS
SELECT
    p.id AS producto_id,
    p.codigo,
    p.nombre,
    p.stock_minimo,
    COALESCE(SUM(el.cantidad_disponible), 0) AS stock_actual
FROM productos p
LEFT JOIN lotes l ON l.producto_id = p.id
LEFT JOIN existencias_lote el ON el.lote_id = l.id
GROUP BY p.id, p.codigo, p.nombre, p.stock_minimo;
