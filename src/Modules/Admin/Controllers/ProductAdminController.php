<?php

namespace Caral\Modules\Admin\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;

class ProductAdminController
{
    private function requireAdmin(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        $role = Session::getUserRole();
        if (!in_array($role, ['Super Administrador', 'Marketing', 'Operaciones'])) {
            header('Location: /');
            exit();
        }
    }

    private function getCategories(): array
    {
        return Database::getConnection()
            ->query("SELECT id, name FROM categories ORDER BY name ASC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return $text;
    }

    // GET /admin/productos
    public function index(): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $search    = trim($_GET['search'] ?? '');
        $categoria = $_GET['categoria'] ?? '';
        $estado    = $_GET['estado'] ?? '';
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $perPage   = 12;
        $offset    = ($page - 1) * $perPage;

        $where  = ['1=1'];
        $params = [];

        if ($search !== '') {
            $where[]  = '(p.name LIKE :search OR p.sku LIKE :search)';
            $params['search'] = "%$search%";
        }
        if ($categoria !== '') {
            $where[]  = 'p.category_id = :cat';
            $params['cat'] = (int)$categoria;
        }
        if ($estado !== '') {
            $where[]  = 'p.is_active = :estado';
            $params['estado'] = (int)$estado;
        }

        $whereStr = implode(' AND ', $where);

        $totalStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereStr");
        $totalStmt->execute($params);
        $total = (int)$totalStmt->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $whereStr
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo Template::renderAdmin('products/index', [
            'products'         => $products,
            'filterCategories' => $this->getCategories(),
            'total'            => $total,
            'totalPages'       => $totalPages,
            'currentPage'      => $page,
            'search'           => $search,
            'categoria'        => $categoria,
            'estado'           => $estado,
        ]);
    }

    // GET /admin/productos/nuevo
    public function create(): void
    {
        $this->requireAdmin();
        echo Template::renderAdmin('products/form', [
            'categories' => $this->getCategories(),
            'errors'     => [],
        ]);
    }

    // POST /admin/productos/guardar
    public function store(): void
    {
        $this->requireAdmin();
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            echo Template::renderAdmin('products/form', [
                'categories' => $this->getCategories(),
                'errors'     => $errors,
            ]);
            return;
        }

        $db   = Database::getConnection();
        $slug = !empty($_POST['slug']) ? $this->slugify($_POST['slug']) : $this->slugify($_POST['name']);

        // Garantizar slug único
        $slugBase = $slug;
        $i = 1;
        while (true) {
            $chk = $db->prepare("SELECT id FROM products WHERE slug = :slug");
            $chk->execute(['slug' => $slug]);
            if (!$chk->fetchColumn()) break;
            $slug = "$slugBase-$i";
            $i++;
        }

        $stmt = $db->prepare("
            INSERT INTO products (category_id, name, slug, sku, short_description, description, price, stock, image_url, is_active)
            VALUES (:category_id, :name, :slug, :sku, :short_description, :description, :price, :stock, :image_url, :is_active)
        ");
        $stmt->execute([
            'category_id'       => (int)$_POST['category_id'],
            'name'              => trim($_POST['name']),
            'slug'              => $slug,
            'sku'               => strtoupper(trim($_POST['sku'])),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description'       => trim($_POST['description'] ?? ''),
            'price'             => (float)$_POST['price'],
            'stock'             => (int)$_POST['stock'],
            'image_url'         => trim($_POST['image_url'] ?? ''),
            'is_active'         => isset($_POST['is_active']) ? 1 : 0,
        ]);

        Session::set('flash_success', "Producto «{$_POST['name']}» creado correctamente.");
        header('Location: /admin/productos');
        exit();
    }

    // GET /admin/productos/{id}/editar
    public function edit(int $id): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Session::set('flash_error', 'Producto no encontrado.');
            header('Location: /admin/productos');
            exit();
        }

        echo Template::renderAdmin('products/form', [
            'product'    => $product,
            'categories' => $this->getCategories(),
            'errors'     => [],
        ]);
    }

    // POST /admin/productos/{id}/actualizar
    public function update(int $id): void
    {
        $this->requireAdmin();
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $db   = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            echo Template::renderAdmin('products/form', [
                'product'    => $product,
                'categories' => $this->getCategories(),
                'errors'     => $errors,
            ]);
            return;
        }

        $db   = Database::getConnection();
        $slug = !empty($_POST['slug']) ? $this->slugify($_POST['slug']) : $this->slugify($_POST['name']);

        // Garantizar slug único (excluyendo el producto actual)
        $slugBase = $slug;
        $i = 1;
        while (true) {
            $chk = $db->prepare("SELECT id FROM products WHERE slug = :slug AND id != :id");
            $chk->execute(['slug' => $slug, 'id' => $id]);
            if (!$chk->fetchColumn()) break;
            $slug = "$slugBase-$i";
            $i++;
        }

        $stmt = $db->prepare("
            UPDATE products SET
                category_id       = :category_id,
                name              = :name,
                slug              = :slug,
                sku               = :sku,
                short_description = :short_description,
                description       = :description,
                price             = :price,
                stock             = :stock,
                image_url         = :image_url,
                is_active         = :is_active
            WHERE id = :id
        ");
        $stmt->execute([
            'category_id'       => (int)$_POST['category_id'],
            'name'              => trim($_POST['name']),
            'slug'              => $slug,
            'sku'               => strtoupper(trim($_POST['sku'])),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description'       => trim($_POST['description'] ?? ''),
            'price'             => (float)$_POST['price'],
            'stock'             => (int)$_POST['stock'],
            'image_url'         => trim($_POST['image_url'] ?? ''),
            'is_active'         => isset($_POST['is_active']) ? 1 : 0,
            'id'                => $id,
        ]);

        Session::set('flash_success', "Producto «{$_POST['name']}» actualizado correctamente.");
        header('Location: /admin/productos');
        exit();
    }

    // POST /admin/productos/{id}/eliminar
    public function destroy(int $id): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT name FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Session::set('flash_error', 'Producto no encontrado.');
            header('Location: /admin/productos');
            exit();
        }

        $del = $db->prepare("DELETE FROM products WHERE id = :id");
        $del->execute(['id' => $id]);

        Session::set('flash_success', "Producto «{$product['name']}» eliminado.");
        header('Location: /admin/productos');
        exit();
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'El nombre es obligatorio.';
        }
        if (empty(trim($data['sku'] ?? ''))) {
            $errors['sku'] = 'El SKU es obligatorio.';
        }
        if (empty($data['category_id'])) {
            $errors['category_id'] = 'Selecciona una categoría.';
        }
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) {
            $errors['price'] = 'El precio debe ser un número válido mayor o igual a 0.';
        }
        return $errors;
    }
}
