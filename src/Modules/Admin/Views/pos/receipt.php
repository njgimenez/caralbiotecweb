<?php
$docType = $order['document_type'] ?? 'boleta';
$title = $docType === 'factura' ? 'Factura' : 'Boleta';
$payment = str_replace('pos_', '', (string)$order['payment_method']);
$company = $companySettings ?? [];
$igvPercent = (float)($company['igv_percent'] ?? 18);
$taxable = $order['total'] > 0 ? round((float)$order['total'] / (1 + ($igvPercent / 100)), 2) : 0;
$igv = round((float)$order['total'] - $taxable, 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?> <?= $this->e($order['order_number']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; color:#111827; margin:0; background:#f3f4f6; }
        .receipt { width: 320px; margin: 20px auto; background:white; padding:18px; border:1px solid #e5e7eb; }
        .center { text-align:center; }
        .brand { font-weight:800; font-size:18px; }
        .muted { color:#6b7280; font-size:12px; }
        .title { font-weight:800; font-size:15px; margin-top:12px; text-transform:uppercase; }
        .row { display:flex; justify-content:space-between; gap:12px; font-size:12px; margin:5px 0; }
        .line { border-top:1px dashed #9ca3af; margin:12px 0; }
        table { width:100%; border-collapse:collapse; font-size:12px; }
        th, td { padding:5px 0; vertical-align:top; }
        th { text-align:left; border-bottom:1px solid #e5e7eb; }
        .right { text-align:right; }
        .total { font-size:15px; font-weight:800; }
        .actions { width:320px; margin: 0 auto 20px; display:flex; gap:8px; }
        .actions button, .actions a { flex:1; border:0; border-radius:8px; padding:10px; background:#4b2bb0; color:white; text-align:center; text-decoration:none; font-weight:700; cursor:pointer; }
        .actions a { background:#475569; }
        @media print {
            body { background:white; }
            .receipt { margin:0; width:auto; border:0; }
            .actions { display:none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center">
            <div class="brand"><?= $this->e($company['trade_name'] ?: $company['business_name'] ?: 'CARAL BIOTEC') ?></div>
            <div class="muted"><?= $this->e($company['business_name'] ?? 'Caral Biotec') ?></div>
            <?php if (!empty($company['ruc'])): ?><div class="muted">RUC <?= $this->e($company['ruc']) ?></div><?php endif; ?>
            <div class="muted"><?= $this->e($company['address'] ?? 'Lima, Perú') ?></div>
            <div class="muted"><?= $this->e($company['email'] ?? 'contacto@caralbiotec.com') ?></div>
            <div class="title"><?= $this->e($title) ?> de venta</div>
            <div class="muted"><?= $this->e($order['order_number']) ?></div>
        </div>

        <div class="line"></div>

        <div class="row"><span>Fecha</span><strong><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></strong></div>
        <div class="row"><span>Pago</span><strong><?= strtoupper($this->e($payment)) ?></strong></div>
        <?php if ($docType === 'factura'): ?>
            <div class="row"><span>RUC</span><strong><?= $this->e($order['document_number']) ?></strong></div>
            <div class="row"><span>Razón social</span><strong><?= $this->e($order['document_name']) ?></strong></div>
        <?php else: ?>
            <div class="row"><span>Cliente</span><strong>Consumidor final</strong></div>
        <?php endif; ?>

        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="right">Cant.</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td>
                        <?= $this->e($item['product_name']) ?><br>
                        <span class="muted">S/. <?= number_format($item['unit_price'], 2) ?></span>
                    </td>
                    <td class="right"><?= $this->e($item['quantity']) ?></td>
                    <td class="right">S/. <?= number_format($item['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="line"></div>

        <div class="row"><span>Subtotal</span><strong>S/. <?= number_format($order['subtotal'], 2) ?></strong></div>
        <div class="row"><span>Gravada</span><strong>S/. <?= number_format($taxable, 2) ?></strong></div>
        <div class="row"><span>IGV <?= number_format($igvPercent, 2) ?>%</span><strong>S/. <?= number_format($igv, 2) ?></strong></div>
        <div class="row total"><span>Total</span><span>S/. <?= number_format($order['total'], 2) ?></span></div>

        <div class="line"></div>
        <div class="center muted">Gracias por su compra</div>
    </div>

    <div class="actions">
        <button type="button" onclick="window.print()">Imprimir</button>
        <a href="/admin/pos/ventas">Ventas</a>
    </div>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 350));
    </script>
</body>
</html>
