<?php $this->layout('admin::layout', ['title' => 'Gestión de Páginas (CMS) | Admin', 'pageTitle' => 'CMS Visual']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0" style="font-size:.875rem">Administra el diseño de la página de inicio sin modificar código.</p>
</div>

<div class="admin-table-card">
    <div class="admin-table-head">
        <span class="admin-table-head-title"><i class="bi bi-layout-text-window-reverse me-2 text-success"></i>Bloques de la Página de Inicio (Home)</span>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th style="width: 250px;">Sección / Bloque</th>
                    <th>Clave de Bloque</th>
                    <th>Estado de Visibilidad</th>
                    <th>Última Modificación</th>
                    <th style="width: 120px;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blocks as $b): ?>
                <tr>
                    <td>
                        <strong class="text-dark d-block"><?= $this->e($b['title']) ?></strong>
                    </td>
                    <td>
                        <code style="font-size: .8rem; color: #64748b;"><?= $this->e($b['block_key']) ?></code>
                    </td>
                    <td>
                        <?php if ($b['is_active']): ?>
                            <span class="badge badge-active rounded-pill px-2">Visible en la Tienda</span>
                        <?php else: ?>
                            <span class="badge badge-inactive rounded-pill px-2">Oculto</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="text-muted" style="font-size: .85rem;">
                            <?= $b['updated_at'] ? date('d/m/Y H:i', strtotime($b['updated_at'])) : 'Valores por defecto' ?>
                        </span>
                    </td>
                    <td>
                        <a href="/admin/cms/<?= $this->e($b['block_key']) ?>/editar" 
                           class="btn btn-sm d-inline-flex align-items-center gap-1" 
                           style="background: #eff6ff; color: #3b82f6; border: none; font-weight: 600;">
                            <i class="bi bi-pencil-square"></i> Configurar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
