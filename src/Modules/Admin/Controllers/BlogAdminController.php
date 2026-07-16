<?php

namespace Caral\Modules\Admin\Controllers;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;
use Caral\Core\Template;

class BlogAdminController
{
    private function requireEditor(): void
    {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            exit();
        }

        if (!in_array(Session::getUserRole(), ['Super Administrador', 'Marketing', 'Editor'])) {
            header('Location: /');
            exit();
        }
    }

    public function index(): void
    {
        $this->requireEditor();
        $db = Database::getConnection();
        $posts = $db->query("
            SELECT p.*, u.email AS author_email
            FROM blog_posts p
            LEFT JOIN users u ON u.id = p.author_id
            ORDER BY COALESCE(p.published_at, p.created_at) DESC, p.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo Template::renderAdmin('blog/index', ['posts' => $posts]);
    }

    public function create(): void
    {
        $this->requireEditor();
        echo Template::renderAdmin('blog/form', [
            'post' => $this->emptyPost(),
            'action' => '/admin/blog/guardar',
            'mode' => 'create'
        ]);
    }

    public function store(): void
    {
        $this->requireEditor();
        $db = Database::getConnection();
        $data = $this->validatedData();
        $slug = $this->uniqueSlug($db, $data['slug'] ?: $data['title']);
        $publishedAt = $data['status'] === 'published' ? ($data['published_at'] ?: date('Y-m-d H:i:s')) : null;

        $stmt = $db->prepare("
            INSERT INTO blog_posts
                (author_id, title, slug, excerpt, content_html, featured_image_url, meta_title, meta_description, tags, status, published_at)
            VALUES
                (:author_id, :title, :slug, :excerpt, :content_html, :featured_image_url, :meta_title, :meta_description, :tags, :status, :published_at)
        ");
        $stmt->execute([
            'author_id' => Session::getUserId(),
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'],
            'content_html' => $data['content_html'],
            'featured_image_url' => $data['featured_image_url'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'tags' => $data['tags'],
            'status' => $data['status'],
            'published_at' => $publishedAt,
        ]);

        Session::set('flash_success', 'Entrada creada correctamente.');
        header('Location: /admin/blog');
        exit();
    }

    public function edit(int $id): void
    {
        $this->requireEditor();
        $post = $this->findPost($id);
        if (!$post) {
            Session::set('flash_error', 'Entrada no encontrada.');
            header('Location: /admin/blog');
            exit();
        }

        echo Template::renderAdmin('blog/form', [
            'post' => $post,
            'action' => "/admin/blog/{$id}/actualizar",
            'mode' => 'edit'
        ]);
    }

    public function update(int $id): void
    {
        $this->requireEditor();
        $db = Database::getConnection();
        $post = $this->findPost($id);
        if (!$post) {
            Session::set('flash_error', 'Entrada no encontrada.');
            header('Location: /admin/blog');
            exit();
        }

        $data = $this->validatedData();
        $slug = $this->uniqueSlug($db, $data['slug'] ?: $data['title'], $id);
        $publishedAt = null;
        if ($data['status'] === 'published') {
            $publishedAt = $data['published_at'] ?: ($post['published_at'] ?: date('Y-m-d H:i:s'));
        }

        $stmt = $db->prepare("
            UPDATE blog_posts
            SET title = :title,
                slug = :slug,
                excerpt = :excerpt,
                content_html = :content_html,
                featured_image_url = :featured_image_url,
                meta_title = :meta_title,
                meta_description = :meta_description,
                tags = :tags,
                status = :status,
                published_at = :published_at
            WHERE id = :id
        ");
        $stmt->execute([
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'],
            'content_html' => $data['content_html'],
            'featured_image_url' => $data['featured_image_url'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'tags' => $data['tags'],
            'status' => $data['status'],
            'published_at' => $publishedAt,
            'id' => $id,
        ]);

        Session::set('flash_success', 'Entrada actualizada correctamente.');
        header('Location: /admin/blog');
        exit();
    }

    public function destroy(int $id): void
    {
        $this->requireEditor();
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM blog_posts WHERE id = :id');
        $stmt->execute(['id' => $id]);

        Session::set('flash_success', 'Entrada eliminada.');
        header('Location: /admin/blog');
        exit();
    }

    private function findPost(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT * FROM blog_posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        return $post ?: null;
    }

    private function validatedData(): array
    {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            Session::set('flash_error', 'El título es obligatorio.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/admin/blog'));
            exit();
        }

        $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published']) ? $_POST['status'] : 'draft';
        $publishedAt = trim($_POST['published_at'] ?? '');
        $publishedAt = $publishedAt !== '' ? str_replace('T', ' ', $publishedAt) . ':00' : null;

        return [
            'title' => $title,
            'slug' => trim($_POST['slug'] ?? ''),
            'excerpt' => $this->limitText($_POST['excerpt'] ?? '', 320),
            'content_html' => $this->sanitizeHtml($_POST['content_html'] ?? ''),
            'featured_image_url' => $this->normalizeMediaUrl($_POST['featured_image_url'] ?? ''),
            'meta_title' => $this->limitText($_POST['meta_title'] ?? '', 220),
            'meta_description' => $this->limitText($_POST['meta_description'] ?? '', 320),
            'tags' => $this->limitText($_POST['tags'] ?? '', 500),
            'status' => $status,
            'published_at' => $publishedAt,
        ];
    }

    private function normalizeMediaUrl(string $value): string
    {
        $url = trim($value);
        if ($url === '' || preg_match('#^(?:https?://|/)#i', $url)) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return '';
        }

        if (preg_match('#^(?:uploads|images)/#i', $url)) {
            return '/' . $url;
        }

        if (preg_match('#^(?:www\.)?[a-z0-9-]+(?:\.[a-z0-9-]+)+(?:[/:?#].*)?$#i', $url)) {
            return 'https://' . $url;
        }

        return $url;
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $html);
        $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/javascript\s*:/i', '', $html);
        $html = $this->normalizeStoredMediaEmbeds($html);
        $html = preg_replace('#<div\b[^>]*>#i', '<p>', $html);
        $html = preg_replace('#</div>#i', '</p>', $html);
        $html = strip_tags($html, '<p><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><blockquote><a><img><figure><figcaption><hr><table><thead><tbody><tr><th><td>');

        return $this->embedMediaUrls($html);
    }

    private function normalizeStoredMediaEmbeds(string $html): string
    {
        $html = preg_replace_callback(
            '#<div[^>]*class=["\'][^"\']*blog-video-embed[^"\']*["\'][^>]*>\s*<iframe[^>]+src=["\']https://www\.youtube-nocookie\.com/embed/([a-zA-Z0-9_-]{11})[^"\']*["\'][^>]*>\s*</iframe>\s*</div>#i',
            fn(array $matches): string => '<p>https://youtu.be/' . $matches[1] . '</p>',
            $html
        );
        $html = preg_replace_callback(
            '#<div[^>]*class=["\'][^"\']*blog-video-embed[^"\']*["\'][^>]*>\s*<iframe[^>]+src=["\']https://player\.vimeo\.com/video/([0-9]+)[^"\']*["\'][^>]*>\s*</iframe>\s*</div>#i',
            fn(array $matches): string => '<p>https://vimeo.com/' . $matches[1] . '</p>',
            $html
        );
        $html = preg_replace_callback(
            '#<div[^>]*class=["\'][^"\']*blog-video-embed[^"\']*["\'][^>]*>\s*<video[^>]+src=["\']([^"\']+)["\'][^>]*>.*?</video>\s*</div>#is',
            fn(array $matches): string => '<p>' . htmlspecialchars(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') . '</p>',
            $html
        );
        $html = preg_replace_callback(
            '#<div[^>]*class=["\'][^"\']*blog-video-embed[^"\']*["\'][^>]*>\s*<video[^>]*>\s*<source[^>]+src=["\']([^"\']+)["\'][^>]*>.*?</video>\s*</div>#is',
            fn(array $matches): string => '<p>' . htmlspecialchars(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') . '</p>',
            $html
        );

        return $html;
    }

    private function embedMediaUrls(string $html): string
    {
        $plain = trim(strip_tags($html));
        if ($plain !== '' && preg_match('#^(?:https?://|/)[^\s<]+$#i', $plain)) {
            $html = '<p>' . htmlspecialchars($plain, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $html = preg_replace_callback(
            '#<p>\s*(?:<a[^>]+href=["\']([^"\']+)["\'][^>]*>\s*)?((?:https?://|/)[^\s<]+)(?:\s*</a>)?\s*</p>#i',
            function (array $matches): string {
                $url = html_entity_decode($matches[1] ?: $matches[2], ENT_QUOTES, 'UTF-8');
                $url = trim($url);

                if ($youtubeId = $this->extractYoutubeId($url)) {
                    $src = 'https://www.youtube-nocookie.com/embed/' . htmlspecialchars($youtubeId, ENT_QUOTES, 'UTF-8');
                    return '<div class="blog-video-embed"><iframe src="' . $src . '" title="Video de YouTube" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>';
                }

                if ($vimeoId = $this->extractVimeoId($url)) {
                    $src = 'https://player.vimeo.com/video/' . htmlspecialchars($vimeoId, ENT_QUOTES, 'UTF-8');
                    return '<div class="blog-video-embed"><iframe src="' . $src . '" title="Video de Vimeo" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
                }

                if ($this->isVideoUrl($url)) {
                    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                    return '<div class="blog-video-embed blog-video-file"><video controls preload="metadata" playsinline src="' . $safeUrl . '"></video></div>';
                }

                if ($this->isImageUrl($url)) {
                    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                    return '<figure><img src="' . $safeUrl . '" alt="" loading="lazy"></figure>';
                }

                return $matches[0];
            },
            $html
        );

        return $html;
    }

    private function extractYoutubeId(string $url): ?string
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');

        if (str_contains($host, 'youtu.be')) {
            $id = explode('/', $path)[0] ?? '';
            return preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) ? $id : null;
        }

        if (!str_contains($host, 'youtube.com')) {
            return null;
        }

        if (($parts['query'] ?? '') !== '') {
            parse_str($parts['query'], $query);
            if (!empty($query['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $query['v'])) {
                return $query['v'];
            }
        }

        $segments = explode('/', $path);
        if (in_array($segments[0] ?? '', ['embed', 'shorts'], true) && !empty($segments[1]) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $segments[1])) {
            return $segments[1];
        }

        return null;
    }

    private function extractVimeoId(string $url): ?string
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $isVimeoHost = $host === 'vimeo.com' || str_ends_with($host, '.vimeo.com');
        if (!$isVimeoHost) {
            return null;
        }

        $path = trim($parts['path'] ?? '', '/');
        if (preg_match('#(?:^|/)([0-9]+)(?:$|/)#', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isVideoUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        return (bool)preg_match('/\.(mp4|webm|ogv|ogg)$/i', $path);
    }

    private function isImageUrl(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        return (bool)preg_match('/\.(jpe?g|png|gif|webp|avif)$/i', $path);
    }

    private function uniqueSlug(PDO $db, string $value, ?int $ignoreId = null): string
    {
        $base = $this->slugify($value);
        $slug = $base;
        $i = 2;

        while ($this->slugExists($db, $slug, $ignoreId)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function slugExists(PDO $db, string $slug, ?int $ignoreId): bool
    {
        $sql = 'SELECT COUNT(*) FROM blog_posts WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($ignoreId) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = strtolower((string)$value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string)$value, '-');
        return $value !== '' ? substr($value, 0, 200) : 'entrada-blog';
    }

    private function limitText(string $value, int $limit): string
    {
        return mb_substr(trim($value), 0, $limit);
    }

    private function emptyPost(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content_html' => '',
            'featured_image_url' => '',
            'meta_title' => '',
            'meta_description' => '',
            'tags' => '',
            'status' => 'draft',
            'published_at' => null,
        ];
    }
}
