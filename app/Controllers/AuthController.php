<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use PDO;

final class AuthController
{
    public function loginForm(): void
    {
        if (!empty($_SESSION['auth_user_id'])) {
            $this->redirect('/');
        }
        $this->renderLogin(null, '');
    }

    public function login(): void
    {
        if (!\verify_csrf_token($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->renderLogin('El formulario venció. Recarga la página e inténtalo de nuevo.', (string) ($_POST['login'] ?? ''));
            return;
        }

        $login = mb_substr(trim((string) ($_POST['login'] ?? '')), 0, 190);
        $password = (string) ($_POST['password'] ?? '');
        $ip = mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
        if ($login === '' || $password === '') {
            $this->renderLogin('Usuario o contraseña incorrectos.', $login);
            return;
        }

        $db = Database::connection();
        if ($this->isBlocked($db, $login, $ip)) {
            $this->renderLogin('Demasiados intentos. Espera 15 minutos antes de volver a intentarlo.', $login);
            return;
        }

        $statement = $db->prepare(
            'SELECT u.id, u.rol_id, u.nombres, u.apellidos, u.password_hash
             FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id
             WHERE (u.email = :email OR u.usuario = :username)
               AND u.activo = 1 AND r.activo = 1 LIMIT 1'
        );
        $statement->execute(['email' => $login, 'username' => $login]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (mb_strlen($password) > 128 || !$user || !password_verify($password, $user['password_hash'])) {
            $this->recordFailure($db, $login, $ip);
            $this->renderLogin('Usuario o contraseña incorrectos.', $login);
            return;
        }

        $clear = $db->prepare('DELETE FROM intentos_acceso WHERE email = :email AND ip = :ip');
        $clear->execute(['email' => $login, 'ip' => $ip]);
        $lastLogin = $db->prepare('UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id');
        $lastLogin->execute(['id' => $user['id']]);

        session_regenerate_id(true);
        unset($_SESSION['_csrf']);
        $_SESSION['auth_user_id'] = (int) $user['id'];
        $_SESSION['role_id'] = (int) $user['rol_id'];
        $_SESSION['auth_name'] = trim($user['nombres'] . ' ' . $user['apellidos']);
        $_SESSION['auth_last_activity'] = time();
        unset($_SESSION['permissions']);
        $target = $_SESSION['intended_path'] ?? '/';
        unset($_SESSION['intended_path']);
        if (!is_string($target) || !str_starts_with($target, '/') || str_starts_with($target, '//')) {
            $target = '/';
        }
        $this->redirect($target);
    }

    public function logout(): void
    {
        if (!\verify_csrf_token($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'El formulario venció. Recarga la página.';
            return;
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect('/login');
    }

    private function isBlocked(PDO $db, string $login, string $ip): bool
    {
        $statement = $db->prepare(
            'SELECT bloqueado_hasta FROM intentos_acceso WHERE email = :email AND ip = :ip LIMIT 1'
        );
        $statement->execute(['email' => $login, 'ip' => $ip]);
        $until = $statement->fetchColumn();
        return is_string($until) && strtotime($until) > time();
    }

    private function recordFailure(PDO $db, string $login, string $ip): void
    {
        $statement = $db->prepare(
            'INSERT INTO intentos_acceso (email, ip, intentos, bloqueado_hasta)
             VALUES (:email, :ip, 1, NULL)
             ON DUPLICATE KEY UPDATE
                intentos = IF(bloqueado_hasta IS NOT NULL AND bloqueado_hasta <= NOW(), 1, intentos + 1),
                bloqueado_hasta = NULL'
        );
        $statement->execute(['email' => $login, 'ip' => $ip]);
        $block = $db->prepare(
            'UPDATE intentos_acceso
             SET bloqueado_hasta = CASE WHEN intentos >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE) ELSE NULL END
             WHERE email = :email AND ip = :ip'
        );
        $block->execute(['email' => $login, 'ip' => $ip]);
    }

    private function renderLogin(?string $error, string $login): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $baseUrl = rtrim((string) $app['url'], '/');
        require dirname(__DIR__) . '/Views/auth/login.php';
    }

    private function redirect(string $path): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        header('Location: ' . rtrim((string) $app['url'], '/') . $path, true, 303);
        exit;
    }
}
