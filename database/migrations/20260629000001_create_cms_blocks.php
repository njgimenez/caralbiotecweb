<?php

use Phinx\Migration\AbstractMigration;

class CreateCmsBlocks extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("
            CREATE TABLE cms_blocks (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                block_key VARCHAR(50) NOT NULL,
                title VARCHAR(150) NOT NULL,
                content_json LONGTEXT NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_cms_blocks_key (block_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
}
