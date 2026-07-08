<?php $this->layout('shared::layout', ['title' => 'Finalizar compra - Caral Biotec']) ?>

<?php
$mode = $mode ?? 'details';
$old = $old ?? [];
$errors = $errors ?? [];
$order = $order ?? null;
$izipay = $izipay ?? [];
?>

<?php if ($mode === 'payment' && !empty($izipay['formToken'])): ?>
<script type="text/javascript"
        src="<?= $this->e($izipay['scriptUrl']) ?>"
        kr-public-key="<?= $this->e($izipay['publicKey']) ?>"
        kr-post-url-success="<?= $this->e($izipay['postUrlSuccess']) ?>"
        kr-language="es-ES">
</script>
<link rel="stylesheet" href="<?= $this->e($izipay['classicCssUrl']) ?>">
<script type="text/javascript" src="<?= $this->e($izipay['classicJsUrl']) ?>"></script>
<?php endif; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-success text-decoration-none">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/carrito" class="text-success text-decoration-none">Carrito</a></li>
            <li class="breadcrumb-item active">Finalizar compra</li>
        </ol>
    </nav>

    <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">Finalizar compra</h1>
    <p class="text-muted mb-4">Completa tus datos y realiza el pago de forma segura.</p>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger rounded-3 mb-4">
        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor corrige los siguientes errores:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $e): ?>
            <li><?= $this->e($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <?php if ($mode === 'recovery' && $order): ?>
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2">
                        <span class="badge bg-success me-2">1</span>Compra pendiente
                    </h5>
                    <p class="text-muted mb-3">
                        Encontramos una compra iniciada. Puedes continuar el pago desde el mismo punto o cancelarla para volver al carrito.
                    </p>
                    <div class="border rounded-3 p-3 bg-light mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Orden</span>
                            <strong><?= $this->e($order['order_number']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted">Estado</span>
                            <strong><?= $this->e($order['payment_status']) ?></strong>
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="<?= $this->e($paymentUrl) ?>" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-credit-card me-2"></i>Continuar pago
                        </a>
                        <form action="<?= $this->e($cancelUrl) ?>" method="POST">
                            <button type="submit" class="btn btn-outline-secondary px-4">
                                Cancelar y volver al carrito
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php elseif ($mode === 'payment' && $order): ?>
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2">
                        <span class="badge bg-success me-2">1</span>Pago con Izipay
                    </h5>
                    <p class="text-muted mb-3" style="font-size: .9rem;">
                        Orden <?= $this->e($order['order_number']) ?>. Si refrescas esta pagina, el formulario se regenerara para la misma orden.
                    </p>
                    <div id="micuentawebstd_rest_wrapper">
                        <div class="kr-embedded" kr-popin kr-form-token="<?= $this->e($izipay['formToken']) ?>"></div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <form id="checkout-form" action="/checkout/procesar" method="POST" novalidate>
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <span class="badge bg-success me-2">1</span>Datos personales
                        </h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label fw-semibold">Nombre completo *</label>
                                <input type="text" id="name" name="name" class="form-control"
                                       value="<?= $this->e($old['name'] ?? $user['name'] ?? '') ?>"
                                       placeholder="Juan Garcia Perez" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Correo electronico *</label>
                                <input type="email" id="email" name="email" class="form-control"
                                       value="<?= $this->e($old['email'] ?? $user['email'] ?? '') ?>"
                                       placeholder="correo@ejemplo.com" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Telefono</label>
                                <input type="tel" id="phone" name="phone" class="form-control"
                                       value="<?= $this->e($old['phone'] ?? '') ?>"
                                       placeholder="(+51) 999 999 999">
                            </div>
                            <div class="col-md-4">
                                <label for="document_type" class="form-label fw-semibold">Tipo doc.</label>
                                <select id="document_type" name="document_type" class="form-select">
                                    <?php foreach (['DNI', 'CE', 'RUC', 'PASAPORTE'] as $docType): ?>
                                    <option value="<?= $this->e($docType) ?>" <?= ($old['document_type'] ?? 'DNI') === $docType ? 'selected' : '' ?>>
                                        <?= $this->e($docType) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="document" class="form-label fw-semibold">Documento *</label>
                                <input type="text" id="document" name="document" class="form-control"
                                       value="<?= $this->e($old['document'] ?? '') ?>"
                                       placeholder="12345678" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <span class="badge bg-success me-2">2</span>Direccion de envio
                        </h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="address" class="form-label fw-semibold">Direccion *</label>
                                <input type="text" id="address" name="address" class="form-control"
                                       value="<?= $this->e($old['address'] ?? '') ?>"
                                       placeholder="Av. Javier Prado Este 123, Dpto 401" required>
                            </div>
                            <div class="col-md-6">
                                <label for="district" class="form-label fw-semibold">Distrito *</label>
                                <input type="text" id="district" name="district" class="form-control"
                                       value="<?= $this->e($old['district'] ?? '') ?>"
                                       placeholder="Miraflores" required>
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-semibold">Ciudad</label>
                                <input type="text" id="city" name="city" class="form-control"
                                       value="<?= $this->e($old['city'] ?? 'Lima') ?>"
                                       placeholder="Lima">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1">
                            <span class="badge bg-success me-2">3</span>Pago con Izipay
                        </h5>
                        <p class="text-muted mb-0" style="font-size: .85rem;">
                            Crearemos una orden pendiente y abriremos el formulario seguro de Izipay. Si refrescas o vuelves despues, podras continuar el pago.
                        </p>
                    </div>
                </div>
                <button type="submit" class="btn btn-success py-3 px-4 fw-bold">
                    <i class="bi bi-lock-fill me-2"></i>Continuar a pago
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="border-radius: 16px; top: 90px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Resumen de tu pedido</h5>

                    <div class="mb-3">
                        <?php foreach ($items as $item): ?>
                        <?php
                        $name = $item['product_name'] ?? $item['name'] ?? 'Producto';
                        $quantity = (int)($item['quantity'] ?? 1);
                        $lineTotal = (float)($item['total_price'] ?? (($item['price_seen'] ?? $item['unit_price'] ?? 0) * $quantity));
                        ?>
                        <div class="d-flex align-items-center gap-3 mb-2 pb-2" style="border-bottom: 1px solid #f1f5f9;">
                            <div style="width:48px;height:48px;background:#f6f3ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <i class="bi bi-box-seam text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold" style="font-size:.875rem;"><?= $this->e($name) ?></div>
                                <div class="text-muted" style="font-size:.78rem;">Cant: <?= $this->e($quantity) ?></div>
                            </div>
                            <div class="fw-bold text-success" style="font-size:.875rem;white-space:nowrap">
                                S/. <?= $this->e(number_format($lineTotal, 2)) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal</span>
                        <span>S/. <?= $this->e(number_format((float)$subtotal, 2)) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Envio</span>
                        <span class="text-success fw-bold"><i class="bi bi-truck me-1"></i>Gratis</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-4 text-success">S/. <?= $this->e(number_format((float)$total, 2)) ?></span>
                    </div>

                    <?php if ($order): ?>
                    <p class="text-center text-muted mt-2 mb-0" style="font-size: .75rem;">
                        Orden <?= $this->e($order['order_number']) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
