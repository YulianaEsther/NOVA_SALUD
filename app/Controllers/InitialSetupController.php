<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use PDO;

final class InitialSetupController
{
    public function form(): void
    {
        if ($this->userCount() > 0) {
            $this->redirect('/login');
        }
        $ready = $this->bootstrapRoleReady();
        $error = $ready ? null : 'Primero importa database/permissions_seed.sql para crear el rol Administrador y sus permisos.';
        $this->render($error, ['nombres' => '', 'apellidos' => '', 'email' => '', 'usuario' => '']);
    }

    public function create(): void
    {
        if (!\verify_csrf_token($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            $this->render('El formulario venció. Recarga la página.', $_POST);
            return;
        }
        if ($this->userCount() > 0) {
            $this->redirect('/login');
        }

        $values = [
            'nombres' => trim((string) ($_POST['nombres'] ?? '')),
            'apellidos' => trim((string) ($_POST['apellidos'] ?? '')),
            'email' => mb_strtolower(trim((string) ($_POST['email'] ?? ''))),
            'usuario' => trim((string) ($_POST['usuario'] ?? '')),
        ];
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');
        $errors = [];
        foreach (['nombres' => 120, 'apellidos' => 120, 'email' => 190, 'usuario' => 80] as $field => $max) {
            if ($values[$field] === '' || mb_strlen($values[$field]) > $max) {
                $errors[$field] = 'Este campo es obligatorio y admite hasta ' . $max . ' caracteres.';
            }
        }
        if ($values['email'] !== '' && filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Ingresa un correo electrónico válido.';
        }
        if ($values['usuario'] !== '' && preg_match('/^[A-Za-z0-9._-]{3,80}$/', $values['usuario']) !== 1) {
            $errors['usuario'] = 'Usa entre 3 y 80 letras, números, puntos, guiones o guiones bajos.';
        }
        if (mb_strlen($password) < 12 || mb_strlen($password) > 128) {
            $errors['password'] = 'Usa una contraseña de 12 a 128 caracteres.';
        }
        if ($password !== $confirmation) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
        }
        if (!$this->bootstrapRoleReady()) {
            $errors['_form'] = 'Primero importa database/permissions_seed.sql desde phpMyAdmin.';
        }
        if ($errors !== []) {
            http_response_code(422);
            $this->render(null, $values, $errors);
            return;
        }

        $db = Database::connection();
        $lock = $db->query("SELECT GET_LOCK('nova_salud_initial_admin', 10)")->fetchColumn();
        if ((int) $lock !== 1) {
            http_response_code(503);
            $this->render('No se pudo bloquear el asistente de instalación. Inténtalo nuevamente.', $values);
            return;
        }
        try {
            if ($this->userCount() > 0) {
                $this->redirect('/login');
            }
            $role = $db->prepare("SELECT id FROM roles WHERE nombre = 'Administrador' AND activo = 1 LIMIT 1");
            $role->execute();
            $roleId = $role->fetchColumn();
            if (!$roleId) {
                http_response_code(503);
                $this->render('No está disponible el rol Administrador. Importa database/permissions_seed.sql.', $values);
                return;
            }
            $insert = $db->prepare(
                'INSERT INTO usuarios (rol_id, nombres, apellidos, email, usuario, password_hash, activo)
                 VALUES (:rol_id, :nombres, :apellidos, :email, :usuario, :password_hash, 1)'
            );
            $insert->execute([
                'rol_id' => $roleId,
                'nombres' => $values['nombres'],
                'apellidos' => $values['apellidos'],
                'email' => $values['email'],
                'usuario' => $values['usuario'],
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            session_regenerate_id(true);
            unset($_SESSION['_csrf']);
            $_SESSION['auth_user_id'] = (int) $db->lastInsertId();
            $_SESSION['role_id'] = (int) $roleId;
            $_SESSION['auth_name'] = trim($values['nombres'] . ' ' . $values['apellidos']);
            $_SESSION['auth_last_activity'] = time();
            $this->redirect('/');
        } catch (\PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                http_response_code(422);
                $this->render(null, $values, ['_form' => 'El correo o nombre de usuario ya existe.']);
                return;
            }
            throw $exception;
        } finally {
            $db->query("SELECT RELEASE_LOCK('nova_salud_initial_admin')");
        }
    }

    private function userCount(): int
    {
        return (int) Database::connection()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }

    private function bootstrapRoleReady(): bool
    {
        $statement = Database::connection()->query(
            "SELECT COUNT(*) FROM roles r
             INNER JOIN rol_permisos rp ON rp.rol_id = r.id
             INNER JOIN permisos p ON p.id = rp.permiso_id
             WHERE r.nombre = 'Administrador' AND r.activo = 1"
        );
        return (int) $statement->fetchColumn() > 0;
    }

    /** @param array<string, mixed> $values @param array<string, string> $errors */
    private function render(?string $error, array $values, array $errors = []): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $baseUrl = rtrim((string) $app['url'], '/');
        require dirname(__DIR__) . '/Views/auth/initial_setup.php';
    }

    private function redirect(string $path): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        header('Location: ' . rtrim((string) $app['url'], '/') . $path, true, 303);
        exit;
    }
}
