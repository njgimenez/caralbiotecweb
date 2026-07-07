<?php $this->layout('admin::layout', ['title' => 'Productos | Admin Caral Biotec', 'pageTitle' => 'Gestión de Productos']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0" style="font-size:.875rem">
            <?= $this->e($total) ?> producto(s) en total
        </p>
    </div>
    <a href="/admin/productos/nuevo" class="btn-admin-save d-flex align-items-center gap-2 px-4 py-2 text-decoration-none rounded-3">
        <i class="bi bi-plus-lg"></i> Nuevo producto
    </a>
</div>

<!-- Filtro/búsqueda -->
<div class="admin-form-card mb-4 p-3">
    <form action="/admin/productos" method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o SKU..." value="<?= $this->e($search ?? '') ?>">
        </div>
        <div class="col-6 col-md-3">
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                <?php foreach ($filterCategories as $fc): ?>
                    <option value="<?= $this->e($fc['id']) ?>" <?= (($categoria ?? '') == $fc['id']) ? 'selected' : '' ?>>
                        <?= $this->e($fc['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="estado" class="form-select">
                <option value="">Estado</option>
                <option value="1" <?= (($estado ?? '') === '1') ? 'selected' : '' ?>>Activo</option>
                <option value="0" <?= (($estado ?? '') === '0') ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn-admin-save px-3 py-2 rounded-3 flex-fill text-decoration-none">
                <i class="bi bi-search"></i> Filtrar
            </button>
            <a href="/admin/productos" class="btn-admin-cancel rounded-3 py-2 px-3">✕</a>
        </div>
    </form>
</div>

<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th style="width:130px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No se encontraron productos.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="text-muted" style="font-size:.8rem"><?= $this->e($p['id']) ?></td>
                        <td>
                            <div class="fw-600"><?= $this->e($p['name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= $this->e($p['sku']) ?></div>
                        </td>
                        <td><span class="text-muted"><?= $this->e($p['category_name']) ?></span></td>
                        <td class="fw-600 text-success">S/. <?= $this->e(number_format($p['price'], 2)) ?></td>
                        <td>
                            <?php if ($p['stock'] < 10): ?>
                                <span class="fw-600 text-danger"><?= $this->e($p['stock']) ?> <i class="bi bi-exclamation-triangle-fill"></i></span>
                            <?php else: ?>
                                <?= $this->e($p['stock']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['is_active']): ?>
                                <span class="badge badge-active rounded-pill px-2">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-inactive rounded-pill px-2">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/producto/<?= $this->e($p['slug']) ?>" target="_blank"
                                   class="btn btn-sm" style="background:#f6f3ff;color:#4b2bb0;border:none"
                                   title="Ver en tienda">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/admin/productos/<?= $this->e($p['id']) ?>/editar"
                                   class="btn btn-sm" style="background:#eff6ff;color:#3b82f6;border:none"
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="/admin/productos/<?= $this->e($p['id']) ?>/eliminar" method="POST"
                                      onsubmit="return confirm('¿Eliminar el producto «<?= addslashes($p['name']) ?>»?')">
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

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
    <div class="p-3 border-top d-flex justify-content-center">
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($currentPage == $i) ? 'active' : '' ?>">
                        <a class="page-link rounded" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>&categoria=<?= urlencode($categoria ?? '') ?>&estado=<?= urlencode($estado ?? '') ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
