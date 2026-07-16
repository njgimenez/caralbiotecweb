<?php

use Phinx\Migration\AbstractMigration;

class AddOrderDeliveryEnvironmentFields extends AbstractMigration
{
    public function up(): void
    {
        $this->addColumnIfMissing('payment_environment', "VARCHAR(20) NOT NULL DEFAULT 'test' AFTER payment_provider");
        $this->addColumnIfMissing('delivery_type', "VARCHAR(20) NOT NULL DEFAULT 'lima' AFTER fulfillment_method");
        $this->addColumnIfMissing('courier', "VARCHAR(30) NULL AFTER delivery_type");
        $this->addIndexIfMissing('idx_orders_payment_environment', 'payment_environment');
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE orders DROP INDEX idx_orders_payment_environment");
        $this->execute("ALTER TABLE orders DROP COLUMN courier, DROP COLUMN delivery_type, DROP COLUMN payment_environment");
    }

    private function addColumnIfMissing(string $column, string $definition): void
    {
        $row = $this->fetchRow("\n            SELECT COUNT(*) AS total\n            FROM INFORMATION_SCHEMA.COLUMNS\n            WHERE TABLE_SCHEMA = DATABASE()\n              AND TABLE_NAME = 'orders'\n              AND COLUMN_NAME = '{$column}'\n        ");

        if ((int)($row['total'] ?? 0) === 0) {
            $this->execute("ALTER TABLE orders ADD COLUMN {$column} {$definition}");
        }
    }

    private function addIndexIfMissing(string $index, string $column): void
    {
        $row = $this->fetchRow("\n            SELECT COUNT(*) AS total\n            FROM INFORMATION_SCHEMA.STATISTICS\n            WHERE TABLE_SCHEMA = DATABASE()\n              AND TABLE_NAME = 'orders'\n              AND INDEX_NAME = '{$index}'\n        ");

        if ((int)($row['total'] ?? 0) === 0) {
            $this->execute("ALTER TABLE orders ADD INDEX {$index} ({$column})");
        }
    }
}
