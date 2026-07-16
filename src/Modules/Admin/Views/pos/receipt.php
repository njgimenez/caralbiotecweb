<?php
$docType = strtolower((string)($order['document_type'] ?? 'boleta'));
$title = !isset($receiptNumber) && $docType === 'factura' ? 'Factura' : 'Boleta';
$displayNumber = $receiptNumber ?? $order['order_number'];
$payment = str_replace(['pos_', '_'], ['', ' '], (string)$order['payment_method']);
$company = $companySettings ?? [];
$igvPercent = (float)($company['igv_percent'] ?? 18);
$total = (float)$order['total'];
$subtotal = (float)$order['subtotal'];
$shipping = (float)($order['shipping_cost'] ?? 0);
$discount = max(0, round(($subtotal + $shipping) - $total, 2));
$taxable = $total > 0 ? round($total / (1 + ($igvPercent / 100)), 2) : 0;
$igv = round($total - $taxable, 2);
$customerName = trim((string)($order['document_name'] ?: ($order['customer_name'] ?? '')));
$customerName = $customerName !== '' ? $customerName : 'Consumidor final';
$customerDocumentType = strtoupper((string)($order['customer_document_type'] ?: ($docType === 'factura' ? 'RUC' : 'DNI')));
$customerDocument = (string)($order['customer_document'] ?: ($order['document_number'] ?? ''));
$itemsCount = array_sum(array_map(static fn(array $item): int => (int)$item['quantity'], $orderItems));
$returnUrl = $returnUrl ?? '/admin/pos/ventas';
$returnLabel = $returnLabel ?? 'Ventas';
$autoPrint = $autoPrint ?? true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?> <?= $this->e($displayNumber) ?></title>
    <style>
        * { box-sizing:border-box; }
        body { font-family:"Courier New", monospace; color:#111; margin:0; background:#f3f4f6; }
        .receipt { width:360px; margin:20px auto; background:#fff; padding:20px 18px; border:1px solid #d1d5db; box-shadow:0 8px 24px rgba(15,23,42,.08); }
        .center { text-align:center; }
        .brand { font-weight:800; font-size:17px; }
        .muted { color:#374151; font-size:11px; line-height:1.4; }
        .title { font-weight:800; font-size:15px; margin-top:13px; text-transform:uppercase; }
        .number { font-size:14px; font-weight:800; margin-top:4px; }
        .row { display:flex; justify-content:space-between; gap:12px; font-size:11px; margin:5px 0; }
        .row strong { text-align:right; overflow-wrap:anywhere; }
        .line { border-top:1px dashed #6b7280; margin:11px 0; }
        table { width:100%; border-collapse:collapse; font-size:10.5px; table-layout:fixed; }
        th, td { padding:5px 2px; vertical-align:top; overflow-wrap:anywhere; }
        th { text-align:left; border-bottom:1px dashed #6b7280; }
        .description { width:45%; }
        .qty { width:13%; }
        .money { width:21%; }
        .right { text-align:right; }
        .total { font-size:14px; font-weight:800; margin-top:7px; }
        .lookup { border:1px solid #111; padding:9px 7px; margin:12px auto; font-size:10px; overflow-wrap:anywhere; }
        .thanks { font-size:14px; font-weight:800; margin-top:14px; }
        .actions { width:360px; margin:0 auto 20px; display:flex; gap:8px; font-family:Arial,sans-serif; }
        .actions button, .actions a { flex:1; border:0; border-radius:8px; padding:10px; background:#4b2bb0; color:white; text-align:center; text-decoration:none; font-weight:700; cursor:pointer; }
        .actions a { background:#475569; }
        @media print {
            @page { size:80mm auto; margin:3mm; }
            body { background:white; }
            .receipt { margin:0; width:74mm; border:0; padding:0; box-shadow:none; }
            .actions { display:none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center">
            <div class="brand"><?= $this->e($company['trade_name'] ?: $company['business_name'] ?: 'CARAL BIOTEC') ?></div>
            <?php if (!empty($company['business_name'])): ?><div class="muted"><?= $this->e($company['business_name']) ?></div><?php endif; ?>
            <?php if (!empty($company['ruc'])): ?><div class="muted">RUC: <?= $this->e($company['ruc']) ?></div><?php endif; ?>
            <div class="muted"><?= $this->e($company['address'] ?? 'Lima, Perú') ?></div>
            <?php if (!empty($company['phone'])): ?><div class="muted">Tel: <?= $this->e($company['phone']) ?></div><?php endif; ?>
            <div class="title"><?= $this->e($title) ?> de venta</div>
            <div class="number"><?= $this->e($displayNumber) ?></div>
        </div>

        <div class="line"></div>

        <div class="row"><span>Fecha de emisión</span><strong><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></strong></div>
        <div class="row"><span>Pago</span><strong><?= strtoupper($this->e($payment)) ?></strong></div>
        <div class="row"><span>Señor(es)</span><strong><?= $this->e($customerName) ?></strong></div>
        <?php if ($customerDocument !== ''): ?>
            <div class="row"><span><?= $this->e($customerDocumentType) ?></span><strong><?= $this->e($customerDocument) ?></strong></div>
        <?php endif; ?>
        <div class="row"><span>Orden</span><strong><?= $this->e($order['order_number']) ?></strong></div>

        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th class="description">Descripción</th>
                    <th class="right qty">Cant.</th>
                    <th class="right money">Precio</th>
                    <th class="right money">Importe</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td><?= $this->e($item['product_name']) ?><?php if (!empty($item['product_sku'])): ?><br><span class="muted"><?= $this->e($item['product_sku']) ?></span><?php endif; ?></td>
                    <td class="right"><?= $this->e($item['quantity']) ?></td>
                    <td class="right"><?= number_format($item['unit_price'], 2) ?></td>
                    <td class="right">S/. <?= number_format($item['total_price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if ($shipping > 0): ?>
                <tr>
                    <td>Colocación / envío</td><td class="right">1</td><td class="right"><?= number_format($shipping, 2) ?></td><td class="right">S/. <?= number_format($shipping, 2) ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="line"></div>

        <div class="row"><span>Subtotal</span><strong>S/. <?= number_format($subtotal + $shipping, 2) ?></strong></div>
        <div class="row"><span>Descuento global</span><strong>S/. <?= number_format($discount, 2) ?></strong></div>
        <div class="row"><span>Op. gravada</span><strong>S/. <?= number_format($taxable, 2) ?></strong></div>
        <div class="row"><span>Op. exonerada</span><strong>S/. 0.00</strong></div>
        <div class="row"><span>Op. inafecta</span><strong>S/. 0.00</strong></div>
        <div class="row"><span>IGV <?= number_format($igvPercent, 2) ?>%</span><strong>S/. <?= number_format($igv, 2) ?></strong></div>
        <div class="row total"><span>Total</span><span>S/. <?= number_format($total, 2) ?></span></div>

        <div class="line"></div>
        <div class="center muted">Documento contiene <?= $this->e($itemsCount) ?> artículo(s)</div>
        <div class="lookup center"><strong>CÓDIGO DE CONSULTA</strong><br><?= $this->e($order['order_number']) ?></div>
        <div class="center thanks">GRACIAS POR SU COMPRA</div>
    </div>

    <div class="actions">
        <button type="button" onclick="window.print()">Imprimir</button>
        <a href="<?= $this->e($returnUrl) ?>"><?= $this->e($returnLabel) ?></a>
    </div>

    <?php if ($autoPrint): ?>
    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 350));
    </script>
    <?php endif; ?>
</body>
</html>
