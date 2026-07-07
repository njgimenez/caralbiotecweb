<?php

namespace Caral\Modules\Admin\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;
use Caral\Modules\Admin\Services\CompanySettingsService;
use Caral\Modules\Checkout\Services\OrderService;

class PosController
{
    private const OPERATOR_EMAIL = 'operador@caralbiotec.com';

    private function requirePosOperator(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit();
        }

        if (Session::getUserEmail() !== self::OPERATOR_EMAIL) {
            header('Location: /');
            exit();
        }
    }

    public function index(): void
    {
        $this->requirePosOperator();
        $db = Database::getConnection();

        $products = $db->query("
            SELECT p.id, p.name, p.slug, p.sku, p.price, p.stock, p.image_url, c.name AS category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1
            ORDER BY c.name ASC, p.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $categories = $db->query("
            SELECT DISTINCT c.name
            FROM categories c
            JOIN products p ON p.category_id = c.id
            WHERE p.is_active = 1
            ORDER BY c.name ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        echo Template::renderAdmin('pos/index', [
            'products' => $products,
            'categories' => $categories,
            'companySettings' => CompanySettingsService::get(),
        ]);
    }

    public function store(): void
    {
        $this->requirePosOperator();
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Solicitud inválida.']);
            return;
        }

        $items = $payload['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            http_response_code(422);
            echo json_encode(['error' => 'Agrega al menos un producto.']);
            return;
        }

        $paymentMethod = $payload['payment_method'] ?? 'efectivo';
        $allowedPayments = ['efectivo', 'yape', 'plin', 'tarjeta'];
        if (!in_array($paymentMethod, $allowedPayments, true)) {
            http_response_code(422);
            echo json_encode(['error' => 'Método de pago no válido.']);
            return;
        }

        $documentType = $payload['document_type'] ?? 'boleta';
        if (!in_array($documentType, ['boleta', 'factura'], true)) {
            http_response_code(422);
            echo json_encode(['error' => 'Tipo de comprobante no válido.']);
            return;
        }

        $documentNumber = preg_replace('/\D+/', '', (string)($payload['document_number'] ?? ''));
        $documentName = trim((string)($payload['document_name'] ?? ''));

        if ($documentType === 'factura') {
            if (!preg_match('/^\d{11}$/', $documentNumber)) {
                http_response_code(422);
                echo json_encode(['error' => 'Ingresa un RUC válido de 11 dígitos.']);
                return;
            }

            if ($documentName === '') {
                http_response_code(422);
                echo json_encode(['error' => 'Ingresa la razón social para la factura.']);
                return;
            }
        } else {
            $documentNumber = null;
            $documentName = null;
        }

        $discount = max(0, round((float)($payload['discount'] ?? 0), 2));
        $received = max(0, round((float)($payload['received'] ?? 0), 2));

        $requested = [];
        foreach ($items as $item) {
            $productId = (int)($item['id'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                http_response_code(422);
                echo json_encode(['error' => 'Producto o cantidad no válida.']);
                return;
            }

            $requested[$productId] = ($requested[$productId] ?? 0) + $quantity;
        }

        $db = Database::getConnection();
        $companySettings = CompanySettingsService::get();
        $db->beginTransaction();

        try {
            $subtotal = 0.0;
            $orderLines = [];

            $productStmt = $db->prepare("
                SELECT id, name, sku, price, stock
                FROM products
                WHERE id = :id AND is_active = 1
                FOR UPDATE
            ");

            foreach ($requested as $productId => $quantity) {
                $productStmt->execute(['id' => $productId]);
                $product = $productStmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new \RuntimeException('Uno de los productos ya no está disponible.');
                }

                if ((int)$product['stock'] < $quantity) {
                    throw new \RuntimeException("Stock insuficiente para {$product['name']}.");
                }

                $unitPrice = (float)$product['price'];
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;

                $orderLines[] = [
                    'product_id' => (int)$product['id'],
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $discount = min($discount, $subtotal);
            $total = round($subtotal - $discount, 2);

            if ($total <= 0) {
                throw new \RuntimeException('El total debe ser mayor a cero.');
            }

            if ($paymentMethod === 'efectivo' && $received < $total) {
                throw new \RuntimeException('El monto recibido no cubre el total.');
            }

            $orderNumber = OrderService::generateOrderNumber();
            $notes = sprintf(
                'Venta POS. Operador: %s. Comprobante: %s. Descuento: S/. %.2f. Recibido: S/. %.2f. Vuelto: S/. %.2f.',
                Session::getUserEmail(),
                strtoupper($documentType),
                $discount,
                $received,
                round(max($received - $total, 0), 2)
            );

            $orderStmt = $db->prepare("
                INSERT INTO orders (
                    order_number, user_id,
                    customer_name, customer_email, customer_phone,
                    subtotal, shipping_cost, total, currency,
                    document_type, document_number, document_name,
                    payment_method, payment_status, status, notes
                ) VALUES (
                    :order_number, :user_id,
                    :customer_name, :customer_email, NULL,
                    :subtotal, 0.00, :total, 'PEN',
                    :document_type, :document_number, :document_name,
                    :payment_method, 'paid', 'delivered', :notes
                )
            ");
            $orderStmt->execute([
                'order_number' => $orderNumber,
                'user_id' => Session::getUserId(),
                'customer_name' => 'Venta POS',
                'customer_email' => Session::getUserEmail(),
                'subtotal' => $subtotal,
                'total' => $total,
                'document_type' => $documentType,
                'document_number' => $documentNumber,
                'document_name' => $documentName,
                'payment_method' => 'pos_' . $paymentMethod,
                'notes' => $notes . sprintf(' IGV aplicado: %.2f%%.', (float)$companySettings['igv_percent']),
            ]);
            $orderId = (int)$db->lastInsertId();

            $lineStmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price)
                VALUES (:order_id, :product_id, :name, :sku, :quantity, :unit_price, :total_price)
            ");
            $stockStmt = $db->prepare("
                UPDATE products
                SET stock = stock - :stock_quantity
                WHERE id = :stock_product_id AND stock >= :stock_min_quantity
            ");

            foreach ($orderLines as $line) {
                $lineStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $line['product_id'],
                    'name' => $line['name'],
                    'sku' => $line['sku'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total_price' => $line['total_price'],
                ]);

                $stockStmt->execute([
                    'stock_quantity' => $line['quantity'],
                    'stock_product_id' => $line['product_id'],
                    'stock_min_quantity' => $line['quantity'],
                ]);

                if ($stockStmt->rowCount() !== 1) {
                    throw new \RuntimeException("No se pudo descontar stock para {$line['name']}.");
                }
            }

            $db->commit();

            echo json_encode([
                'ok' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'change' => round(max($received - $total, 0), 2),
                'receipt_url' => "/admin/pos/comprobante/{$orderId}",
            ]);
        } catch (\Throwable $e) {
            $db->rollBack();
            http_response_code(422);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function sales(): void
    {
        $this->requirePosOperator();
        [$from, $to, $orders, $summary] = $this->getPosSalesData();

        echo Template::renderAdmin('pos/sales', [
            'from' => $from,
            'to' => $to,
            'orders' => $orders,
            'summary' => $summary,
        ]);
    }

    public function report(): void
    {
        $this->requirePosOperator();
        [$from, $to, $orders, $summary] = $this->getPosSalesData();

        echo Template::renderAdmin('pos/report', [
            'from' => $from,
            'to' => $to,
            'orders' => $orders,
            'summary' => $summary,
            'companySettings' => CompanySettingsService::get(),
        ]);
    }

    public function receipt(string $id): void
    {
        $this->requirePosOperator();

        $order = OrderService::findById((int)$id);
        if (!$order || !str_starts_with((string)$order['payment_method'], 'pos_')) {
            http_response_code(404);
            echo 'Comprobante no encontrado.';
            return;
        }

        echo Template::renderAdmin('pos/receipt', [
            'order' => $order,
            'orderItems' => OrderService::getItems((int)$id),
            'companySettings' => CompanySettingsService::get(),
        ]);
    }

    private function getPosSalesData(): array
    {
        $from = $_GET['from'] ?? date('Y-m-d');
        $to = $_GET['to'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = date('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = date('Y-m-d');
        }

        $db = Database::getConnection();
        $params = [
            'from' => $from . ' 00:00:00',
            'to' => $to . ' 23:59:59',
        ];

        $stmt = $db->prepare("
            SELECT *
            FROM orders
            WHERE payment_method LIKE 'pos_%'
              AND created_at BETWEEN :from AND :to
            ORDER BY created_at DESC
        ");
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summaryStmt = $db->prepare("
            SELECT
                COUNT(*) AS sales_count,
                COALESCE(SUM(total), 0) AS total_amount,
                COALESCE(SUM(CASE WHEN payment_method = 'pos_efectivo' THEN total ELSE 0 END), 0) AS cash_total,
                COALESCE(SUM(CASE WHEN payment_method = 'pos_yape' THEN total ELSE 0 END), 0) AS yape_total,
                COALESCE(SUM(CASE WHEN payment_method = 'pos_plin' THEN total ELSE 0 END), 0) AS plin_total,
                COALESCE(SUM(CASE WHEN payment_method = 'pos_tarjeta' THEN total ELSE 0 END), 0) AS card_total
            FROM orders
            WHERE payment_method LIKE 'pos_%'
              AND created_at BETWEEN :from AND :to
        ");
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [$from, $to, $orders, $summary];
    }
}
