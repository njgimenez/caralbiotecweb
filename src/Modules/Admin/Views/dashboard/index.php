<?php $this->layout('admin::layout', ['title' => 'Dashboard | Admin Caral Biotec', 'pageTitle' => 'Dashboard']) ?>

<div class="row g-4 mb-4">
    <!-- Stat: Productos -->
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#e7e0ff">
                <i class="bi bi-box-seam text-success"></i>
            </div>
            <div class="stat-card-value"><?= $this->e($stats['total_products']) ?></div>
            <div class="stat-card-label">Productos activos</div>
            <div class="stat-card-delta delta-up"><i class="bi bi-arrow-up-short"></i> En catálogo</div>
        </div>
    </div>
    <!-- Stat: Categorías -->
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#dbeafe">
                <i class="bi bi-tags" style="color:#3b82f6"></i>
            </div>
            <div class="stat-card-value"><?= $this->e($stats['total_categories']) ?></div>
            <div class="stat-card-label">Categorías</div>
            <div class="stat-card-delta" style="color:#3b82f6"><i class="bi bi-grid"></i> Secciones del catálogo</div>
        </div>
    </div>
    <!-- Stat: Usuarios -->
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#fef9c3">
                <i class="bi bi-people" style="color:#ca8a04"></i>
            </div>
            <div class="stat-card-value"><?= $this->e($stats['total_users']) ?></div>
            <div class="stat-card-label">Usuarios registrados</div>
            <div class="stat-card-delta" style="color:#ca8a04"><i class="bi bi-person-check"></i> En el sistema</div>
        </div>
    </div>
    <!-- Stat: Stock bajo -->
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon" style="background:#fee2e2">
                <i class="bi bi-exclamation-triangle" style="color:#dc2626"></i>
            </div>
            <div class="stat-card-value"><?= $this->e($stats['low_stock']) ?></div>
            <div class="stat-card-label">Stock bajo (&lt;10 uds.)</div>
            <div class="stat-card-delta delta-down"><i class="bi bi-arrow-down-short"></i> Requieren atención</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Últimos productos -->
    <div class="col-12 col-lg-7">
        <div class="admin-table-card">
            <div class="admin-table-head">
                <span class="admin-table-head-title"><i class="bi bi-box-seam me-2 text-success"></i>Últimos productos</span>
                <a href="/admin/productos/nuevo" class="btn btn-sm btn-admin-save ms-auto">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo
                </a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProducts as $p): ?>
                        <tr>
                            <td>
                                <a href="/admin/productos/<?= $this->e($p['id']) ?>/editar" class="text-dark fw-500 text-decoration-none">
                                    <?= $this->e($p['name']) ?>
                                </a>
                                <div class="text-muted" style="font-size:.75rem"><?= $this->e($p['sku']) ?></div>
                            </td>
                            <td><span class="text-muted"><?= $this->e($p['category_name']) ?></span></td>
                            <td class="fw-600 text-success">S/. <?= $this->e(number_format($p['price'], 2)) ?></td>
                            <td>
                                <?php if ($p['stock'] < 10): ?>
                                    <span class="text-danger fw-600"><?= $this->e($p['stock']) ?></span>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 text-center border-top">
                <a href="/admin/productos" class="text-success text-decoration-none" style="font-size:.875rem; font-weight:500">
                    Ver todos los productos <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Categorías -->
    <div class="col-12 col-lg-5">
        <div class="admin-table-card">
            <div class="admin-table-head">
                <span class="admin-table-head-title"><i class="bi bi-tags me-2" style="color:#3b82f6"></i>Categorías</span>
                <a href="/admin/categorias/nueva" class="btn btn-sm btn-admin-save ms-auto">
                    <i class="bi bi-plus-lg me-1"></i> Nueva
                </a>
            </div>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr><th>Nombre</th><th>Slug</th><th>Productos</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <a href="/admin/categorias/<?= $this->e($cat['id']) ?>/editar" class="text-dark text-decoration-none fw-500">
                                    <?= $this->e($cat['name']) ?>
                                </a>
                            </td>
                            <td><code style="font-size:.75rem;color:#64748b"><?= $this->e($cat['slug']) ?></code></td>
                            <td><span class="badge rounded-pill" style="background:#e7e0ff;color:#4b2bb0;font-size:.75rem"><?= $this->e($cat['product_count']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-3 text-center border-top">
                <a href="/admin/categorias" class="text-success text-decoration-none" style="font-size:.875rem; font-weight:500">
                    Gestionar categorías <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
