<?php
declare(strict_types=1);

return [
    'clientes' => [
        'title' => 'Clientes', 'table' => 'clientes', 'active_column' => 'activo',
        'search_columns' => ['nombres', 'apellidos', 'documento'],
        'columns' => ['nombres' => 'Nombres', 'apellidos' => 'Apellidos', 'documento' => 'Documento', 'telefono' => 'Teléfono', 'email' => 'Correo'],
        'fields' => [
            ['key' => 'nombres', 'label' => 'Nombres', 'type' => 'text', 'required' => true, 'max' => 120],
            ['key' => 'apellidos', 'label' => 'Apellidos', 'type' => 'text', 'required' => false, 'max' => 120],
            ['key' => 'documento', 'label' => 'Documento', 'type' => 'text', 'required' => false, 'max' => 30],
            ['key' => 'telefono', 'label' => 'Teléfono', 'type' => 'text', 'required' => false, 'max' => 30],
            ['key' => 'email', 'label' => 'Correo electrónico', 'type' => 'email', 'required' => false, 'max' => 190],
        ],
    ],
    'proveedores' => [
        'title' => 'Proveedores', 'table' => 'proveedores', 'active_column' => 'activo',
        'search_columns' => ['nombre', 'documento', 'email'],
        'columns' => ['nombre' => 'Nombre / razón social', 'documento' => 'Documento', 'telefono' => 'Teléfono', 'email' => 'Correo'],
        'fields' => [
            ['key' => 'nombre', 'label' => 'Nombre o razón social', 'type' => 'text', 'required' => true, 'max' => 160],
            ['key' => 'documento', 'label' => 'Documento', 'type' => 'text', 'required' => false, 'max' => 30],
            ['key' => 'telefono', 'label' => 'Teléfono', 'type' => 'text', 'required' => false, 'max' => 30],
            ['key' => 'email', 'label' => 'Correo electrónico', 'type' => 'email', 'required' => false, 'max' => 190],
            ['key' => 'direccion', 'label' => 'Dirección', 'type' => 'textarea', 'required' => false, 'max' => 255],
        ],
    ],
    'marcas' => [
        'title' => 'Marcas', 'table' => 'marcas', 'active_column' => 'activo',
        'search_columns' => ['nombre'], 'columns' => ['nombre' => 'Nombre'],
        'fields' => [
            ['key' => 'nombre', 'label' => 'Nombre de la marca', 'type' => 'text', 'required' => true, 'max' => 120],
        ],
    ],
    'roles' => [
        'title' => 'Roles', 'table' => 'roles', 'active_column' => 'activo',
        'search_columns' => ['nombre', 'descripcion'],
        'columns' => ['nombre' => 'Rol', 'descripcion' => 'Descripción'],
        'fields' => [
            ['key' => 'nombre', 'label' => 'Nombre del rol', 'type' => 'text', 'required' => true, 'max' => 60],
            ['key' => 'descripcion', 'label' => 'Descripción', 'type' => 'textarea', 'required' => false, 'max' => 255],
            ['key' => 'permisos', 'label' => 'Permisos asignados', 'type' => 'permissions', 'required' => false],
        ],
    ],
    'usuarios' => [
        'title' => 'Usuarios', 'table' => 'usuarios', 'active_column' => 'activo',
        'search_columns' => ['u.usuario', 'u.nombres', 'u.apellidos', 'u.email'],
        'list_sql' => 'SELECT u.id, u.usuario, u.nombres, u.apellidos, u.email, r.nombre AS rol, u.activo
                       FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id',
        'columns' => ['usuario' => 'Usuario', 'nombres' => 'Nombres', 'apellidos' => 'Apellidos', 'email' => 'Correo', 'rol' => 'Rol'],
        'fields' => [
            ['key' => 'rol_id', 'label' => 'Rol', 'type' => 'select', 'required' => true, 'options' => 'roles'],
            ['key' => 'nombres', 'label' => 'Nombres', 'type' => 'text', 'required' => true, 'max' => 120],
            ['key' => 'apellidos', 'label' => 'Apellidos', 'type' => 'text', 'required' => true, 'max' => 120],
            ['key' => 'email', 'label' => 'Correo electrónico', 'type' => 'email', 'required' => true, 'max' => 190],
            ['key' => 'usuario', 'label' => 'Nombre de usuario', 'type' => 'text', 'required' => true, 'max' => 80],
            ['key' => 'password', 'column' => 'password_hash', 'label' => 'Contraseña', 'type' => 'password', 'required' => true, 'max' => 128],
        ],
    ],
    'lotes' => [
        'title' => 'Lotes', 'table' => 'lotes', 'active_column' => null,
        'search_columns' => ['l.codigo_lote', 'p.nombre', 'p.codigo'],
        'list_sql' => 'SELECT l.id, l.codigo_lote, p.nombre AS producto, p.codigo AS codigo_producto,
                       l.fecha_vencimiento, l.costo_unitario, COALESCE(e.cantidad_disponible, 0) AS stock,
                       c.id AS compra_referencia
                       FROM lotes l INNER JOIN productos p ON p.id = l.producto_id
                       INNER JOIN compras c ON c.id = l.compra_id
                       LEFT JOIN existencias_lote e ON e.lote_id = l.id',
        'columns' => ['codigo_lote' => 'Lote', 'producto' => 'Producto', 'fecha_vencimiento' => 'Vencimiento', 'costo_unitario' => 'Costo unitario', 'stock' => 'Stock', 'compra_referencia' => 'Compra'],
        'fields' => [
            ['key' => 'compra_id', 'label' => 'Compra relacionada', 'type' => 'select', 'required' => true, 'options' => 'compras', 'editable_on_update' => false],
            ['key' => 'producto_id', 'label' => 'Producto', 'type' => 'select', 'required' => true, 'options' => 'productos', 'editable_on_update' => false],
            ['key' => 'codigo_lote', 'label' => 'Código del lote', 'type' => 'text', 'required' => true, 'max' => 100],
            ['key' => 'fecha_vencimiento', 'label' => 'Fecha de vencimiento', 'type' => 'date', 'required' => false],
            ['key' => 'costo_unitario', 'label' => 'Costo unitario', 'type' => 'decimal', 'required' => true, 'scale' => 4],
        ],
    ],
];
