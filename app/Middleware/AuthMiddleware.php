<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use PDO;

final class AuthMiddleware
{
    public function handle(string $method, string $uri): bool
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if (in_array($path, ['/login', '/configuracion/inicial'], true)) {
            return true;
        }

        $userId = filter_var($_SESSION['auth_user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId === null || $userId < 1) {
            $query = parse_url($uri, PHP_URL_QUERY);
            $_SESSION['intended_path'] = strtoupper($method) === 'GET'
                ? $path . (is_string($query) && $query !== '' ? '?' . $query : '')
                : '/';
            header('Location: ' . $this->baseUrl() . '/login', true, 303);
            return false;
        }
        $lastActivity = (int) ($_SESSION['auth_last_activity'] ?? 0);
        if ($lastActivity > 0 && time() - $lastActivity > 1800) {
            $this->clearAuthentication();
            header('Location: ' . $this->baseUrl() . '/login', true, 303);
            return false;
        }

        $db = Database::connection();
        $userQuery = $db->prepare(
            'SELECT u.id, u.rol_id, u.nombres, u.apellidos
             FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.id = :id AND u.activo = 1 AND r.activo = 1 LIMIT 1'
        );
        $userQuery->execute(['id' => $userId]);
        $user = $userQuery->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $this->clearAuthentication();
            header('Location: ' . $this->baseUrl() . '/login', true, 303);
            return false;
        }

        $permissionQuery = $db->prepare(
            'SELECT p.codigo FROM permisos p
             INNER JOIN rol_permisos rp ON rp.permiso_id = p.id
             WHERE rp.rol_id = :role_id'
        );
        $permissionQuery->execute(['role_id' => $user['rol_id']]);
        $permissions = array_column($permissionQuery->fetchAll(PDO::FETCH_ASSOC), 'codigo');
        $_SESSION['role_id'] = (int) $user['rol_id'];
        $_SESSION['auth_name'] = trim($user['nombres'] . ' ' . $user['apellidos']);
        $_SESSION['permissions'] = $permissions;
        $_SESSION['auth_last_activity'] = time();

        if ($path === '/logout') {
            return strtoupper($method) === 'POST';
        }

        $required = $this->requiredPermissions(strtoupper($method), $path);
        if ($required === []) {
            return true;
        }
        foreach ($required as $permission) {
            if (!in_array($permission, $permissions, true)) {
                http_response_code(403);
                header('Content-Type: text/html; charset=UTF-8');
                echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Acceso denegado</title><body><h1>Acceso denegado</h1><p>Tu rol no tiene permiso para esta operación.</p><a href="' . htmlspecialchars($this->baseUrl() . '/', ENT_QUOTES, 'UTF-8') . '">Volver al inicio</a></body></html>';
                return false;
            }
        }
        return true;
    }

    /** @return list<string> */
    private function requiredPermissions(string $method, string $path): array
    {
        if ($path === '/') {
            return ['dashboard.ver'];
        }
        $modules = ['productos', 'categorias', 'clientes', 'lotes', 'marcas', 'usuarios', 'roles', 'proveedores'];
        $parts = explode('/', trim($path, '/'));
        $module = $parts[0] ?? '';
        if (!in_array($module, $modules, true)) {
            return [];
        }
        $action = $parts[1] ?? '';
        if ($method === 'GET') {
            $permissionAction = match ($action) {
                'nuevo', 'nueva', 'crear' => 'crear',
                'editar' => 'editar',
                default => 'ver',
            };
        } else {
            $permissionAction = match ($action) {
                'crear' => 'crear',
                'actualizar' => 'editar',
                'activar' => 'activar',
                'desactivar' => 'desactivar',
                'eliminar' => 'eliminar',
                default => 'ver',
            };
        }
        $required = [$module . '.' . $permissionAction];
        if ($module === 'roles' && in_array($permissionAction, ['crear', 'editar'], true)) {
            $required[] = 'roles.asignar_permisos';
        }
        return $required;
    }

    private function baseUrl(): string
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        return rtrim((string) $app['url'], '/');
    }

    private function clearAuthentication(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
