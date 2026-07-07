<?php $this->layout('shared::layout', ['title' => $title ?? $post['title'], 'metaDescription' => $metaDescription ?? $post['excerpt']]); ?>

<?php
$tags = array_values(array_filter(array_map('trim', explode(',', (string)($post['tags'] ?? '')))));
$publishedDate = !empty($post['published_at']) ? date('d/m/Y', strtotime($post['published_at'])) : '';
$canonical = '/blog/' . $post['slug'];
?>

<style>
    .article-shell { max-width: 920px; margin: 0 auto; }
    .article-kicker { color:#4b2bb0; font-weight:800; font-size:.78rem; letter-spacing:.08em; text-transform:uppercase; }
    .article-title { color:#0f172a; font-weight:800; letter-spacing:0; line-height:1.08; }
    .article-meta { color:#64748b; display:flex; gap:1rem; flex-wrap:wrap; font-size:.9rem; }
    .article-cover { border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; background:#f1f5f9; }
    .article-cover img { width:100%; height:auto; max-height:520px; object-fit:cover; display:block; }
    .article-content { color:#1f2937; font-size:1.05rem; line-height:1.78; }
    .article-content h2, .article-content h3, .article-content h4 { color:#0f172a; font-weight:800; margin-top:2rem; margin-bottom:.8rem; }
    .article-content p { margin-bottom:1.1rem; }
    .article-content img { max-width:100%; height:auto; border-radius:8px; margin:1rem 0; }
    .article-content figure { margin:1.5rem 0; }
    .article-content figure img { margin:0; width:100%; }
    .article-content .blog-video-embed { position:relative; width:100%; aspect-ratio:16/9; margin:1.5rem 0; border-radius:8px; overflow:hidden; background:#0f172a; }
    .article-content .blog-video-embed iframe { position:absolute; inset:0; width:100%; height:100%; border:0; }
    .article-content blockquote { border-left:4px solid #6f5add; background:#f6f3ff; padding:1rem 1.25rem; color:#1f113c; border-radius:0 8px 8px 0; }
    .article-content table { width:100%; border-collapse:collapse; margin:1.25rem 0; font-size:.95rem; }
    .article-content th, .article-content td { border:1px solid #e2e8f0; padding:.7rem; }
    .article-content th { background:#f8fafc; color:#0f172a; }
    .article-tag { display:inline-flex; border:1px solid #d4ccff; background:#f6f3ff; color:#37207a; border-radius:999px; padding:.22rem .65rem; font-size:.76rem; font-weight:700; text-decoration:none; }
    .related-card { border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; background:#fff; height:100%; }
    .related-card img { width:100%; aspect-ratio:16/9; object-fit:cover; background:#f1f5f9; }
</style>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post['title'],
    'description' => $post['meta_description'] ?: $post['excerpt'],
    'image' => $post['featured_image_url'] ?: null,
    'datePublished' => $post['published_at'],
    'dateModified' => $post['updated_at'] ?: $post['published_at'],
    'author' => ['@type' => 'Organization', 'name' => 'Caral Biotec'],
    'publisher' => ['@type' => 'Organization', 'name' => 'Caral Biotec'],
    'mainEntityOfPage' => $canonical,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<article class="py-5">
    <div class="container">
        <div class="article-shell">
            <a href="/blog" class="text-success text-decoration-none fw-semibold d-inline-flex align-items-center gap-1 mb-4">
                <i class="bi bi-arrow-left"></i> Blog
            </a>
            <div class="article-kicker mb-2">Guía Caral Biotec</div>
            <h1 class="article-title display-5 mb-3"><?= $this->e($post['title']) ?></h1>
            <p class="lead text-muted mb-3"><?= $this->e($post['excerpt'] ?? '') ?></p>
            <div class="article-meta mb-4">
                <span><i class="bi bi-calendar3 me-1"></i><?= $this->e($publishedDate) ?></span>
                <span><i class="bi bi-person me-1"></i><?= $this->e($post['author_email'] ?? 'Caral Biotec') ?></span>
            </div>
            <?php if (!empty($post['featured_image_url'])): ?>
                <div class="article-cover mb-5">
                    <img src="<?= $this->e($post['featured_image_url']) ?>" alt="<?= $this->e($post['title']) ?>" loading="eager" fetchpriority="high">
                </div>
            <?php endif; ?>
            <div class="article-content">
                <?= $post['content_html'] ?>
            </div>
            <?php if ($tags): ?>
                <div class="d-flex gap-2 flex-wrap mt-5 pt-4 border-top">
                    <?php foreach ($tags as $tag): ?>
                        <a href="/blog?tag=<?= urlencode($tag) ?>" class="article-tag"><?= $this->e($tag) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php if ($related): ?>
<section class="pb-5">
    <div class="container">
        <div class="article-shell">
            <h2 class="h4 fw-bold mb-3">Más entradas recientes</h2>
            <div class="row g-3">
                <?php foreach ($related as $item): ?>
                    <div class="col-md-4">
                        <article class="related-card">
                            <?php if (!empty($item['featured_image_url'])): ?>
                                <img src="<?= $this->e($item['featured_image_url']) ?>" alt="<?= $this->e($item['title']) ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="p-3">
                                <h3 class="h6 fw-bold mb-2"><a href="/blog/<?= $this->e($item['slug']) ?>" class="text-dark text-decoration-none"><?= $this->e($item['title']) ?></a></h3>
                                <p class="small text-muted mb-0"><?= $this->e($item['excerpt'] ?? '') ?></p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
