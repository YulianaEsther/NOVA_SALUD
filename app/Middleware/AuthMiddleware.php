<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use PDO;

final class AuthMiddleware
{
    public function handle(string $method, string $uri, bool $routeExists = true): bool
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if (in_array($path, ['/login', '/configuracion/inicial'], true)) {
            return true;
        }

        if (!$routeExists) {
            unset($_SESSION['intended_path']);
            if (!empty($_SESSION['auth_user_id'])) {
                $this->clearAuthentication();
            }
            $this->redirectToLogin('pagina');
            return false;
        }

        $userId = filter_var($_SESSION['auth_user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId === null || $userId < 1) {
            unset($_SESSION['intended_path']);
            $this->redirectToLogin();
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
             LEFT JOIN rol_permisos rp ON rp.permiso_id = p.id AND rp.rol_id = :role_id
             LEFT JOIN usuario_permisos up ON up.permiso_id = p.id AND up.usuario_id = :user_id
             WHERE (up.usuario_id IS NOT NULL AND up.permitido = 1)
                OR (up.usuario_id IS NULL AND rp.permiso_id IS NOT NULL)'
        );
        $permissionQuery->execute(['role_id' => $user['rol_id'], 'user_id' => $user['id']]);
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
                $this->clearAuthentication();
                $this->redirectToLogin('permiso');
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
        $modules = ['productos', 'ventas', 'compras', 'existencias', 'movimientos', 'alertas', 'auditoria', 'categorias', 'clientes', 'lotes', 'marcas', 'usuarios', 'roles', 'proveedores'];
        $parts = explode('/', trim($path, '/'));
        $module = $parts[0] ?? '';
        if (!in_array($module, $modules, true)) {
            return [];
        }
        $action = $parts[1] ?? '';
        if ($method === 'GET') {
            $permissionAction = match ($action) {
                'nuevo', 'nueva', 'crear' => 'crear',
                'detalle' => 'ver',
                'ajustar' => 'ajustar',
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
                'anular' => 'anular',
                'ajustar' => 'ajustar',
                'atender' => 'atender',
                'descartar' => 'descartar',
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

    private function redirectToLogin(string $reason = ''): void
    {
        $query = $reason === '' ? '' : '?acceso=' . rawurlencode($reason);
        header('Location: ' . $this->baseUrl() . '/login' . $query, true, 303);
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
