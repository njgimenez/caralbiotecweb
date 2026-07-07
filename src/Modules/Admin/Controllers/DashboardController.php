<?php

namespace Caral\Modules\Admin\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;

class DashboardController
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

    public function index(): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        // KPIs
        $stats = [
            'total_products'   => $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn(),
            'total_categories' => $db->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
            'total_users'      => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'low_stock'        => $db->query("SELECT COUNT(*) FROM products WHERE stock < 10 AND is_active = 1")->fetchColumn(),
        ];

        // Últimos 5 productos
        $recentProducts = $db->query("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Categorías con conteo de productos
        $categories = $db->query("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY c.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo Template::renderAdmin('dashboard/index', [
            'stats'          => $stats,
            'recentProducts' => $recentProducts,
            'categories'     => $categories,
        ]);
    }
}
