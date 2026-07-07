<?php

namespace Caral\Modules\Catalog\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Template;

class ProductController
{
    public function index(): void
    {
        $categorySlug = $_GET['categoria'] ?? null;
        $conditionSlug = $_GET['condicion'] ?? null;
        $search = $_GET['search'] ?? null;

        $db = Database::getConnection();
        $products = [];
        $title = 'Catálogo de Productos - Caral Biotec';
        $filterName = '';

        try {
            $query = "
                SELECT DISTINCT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id
            ";
            $params = [];

            if ($categorySlug) {
                $query .= " WHERE c.slug = :category_slug";
                $params['category_slug'] = $categorySlug;

                // Obtener nombre de la categoría para el título
                $catStmt = $db->prepare("SELECT name FROM categories WHERE slug = :slug");
                $catStmt->execute(['slug' => $categorySlug]);
                $category = $catStmt->fetch(PDO::FETCH_ASSOC);
                if ($category) {
                    $filterName = 'Categoría: ' . $category['name'];
                }
            } elseif ($conditionSlug) {
                $query .= " 
                    JOIN product_conditions pc ON p.id = pc.product_id
                    JOIN health_conditions hc ON pc.health_condition_id = hc.id
                    WHERE hc.slug = :condition_slug
                ";
                $params['condition_slug'] = $conditionSlug;

                // Obtener nombre de la condición para el título
                $condStmt = $db->prepare("SELECT name FROM health_conditions WHERE slug = :slug");
                $condStmt->execute(['slug' => $conditionSlug]);
                $condition = $condStmt->fetch(PDO::FETCH_ASSOC);
                if ($condition) {
                    $filterName = 'Condición: ' . $condition['name'];
                }
            } elseif ($search) {
                $query .= " WHERE p.name LIKE :search OR p.description LIKE :search OR p.sku LIKE :search";
                $params['search'] = '%' . $search . '%';
                $filterName = 'Búsqueda: "' . $search . '"';
            }

            $query .= " AND p.is_active = 1 ORDER BY p.name ASC";
            
            // Si no hay WHERE previo, limpiar el query en caso de búsqueda u otros filtros vacíos
            if (!$categorySlug && !$conditionSlug && !$search) {
                $query = str_replace(" AND p.is_active = 1", " WHERE p.is_active = 1", $query);
            }

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener todas las categorías para la barra lateral de filtros
            $catQuery = $db->query("SELECT name, slug FROM categories ORDER BY id ASC");
            $categoriesList = $catQuery->fetchAll(PDO::FETCH_ASSOC);

            // Obtener todas las condiciones de salud para la barra lateral
            $condQuery = $db->query("SELECT name, slug FROM health_conditions ORDER BY id ASC");
            $conditionsList = $condQuery->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $categoriesList = [];
            $conditionsList = [];
        }

        echo Template::render('Catalog', 'list', [
            'products' => $products,
            'title' => $title,
            'filterName' => $filterName,
            'categoriesList' => $categoriesList,
            'conditionsList' => $conditionsList
        ]);
    }

    public function show(string $slug): void
    {
        $db = Database::getConnection();

        try {
            // 1. Consultar el producto por slug
            $stmt = $db->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.slug = :slug AND p.is_active = 1
            ");
            $stmt->execute(['slug' => $slug]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                // Producto no encontrado
                header("HTTP/1.0 404 Not Found");
                echo "Producto no encontrado.";
                exit();
            }

            // 2. Obtener productos recomendados de la misma categoría
            $recStmt = $db->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = :cat_id AND p.id != :p_id AND p.is_active = 1 
                LIMIT 4
            ");
            $recStmt->execute([
                'cat_id' => $product['category_id'],
                'p_id' => $product['id']
            ]);
            $relatedProducts = $recStmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $product = null;
            $relatedProducts = [];
        }

        echo Template::render('Catalog', 'detail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }
}
