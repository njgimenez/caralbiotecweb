<?php

namespace Caral\Modules\Admin\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;

class CategoryAdminController
{
    private function requireAdmin(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
        $role = Session::getUserRole();
        if (!in_array($role, ['Super Administrador', 'Marketing'])) {
            header('Location: /');
            exit();
        }
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return $text;
    }

    // GET /admin/categorias
    public function index(): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $categories = $db->query("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY c.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo Template::renderAdmin('categories/index', [
            'categories' => $categories,
        ]);
    }

    // GET /admin/categorias/nueva
    public function create(): void
    {
        $this->requireAdmin();
        echo Template::renderAdmin('categories/form', ['errors' => []]);
    }

    // POST /admin/categorias/guardar
    public function store(): void
    {
        $this->requireAdmin();
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            echo Template::renderAdmin('categories/form', ['errors' => $errors]);
            return;
        }

        $db   = Database::getConnection();
        $slug = !empty($_POST['slug']) ? $this->slugify($_POST['slug']) : $this->slugify($_POST['name']);

        // Slug único
        $slugBase = $slug;
        $i = 1;
        while (true) {
            $chk = $db->prepare("SELECT id FROM categories WHERE slug = :slug");
            $chk->execute(['slug' => $slug]);
            if (!$chk->fetchColumn()) break;
            $slug = "$slugBase-$i";
            $i++;
        }

        $stmt = $db->prepare("
            INSERT INTO categories (name, slug, description, image_url)
            VALUES (:name, :slug, :description, :image_url)
        ");
        $stmt->execute([
            'name'        => trim($_POST['name']),
            'slug'        => $slug,
            'description' => trim($_POST['description'] ?? ''),
            'image_url'   => trim($_POST['image_url'] ?? ''),
        ]);

        Session::set('flash_success', "Categoría «{$_POST['name']}» creada correctamente.");
        header('Location: /admin/categorias');
        exit();
    }

    // GET /admin/categorias/{id}/editar
    public function edit(int $id): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            Session::set('flash_error', 'Categoría no encontrada.');
            header('Location: /admin/categorias');
            exit();
        }

        echo Template::renderAdmin('categories/form', [
            'category' => $category,
            'errors'   => [],
        ]);
    }

    // POST /admin/categorias/{id}/actualizar
    public function update(int $id): void
    {
        $this->requireAdmin();
        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $db   = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            echo Template::renderAdmin('categories/form', [
                'category' => $category,
                'errors'   => $errors,
            ]);
            return;
        }

        $db   = Database::getConnection();
        $slug = !empty($_POST['slug']) ? $this->slugify($_POST['slug']) : $this->slugify($_POST['name']);

        // Slug único excluyendo actual
        $slugBase = $slug;
        $i = 1;
        while (true) {
            $chk = $db->prepare("SELECT id FROM categories WHERE slug = :slug AND id != :id");
            $chk->execute(['slug' => $slug, 'id' => $id]);
            if (!$chk->fetchColumn()) break;
            $slug = "$slugBase-$i";
            $i++;
        }

        $stmt = $db->prepare("
            UPDATE categories SET
                name        = :name,
                slug        = :slug,
                description = :description,
                image_url   = :image_url
            WHERE id = :id
        ");
        $stmt->execute([
            'name'        => trim($_POST['name']),
            'slug'        => $slug,
            'description' => trim($_POST['description'] ?? ''),
            'image_url'   => trim($_POST['image_url'] ?? ''),
            'id'          => $id,
        ]);

        Session::set('flash_success', "Categoría «{$_POST['name']}» actualizada.");
        header('Location: /admin/categorias');
        exit();
    }

    // POST /admin/categorias/{id}/eliminar
    public function destroy(int $id): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT name FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cat) {
            Session::set('flash_error', 'Categoría no encontrada.');
            header('Location: /admin/categorias');
            exit();
        }

        // Verificar si tiene productos
        $countStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
        $countStmt->execute(['id' => $id]);
        $count = (int)$countStmt->fetchColumn();

        if ($count > 0) {
            Session::set('flash_error', "No puedes eliminar la categoría «{$cat['name']}» porque tiene $count producto(s) asociado(s).");
            header('Location: /admin/categorias');
            exit();
        }

        $del = $db->prepare("DELETE FROM categories WHERE id = :id");
        $del->execute(['id' => $id]);

        Session::set('flash_success', "Categoría «{$cat['name']}» eliminada.");
        header('Location: /admin/categorias');
        exit();
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors['name'] = 'El nombre es obligatorio.';
        }
        return $errors;
    }
}
