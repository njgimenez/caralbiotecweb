<?php $this->layout('admin::layout', ['title' => 'Ventas POS | Caral Biotec', 'pageTitle' => 'Ventas POS']) ?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.25rem">Ventas realizadas</h2>
        <div class="text-muted" style="font-size:.85rem">Consulta ventas POS e imprime comprobantes o reportes.</div>
    </div>
    <a href="/admin/pos" class="btn-admin-save text-decoration-none px-3 py-2 rounded-3">
        <i class="bi bi-calculator me-1"></i>Nueva venta
    </a>
</div>

<form method="GET" action="/admin/pos/ventas" class="admin-form-card mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Desde</label>
            <input type="date" name="from" class="form-control" value="<?= $this->e($from) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Hasta</label>
            <input type="date" name="to" class="form-control" value="<?= $this->e($to) ?>">
        </div>
        <div class="col-md-6 d-flex gap-2">
            <button type="submit" class="btn-admin-save rounded-3 px-3">Filtrar</button>
            <a href="/admin/pos/reporte?from=<?= $this->e($from) ?>&to=<?= $this->e($to) ?>" target="_blank" class="btn-admin-cancel rounded-3 px-3">
                <i class="bi bi-printer me-1"></i>Imprimir reporte
            </a>
        </div>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2"><div class="stat-card"><div class="stat-card-value"><?= (int)($summary['sales_count'] ?? 0) ?></div><div class="stat-card-label">Ventas</div></div></div>
    <div class="col-6 col-lg-2"><div class="stat-card"><div class="stat-card-value">S/. <?= number_format((float)($summary['total_amount'] ?? 0), 2) ?></div><div class="stat-card-label">Total</div></div></div>
    <div class="col-6 col-lg-2"><div class="stat-card"><div class="stat-card-value">S/. <?= number_format((float)($summary['cash_total'] ?? 0), 2) ?></div><div class="stat-card-label">Efectivo</div></div></div>
    <div class="col-6 col-lg-2"><div class="stat-card"><div class="stat-card-value">S/. <?= number_format((float)($summary['yape_total'] ?? 0), 2) ?></div><div class="stat-card-label">Yape</div></div></div>
    <div class="col-6 col-lg-2"><div class="stat-card"><div class="stat-card-value">S/. <?= number_format((float)($summary['plin_total'] ?? 0), 2) ?></div><div class="stat-card-label">Plin</div></div></div>
    <div class="col-6 col-lg-2"><div class="stat-card"><div class="stat-card-value">S/. <?= number_format((float)($summary['card_total'] ?? 0), 2) ?></div><div class="stat-card-label">Tarjeta</div></div></div>
</div>

<div class="admin-table-card">
    <div class="admin-table-head">
        <span class="admin-table-head-title"><i class="bi bi-journal-text me-2 text-success"></i><?= count($orders) ?> venta(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Comprobante</th>
                    <th>Tipo</th>
                    <th>Cliente</th>
                    <th>Pago</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th style="width:120px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center text-muted py-5">No hay ventas en el rango seleccionado.</td></tr>
                <?php endif; ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="fw-bold"><?= $this->e($order['order_number']) ?></td>
                    <td><?= strtoupper($this->e($order['document_type'] ?? 'boleta')) ?></td>
                    <td>
                        <div class="fw-semibold"><?= $this->e($order['document_name'] ?: 'Consumidor final') ?></div>
                        <?php if (!empty($order['document_number'])): ?><small class="text-muted"><?= $this->e($order['document_number']) ?></small><?php endif; ?>
                    </td>
                    <td><?= strtoupper(str_replace('pos_', '', $this->e($order['payment_method']))) ?></td>
                    <td class="fw-bold text-success">S/. <?= number_format((float)$order['total'], 2) ?></td>
                    <td class="text-muted"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td>
                        <a href="/admin/pos/comprobante/<?= $this->e($order['id']) ?>" target="_blank" class="btn btn-sm" style="background:#f6f3ff;color:#4b2bb0;border:none;font-weight:700">
                            <i class="bi bi-printer"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
