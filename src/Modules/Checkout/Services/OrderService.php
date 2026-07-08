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

    public static function generateCheckoutToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function createPendingFromCart(array $customerData, string $checkoutToken): int
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
        $orderNumber = self::generatePaymentOrderNumber();

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO orders (
                    order_number, user_id, source_cart_id,
                    customer_name, customer_email, customer_phone,
                    customer_document_type, customer_document,
                    shipping_address, shipping_district, shipping_city,
                    subtotal, shipping_cost, total, currency,
                    payment_method, payment_provider, payment_status, status,
                    checkout_token_hash, checkout_expires_at
                ) VALUES (
                    :order_number, :user_id, :source_cart_id,
                    :name, :email, :phone,
                    :document_type, :document,
                    :address, :district, :city,
                    :subtotal, :shipping, :total, 'PEN',
                    'izipay', 'izipay_formtoken', 'pending', 'pending',
                    :checkout_token_hash, DATE_ADD(NOW(), INTERVAL 24 HOUR)
                )
            ");
            $stmt->execute([
                'order_number' => $orderNumber,
                'user_id' => Session::getUserId(),
                'source_cart_id' => CartService::getActiveCartId(),
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'phone' => $customerData['phone'] ?? null,
                'document_type' => $customerData['document_type'] ?? 'DNI',
                'document' => $customerData['document'] ?? null,
                'address' => $customerData['address'] ?? null,
                'district' => $customerData['district'] ?? null,
                'city' => $customerData['city'] ?? 'Lima',
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'checkout_token_hash' => hash('sha256', $checkoutToken),
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
            $lockStmt = $db->prepare("SELECT payment_status, source_cart_id FROM orders WHERE id = :id FOR UPDATE");
            $lockStmt->execute(['id' => $orderId]);
            $order = $lockStmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                throw new \RuntimeException('Orden no encontrada.');
            }

            if ((string)$order['payment_status'] === 'paid') {
                $db->commit();
                return;
            }

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

            if (!empty($order['source_cart_id'])) {
                $cartStmt = $db->prepare("UPDATE shopping_carts SET status = 'completed' WHERE id = :id");
                $cartStmt->execute(['id' => $order['source_cart_id']]);
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
            WHERE id = :id AND payment_status <> 'paid'
        ");
        $stmt->execute([
            'response_json' => json_encode($paymentResponse, JSON_UNESCAPED_UNICODE),
            'id' => $orderId,
        ]);
    }

    public static function markPaymentStarted(int $orderId, array $paymentResponse): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE orders
            SET payment_status = 'payment_started',
                payment_started_at = COALESCE(payment_started_at, CURRENT_TIMESTAMP),
                payment_response_json = :response_json
            WHERE id = :id AND payment_status IN ('pending', 'payment_started')
        ");
        $stmt->execute([
            'response_json' => json_encode($paymentResponse, JSON_UNESCAPED_UNICODE),
            'id' => $orderId,
        ]);
    }

    public static function markFrontendResult(int $orderId, string $paymentStatus, array $paymentResponse): void
    {
        $db = Database::getConnection();
        $status = $paymentStatus === 'PAID' ? 'authorized' : 'failed';
        $orderStatus = $paymentStatus === 'PAID' ? 'payment_review' : 'failed';
        $stmt = $db->prepare("
            UPDATE orders
            SET payment_status = :payment_status,
                status = :status,
                payment_front_response_json = :response_json
            WHERE id = :id AND payment_status <> 'paid'
        ");
        $stmt->execute([
            'payment_status' => $status,
            'status' => $orderStatus,
            'response_json' => json_encode($paymentResponse, JSON_UNESCAPED_UNICODE),
            'id' => $orderId,
        ]);
    }

    public static function markIpnResult(int $orderId, array $paymentResponse): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE orders
            SET payment_ipn_response_json = :response_json,
                payment_ipn_received_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute([
            'response_json' => json_encode($paymentResponse, JSON_UNESCAPED_UNICODE),
            'id' => $orderId,
        ]);
    }

    public static function cancelPending(int $orderId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE orders
            SET payment_status = 'cancelled',
                status = 'cancelled',
                cancelled_at = CURRENT_TIMESTAMP
            WHERE id = :id AND payment_status IN ('pending', 'payment_started', 'authorized', 'failed')
        ");
        $stmt->execute(['id' => $orderId]);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }

    public static function findByOrderNumber(string $orderNumber): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = :order_number LIMIT 1");
        $stmt->execute(['order_number' => $orderNumber]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        return $order ?: null;
    }

    public static function findByCheckoutToken(int $orderId, string $checkoutToken): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT *
            FROM orders
            WHERE id = :id
              AND checkout_token_hash = :token_hash
              AND (checkout_expires_at IS NULL OR checkout_expires_at >= CURRENT_TIMESTAMP)
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $orderId,
            'token_hash' => hash('sha256', $checkoutToken),
        ]);
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

