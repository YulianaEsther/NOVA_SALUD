<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use PDOException;

final class CategoriesController
{
    private Category $categories;

    public function __construct()
    {
        $this->categories = new Category();
    }

    public function index(): void
    {
        $search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $this->render('categories/index.php', [
            'categories' => $this->categories->all($search),
            'search' => $search,
            'success' => \flash('success'),
            'error' => \flash('error'),
        ]);
    }

    public function createForm(): void
    {
        $this->renderForm(['id' => '', 'nombre' => '', 'descripcion' => ''], []);
    }

    public function editForm(): void
    {
        $id = $this->requestId($_GET['id'] ?? null);
        $category = $id ? $this->categories->findActive($id) : null;
        if ($category === null) {
            $this->notFound();
            return;
        }
        $this->renderForm($category, []);
    }

    public function store(): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        [$data, $values, $errors] = $this->validatedInput();
        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($values, $errors);
            return;
        }

        try {
            $this->categories->create($data);
            \flash('success', 'Categoría registrada correctamente.');
            $this->redirect('/categorias');
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                http_response_code(422);
                $this->renderForm($values, ['nombre' => 'Ya existe una categoría con ese nombre.']);
                return;
            }
            throw $exception;
        }
    }

    public function update(): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        $id = $this->requestId($_POST['id'] ?? null);
        if (!$id || $this->categories->findActive($id) === null) {
            $this->notFound();
            return;
        }

        [$data, $values, $errors] = $this->validatedInput();
        $values['id'] = $id;
        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($values, $errors);
            return;
        }

        try {
            $this->categories->update($id, $data);
            \flash('success', 'Categoría actualizada correctamente.');
            $this->redirect('/categorias');
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                http_response_code(422);
                $this->renderForm($values, ['nombre' => 'Ya existe una categoría con ese nombre.']);
                return;
            }
            throw $exception;
        }
    }

    public function deactivate(): void
    {
        $this->changeStatus(false);
    }

    public function activate(): void
    {
        $this->changeStatus(true);
    }

    private function changeStatus(bool $active): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        $id = $this->requestId($_POST['id'] ?? null);
        if (!$id || !$this->categories->setActive($id, $active)) {
            $this->notFound();
            return;
        }
        \flash('success', $active ? 'Categoría activada.' : 'Categoría desactivada. Los productos asociados se conservaron.');
        $this->redirect('/categorias');
    }

    /** @param array<string, mixed> $values @param array<string, string> $errors */
    private function renderForm(array $values, array $errors): void
    {
        $this->render('categories/form.php', [
            'category' => $values,
            'errors' => $errors,
            'error' => \flash('error'),
        ]);
    }

    /** @return array{array{nombre: string, descripcion: ?string}, array<string, string>, array<string, string>} */
    private function validatedInput(): array
    {
        $values = [
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
        ];
        $errors = [];
        if ($values['nombre'] === '' || mb_strlen($values['nombre']) > 100) {
            $errors['nombre'] = 'El nombre es obligatorio y admite hasta 100 caracteres.';
        }
        if (mb_strlen($values['descripcion']) > 255) {
            $errors['descripcion'] = 'La descripción admite hasta 255 caracteres.';
        }
        $data = [
            'nombre' => $values['nombre'],
            'descripcion' => $values['descripcion'] === '' ? null : $values['descripcion'],
        ];
        return [$data, $values, $errors];
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

    /** @param array<string, mixed> $data */
    private function render(string $template, array $data): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        $viewPath = dirname(__DIR__) . '/Views/' . $template;
        $data['title'] ??= match ($template) {
            'categories/form.php' => !empty($data['category']['id']) ? 'Editar categoría' : 'Registrar categoría',
            'errors/404.php' => 'No encontrado',
            'errors/419.php' => 'Formulario vencido',
            default => 'Categorías',
        };
        \extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/layout.php';
    }

    private function redirect(string $path): void
    {
        $app = require dirname(__DIR__, 2) . '/config/app.php';
        header('Location: ' . $app['url'] . $path, true, 303);
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404.php', []);
    }
}
