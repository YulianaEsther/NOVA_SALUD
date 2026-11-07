<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\AdminResource;
use PDOException;

final class AdminResourcesController
{
    /** @var array<string, array<string, mixed>> */
    private array $definitions;

    public function __construct()
    {
        $this->definitions = require dirname(__DIR__, 2) . '/config/admin_resources.php';
    }

    public function index(string $slug): void
    {
        [$definition, $model] = $this->resource($slug);
        $search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $this->render('resources/index.php', [
            'definition' => $definition,
            'slug' => $slug,
            'rows' => $model->all($search),
            'search' => $search,
            'success' => \flash('success'),
            'error' => \flash('error'),
        ]);
    }

    public function createForm(string $slug): void
    {
        [$definition] = $this->resource($slug);
        $values = [];
        foreach ($definition['fields'] as $field) {
            $values[$field['key']] = '';
        }
        if ($slug === 'roles') {
            $values['permission_ids'] = [];
        }
        $this->renderForm($slug, $definition, $values, []);
    }

    public function editForm(string $slug): void
    {
        [$definition, $model] = $this->resource($slug);
        $id = $this->requestId($_GET['id'] ?? null);
        $row = $id ? $model->find($id) : null;
        if ($row === null || (isset($definition['active_column']) && $definition['active_column'] !== null && (int) $row[$definition['active_column']] !== 1)) {
            $this->notFound($definition);
            return;
        }
        $values = ['id' => $id];
        foreach ($definition['fields'] as $field) {
            $column = $field['column'] ?? $field['key'];
            if ($field['type'] === 'permissions') {
                $values['permission_ids'] = $model->rolePermissionIds($id);
                continue;
            }
            if ($field['type'] === 'user_permissions') {
                $values['permission_ids'] = $model->effectiveUserPermissionIds($id);
                continue;
            }
            $values[$field['key']] = $field['type'] === 'password' ? '' : ($row[$column] ?? '');
        }
        $this->renderForm($slug, $definition, $values, []);
    }

    public function store(string $slug): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        [$definition, $model] = $this->resource($slug);
        [$data, $values, $errors] = $this->validateInput($definition, false);
        $permissionIds = $this->validatePermissions($definition, $errors);
        if ($slug === 'roles') {
            $values['permission_ids'] = $this->submittedPermissionIds();
        } elseif ($slug === 'usuarios') {
            $values['permission_ids'] = $this->submittedPermissionIds();
        }
        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($slug, $definition, $values, $errors);
            return;
        }
        try {
            $model->create($data, $permissionIds);
            \flash('success', $definition['title'] . ' registrado correctamente.');
            $this->redirect('/' . $slug);
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                http_response_code(422);
                $this->renderForm($slug, $definition, $values, ['_form' => 'Ya existe un registro con uno de esos datos únicos.']);
                return;
            }
            throw $exception;
        }
    }

    public function update(string $slug): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        [$definition, $model] = $this->resource($slug);
        $id = $this->requestId($_POST['id'] ?? null);
        $row = $id ? $model->find($id) : null;
        if ($row === null || (isset($definition['active_column']) && $definition['active_column'] !== null && (int) $row[$definition['active_column']] !== 1)) {
            $this->notFound($definition);
            return;
        }
        [$data, $values, $errors] = $this->validateInput($definition, true, $row);
        $permissionIds = $this->validatePermissions($definition, $errors);
        if ($slug === 'roles') {
            $values['permission_ids'] = $this->submittedPermissionIds();
        } elseif ($slug === 'usuarios') {
            $values['permission_ids'] = $this->submittedPermissionIds();
        }
        $values['id'] = $id;
        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($slug, $definition, $values, $errors);
            return;
        }
        try {
            $model->update($id, $data, in_array($slug, ['roles', 'usuarios'], true) ? $permissionIds : null);
            \flash('success', $definition['title'] . ' actualizado correctamente.');
            $this->redirect('/' . $slug);
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                http_response_code(422);
                $this->renderForm($slug, $definition, $values, ['_form' => 'Ya existe un registro con uno de esos datos únicos.']);
                return;
            }
            throw $exception;
        }
    }

    public function deactivate(string $slug): void
    {
        $this->changeActive($slug, false);
    }

    public function activate(string $slug): void
    {
        $this->changeActive($slug, true);
    }

    private function changeActive(string $slug, bool $active): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        [$definition, $model] = $this->resource($slug);
        $id = $this->requestId($_POST['id'] ?? null);
        if ($slug === 'roles' && !$active && $id && $model->roleHasActiveUsers($id)) {
            \flash('error', 'No se puede desactivar un rol asignado a usuarios activos.');
            $this->redirect('/roles');
        }
        if ($slug === 'usuarios' && !$active && $id === (int) ($_SESSION['auth_user_id'] ?? 0)) {
            \flash('error', 'No puedes desactivar tu propia cuenta mientras tienes una sesión activa.');
            $this->redirect('/usuarios');
        }
        if ($slug === 'usuarios' && !$active && $id && $model->isLastActiveAdministrator($id)) {
            \flash('error', 'No se puede desactivar al último usuario con permisos de administración.');
            $this->redirect('/usuarios');
        }
        if (!$id || !$model->setActive($id, $active)) {
            $this->notFound($definition);
            return;
        }
        \flash('success', $definition['title'] . ($active ? ' activado.' : ' desactivado.'));
        $this->redirect('/' . $slug);
    }

    public function deleteLot(): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        [$definition, $model] = $this->resource('lotes');
        $id = $this->requestId($_POST['id'] ?? null);
        if (!$id) {
            $this->notFound($definition);
            return;
        }
        if (!$model->deleteLotIfUnused($id)) {
            \flash('error', 'No se puede eliminar: el lote tiene existencias o está relacionado con compras, ventas o movimientos.');
        } else {
            \flash('success', 'Lote eliminado porque aún no tenía movimientos ni existencias.');
        }
        $this->redirect('/lotes');
    }

    /** @param array<string, mixed> $definition @param array<string, mixed> $values @param array<string, string> $errors */
    private function renderForm(string $slug, array $definition, array $values, array $errors): void
    {
        $model = new AdminResource($definition);
        foreach ($definition['fields'] as $index => $field) {
            if (isset($field['options'])) {
                $definition['fields'][$index]['choices'] = $model->options($field['options']);
            }
            if (in_array($field['type'], ['permissions', 'user_permissions'], true)) {
                $definition['fields'][$index]['choices'] = $model->permissionOptions();
            }
        }
        $this->render('resources/form.php', [
            'definition' => $definition,
            'slug' => $slug,
            'values' => $values,
            'errors' => $errors,
            'editing' => !empty($values['id']),
            'error' => \flash('error'),
        ]);
    }

    /** @param array<string, mixed> $definition @return array{array<string, mixed>, array<string, mixed>, array<string, string>} */
    private function validateInput(array $definition, bool $editing, array $current = []): array
    {
        $values = [];
        $data = [];
        $errors = [];
        foreach ($definition['fields'] as $field) {
            $key = $field['key'];
            if (in_array($field['type'], ['permissions', 'user_permissions'], true)) {
                continue;
            }
            if ($editing && ($field['editable_on_update'] ?? true) === false) {
                $values[$key] = $current[$field['column'] ?? $key] ?? '';
                continue;
            }
            $value = trim((string) ($_POST[$key] ?? ''));
            $values[$key] = $value;
            $required = (bool) ($field['required'] ?? false);
            if ($field['type'] === 'password' && $editing) {
                $required = false;
            }
            if ($required && $value === '') {
                $errors[$key] = 'Este campo es obligatorio.';
                continue;
            }
            if ($value === '') {
                if ($field['type'] !== 'password') {
                    $data[$field['column'] ?? $key] = null;
                }
                continue;
            }

            $type = $field['type'];
            if (isset($field['max']) && mb_strlen($value) > (int) $field['max']) {
                $errors[$key] = 'El valor supera el máximo permitido de ' . $field['max'] . ' caracteres.';
                continue;
            }
            if ($type === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $errors[$key] = 'Ingresa un correo electrónico válido.';
                continue;
            }
            if ($type === 'select') {
                $id = filter_var($value, FILTER_VALIDATE_INT);
                $choices = (new AdminResource($definition))->options($field['options']);
                $valid = false;
                if ($id !== false) {
                    foreach ($choices as $choice) {
                        if ((int) $choice['id'] === (int) $id) {
                            $valid = true;
                            break;
                        }
                    }
                }
                if (!$valid) {
                    $errors[$key] = 'Selecciona una opción válida.';
                    continue;
                }
                $value = (string) $id;
            }
            if ($type === 'decimal') {
                $scale = (int) ($field['scale'] ?? 2);
                $wholeDigits = 12 - $scale;
                $pattern = '/^(?:0|[1-9]\d{0,' . ($wholeDigits - 1) . '})(?:\.\d{1,' . $scale . '})?$/';
                if (preg_match($pattern, $value) !== 1) {
                    $errors[$key] = 'Ingresa una cantidad no negativa con hasta ' . $scale . ' decimales.';
                    continue;
                }
            }
            if ($type === 'date') {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) {
                    $errors[$key] = 'Ingresa una fecha válida.';
                    continue;
                }
            }
            if ($type === 'password') {
                if (mb_strlen($value) < 12) {
                    $errors[$key] = 'La contraseña debe tener al menos 12 caracteres.';
                    continue;
                }
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            $data[$field['column'] ?? $key] = $value;
        }
        return [$data, $values, $errors];
    }

    /** @param array<string, mixed> $definition @param array<string, string> $errors @return list<int> */
    private function validatePermissions(array $definition, array &$errors): array
    {
        if (!in_array($definition['table'], ['roles', 'usuarios'], true)) {
            return [];
        }
        $submitted = $_POST['permission_ids'] ?? [];
        if (!is_array($submitted)) {
            $errors['permission_ids'] = 'La selección de permisos no es válida.';
            return [];
        }
        $allowed = (new AdminResource($definition))->permissionOptions();
        if ($allowed === []) {
            $errors['permission_ids'] = 'No hay permisos disponibles. Ejecuta las migraciones de permisos.';
            return [];
        }
        $allowedIds = [];
        $codesById = [];
        foreach ($allowed as $permission) {
            $allowedIds[] = (int) $permission['id'];
            $codesById[(int) $permission['id']] = $permission['codigo'];
        }
        $ownPermissions = $_SESSION['permissions'] ?? [];
        $ids = [];
        foreach ($submitted as $value) {
            $id = filter_var($value, FILTER_VALIDATE_INT);
            if ($id === false || !in_array((int) $id, $allowedIds, true)) {
                $errors['permission_ids'] = 'Uno o más permisos seleccionados no son válidos.';
                return [];
            }
            if (!in_array($codesById[(int) $id], $ownPermissions, true)) {
                $errors['permission_ids'] = 'No puedes asignar permisos que tu propio usuario no tiene.';
                return [];
            }
            $ids[] = (int) $id;
        }
        $targetId = $this->requestId($_POST['id'] ?? null);
        if ($definition['table'] === 'roles') {
            $currentRoleId = (int) ($_SESSION['role_id'] ?? 0);
            if ($targetId !== null && $targetId === $currentRoleId) {
                foreach (['roles.ver', 'roles.editar', 'roles.asignar_permisos'] as $requiredCode) {
                    $assignmentId = array_search($requiredCode, $codesById, true);
                    if ($assignmentId !== false && !in_array((int) $assignmentId, $ids, true)) {
                        $errors['permission_ids'] = 'Tu propio rol debe conservar los permisos de consulta, edición y asignación de roles.';
                        break;
                    }
                }
            }
        } elseif ($targetId !== null && $targetId === (int) ($_SESSION['auth_user_id'] ?? 0)) {
            foreach (['usuarios.ver', 'usuarios.editar', 'roles.asignar_permisos'] as $requiredCode) {
                $assignmentId = array_search($requiredCode, $codesById, true);
                if ($assignmentId !== false && !in_array((int) $assignmentId, $ids, true)) {
                    $errors['permission_ids'] = 'Conserva en tu usuario los permisos para consultar y editar usuarios y asignar permisos.';
                    break;
                }
            }
        }
        if ($definition['table'] === 'usuarios' && $targetId !== null) {
            $adminPermissionId = array_search('roles.asignar_permisos', $codesById, true);
            if ($adminPermissionId !== false
                && !in_array((int) $adminPermissionId, $ids, true)
                && (new AdminResource($definition))->isLastActiveAdministrator($targetId)) {
                $errors['permission_ids'] = 'No se puede quitar el permiso de administración al último administrador activo.';
            }
        }
        return array_values(array_unique($ids));
    }

    /** @return list<int> */
    private function submittedPermissionIds(): array
    {
        $submitted = $_POST['permission_ids'] ?? [];
        if (!is_array($submitted)) {
            return [];
        }
        return array_values(array_filter(array_map(static fn ($value) => filter_var($value, FILTER_VALIDATE_INT), $submitted), static fn ($id) => $id !== false));
    }

    private function validCsrf(): bool
    {
        if (\verify_csrf_token($_POST['_csrf'] ?? null)) {
            return true;
        }
        http_response_code(419);
        $this->render('errors/419.php', []);
        return false;
    }

    private function requestId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);
        return $id !== false && $id > 0 ? $id : null;
    }

    /** @return array{array<string, mixed>, AdminResource} */
    private function resource(string $slug): array
    {
        if (!isset($this->definitions[$slug])) {
            http_response_code(404);
            exit('Recurso no encontrado');
        }
        $definition = $this->definitions[$slug];
        return [$definition, new AdminResource($definition)];
    }

    /** @param array<string, mixed> $data */
    private function render(string $template, array $data): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $viewPath = dirname(__DIR__) . '/Views/' . $template;
        $data['title'] ??= $data['definition']['title'] ?? 'NOVA SALUD';
        \extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/layout.php';
    }

    private function redirect(string $path): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        header('Location: ' . $app['url'] . $path, true, 303);
        exit;
    }

    /** @param array<string, mixed> $definition */
    private function notFound(array $definition): void
    {
        http_response_code(404);
        $this->render('errors/404.php', ['title' => $definition['title'] . ' no encontrado']);
    }
}
