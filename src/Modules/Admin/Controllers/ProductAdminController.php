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
        return Database::getConnection()->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return $text ?: 'producto';
    }

    private function ensureMediaTable(PDO $db): void
    {
        $db->exec('
            CREATE TABLE IF NOT EXISTS product_media (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                product_id BIGINT UNSIGNED NOT NULL,
                media_type VARCHAR(20) NOT NULL DEFAULT "image",
                url VARCHAR(600) NOT NULL,
                title VARCHAR(190) NULL,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_product_media_product (product_id),
                CONSTRAINT fk_product_media_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');
    }

    private function getProductMedia(int $productId): array
    {
        $db = Database::getConnection();
        $this->ensureMediaTable($db);
        $stmt = $db->prepare('SELECT * FROM product_media WHERE product_id = :id ORDER BY sort_order ASC, id ASC');
        $stmt->execute(['id' => $productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function saveProductMedia(PDO $db, int $productId, string $coverUrl): void
    {
        $this->ensureMediaTable($db);
        $db->prepare('DELETE FROM product_media WHERE product_id = :id')->execute(['id' => $productId]);

        $media = $_POST['media'] ?? [];
        $rows = [];
        if (is_array($media)) {
            foreach ($media as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $url = trim((string)($item['url'] ?? ''));
                if ($url === '') {
                    continue;
                }
                $type = strtolower(trim((string)($item['type'] ?? 'image')));
                $rows[] = [
                    'type' => in_array($type, ['video', 'image'], true) ? $type : 'image',
                    'url' => $url,
                    'title' => trim((string)($item['title'] ?? '')),
                ];
            }
        }

        if ($rows === [] && $coverUrl !== '') {
            $rows[] = ['type' => 'image', 'url' => $coverUrl, 'title' => 'Imagen principal'];
        }

        $stmt = $db->prepare('
            INSERT INTO product_media (product_id, media_type, url, title, sort_order)
            VALUES (:product_id, :media_type, :url, :title, :sort_order)
        ');
        foreach ($rows as $index => $row) {
            $stmt->execute([
                'product_id' => $productId,
                'media_type' => $row['type'],
                'url' => $row['url'],
                'title' => $row['title'],
                'sort_order' => $index,
            ]);
        }
    }

    public function index(): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();
        $search = trim($_GET['search'] ?? '');
        $categoria = $_GET['categoria'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];

        if ($search !== '') { $where[] = '(p.name LIKE :search OR p.sku LIKE :search)'; $params['search'] = "%$search%"; }
        if ($categoria !== '') { $where[] = 'p.category_id = :cat'; $params['cat'] = (int)$categoria; }
        if ($estado !== '') { $where[] = 'p.is_active = :estado'; $params['estado'] = (int)$estado; }
        $whereStr = implode(' AND ', $where);

        $totalStmt = $db->prepare("SELECT COUNT(*) FROM products p WHERE $whereStr");
        $totalStmt->execute($params);
        $total = (int)$totalStmt->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereStr ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) { $stmt->bindValue(":$k", $v); }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        echo Template::renderAdmin('products/index', [
            'products' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'filterCategories' => $this->getCategories(),
            'total' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'categoria' => $categoria,
            'estado' => $estado,
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();
        echo Template::renderAdmin('products/form', ['categories' => $this->getCategories(), 'errors' => [], 'media' => []]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $errors = $this->validate($_POST);
        if ($errors) {
            echo Template::renderAdmin('products/form', ['categories' => $this->getCategories(), 'errors' => $errors, 'media' => $this->postedMedia()]);
            return;
        }

        $db = Database::getConnection();
        $this->ensureMediaTable($db);
        $slug = !empty($_POST['slug']) ? $this->slugify($_POST['slug']) : $this->slugify($_POST['name']);
        $slug = $this->uniqueSlug($db, $slug);

        $stmt = $db->prepare('
            INSERT INTO products (category_id, name, slug, sku, short_description, description, price, stock, image_url, is_active)
            VALUES (:category_id, :name, :slug, :sku, :short_description, :description, :price, :stock, :image_url, :is_active)
        ');
        $coverUrl = trim($_POST['image_url'] ?? '');
        $stmt->execute($this->productParams($slug, $coverUrl));
        $productId = (int)$db->lastInsertId();
        $this->saveProductMedia($db, $productId, $coverUrl);

        Session::set('flash_success', 'Producto creado correctamente.');
        header('Location: /admin/productos');
        exit();
    }

    public function edit(int $id): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            Session::set('flash_error', 'Producto no encontrado.');
            header('Location: /admin/productos');
            exit();
        }
        echo Template::renderAdmin('products/form', ['product' => $product, 'categories' => $this->getCategories(), 'errors' => [], 'media' => $this->getProductMedia($id)]);
    }

    public function update(int $id): void
    {
        $this->requireAdmin();
        $errors = $this->validate($_POST);
        $db = Database::getConnection();
        if ($errors) {
            $stmt = $db->prepare('SELECT * FROM products WHERE id = :id');
            $stmt->execute(['id' => $id]);
            echo Template::renderAdmin('products/form', ['product' => $stmt->fetch(PDO::FETCH_ASSOC), 'categories' => $this->getCategories(), 'errors' => $errors, 'media' => $this->postedMedia()]);
            return;
        }

        $this->ensureMediaTable($db);
        $slug = !empty($_POST['slug']) ? $this->slugify($_POST['slug']) : $this->slugify($_POST['name']);
        $slug = $this->uniqueSlug($db, $slug, $id);
        $coverUrl = trim($_POST['image_url'] ?? '');
        $params = $this->productParams($slug, $coverUrl);
        $params['id'] = $id;

        $stmt = $db->prepare('
            UPDATE products SET category_id=:category_id, name=:name, slug=:slug, sku=:sku,
                short_description=:short_description, description=:description, price=:price,
                stock=:stock, image_url=:image_url, is_active=:is_active
            WHERE id=:id
        ');
        $stmt->execute($params);
        $this->saveProductMedia($db, $id, $coverUrl);

        Session::set('flash_success', 'Producto actualizado correctamente.');
        header('Location: /admin/productos');
        exit();
    }

    public function destroy(int $id): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            Session::set('flash_error', 'Producto no encontrado.');
            header('Location: /admin/productos');
            exit();
        }
        $db->prepare('DELETE FROM products WHERE id = :id')->execute(['id' => $id]);
        Session::set('flash_success', 'Producto eliminado.');
        header('Location: /admin/productos');
        exit();
    }

    private function postedMedia(): array
    {
        $rows = [];
        foreach (($_POST['media'] ?? []) as $item) {
            if (is_array($item) && trim((string)($item['url'] ?? '')) !== '') {
                $rows[] = ['media_type' => $item['type'] ?? 'image', 'url' => $item['url'], 'title' => $item['title'] ?? ''];
            }
        }
        return $rows;
    }

    private function uniqueSlug(PDO $db, string $slug, ?int $excludeId = null): string
    {
        $base = $slug;
        $i = 1;
        while (true) {
            $sql = 'SELECT id FROM products WHERE slug = :slug' . ($excludeId ? ' AND id != :id' : '');
            $stmt = $db->prepare($sql);
            $params = ['slug' => $slug];
            if ($excludeId) { $params['id'] = $excludeId; }
            $stmt->execute($params);
            if (!$stmt->fetchColumn()) { return $slug; }
            $slug = $base . '-' . $i++;
        }
    }

    private function productParams(string $slug, string $coverUrl): array
    {
        return [
            'category_id' => (int)$_POST['category_id'],
            'name' => trim($_POST['name']),
            'slug' => $slug,
            'sku' => strtoupper(trim($_POST['sku'])),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float)$_POST['price'],
            'stock' => (int)$_POST['stock'],
            'image_url' => $coverUrl,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (trim($data['name'] ?? '') === '') { $errors['name'] = 'El nombre es obligatorio.'; }
        if (trim($data['sku'] ?? '') === '') { $errors['sku'] = 'El SKU es obligatorio.'; }
        if (empty($data['category_id'])) { $errors['category_id'] = 'Selecciona una categoria.'; }
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0) { $errors['price'] = 'El precio debe ser un numero valido mayor o igual a 0.'; }
        return $errors;
    }
}