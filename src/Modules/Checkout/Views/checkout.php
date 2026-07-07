<?php $this->layout('shared::layout', ['title' => 'Finalizar compra - Caral Biotec']) ?>

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

    <form id="checkout-form" action="/checkout/procesar" method="POST" novalidate>
        <input type="hidden" id="izipay_response" name="izipay_response" value="">
        <input type="hidden" id="izipay_order_number" name="izipay_order_number" value="<?= $this->e($izipay['orderNumber']) ?>">

        <div class="row g-4">
            <div class="col-12 col-lg-7">
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
                        <p class="text-muted mb-3" style="font-size: .85rem;">
                            <i class="bi bi-shield-lock-fill text-success me-1"></i>
                            El pago se procesa en el formulario seguro de <strong>Izipay</strong>.
                        </p>

                        <div class="border rounded-3 p-3 bg-light">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                    <i class="bi bi-credit-card-2-front text-success fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Checkout pop-up</div>
                                    <div class="text-muted" style="font-size:.85rem;">Tarjetas, QR, Yape y otros medios segun tu configuracion Izipay.</div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($izipay['demoMode'])): ?>
                        <div class="alert alert-warning mt-3 mb-0 rounded-3" style="font-size:.85rem;">
                            Modo demo activo. Usa aprobacion simulada para validar el flujo local; no procesa pagos reales.
                        </div>
                        <?php endif; ?>

                        <div id="payment-error" class="alert alert-danger mt-3 d-none rounded-3" role="alert"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="border-radius: 16px; top: 90px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Resumen de tu pedido</h5>

                        <div class="mb-3">
                            <?php foreach ($items as $item): ?>
                            <div class="d-flex align-items-center gap-3 mb-2 pb-2" style="border-bottom: 1px solid #f1f5f9;">
                                <div style="width:48px;height:48px;background:#f6f3ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="bi bi-box-seam text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size:.875rem;"><?= $this->e($item['name']) ?></div>
                                    <div class="text-muted" style="font-size:.78rem;">Cant: <?= $this->e($item['quantity']) ?></div>
                                </div>
                                <div class="fw-bold text-success" style="font-size:.875rem;white-space:nowrap">
                                    S/. <?= $this->e(number_format($item['price_seen'] * $item['quantity'], 2)) ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subtotal</span>
                            <span>S/. <?= $this->e(number_format($subtotal, 2)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Envio</span>
                            <span class="text-success fw-bold"><i class="bi bi-truck me-1"></i>Gratis</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-4 text-success">S/. <?= $this->e(number_format($total, 2)) ?></span>
                        </div>

                        <button type="submit" id="btn-pay" class="btn w-100 py-3 fw-bold"
                                style="background:linear-gradient(135deg,#4b2bb0,#6f5add);color:white;border-radius:12px;font-size:1rem;border:none;box-shadow:0 4px 16px rgba(75,43,176,.35);transition:all .2s">
                            <i class="bi bi-lock-fill me-2"></i>
                            Pagar S/. <?= $this->e(number_format($total, 2)) ?>
                        </button>
                        <p class="text-center text-muted mt-2 mb-0" style="font-size: .75rem;">
                            Orden <?= $this->e($izipay['orderNumber']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="izipayDemoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Izipay demo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tarjeta demo</span>
                        <strong>4111 1111 1111 1111</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Vencimiento</span>
                        <strong>12/30</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">CVV</span>
                        <strong>123</strong>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Total</span>
                    <strong class="fs-4 text-success">S/. <?= $this->e(number_format($total, 2)) ?></strong>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success fw-bold" id="btn-demo-approve">
                    Aprobar pago demo
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (empty($izipay['demoMode'])): ?>
<script src="<?= $this->e($izipay['sdkUrl']) ?>"></script>
<?php endif; ?>
<script>
(function () {
    'use strict';

    const form = document.getElementById('checkout-form');
    const payBtn = document.getElementById('btn-pay');
    const errorDiv = document.getElementById('payment-error');
    const responseInput = document.getElementById('izipay_response');
    const config = <?= json_encode($izipay, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const total = <?= json_encode(number_format((float)$total, 2, '.', '')) ?>;

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        clearError();

        if (!validateCustomerFields()) {
            showError('Completa tus datos personales y direccion de envio.');
            return;
        }

        if (config.demoMode) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('izipayDemoModal')).show();
            return;
        }

        openIzipayCheckout();
    });

    document.getElementById('btn-demo-approve').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Aprobando...';
        responseInput.value = JSON.stringify(buildDemoResponse());
        bootstrap.Modal.getOrCreateInstance(document.getElementById('izipayDemoModal')).hide();
        form.submit();
    });

    function openIzipayCheckout() {
        if (!window.Izipay) {
            showError('No se pudo cargar el SDK de Izipay. Verifica tu conexion o vuelve a intentar.');
            return;
        }

        if (!config.merchantCode || !config.tokenSession || !config.keyRSA) {
            showError('Faltan credenciales sandbox de Izipay: merchantCode, tokenSession o keyRSA.');
            return;
        }

        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Abriendo Izipay...';

        try {
            const checkout = new Izipay({ config: buildIzipayConfig() });
            checkout.LoadForm({
                authorization: config.tokenSession,
                keyRSA: config.keyRSA,
                callbackResponse: function (response) {
                    responseInput.value = JSON.stringify(response || {});
                    form.submit();
                }
            });
        } catch (error) {
            showError(error.message || 'No se pudo abrir el checkout de Izipay.');
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pagar S/. ' + total;
        }
    }

    function buildIzipayConfig() {
        const customer = customerData();
        return {
            action: 'pay',
            merchantCode: config.merchantCode,
            transactionId: config.transactionId,
            order: {
                orderNumber: config.orderNumber,
                currency: 'PEN',
                amount: total,
                processType: 'AT',
                merchantBuyerId: customer.email || 'cliente-local',
                dateTimeTransaction: config.dateTimeTransaction
            },
            billing: customer,
            shipping: customer,
            render: { typeForm: 'pop-up' }
        };
    }

    function buildDemoResponse() {
        const now = new Date();
        const date = now.toISOString().slice(0, 10).replace(/-/g, '');
        const time = now.toTimeString().slice(0, 8).replace(/:/g, '');
        const payload = {
            code: '00',
            message: 'Operacion exitosa',
            messageUser: 'Operacion exitosa',
            messageUserEng: 'Successful',
            transactionId: config.transactionId,
            response: {
                payMethod: 'CARD',
                order: [{
                    payMethodAuthorization: 'CARD',
                    codeAuth: '831000',
                    currency: 'PEN',
                    amount: total,
                    installment: '00',
                    deferred: '0',
                    orderNumber: config.orderNumber,
                    stateMessage: 'Autorizado',
                    dateTransaction: date,
                    timeTransaction: time,
                    uniqueId: 'DEMO-' + config.transactionId,
                    referenceNumber: String(Math.floor(1000000 + Math.random() * 8999999))
                }],
                card: {
                    brand: 'VS',
                    pan: '411111******1111',
                    save: 'false'
                },
                billing: customerData(),
                merchant: {
                    merchantCode: config.merchantCode || 'DEMO-MERCHANT'
                },
                token: {
                    merchantBuyerId: document.getElementById('email').value,
                    cardToken: '',
                    alias: ''
                }
            },
            signature: 'DEMO_SIGNATURE'
        };
        payload.payloadHttp = JSON.stringify(payload);
        return payload;
    }

    function customerData() {
        const fullName = document.getElementById('name').value.trim();
        const parts = fullName.split(/\s+/);
        const firstName = parts.shift() || 'Cliente';
        const lastName = parts.join(' ') || 'Caral';
        return {
            firstName: firstName,
            lastName: lastName,
            email: document.getElementById('email').value.trim(),
            phoneNumber: document.getElementById('phone').value.trim() || '999999999',
            street: document.getElementById('address').value.trim(),
            city: document.getElementById('city').value.trim() || 'Lima',
            state: document.getElementById('district').value.trim() || 'Lima',
            country: 'PE',
            postalCode: '15000',
            documentType: 'DNI',
            document: '12345678'
        };
    }

    function validateCustomerFields() {
        return document.getElementById('name').value.trim().length >= 3
            && document.getElementById('email').checkValidity()
            && document.getElementById('address').value.trim() !== ''
            && document.getElementById('district').value.trim() !== '';
    }

    function clearError() {
        errorDiv.classList.add('d-none');
        errorDiv.textContent = '';
    }

    function showError(message) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('d-none');
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
})();
</script>
