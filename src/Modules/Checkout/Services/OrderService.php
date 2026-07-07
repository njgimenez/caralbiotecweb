<?php

namespace Caral\Modules\Checkout\Services;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Modules\Cart\Services\CartService;

class OrderService
{
    public static function generateOrderNumber(): string
    {
        return 'CAR-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }

    public static function generatePaymentOrderNumber(): string
    {
        return date('YmdHis') . random_int(1000, 9999);
    }

    public static function createFromCart(array $customerData): int
    {
        $db = Database::getConnection();
        $items = CartService::getItems();

        if (empty($items)) {
            throw new \RuntimeException('El carrito esta vacio.');
        }

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price_seen'] * $item['quantity'];
        }

        $shipping = 0.00;
        $total = $subtotal + $shipping;

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO orders (
                    order_number, user_id,
                    customer_name, customer_email, customer_phone,
                    shipping_address, shipping_district, shipping_city,
                    subtotal, shipping_cost, total, currency,
                    payment_method, payment_status, status
                ) VALUES (
                    :order_number, :user_id,
                    :name, :email, :phone,
                    :address, :district, :city,
                    :subtotal, :shipping, :total, 'PEN',
                    :payment_method, 'pending', 'pending'
                )
            ");
            $stmt->execute([
                'order_number' => $customerData['order_number'] ?? self::generateOrderNumber(),
                'user_id' => Session::getUserId(),
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? null,
                'address' => $customerData['address'] ?? null,
                'district' => $customerData['district'] ?? null,
                'city' => $customerData['city'] ?? 'Lima',
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'payment_method' => $customerData['payment_method'] ?? 'izipay',
            ]);
            $orderId = (int)$db->lastInsertId();

            $lineStmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price)
                VALUES (:order_id, :product_id, :name, :sku, :qty, :unit_price, :total_price)
            ");
            foreach ($items as $item) {
                $lineStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'qty' => $item['quantity'],
                    'unit_price' => $item['price_seen'],
                    'total_price' => $item['price_seen'] * $item['quantity'],
                ]);
            }

            $db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function markAsPaid(int $orderId, string $chargeId, array $paymentResponse): void
    {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                UPDATE orders
                SET payment_status = 'paid',
                    status = 'processing',
                    payment_operation_id = :charge_id,
                    payment_response_json = :response_json
                WHERE id = :id
            ");
            $stmt->execute([
                'charge_id' => $chargeId,
                'response_json' => json_encode($paymentResponse, JSON_UNESCAPED_UNICODE),
                'id' => $orderId,
            ]);

            $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :id");
            $itemsStmt->execute(['id' => $orderId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $stockStmt = $db->prepare("UPDATE products SET stock = stock - :qty_update WHERE id = :pid AND stock >= :qty_check");
            foreach ($items as $item) {
                $stockStmt->execute(['qty_update' => $item['quantity'], 'qty_check' => $item['quantity'], 'pid' => $item['product_id']]);
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function markAsFailed(int $orderId, array $paymentResponse): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE orders
            SET payment_status = 'failed', status = 'failed',
                payment_response_json = :response_json
            WHERE id = :id
        ");
        $stmt->execute([
            'response_json' => json_encode($paymentResponse, JSON_UNESCAPED_UNICODE),
            'id' => $orderId,
        ]);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }

    public static function getItems(int $orderId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = :id ORDER BY id ASC");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

