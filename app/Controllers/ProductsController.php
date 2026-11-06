<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use PDOException;

final class ProductsController
{
    private Product $products;

    public function __construct()
    {
        $this->products = new Product();
    }

    public function index(): void
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        $search = mb_substr($search, 0, 100);
        $categoryId = filter_var($_GET['categoria'] ?? null, FILTER_VALIDATE_INT);
        $categoryId = $categoryId !== false && $categoryId > 0 ? $categoryId : null;

        $this->render('products/index.php', [
            'products' => $this->products->all($search, $categoryId),
            'categories' => $this->products->categories(),
            'search' => $search,
            'selectedCategory' => $categoryId,
            'success' => \flash('success'),
            'error' => \flash('error'),
        ]);
    }

    public function createForm(): void
    {
        $this->renderForm($this->emptyProduct(), []);
    }

    public function editForm(): void
    {
        $id = $this->requestId($_GET['id'] ?? null);
        $product = $id ? $this->products->findActive($id) : null;
        if ($product === null) {
            $this->notFound();
            return;
        }
        $this->renderForm($product, []);
    }

    public function store(): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        [$data, $errors, $formValues] = $this->validatedInput();
        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($formValues, $errors);
            return;
        }

        try {
            $this->products->create($data);
            \flash('success', 'Producto registrado correctamente.');
            $this->redirect('/productos');
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                http_response_code(422);
                $errors['codigo'] = 'Ese código ya está registrado.';
                $this->renderForm($formValues, $errors);
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
        if (!$id || $this->products->findActive($id) === null) {
            $this->notFound();
            return;
        }

        [$data, $errors, $formValues] = $this->validatedInput();
        $formValues['id'] = $id;
        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm($formValues, $errors);
            return;
        }

        try {
            $this->products->update($id, $data);
            \flash('success', 'Producto actualizado correctamente.');
            $this->redirect('/productos');
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                http_response_code(422);
                $errors['codigo'] = 'Ese código ya está registrado.';
                $this->renderForm($formValues, $errors);
                return;
            }
            throw $exception;
        }
    }

    public function deactivate(): void
    {
        if (!$this->validCsrf()) {
            return;
        }
        $id = $this->requestId($_POST['id'] ?? null);
        if (!$id || !$this->products->deactivate($id)) {
            $this->notFound();
            return;
        }
        \flash('success', 'Producto desactivado. Se conservó su historial.');
        $this->redirect('/productos');
    }

    /** @param array<string, mixed> $values @param array<string, string> $errors */
    private function renderForm(array $values, array $errors): void
    {
        $this->render('products/form.php', [
            'product' => $values,
            'errors' => $errors,
            'categories' => $this->products->categories(),
            'brands' => $this->products->brands(),
            'error' => \flash('error'),
        ]);
    }

    /** @return array{array<string, mixed>, array<string, string>, array<string, mixed>} */
    private function validatedInput(): array
    {
        $values = [
            'categoria_id' => trim((string) ($_POST['categoria_id'] ?? '')),
            'marca_id' => trim((string) ($_POST['marca_id'] ?? '')),
            'codigo' => trim((string) ($_POST['codigo'] ?? '')),
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
            'unidad_medida' => trim((string) ($_POST['unidad_medida'] ?? 'unidad')),
            'precio_venta' => trim((string) ($_POST['precio_venta'] ?? '')),
            'stock_minimo' => trim((string) ($_POST['stock_minimo'] ?? '0')),
            'requiere_lote' => isset($_POST['requiere_lote']) ? '1' : '0',
        ];
        $errors = [];

        if (!$this->optionExists($this->products->categories(), $values['categoria_id'])) {
            $errors['categoria_id'] = 'Selecciona una categoría válida.';
        }
        if ($values['marca_id'] !== '' && !$this->optionExists($this->products->brands(), $values['marca_id'])) {
            $errors['marca_id'] = 'Selecciona una marca válida.';
        }
        if ($values['codigo'] === '' || mb_strlen($values['codigo']) > 80) {
            $errors['codigo'] = 'El código es obligatorio y admite hasta 80 caracteres.';
        }
        if ($values['nombre'] === '' || mb_strlen($values['nombre']) > 180) {
            $errors['nombre'] = 'El nombre es obligatorio y admite hasta 180 caracteres.';
        }
        if (mb_strlen($values['descripcion']) > 5000) {
            $errors['descripcion'] = 'La descripción no puede superar los 5000 caracteres.';
        }
        if ($values['unidad_medida'] === '' || mb_strlen($values['unidad_medida']) > 40) {
            $errors['unidad_medida'] = 'Indica una unidad de medida de hasta 40 caracteres.';
        }
        if (!$this->validDecimal($values['precio_venta'], 2)) {
            $errors['precio_venta'] = 'Ingresa un precio válido, no negativo y con hasta 2 decimales.';
        }
        if (!$this->validDecimal($values['stock_minimo'], 3)) {
            $errors['stock_minimo'] = 'Ingresa un mínimo válido, no negativo y con hasta 3 decimales.';
        }

        $data = [
            'categoria_id' => (int) $values['categoria_id'],
            'marca_id' => $values['marca_id'] === '' ? null : (int) $values['marca_id'],
            'codigo' => $values['codigo'],
            'nombre' => $values['nombre'],
            'descripcion' => $values['descripcion'] === '' ? null : $values['descripcion'],
            'unidad_medida' => $values['unidad_medida'],
            'precio_venta' => $values['precio_venta'],
            'stock_minimo' => $values['stock_minimo'],
            'requiere_lote' => (int) $values['requiere_lote'],
        ];
        return [$data, $errors, $values];
    }

    /** @param list<array<string, mixed>> $options */
    private function optionExists(array $options, string $selected): bool
    {
        if (filter_var($selected, FILTER_VALIDATE_INT) === false) {
            return false;
        }
        foreach ($options as $option) {
            if ((int) $option['id'] === (int) $selected) {
                return true;
            }
        }
        return false;
    }

    private function validDecimal(string $value, int $scale): bool
    {
        $wholeDigits = $scale === 2 ? 10 : 9;
        return preg_match('/^(?:0|[1-9]\d{0,' . ($wholeDigits - 1) . '})(?:\.\d{1,' . $scale . '})?$/', $value) === 1;
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
            'products/form.php' => !empty($data['product']['id']) ? 'Editar producto' : 'Registrar producto',
            'errors/404.php' => 'No encontrado',
            'errors/419.php' => 'Formulario vencido',
            default => 'Productos',
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

    /** @return array<string, string> */
    private function emptyProduct(): array
    {
        return [
            'id' => '', 'categoria_id' => '', 'marca_id' => '', 'codigo' => '',
            'nombre' => '', 'descripcion' => '', 'unidad_medida' => 'unidad',
            'precio_venta' => '', 'stock_minimo' => '0', 'requiere_lote' => '1',
        ];
    }
}
