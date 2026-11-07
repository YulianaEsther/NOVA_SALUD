-- Ejecutar una vez en bases existentes para habilitar permisos individuales.
-- permitido = 1 concede el permiso; permitido = 0 lo revoca aunque el rol lo tenga.
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
