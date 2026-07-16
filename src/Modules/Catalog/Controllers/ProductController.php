<?php

namespace Caral\Modules\Catalog\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Template;

class ProductController
{
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

    public function index(): void
    {
        $categorySlug = $_GET['categoria'] ?? null;
        $conditionSlug = $_GET['condicion'] ?? null;
        $search = $_GET['search'] ?? null;
        $db = Database::getConnection();
        $products = [];
        $title = 'Catalogo de Productos - Caral Biotec';
        $filterName = '';
        $categoriesList = [];
        $conditionsList = [];

        try {
            $query = 'SELECT DISTINCT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id';
            $params = [];
            if ($categorySlug) {
                $query .= ' WHERE c.slug = :category_slug';
                $params['category_slug'] = $categorySlug;
                $catStmt = $db->prepare('SELECT name FROM categories WHERE slug = :slug');
                $catStmt->execute(['slug' => $categorySlug]);
                $category = $catStmt->fetch(PDO::FETCH_ASSOC);
                if ($category) { $filterName = 'Categoria: ' . $category['name']; }
            } elseif ($conditionSlug) {
                $query .= ' JOIN product_conditions pc ON p.id = pc.product_id JOIN health_conditions hc ON pc.health_condition_id = hc.id WHERE hc.slug = :condition_slug';
                $params['condition_slug'] = $conditionSlug;
                $condStmt = $db->prepare('SELECT name FROM health_conditions WHERE slug = :slug');
                $condStmt->execute(['slug' => $conditionSlug]);
                $condition = $condStmt->fetch(PDO::FETCH_ASSOC);
                if ($condition) { $filterName = 'Condicion: ' . $condition['name']; }
            } elseif ($search) {
                $query .= ' WHERE p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search';
                $params['search'] = '%' . $search . '%';
                $filterName = 'Busqueda: "' . $search . '"';
            }
            $query .= ' AND p.is_active = 1 ORDER BY p.name ASC';
            if (!$categorySlug && !$conditionSlug && !$search) { $query = str_replace(' AND p.is_active = 1', ' WHERE p.is_active = 1', $query); }
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $categoriesList = $db->query('SELECT name, slug FROM categories ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
            $conditionsList = $db->query('SELECT name, slug FROM health_conditions ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        echo Template::render('Catalog', 'list', compact('products', 'title', 'filterName', 'categoriesList', 'conditionsList'));
    }

    public function show(string $slug): void
    {
        $db = Database::getConnection();
        $product = null;
        $relatedProducts = [];
        $media = [];
        try {
            $this->ensureMediaTable($db);
            $stmt = $db->prepare('SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.slug = :slug AND p.is_active = 1');
            $stmt->execute(['slug' => $slug]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                header('HTTP/1.0 404 Not Found');
                echo 'Producto no encontrado.';
                exit();
            }
            $mediaStmt = $db->prepare('SELECT * FROM product_media WHERE product_id = :id ORDER BY sort_order ASC, id ASC');
            $mediaStmt->execute(['id' => $product['id']]);
            $media = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);
            if ($media === [] && !empty($product['image_url'])) {
                $media[] = ['media_type' => 'image', 'url' => $product['image_url'], 'title' => $product['name']];
            }
            $recStmt = $db->prepare('SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = :cat_id AND p.id != :p_id AND p.is_active = 1 LIMIT 4');
            $recStmt->execute(['cat_id' => $product['category_id'], 'p_id' => $product['id']]);
            $relatedProducts = $recStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {}

        echo Template::render('Catalog', 'detail', ['product' => $product, 'relatedProducts' => $relatedProducts, 'media' => $media]);
    }
}