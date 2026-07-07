<?php $this->layout('admin::layout', ['title' => 'Categorías | Admin Caral Biotec', 'pageTitle' => 'Gestión de Categorías']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0" style="font-size:.875rem"><?= count($categories) ?> categoría(s)</p>
    <a href="/admin/categorias/nueva" class="btn-admin-save d-flex align-items-center gap-2 px-4 py-2 text-decoration-none rounded-3">
        <i class="bi bi-plus-lg"></i> Nueva categoría
    </a>
</div>

<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Productos</th>
                    <th style="width:110px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>No hay categorías creadas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="text-muted" style="font-size:.8rem"><?= $this->e($cat['id']) ?></td>
                        <td class="fw-600"><?= $this->e($cat['name']) ?></td>
                        <td><code style="font-size:.75rem;color:#64748b"><?= $this->e($cat['slug']) ?></code></td>
                        <td class="text-muted" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            <?= $this->e($cat['description'] ?? '—') ?>
                        </td>
                        <td>
                            <span class="badge rounded-pill" style="background:#e7e0ff;color:#4b2bb0;font-size:.75rem">
                                <?= $this->e($cat['product_count']) ?> prod.
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/admin/categorias/<?= $this->e($cat['id']) ?>/editar"
                                   class="btn btn-sm" style="background:#eff6ff;color:#3b82f6;border:none" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="/admin/categorias/<?= $this->e($cat['id']) ?>/eliminar" method="POST"
                                      onsubmit="return confirm('¿Eliminar la categoría «<?= addslashes($cat['name']) ?>»? Los productos no se eliminarán.')">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn-admin-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
