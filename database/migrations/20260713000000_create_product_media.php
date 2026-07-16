<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProductMedia extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('
            CREATE TABLE IF NOT EXISTS product_media (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                product_id BIGINT UNSIGNED NOT NULL,
                media_type VARCHAR(20) NOT NULL DEFAULT "image",
                url VARCHAR(600) NOT NULL,
                title VARCHAR(190) NULL,
                sort_order INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_product_media_product (product_id),
                CONSTRAINT fk_product_media_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');
    }

    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS product_media');
    }
}