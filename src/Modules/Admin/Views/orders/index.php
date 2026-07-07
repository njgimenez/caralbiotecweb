<?php $this->layout('admin::layout', ['title' => 'Gestión de Órdenes | Admin', 'pageTitle' => 'Órdenes']) ?>

<?php
$statusColors = [
    'pending'    => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Pendiente'],
    'processing' => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'En proceso'],
    'shipped'    => ['bg' => '#ede9fe', 'color' => '#5b21b6', 'label' => 'Enviado'],
    'delivered'  => ['bg' => '#e7e0ff', 'color' => '#37207a', 'label' => 'Entregado'],
    'cancelled'  => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Cancelado'],
    'failed'     => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Fallido'],
    'refunded'   => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'Reembolsado'],
];
?>

<!-- Contadores rápidos -->
<div class="row g-3 mb-4">
    <?php foreach ($statusColors as $st => $sc): ?>
    <div class="col-6 col-md-4 col-lg-2">
        <a href="/admin/ordenes?status=<?= $st ?>" class="text-decoration-none">
            <div class="stat-card text-center" style="<?= $filters['status'] === $st ? 'border:2px solid #6f5add;' : '' ?>">
                <div class="fw-bold" style="font-size:1.5rem;color:<?= $sc['color'] ?>">
                    <?= $counts[$st] ?? 0 ?>
                </div>
                <div style="font-size:.75rem;color:#64748b"><?= $sc['label'] ?></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Buscador -->
<form method="GET" action="/admin/ordenes" class="mb-4">
    <input type="hidden" name="status" value="<?= $this->e($filters['status']) ?>">
    <div class="d-flex gap-2">
        <input type="text" name="search" class="form-control" style="max-width:320px"
               placeholder="Buscar por número, email o nombre..."
               value="<?= $this->e($filters['search']) ?>">
        <button type="submit" class="btn-admin-save px-3 rounded-3">Buscar</button>
        <?php if ($filters['search'] || $filters['status']): ?>
        <a href="/admin/ordenes" class="btn-admin-cancel px-3 py-2 rounded-3">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<!-- Tabla -->
<div class="admin-table-card">
    <div class="admin-table-head">
        <span class="admin-table-head-title">
            <i class="bi bi-receipt me-2 text-success"></i>
            <?= count($orders) ?> órdenes encontradas
        </span>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>Fecha</th>
                    <th style="width:80px">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        No se encontraron órdenes.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders as $o):
                    $sc = $statusColors[$o['status']] ?? ['bg'=>'#f1f5f9','color'=>'#475569','label'=>$o['status']];
                    $paidColor = $o['payment_status'] === 'paid' ? '#37207a' : ($o['payment_status'] === 'failed' ? '#991b1b' : '#92400e');
                ?>
                <tr>
                    <td>
                        <a href="/admin/ordenes/<?= $this->e($o['id']) ?>" class="fw-bold text-decoration-none text-dark">
                            <?= $this->e($o['order_number']) ?>
                        </a>
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size:.875rem"><?= $this->e($o['customer_name']) ?></div>
                        <small class="text-muted"><?= $this->e($o['customer_email']) ?></small>
                    </td>
                    <td class="fw-bold text-success">S/. <?= $this->e(number_format($o['total'], 2)) ?></td>
                    <td>
                        <span class="badge rounded-pill px-3" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>">
                            <?= $sc['label'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="fw-semibold" style="color:<?= $paidColor ?>;font-size:.82rem">
                            <?= ucfirst($o['payment_status']) ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:#64748b">
                        <?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>
                    </td>
                    <td>
                        <a href="/admin/ordenes/<?= $this->e($o['id']) ?>"
                           class="btn btn-sm d-inline-flex align-items-center gap-1"
                           style="background:#f6f3ff;color:#4b2bb0;border:none;font-weight:600;">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
