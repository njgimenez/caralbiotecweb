<?php

use Phinx\Migration\AbstractMigration;

class AddFulfillmentMethodToOrders extends AbstractMigration
{
    public function up(): void
    {
        $row = $this->fetchRow("
            SELECT COUNT(*) AS total
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'orders'
              AND COLUMN_NAME = 'fulfillment_method'
        ");

        if ((int)($row['total'] ?? 0) === 0) {
            $this->execute("
                ALTER TABLE orders
                    ADD COLUMN fulfillment_method VARCHAR(20) NOT NULL DEFAULT 'delivery' AFTER shipping_city
            ");
        }
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE orders DROP COLUMN fulfillment_method");
    }
}
