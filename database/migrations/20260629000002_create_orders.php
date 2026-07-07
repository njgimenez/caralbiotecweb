<?php

use Phinx\Migration\AbstractMigration;

class CreateOrders extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("
            CREATE TABLE orders (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(30) NOT NULL,
                user_id BIGINT UNSIGNED NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'pending',
                -- Datos del comprador (capturados al momento de la orden)
                customer_name VARCHAR(150) NOT NULL,
                customer_email VARCHAR(254) NOT NULL,
                customer_phone VARCHAR(30) NULL,
                -- Dirección de envío
                shipping_address TEXT NULL,
                shipping_district VARCHAR(100) NULL,
                shipping_city VARCHAR(100) NULL,
                -- Montos
                subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                shipping_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                currency CHAR(3) NOT NULL DEFAULT 'PEN',
                -- Pago
                payment_method VARCHAR(50) NULL,
                payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
                payment_operation_id VARCHAR(100) NULL,
                payment_response_json LONGTEXT NULL,
                -- Notas
                notes TEXT NULL,
                -- Timestamps
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_orders_number (order_number),
                INDEX idx_orders_user (user_id),
                INDEX idx_orders_status (status),
                INDEX idx_orders_email (customer_email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->execute("
            CREATE TABLE order_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id BIGINT UNSIGNED NOT NULL,
                product_id BIGINT UNSIGNED NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                product_sku VARCHAR(80) NOT NULL,
                quantity INT UNSIGNED NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                total_price DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
}
