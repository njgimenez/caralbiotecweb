<?php

use Phinx\Migration\AbstractMigration;

class AddIzipayFormtokenCheckoutFields extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE orders
                ADD COLUMN source_cart_id BIGINT UNSIGNED NULL AFTER user_id,
                ADD COLUMN customer_document_type VARCHAR(20) NULL AFTER customer_phone,
                ADD COLUMN customer_document VARCHAR(30) NULL AFTER customer_document_type,
                ADD COLUMN payment_provider VARCHAR(50) NULL AFTER payment_method,
                ADD COLUMN payment_started_at TIMESTAMP NULL AFTER payment_status,
                ADD COLUMN payment_front_response_json LONGTEXT NULL AFTER payment_response_json,
                ADD COLUMN payment_ipn_response_json LONGTEXT NULL AFTER payment_front_response_json,
                ADD COLUMN payment_ipn_received_at TIMESTAMP NULL AFTER payment_ipn_response_json,
                ADD COLUMN checkout_token_hash CHAR(64) NULL AFTER payment_ipn_received_at,
                ADD COLUMN checkout_expires_at TIMESTAMP NULL AFTER checkout_token_hash,
                ADD COLUMN cancelled_at TIMESTAMP NULL AFTER notes,
                ADD INDEX idx_orders_source_cart (source_cart_id),
                ADD INDEX idx_orders_checkout_token (checkout_token_hash),
                ADD INDEX idx_orders_payment_status (payment_status)
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE orders
                DROP INDEX idx_orders_payment_status,
                DROP INDEX idx_orders_checkout_token,
                DROP INDEX idx_orders_source_cart,
                DROP COLUMN cancelled_at,
                DROP COLUMN checkout_expires_at,
                DROP COLUMN checkout_token_hash,
                DROP COLUMN payment_ipn_received_at,
                DROP COLUMN payment_ipn_response_json,
                DROP COLUMN payment_front_response_json,
                DROP COLUMN payment_started_at,
                DROP COLUMN payment_provider,
                DROP COLUMN customer_document,
                DROP COLUMN customer_document_type,
                DROP COLUMN source_cart_id
        ");
    }
}
