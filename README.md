# NOVA SALUD

Base inicial de la aplicación PHP con MVC, PDO y Tailwind CSS.

## Requisitos

- XAMPP con PHP 8.x, Apache y MySQL 8.x.
- Node.js y npm solo para compilar Tailwind CSS.

## Configuración local

1. Copia `.env.example` como `.env` y completa los datos locales de MySQL.
2. Crea en MySQL la base indicada en `DB_NAME` (por defecto sugerido: `nova_salud`).
3. Configura Apache para que el `DocumentRoot` del sitio sea la carpeta `public/` del proyecto. No publiques la raíz del repositorio.
4. Desde la raíz del proyecto ejecuta `npm install` y luego `npm run css:build`.
5. Abre el sitio mediante el host local configurado en Apache.

La conexión PDO se inicializa al llamar `App\\Core\\Database::connection()`; no se conecta a la base durante la página inicial.
En desarrollo se puede activar `APP_DEBUG=true`. Antes de publicar, establece `APP_ENV=production` y `APP_DEBUG=false`.

## Base de datos

1. En phpMyAdmin crea la base `nova_salud` con cotejamiento `utf8mb4_unicode_ci` (o usa el nombre configurado en `DB_NAME`).
2. Selecciona esa base, abre la pestaña **Importar** y carga `database/nova_salud.sql`.
3. Importa también `database/permissions_seed.sql`. Crea el catálogo de permisos y el rol Administrador, pero no genera credenciales.
4. Si la base ya existía antes de añadir autenticación, importa `database/auth_migration.sql` para crear la tabla de intentos de acceso.
5. Abre `/configuracion/inicial` y crea el primer usuario administrador con una contraseña propia de al menos 12 caracteres. El asistente se cierra después de crear el primer usuario.
6. Las compras y ventas deben actualizar saldo de lote, encabezado/detalle y movimiento dentro de una sola transacción PDO.

## Módulo de productos

- Abre `http://nova-salud.local/productos` (o la URL configurada en `APP_URL`).
- Para registrar un producto debe existir al menos una categoría activa; puedes registrar las categorías confirmadas por el negocio en phpMyAdmin, tabla `categorias`.
- El botón **Desactivar** aplica borrado lógico para preservar el historial.
- El acceso y las acciones están protegidos por permisos asignados al rol del usuario.

## Módulo de categorías

- Abre `http://nova-salud.local/categorias` (o la URL configurada en `APP_URL`).
- Permite registrar, consultar, buscar, editar, desactivar y reactivar categorías.
- La desactivación es lógica; conserva los productos asociados y la categoría puede reactivarse.

## Otros módulos administrativos

- `/clientes`, `/proveedores`, `/marcas`, `/usuarios` y `/roles` permiten crear, consultar, editar y activar/desactivar registros.
- `/lotes` permite registrar lotes vinculados a un producto y compra, consultar sus existencias y editar sus datos. Un lote se crea con saldo cero; las existencias se registran por compras. Solo se elimina si no tiene saldo ni relaciones de inventario.
- Las contraseñas nuevas o modificadas se almacenan con `password_hash()`; en edición, dejar la contraseña vacía conserva la existente.
- Los roles permiten guardar nombre, descripción y permisos asignados.
- La autenticación, la asignación de permisos y la autorización por rol protegen las rutas administrativas.
- El sistema bloquea por 15 minutos el acceso de una combinación usuario/IP tras cinco contraseñas incorrectas.
- Las sesiones se regeneran al autenticar y caducan tras 30 minutos sin actividad.

## Estructura

- `app/`: controladores, modelos, vistas y componentes MVC.
- `config/`: configuración de la aplicación y base de datos.
- `database/`: futuras migraciones y datos iniciales.
- `public/`: único directorio que debe exponer Apache.
- `resources/`: archivos fuente de estilos.
- `routes/`: definición de rutas.
- `storage/`: archivos internos y registros.
