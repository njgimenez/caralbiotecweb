<?php

namespace Caral\Modules\Admin\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;
use Caral\Modules\Checkout\Services\OrderService;

class OrderAdminController
{
    private function requireAdmin(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /login'); exit();
        }
        if (!in_array(Session::getUserRole(), ['Super Administrador', 'Operaciones', 'Marketing'])) {
            header('Location: /'); exit();
        }
    }

    // GET /admin/ordenes
    public function index(): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();

        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');

        $where = ['1=1'];
        $params = [];

        if ($status) {
            $where[] = 'o.status = :status';
            $params['status'] = $status;
        }
        if ($search) {
            $where[] = '(o.order_number LIKE :search OR o.customer_email LIKE :search OR o.customer_name LIKE :search)';
            $params['search'] = "%{$search}%";
        }

        $sql = "SELECT o.* FROM orders o WHERE " . implode(' AND ', $where) . " ORDER BY o.created_at DESC LIMIT 100";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Contadores por estado
        $counts = $db->query("
            SELECT status, COUNT(*) as cnt FROM orders GROUP BY status
        ")->fetchAll(PDO::FETCH_KEY_PAIR);

        echo Template::renderAdmin('orders/index', [
            'orders' => $orders,
            'counts' => $counts,
            'filters' => ['status' => $status, 'search' => $search],
        ]);
    }

    // GET /admin/ordenes/{id}
    public function show(string $id): void
    {
        $this->requireAdmin();
        $order = OrderService::findById((int)$id);

        if (!$order) {
            Session::set('flash_error', 'Orden no encontrada.');
            header('Location: /admin/ordenes');
            exit();
        }

        $orderItems = OrderService::getItems((int)$id);

        echo Template::renderAdmin('orders/show', [
            'order'      => $order,
            'orderItems' => $orderItems,
        ]);
    }

    // POST /admin/ordenes/{id}/estado
    public function updateStatus(string $id): void
    {
        $this->requireAdmin();
        $db = Database::getConnection();
        $newStatus = $_POST['status'] ?? '';

        $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        if (!in_array($newStatus, $allowed)) {
            Session::set('flash_error', 'Estado no válido.');
            header("Location: /admin/ordenes/{$id}");
            exit();
        }

        $db->prepare("UPDATE orders SET status = :status WHERE id = :id")
           ->execute(['status' => $newStatus, 'id' => (int)$id]);

        Session::set('flash_success', "Estado de la orden actualizado a «{$newStatus}».");
        header("Location: /admin/ordenes/{$id}");
        exit();
    }
}
