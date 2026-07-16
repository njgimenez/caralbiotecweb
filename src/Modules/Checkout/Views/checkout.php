<?php $this->layout('shared::layout', ['title' => 'Finalizar compra - Caral Biotec']) ?>

<?php
$mode = $mode ?? 'details';
$old = $old ?? [];
$errors = $errors ?? [];
$order = $order ?? null;
$izipay = $izipay ?? [];
$isPaymentMode = $mode === 'payment' && $order;
$deliveryOptions = $deliveryOptions ?? [];
$cityOptions = $cityOptions ?? ['Lima'];
$selectedDistrict = (string)($old['district'] ?? $order['shipping_district'] ?? '');
$selectedCity = (string)($old['city'] ?? $order['shipping_city'] ?? 'Lima');
$selectedCourier = (string)($old['courier'] ?? $order['courier'] ?? '');
$selectedFulfillment = (string)($old['fulfillment_method'] ?? $order['fulfillment_method'] ?? 'delivery');
$selectedFulfillment = $selectedFulfillment === 'pickup' ? 'pickup' : 'delivery';
$provinceHandlingFee = (float)($provinceHandlingFee ?? 15.00);
$isProvinceOrder = ($order['delivery_type'] ?? '') === 'province';
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

<style>
    .checkout-payment-shell {
        max-width: 520px;
        margin: 0 auto;
    }

    .checkout-payment-shell #micuentawebstd_rest_wrapper {
        display: flex;
        justify-content: center;
    }

    .checkout-payment-shell .kr-embedded {
        width: 100%;
        max-width: 420px;
    }

	    .checkout-izipay-logo {
	        display: block;
	        width: min(220px, 70%);
	        height: auto;
	        margin: 1.25rem auto 0;
	    }

	    .fulfillment-option {
	        border: 1px solid #e2e8f0;
	        border-radius: 12px;
	        padding: 1rem;
	        height: 100%;
	        cursor: pointer;
	        transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
	    }

	    .fulfillment-option:has(input:checked) {
	        border-color: var(--green-700);
	        box-shadow: 0 8px 22px rgba(75, 43, 176, .12);
	        background: #fbfaff;
	    }

	    .fulfillment-option input {
	        margin-top: .25rem;
	    }
	</style>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-success text-decoration-none">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/carrito" class="text-success text-decoration-none">Carrito</a></li>
            <li class="breadcrumb-item active">Finalizar compra</li>
        </ol>
    </nav>

    <?php if (!$isPaymentMode): ?>
    <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">Finalizar compra</h1>
    <p class="text-muted mb-4">Completa tus datos y realiza el pago de forma segura.</p>
    <?php endif; ?>

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

    <div class="row g-4 <?= $isPaymentMode ? 'justify-content-center' : '' ?>">
        <div class="<?= $isPaymentMode ? 'col-12 col-md-8 col-lg-6' : 'col-12 col-lg-7' ?>">
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
            <div class="card border-0 shadow-sm mb-4 checkout-payment-shell" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-center mb-4">
                        Orden <?= $this->e($order['order_number']) ?>
                    </h5>
                    <div id="micuentawebstd_rest_wrapper">
                        <div class="kr-embedded" kr-popin kr-form-token="<?= $this->e($izipay['formToken']) ?>">
                            <button class="kr-payment-button"></button>
                            <div class="kr-form-error"></div>
                        </div>
                    </div>
                    <img src="/images/izipay-logo.png" alt="Izipay" class="checkout-izipay-logo">
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
	                            <span class="badge bg-success me-2">2</span>Entrega
	                        </h5>
	                        <div class="row g-3">
	                            <div class="col-md-6">
	                                <label class="fulfillment-option d-flex gap-3">
	                                    <input type="radio" name="fulfillment_method" value="delivery" <?= $selectedFulfillment === 'delivery' ? 'checked' : '' ?>>
	                                    <span>
	                                        <span class="fw-bold d-block"><i class="bi bi-truck me-2 text-success"></i>Delivery</span>
	                                        <span class="text-muted" style="font-size:.85rem;">Te enviamos el pedido segun tu distrito.</span>
	                                    </span>
	                                </label>
	                            </div>
	                            <div class="col-md-6">
	                                <label class="fulfillment-option d-flex gap-3">
	                                    <input type="radio" name="fulfillment_method" value="pickup" <?= $selectedFulfillment === 'pickup' ? 'checked' : '' ?>>
	                                    <span>
	                                        <span class="fw-bold d-block"><i class="bi bi-shop me-2 text-success"></i>Recojo en tienda</span>
	                                        <span class="text-muted" style="font-size:.85rem;">Sin costo de envio. Coordinamos el recojo de tu pedido.</span>
	                                    </span>
	                                </label>
	                            </div>
	                            <div class="col-12" id="pickup-note" style="display:none;">
	                                <div class="rounded-3 p-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
	                                    <div class="fw-semibold"><i class="bi bi-info-circle me-2 text-success"></i>Recojo en tienda seleccionado</div>
	                                    <div class="text-muted" style="font-size:.875rem;">No se cobrara delivery. Te contactaremos para coordinar horario y punto de recojo.</div>
	                                </div>
	                            </div>
	                            <div class="col-12" id="delivery-fields">
	                                <div class="row g-3">
	                                    <div class="col-12">
	                                        <label for="address" class="form-label fw-semibold">Direccion *</label>
	                                        <input type="text" id="address" name="address" class="form-control"
	                                               value="<?= $this->e($old['address'] ?? '') ?>"
	                                               placeholder="Av. Javier Prado Este 123, Dpto 401">
	                                    </div>
	                                    <div class="col-md-6">
	                                        <label for="city" class="form-label fw-semibold">Ciudad *</label>
	                                        <select id="city" name="city" class="form-select" required>
	                                            <?php foreach ($cityOptions as $city): ?>
	                                            <option value="<?= $this->e($city) ?>" <?= $selectedCity === $city ? 'selected' : '' ?>><?= $this->e($city) ?></option>
	                                            <?php endforeach; ?>
	                                        </select>
	                                    </div>
	                                    <div class="col-md-6" id="district-field">
	                                        <label for="district" class="form-label fw-semibold">Distrito *</label>
	                                        <select id="district" name="district" class="form-select">
	                                            <option value="">Escoge tu distrito</option>
	                                            <?php foreach ($deliveryOptions as $delivery): ?>
	                                            <option value="<?= $this->e($delivery['district']) ?>" <?= $selectedDistrict === $delivery['district'] ? 'selected' : '' ?>>
	                                                <?= $this->e($delivery['district']) ?>
	                                            </option>
	                                            <?php endforeach; ?>
	                                        </select>
	                                    </div>
	                                    <div class="col-md-6" id="courier-field" style="display:none;">
	                                        <label for="courier" class="form-label fw-semibold">Courier para provincia *</label>
	                                        <select id="courier" name="courier" class="form-select">
	                                            <option value="">Selecciona courier</option>
	                                            <?php foreach (['OLVA', 'SHALOM'] as $courier): ?>
	                                            <option value="<?= $this->e($courier) ?>" <?= $selectedCourier === $courier ? 'selected' : '' ?>><?= $this->e($courier) ?></option>
	                                            <?php endforeach; ?>
	                                        </select>
	                                        <small class="text-muted">Para ciudades fuera de Lima el envío se tratará como provincia.</small>
	                                    </div>
                                        <div class="col-12" id="province-fee-note" style="display:none;">
                                            <div class="rounded-3 p-3" style="background:#fff8e6;border:1px solid #f3d58a;">
                                                <div class="fw-semibold"><i class="bi bi-box-seam me-2 text-success"></i>Colocación en oficina de courier: S/. <?= $this->e(number_format($provinceHandlingFee, 2)) ?></div>
                                                <div class="text-muted mt-1" style="font-size:.875rem;">Esta tarifa cubre la entrega de tu producto en la oficina de OLVA o SHALOM. El costo del transporte a provincia no está incluido y se coordina directamente con el courier.</div>
                                            </div>
                                        </div>
	                                    <div class="col-md-6" id="zone-field">
	                                        <label for="delivery_zone" class="form-label fw-semibold">Zona</label>
	                                        <input type="text" id="delivery_zone" class="form-control" value="" readonly>
	                                    </div>
	                                    <div class="col-md-6" id="fee-field">
	                                        <label for="delivery_fee" class="form-label fw-semibold">Delivery</label>
	                                        <input type="text" id="delivery_fee" class="form-control" value="" readonly>
	                                    </div>
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1">
                            <span class="badge bg-success me-2">3</span>Pago con Tarjeta
                        </h5>
                        <p class="text-muted mb-0" style="font-size: .85rem;">
                            Crearemos una orden para que puedas usar el formulario de pago para cancelar tu compra.
                        </p>
                    </div>
                </div>
                <button type="submit" class="btn btn-success py-3 px-4 fw-bold">
                    <i class="bi bi-lock-fill me-2"></i>Continuar a pago
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if ($isPaymentMode): ?>
        <div class="w-100"></div>
        <?php endif; ?>
        <div class="<?= $isPaymentMode ? 'col-12 col-md-8 col-lg-6' : 'col-12 col-lg-5' ?>">
            <div class="card border-0 shadow-sm <?= $isPaymentMode ? '' : 'sticky-top' ?>" style="border-radius: 16px; <?= $isPaymentMode ? '' : 'top: 90px;' ?>">
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
                        <span class="text-muted" id="summary-shipping-label"><?= $isProvinceOrder ? 'Colocación en courier' : 'Envío' ?></span>
                        <span class="fw-bold" id="summary-shipping">
                            <?php if ((float)$shipping > 0): ?>
                            S/. <?= $this->e(number_format((float)$shipping, 2)) ?>
                            <?php else: ?>
	                            <?= $selectedFulfillment === 'pickup' ? 'Recojo en tienda' : 'Selecciona distrito' ?>
	                            <?php endif; ?>
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total</span>
                        <span class="fw-bold fs-4 text-success" id="summary-total">S/. <?= $this->e(number_format((float)$total, 2)) ?></span>
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

<?php if (!$isPaymentMode): ?>
<script>
(() => {
	    const deliveryOptions = <?= json_encode($deliveryOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
	    const subtotal = <?= json_encode((float)$subtotal) ?>;
        const provinceHandlingFee = <?= json_encode($provinceHandlingFee) ?>;
	    const fulfillment = Array.from(document.querySelectorAll('input[name="fulfillment_method"]'));
	    const deliveryFields = document.getElementById('delivery-fields');
	    const pickupNote = document.getElementById('pickup-note');
	    const address = document.getElementById('address');
	    const district = document.getElementById('district');
	    const city = document.getElementById('city');
	    const courier = document.getElementById('courier');
	    const districtField = document.getElementById('district-field');
	    const courierField = document.getElementById('courier-field');
        const provinceFeeNote = document.getElementById('province-fee-note');
	    const zoneField = document.getElementById('zone-field');
	    const feeField = document.getElementById('fee-field');
	    const zone = document.getElementById('delivery_zone');
	    const fee = document.getElementById('delivery_fee');
    const summaryShippingLabel = document.getElementById('summary-shipping-label');
    const summaryShipping = document.getElementById('summary-shipping');
    const summaryTotal = document.getElementById('summary-total');
    const formatMoney = value => `S/. ${Number(value).toFixed(2)}`;
    const normalizeCity = value => String(value || 'Lima').trim().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const isLimaCity = () => ['lima', 'lima metropolitana', ''].includes(normalizeCity(city?.value));

	    const updateDelivery = () => {
	        const method = fulfillment.find(input => input.checked)?.value || 'delivery';
	        const isPickup = method === 'pickup';
	        if (deliveryFields) deliveryFields.style.display = isPickup ? 'none' : '';
	        if (pickupNote) pickupNote.style.display = isPickup ? '' : 'none';
	        if (address) address.required = !isPickup;
	        if (district) district.required = !isPickup;

	        if (isPickup) {
	            if (zone) zone.value = '';
	            if (fee) fee.value = formatMoney(0);
	            if (district) district.required = false;
	            if (courier) courier.required = false;
	            if (courierField) courierField.style.display = 'none';
                if (provinceFeeNote) provinceFeeNote.style.display = 'none';
                if (summaryShippingLabel) summaryShippingLabel.textContent = 'Envío';
	            summaryShipping.textContent = 'Recojo en tienda';
	            summaryTotal.textContent = formatMoney(subtotal);
	            return;
	        }

        const isProvince = !isLimaCity();
        if (districtField) districtField.style.display = isProvince ? 'none' : '';
        if (zoneField) zoneField.style.display = isProvince ? 'none' : '';
        if (feeField) feeField.style.display = isProvince ? 'none' : '';
        if (courierField) courierField.style.display = isProvince ? '' : 'none';
        if (provinceFeeNote) provinceFeeNote.style.display = isProvince ? '' : 'none';
        if (district) district.required = !isProvince;
        if (courier) courier.required = isProvince;

        if (isProvince) {
            if (zone) zone.value = 'Envio a Provincia';
            if (fee) fee.value = formatMoney(provinceHandlingFee);
            if (summaryShippingLabel) summaryShippingLabel.textContent = 'Colocación en courier';
            summaryShipping.textContent = formatMoney(provinceHandlingFee);
            summaryTotal.textContent = formatMoney(subtotal + provinceHandlingFee);
            return;
        }

        if (summaryShippingLabel) summaryShippingLabel.textContent = 'Envío';
	        const selected = deliveryOptions.find(item => item.district === district.value);
	        if (!selected) {
	            if (zone) zone.value = '';
            if (fee) fee.value = '';
            summaryShipping.textContent = 'Selecciona distrito';
            summaryTotal.textContent = formatMoney(subtotal);
            return;
        }

        if (zone) zone.value = selected.zone;
        if (fee) fee.value = formatMoney(selected.fee);
        summaryShipping.textContent = formatMoney(selected.fee);
        summaryTotal.textContent = formatMoney(subtotal + Number(selected.fee));
    };

	    if (district) {
	        district.addEventListener('change', updateDelivery);
	    }
        if (city) {
            city.addEventListener('input', updateDelivery);
            city.addEventListener('change', updateDelivery);
        }
        if (courier) {
            courier.addEventListener('change', updateDelivery);
        }
	    fulfillment.forEach(input => input.addEventListener('change', updateDelivery));
	    updateDelivery();
	})();
	</script>
<?php endif; ?>
