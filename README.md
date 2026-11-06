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

## Estructura

- `app/`: controladores, modelos, vistas y componentes MVC.
- `config/`: configuración de la aplicación y base de datos.
- `database/`: futuras migraciones y datos iniciales.
- `public/`: único directorio que debe exponer Apache.
- `resources/`: archivos fuente de estilos.
- `routes/`: definición de rutas.
- `storage/`: archivos internos y registros.
