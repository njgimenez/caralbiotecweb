<?php $this->layout('admin::layout', ['title' => 'Blog | Admin', 'pageTitle' => 'Blog']); ?>

<div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
    <div>
        <h1 class="h4 fw-bold mb-1">Entradas del blog</h1>
        <p class="text-muted mb-0">Administra guías, novedades y contenido SEO.</p>
    </div>
    <a href="/admin/blog/nuevo" class="btn-admin-save text-decoration-none"><i class="bi bi-plus-lg me-1"></i> Nueva entrada</a>
</div>

<div class="admin-table-card">
    <div class="admin-table-head">
        <div class="admin-table-head-title">Listado editorial</div>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Entrada</th>
                    <th>Estado</th>
                    <th>Autor</th>
                    <th>Publicación</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td>
                        <div class="fw-semibold text-dark"><?= $this->e($post['title']) ?></div>
                        <div class="small text-muted">/blog/<?= $this->e($post['slug']) ?></div>
                    </td>
                    <td>
                        <?php if ($post['status'] === 'published'): ?>
                            <span class="badge badge-active">Publicado</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">Borrador</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= $this->e($post['author_email'] ?? 'Sin autor') ?></td>
                    <td class="text-muted"><?= $post['published_at'] ? $this->e(date('d/m/Y H:i', strtotime($post['published_at']))) : '-' ?></td>
                    <td class="text-end">
                        <?php if ($post['status'] === 'published'): ?>
                            <a href="/blog/<?= $this->e($post['slug']) ?>" target="_blank" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i></a>
                        <?php endif; ?>
                        <a href="/admin/blog/<?= (int)$post['id'] ?>/editar" class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></a>
                        <form action="/admin/blog/<?= (int)$post['id'] ?>/eliminar" method="POST" class="d-inline" onsubmit="return confirm('Eliminar esta entrada?');">
                            <button class="btn-admin-danger" type="submit"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$posts): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Aún no hay entradas.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
