<?php

use Phinx\Migration\AbstractMigration;

class CreateBlogPostsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->execute("
            CREATE TABLE blog_posts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                author_id BIGINT UNSIGNED NULL,
                title VARCHAR(220) NOT NULL,
                slug VARCHAR(220) NOT NULL,
                excerpt VARCHAR(320) NULL,
                content_html MEDIUMTEXT NOT NULL,
                featured_image_url VARCHAR(500) NULL,
                meta_title VARCHAR(220) NULL,
                meta_description VARCHAR(320) NULL,
                tags VARCHAR(500) NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'draft',
                published_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_blog_posts_slug (slug),
                KEY idx_blog_posts_status_published (status, published_at),
                KEY idx_blog_posts_author (author_id),
                FULLTEXT KEY ft_blog_posts_search (title, excerpt, content_html, tags),
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }
}
