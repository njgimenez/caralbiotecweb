<?php

namespace Caral\Modules\Admin\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;
use Caral\Modules\Checkout\Services\OrderService;
use Caral\Modules\Admin\Services\CompanySettingsService;

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
        $activeEnvironment = method_exists(CompanySettingsService::class, 'izipayMode')
            ? CompanySettingsService::izipayMode()
            : (in_array(strtolower(trim((string)($_ENV['IZIPAY_MODE'] ?? 'test'))), ['production', 'prod', 'live'], true) ? 'production' : 'test');

        $where = ['(o.payment_environment = :payment_environment OR o.payment_environment IS NULL)'];
        $params = ['payment_environment' => $activeEnvironment];

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

        // Contadores por estado del ambiente activo
        $countStmt = $db->prepare("
            SELECT status, COUNT(*) as cnt
            FROM orders
            WHERE payment_environment = :payment_environment OR payment_environment IS NULL
            GROUP BY status
        ");
        $countStmt->execute(['payment_environment' => $activeEnvironment]);
        $counts = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        echo Template::renderAdmin('orders/index', [
            'orders' => $orders,
            'counts' => $counts,
            'activeEnvironment' => $activeEnvironment,
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

    // GET /admin/ordenes/{id}/boleta
    public function receipt(string $id): void
    {
        $this->requireAdmin();
        $order = OrderService::findById((int)$id);

        if (!$order) {
            http_response_code(404);
            echo 'Boleta no encontrada.';
            return;
        }

        echo Template::renderAdmin('pos/receipt', [
            'order' => $order,
            'orderItems' => OrderService::getItems((int)$id),
            'companySettings' => CompanySettingsService::get(),
            'receiptNumber' => 'B001-' . str_pad((string)$id, 8, '0', STR_PAD_LEFT),
            'returnUrl' => '/admin/ordenes',
            'returnLabel' => 'Órdenes',
            'autoPrint' => false,
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
