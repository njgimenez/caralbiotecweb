<?php $this->layout('shared::layout', ['title' => $title ?? 'Blog | Caral Biotec', 'metaDescription' => $metaDescription ?? '']); ?>

<?php
function blogTags(?string $tags): array {
    return array_values(array_filter(array_map('trim', explode(',', (string)$tags))));
}
function blogDate(?string $date): string {
    return $date ? date('d/m/Y', strtotime($date)) : '';
}
?>

<style>
    .blog-hero { background:#f8fafc; border-bottom:1px solid #e5e7eb; }
    .blog-eyebrow { color:#4b2bb0; font-weight:800; font-size:.78rem; letter-spacing:.08em; text-transform:uppercase; }
    .blog-title { color:#0f172a; font-weight:800; letter-spacing:0; }
    .blog-search { border:1px solid #dbe3ec; border-radius:8px; overflow:hidden; background:#fff; }
    .blog-search input { border:0; min-height:48px; }
    .blog-search input:focus { box-shadow:none; }
    .blog-featured { display:grid; grid-template-columns:1.08fr .92fr; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; background:#fff; }
    .blog-featured-media { min-height:330px; background:#e5e7eb; }
    .blog-featured-media img, .blog-card-media img { width:100%; height:100%; object-fit:cover; display:block; }
    .blog-featured-body { padding:2rem; display:flex; flex-direction:column; justify-content:center; }
    .blog-card { border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; background:#fff; height:100%; transition:box-shadow .2s, transform .2s; }
    .blog-card:hover { box-shadow:0 14px 32px rgba(15,23,42,.08); transform:translateY(-2px); }
    .blog-card-media { aspect-ratio:16/9; background:#e5e7eb; }
    .blog-card-body { padding:1.1rem; }
    .blog-meta { color:#64748b; font-size:.82rem; display:flex; gap:.65rem; flex-wrap:wrap; }
    .blog-post-title a { color:#0f172a; text-decoration:none; }
    .blog-post-title a:hover { color:#4b2bb0; }
    .blog-tag { display:inline-flex; align-items:center; border:1px solid #d4ccff; background:#f6f3ff; color:#37207a; border-radius:999px; padding:.18rem .55rem; font-size:.72rem; font-weight:700; text-decoration:none; }
    .blog-empty { border:1px dashed #cbd5e1; border-radius:8px; padding:3rem 1.5rem; text-align:center; background:#fff; }
    @media (max-width: 767px) {
        .blog-featured { grid-template-columns:1fr; }
        .blog-featured-media { min-height:220px; }
        .blog-featured-body { padding:1.25rem; }
    }
</style>

<section class="blog-hero py-5">
    <div class="container">
        <div class="row align-items-end g-4">
            <div class="col-lg-7">
                <div class="blog-eyebrow mb-2">Blog Caral Biotec</div>
                <h1 class="blog-title display-6 mb-3">Guías prácticas para bienestar, nutrición y recuperación</h1>
                <p class="text-muted mb-0">Contenido claro para tomar mejores decisiones sobre salud, cuidado diario y productos especializados.</p>
            </div>
            <div class="col-lg-5">
                <form action="/blog" method="GET" class="blog-search d-flex">
                    <input type="text" name="q" class="form-control" placeholder="Buscar artículos" value="<?= $this->e($search ?? '') ?>">
                    <button class="btn btn-success px-4" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if ($featured): ?>
            <article class="blog-featured mb-5">
                <a class="blog-featured-media" href="/blog/<?= $this->e($featured['slug']) ?>" aria-label="<?= $this->e($featured['title']) ?>">
                    <?php if (!empty($featured['featured_image_url'])): ?>
                        <img src="<?= $this->e($featured['featured_image_url']) ?>" alt="<?= $this->e($featured['title']) ?>" loading="eager" fetchpriority="high">
                    <?php endif; ?>
                </a>
                <div class="blog-featured-body">
                    <div class="blog-meta mb-3">
                        <span><i class="bi bi-calendar3 me-1"></i><?= $this->e(blogDate($featured['published_at'])) ?></span>
                        <span><i class="bi bi-person me-1"></i><?= $this->e($featured['author_email'] ?? 'Caral Biotec') ?></span>
                    </div>
                    <h2 class="blog-post-title h3 fw-bold mb-3"><a href="/blog/<?= $this->e($featured['slug']) ?>"><?= $this->e($featured['title']) ?></a></h2>
                    <p class="text-muted mb-4"><?= $this->e($featured['excerpt'] ?? '') ?></p>
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach (array_slice(blogTags($featured['tags'] ?? ''), 0, 3) as $tag): ?>
                                <a href="/blog?tag=<?= urlencode($tag) ?>" class="blog-tag"><?= $this->e($tag) ?></a>
                            <?php endforeach; ?>
                        </div>
                        <a href="/blog/<?= $this->e($featured['slug']) ?>" class="btn btn-success">Leer entrada</a>
                    </div>
                </div>
            </article>
        <?php endif; ?>

        <?php if (!$posts): ?>
            <div class="blog-empty">
                <h2 class="h5 fw-bold">No hay entradas publicadas</h2>
                <p class="text-muted mb-0">Cuando el equipo editorial publique contenido, aparecerá en esta sección.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($rest as $post): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="blog-card">
                            <a class="blog-card-media d-block" href="/blog/<?= $this->e($post['slug']) ?>">
                                <?php if (!empty($post['featured_image_url'])): ?>
                                    <img src="<?= $this->e($post['featured_image_url']) ?>" alt="<?= $this->e($post['title']) ?>" loading="lazy">
                                <?php endif; ?>
                            </a>
                            <div class="blog-card-body">
                                <div class="blog-meta mb-2">
                                    <span><i class="bi bi-calendar3 me-1"></i><?= $this->e(blogDate($post['published_at'])) ?></span>
                                </div>
                                <h2 class="blog-post-title h5 fw-bold mb-2"><a href="/blog/<?= $this->e($post['slug']) ?>"><?= $this->e($post['title']) ?></a></h2>
                                <p class="text-muted small mb-3"><?= $this->e($post['excerpt'] ?? '') ?></p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php foreach (array_slice(blogTags($post['tags'] ?? ''), 0, 2) as $tag): ?>
                                        <a href="/blog?tag=<?= urlencode($tag) ?>" class="blog-tag"><?= $this->e($tag) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
