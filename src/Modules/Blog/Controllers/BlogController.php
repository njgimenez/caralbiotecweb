<?php

namespace Caral\Modules\Blog\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Template;

class BlogController
{
    public function index(): void
    {
        $db = Database::getConnection();
        $search = trim($_GET['q'] ?? '');
        $tag = trim($_GET['tag'] ?? '');
        $where = ["p.status = 'published'", 'p.published_at IS NOT NULL', 'p.published_at <= NOW()'];
        $params = [];

        if ($search !== '') {
            $where[] = '(p.title LIKE :search_title OR p.excerpt LIKE :search_excerpt OR p.content_html LIKE :search_content OR p.tags LIKE :search_tags)';
            $term = '%' . $search . '%';
            $params['search_title'] = $term;
            $params['search_excerpt'] = $term;
            $params['search_content'] = $term;
            $params['search_tags'] = $term;
        }

        if ($tag !== '') {
            $where[] = 'p.tags LIKE :tag';
            $params['tag'] = '%' . $tag . '%';
        }

        $stmt = $db->prepare("
            SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image_url, p.tags, p.published_at, u.email AS author_email
            FROM blog_posts p
            LEFT JOIN users u ON u.id = p.author_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.published_at DESC, p.id DESC
            LIMIT 24
        ");
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $featured = $posts[0] ?? null;
        $rest = array_slice($posts, 1);

        echo Template::render('Blog', 'index', [
            'posts' => $posts,
            'featured' => $featured,
            'rest' => $rest,
            'search' => $search,
            'tag' => $tag,
            'title' => 'Blog de salud y bienestar | Caral Biotec',
            'metaDescription' => 'Consejos, guías y novedades sobre bienestar, rehabilitación, nutrición y cuidado integral de la salud.'
        ]);
    }

    public function show(string $slug): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.*, u.email AS author_email
            FROM blog_posts p
            LEFT JOIN users u ON u.id = p.author_id
            WHERE p.slug = :slug
              AND p.status = 'published'
              AND p.published_at IS NOT NULL
              AND p.published_at <= NOW()
            LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            http_response_code(404);
            echo Template::render('Blog', 'not_found', [
                'title' => 'Entrada no encontrada | Caral Biotec'
            ]);
            return;
        }

        $relatedStmt = $db->prepare("
            SELECT id, title, slug, excerpt, featured_image_url, published_at
            FROM blog_posts
            WHERE status = 'published'
              AND published_at IS NOT NULL
              AND published_at <= NOW()
              AND id <> :id
            ORDER BY published_at DESC, id DESC
            LIMIT 3
        ");
        $relatedStmt->execute(['id' => $post['id']]);

        echo Template::render('Blog', 'show', [
            'post' => $post,
            'related' => $relatedStmt->fetchAll(PDO::FETCH_ASSOC),
            'title' => ($post['meta_title'] ?: $post['title']) . ' | Caral Biotec',
            'metaDescription' => $post['meta_description'] ?: $post['excerpt']
        ]);
    }
}
