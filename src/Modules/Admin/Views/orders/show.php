<?php $this->layout('admin::layout', ['title' => 'Detalle de Orden | Admin', 'pageTitle' => 'Detalle de Orden']) ?>

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
$sc = $statusColors[$order['status']] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>$order['status']];
$isPickup = ($order['fulfillment_method'] ?? 'delivery') === 'pickup';
$isProvince = ($order['delivery_type'] ?? '') === 'province';
?>

<div class="mb-4 d-flex align-items-center gap-3">
    <a href="/admin/ordenes" class="btn-admin-cancel py-2 px-3 rounded-3">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
    <h2 class="fw-bold mb-0" style="font-size:1.2rem"><?= $this->e($order['order_number']) ?></h2>
    <span class="badge rounded-pill px-3 py-2" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>">
        <?= $sc['label'] ?>
    </span>
    <a href="/admin/ordenes/<?= $this->e($order['id']) ?>/boleta" target="_blank" class="btn-admin-save py-2 px-3 rounded-3 ms-auto">
        <i class="bi bi-receipt me-1"></i>Ver boleta
    </a>
</div>

<div class="row g-4">

    <!-- ── Información de la orden ── -->
    <div class="col-12 col-lg-8">

        <!-- Líneas de productos -->
        <div class="admin-table-card mb-4">
            <div class="admin-table-head">
                <span class="admin-table-head-title"><i class="bi bi-box-seam me-2 text-success"></i>Productos</span>
            </div>
            <table class="table admin-table mb-0">
                <thead><tr>
                    <th>Producto</th><th class="text-center">Cant.</th>
                    <th class="text-end">P. Unit.</th><th class="text-end">Total</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($orderItems as $line): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= $this->e($line['product_name']) ?></div>
                            <small class="text-muted">SKU: <?= $this->e($line['product_sku']) ?></small>
                        </td>
                        <td class="text-center"><?= $this->e($line['quantity']) ?></td>
                        <td class="text-end">S/. <?= $this->e(number_format($line['unit_price'], 2)) ?></td>
                        <td class="text-end fw-bold">S/. <?= $this->e(number_format($line['total_price'], 2)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold text-success fs-5">S/. <?= $this->e(number_format($order['total'], 2)) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Datos del cliente -->
        <div class="admin-form-card mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-person me-2 text-success"></i>Datos del cliente</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted">Nombre</small>
                    <div class="fw-semibold"><?= $this->e($order['customer_name']) ?></div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Email</small>
                    <div class="fw-semibold"><?= $this->e($order['customer_email']) ?></div>
                </div>
                <?php if ($order['customer_phone']): ?>
                <div class="col-md-6">
                    <small class="text-muted">Teléfono</small>
                    <div class="fw-semibold"><?= $this->e($order['customer_phone']) ?></div>
                </div>
                <?php endif; ?>
	                <div class="col-md-6">
	                    <small class="text-muted">Modalidad</small>
	                    <div class="fw-semibold"><?= $isPickup ? 'Recojo en tienda' : ($isProvince ? 'Envio a Provincia' : 'Delivery Lima') ?></div>
	                </div>
	                <?php if (!$isPickup && $order['shipping_address']): ?>
	                <div class="col-12">
	                    <small class="text-muted">Dirección de envío</small>
	                    <div class="fw-semibold">
	                        <?= $this->e($order['shipping_address']) ?>,
	                        <?= $isProvince ? $this->e($order['shipping_city']) : ($this->e($order['shipping_district']) . ', ' . $this->e($order['shipping_city'])) ?>
	                    </div>
	                </div>
	                <?php if ($isProvince): ?>
	                <div class="col-md-6">
	                    <small class="text-muted">Tipo de envio</small>
	                    <div class="fw-semibold">Envio a Provincia</div>
	                </div>
	                <div class="col-md-6">
	                    <small class="text-muted">Courier provincia</small>
	                    <div class="fw-semibold"><?= $this->e($order['courier'] ?: 'Por definir') ?></div>
	                </div>
	                <?php elseif (!empty($order['delivery_zone'])): ?>
	                <div class="col-md-4">
	                    <small class="text-muted">Zona delivery</small>
	                    <div class="fw-semibold"><?= $this->e($order['delivery_zone']) ?></div>
	                </div>
	                <div class="col-md-4">
	                    <small class="text-muted">Ruta</small>
	                    <div class="fw-semibold"><?= $this->e($order['delivery_route_code']) ?> · <?= $this->e($order['delivery_route_name']) ?></div>
	                </div>
	                <div class="col-md-4">
	                    <small class="text-muted">Tiempo estimado</small>
	                    <div class="fw-semibold"><?= $this->e($order['delivery_time_min']) ?>-<?= $this->e($order['delivery_time_max']) ?> min</div>
	                </div>
	                <?php endif; ?>
	                <?php endif; ?>
	            </div>
	        </div>

        <!-- Datos de pago Izipay -->
        <?php if ($order['payment_operation_id']): ?>
        <div class="admin-form-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-credit-card me-2 text-success"></i>Pago Izipay</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted">ID de operacion</small>
                    <div class="fw-semibold" style="font-family:monospace;font-size:.85rem">
                        <?= $this->e($order['payment_operation_id']) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <small class="text-muted">Estado del pago</small>
                    <div class="fw-semibold text-success"><?= ucfirst($this->e($order['payment_status'])) ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Panel lateral: Estado + Fechas ── -->
    <div class="col-12 col-lg-4">

        <!-- Cambiar estado -->
        <div class="admin-form-card mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-arrow-repeat me-2 text-success"></i>Cambiar estado</h6>
            <form action="/admin/ordenes/<?= $this->e($order['id']) ?>/estado" method="POST">
                <select name="status" class="form-select mb-3">
                    <?php foreach ($statusColors as $st => $scOpt): ?>
                    <option value="<?= $st ?>" <?= $order['status'] === $st ? 'selected' : '' ?>>
                        <?= $scOpt['label'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-admin-save w-100 py-2 rounded-3 text-center">
                    <i class="bi bi-floppy me-1"></i>Actualizar estado
                </button>
            </form>
        </div>

        <!-- Resumen -->
        <div class="admin-form-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-success"></i>Resumen</h6>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Subtotal</span>
                <span>S/. <?= $this->e(number_format($order['subtotal'], 2)) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
	                <span class="text-muted"><?= $isPickup ? 'Recojo' : ($isProvince ? 'Colocación en courier' : 'Envío') ?></span>
	                <span><?= $isPickup ? '<span class="text-success">Sin costo</span>' : ($order['shipping_cost'] > 0 ? 'S/. ' . number_format($order['shipping_cost'], 2) : '<span class="text-success">Gratis</span>') ?></span>
            </div>
            <?php if ($isProvince): ?><small class="text-muted d-block mb-2">El flete se coordina directamente con el courier.</small><?php endif; ?>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span>
                <span class="text-success">S/. <?= $this->e(number_format($order['total'], 2)) ?></span>
            </div>
            <hr>
            <small class="text-muted d-block">Ambiente Izipay: <?= ($order['payment_environment'] ?? 'test') === 'production' ? 'Produccion' : 'Desarrollo / Test' ?></small>
            <small class="text-muted d-block">Creado: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
            <?php if ($order['updated_at']): ?>
            <small class="text-muted d-block">Actualizado: <?= date('d/m/Y H:i', strtotime($order['updated_at'])) ?></small>
            <?php endif; ?>
        </div>
    </div>
</div>
