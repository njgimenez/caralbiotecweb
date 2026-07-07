<?php $company = $companySettings ?? []; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte POS <?= $this->e($from) ?> a <?= $this->e($to) ?></title>
    <style>
        body { font-family: Arial, sans-serif; color:#111827; margin:24px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; }
        .brand { font-size:20px; font-weight:800; }
        .muted { color:#6b7280; font-size:12px; }
        .title { font-size:18px; font-weight:800; margin-top:8px; }
        .summary { display:grid; grid-template-columns:repeat(6, 1fr); gap:8px; margin:18px 0; }
        .box { border:1px solid #e5e7eb; border-radius:8px; padding:10px; }
        .box strong { display:block; font-size:14px; margin-bottom:3px; }
        table { width:100%; border-collapse:collapse; font-size:12px; }
        th, td { border-bottom:1px solid #e5e7eb; padding:8px 6px; text-align:left; }
        th { background:#f9fafb; }
        .right { text-align:right; }
        .actions { margin-bottom:16px; }
        .actions button { border:0; border-radius:8px; padding:10px 14px; background:#4b2bb0; color:white; font-weight:700; cursor:pointer; }
        @media print {
            body { margin:12mm; }
            .actions { display:none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Imprimir reporte</button>
    </div>

    <div class="head">
        <div>
            <div class="brand"><?= $this->e($company['trade_name'] ?: $company['business_name'] ?: 'CARAL BIOTEC') ?></div>
            <div class="muted"><?= $this->e($company['business_name'] ?? 'Caral Biotec') ?><?= !empty($company['ruc']) ? ' · RUC ' . $this->e($company['ruc']) : '' ?></div>
            <div class="muted">Reporte de ventas POS</div>
            <div class="title">Del <?= $this->e($from) ?> al <?= $this->e($to) ?></div>
        </div>
        <div class="muted">Generado: <?= date('d/m/Y H:i') ?></div>
    </div>

    <div class="summary">
        <div class="box"><strong><?= (int)($summary['sales_count'] ?? 0) ?></strong><span class="muted">Ventas</span></div>
        <div class="box"><strong>S/. <?= number_format((float)($summary['total_amount'] ?? 0), 2) ?></strong><span class="muted">Total</span></div>
        <div class="box"><strong>S/. <?= number_format((float)($summary['cash_total'] ?? 0), 2) ?></strong><span class="muted">Efectivo</span></div>
        <div class="box"><strong>S/. <?= number_format((float)($summary['yape_total'] ?? 0), 2) ?></strong><span class="muted">Yape</span></div>
        <div class="box"><strong>S/. <?= number_format((float)($summary['plin_total'] ?? 0), 2) ?></strong><span class="muted">Plin</span></div>
        <div class="box"><strong>S/. <?= number_format((float)($summary['card_total'] ?? 0), 2) ?></strong><span class="muted">Tarjeta</span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Pago</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                <td><?= $this->e($order['order_number']) ?></td>
                <td><?= strtoupper($this->e($order['document_type'] ?? 'boleta')) ?></td>
                <td><?= $this->e($order['document_name'] ?: 'Consumidor final') ?></td>
                <td><?= strtoupper(str_replace('pos_', '', $this->e($order['payment_method']))) ?></td>
                <td class="right">S/. <?= number_format((float)$order['total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
            <tr><td colspan="6">No hay ventas en el rango seleccionado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 350));
    </script>
</body>
</html>
