<?php $this->layout('shared::layout', ['title' => '¡Pedido confirmado! — Caral Biotec']) ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <!-- ── Encabezado de éxito ── -->
            <div class="text-center mb-5">
                <div style="width:90px;height:90px;background:linear-gradient(135deg,#4b2bb0,#6f5add);
                            border-radius:50%;display:inline-flex;align-items:center;
                            justify-content:center;box-shadow:0 8px 24px rgba(75,43,176,.35);
                            margin-bottom:1.5rem;animation:bounce .6s ease;">
                    <i class="bi bi-check-lg text-white" style="font-size:2.5rem;"></i>
                </div>
                <h1 class="fw-bold mb-1" style="font-size:1.75rem;">¡Pedido confirmado!</h1>
                <p class="text-muted">
                    Gracias por tu compra. Hemos recibido tu pago correctamente.
                </p>
            </div>

            <!-- ── Detalle de la orden ── -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Orden <?= $this->e($order['order_number']) ?></h5>
                        <span class="badge rounded-pill px-3 py-2"
                              style="background:#e7e0ff;color:#4b2bb0;font-size:.8rem;">
                            <i class="bi bi-check-circle-fill me-1"></i>Pagado
                        </span>
                    </div>

                    <!-- Datos del comprador -->
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">Comprador</small>
                                <strong><?= $this->e($order['customer_name']) ?></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Correo</small>
                                <strong><?= $this->e($order['customer_email']) ?></strong>
                            </div>
                            <?php if ($order['shipping_address']): ?>
                            <div class="col-12">
                                <small class="text-muted d-block">Dirección de envío</small>
                                <strong>
                                    <?= $this->e($order['shipping_address']) ?>,
                                    <?= $this->e($order['shipping_district']) ?>,
                                    <?= $this->e($order['shipping_city']) ?>
                                </strong>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Líneas de la orden -->
                    <h6 class="fw-bold mb-2">Productos</h6>
                    <?php foreach ($orderItems as $line): ?>
                    <div class="d-flex justify-content-between align-items-center py-2"
                         style="border-bottom:1px solid #f1f5f9;">
                        <div>
                            <div class="fw-semibold" style="font-size:.875rem;">
                                <?= $this->e($line['product_name']) ?>
                            </div>
                            <small class="text-muted">
                                <?= $this->e($line['quantity']) ?> x S/. <?= $this->e(number_format($line['unit_price'], 2)) ?>
                            </small>
                        </div>
                        <span class="fw-bold" style="color:var(--green-700)">
                            S/. <?= $this->e(number_format($line['total_price'], 2)) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>

                    <!-- Total -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="fw-bold fs-5">Total pagado</span>
                        <span class="fw-bold fs-4" style="color:var(--green-700)">
                            S/. <?= $this->e(number_format($order['total'], 2)) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ── Próximos pasos ── -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;border-left:4px solid #6f5add !important;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2" style="color:var(--green-700)"></i>¿Qué sigue?</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex gap-2">
                            <i class="bi bi-envelope mt-1" style="color:var(--green-700)"></i>
                            <span>Recibirás un email de confirmación en <strong><?= $this->e($order['customer_email']) ?></strong>.</span>
                        </li>
                        <li class="mb-2 d-flex gap-2">
                            <i class="bi bi-truck mt-1" style="color:var(--green-700)"></i>
                            <span>Tu pedido será procesado y enviado dentro de <strong>24-48 horas hábiles</strong>.</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="bi bi-whatsapp mt-1" style="color:var(--green-700)"></i>
                            <span>¿Tienes dudas? Escríbenos por <a href="https://wa.me/51947123456" target="_blank" style="color:var(--green-700);font-weight:700">WhatsApp</a>.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ── Acciones ── -->
            <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                <a href="/productos" class="btn btn-primary-custom px-4 py-2 fw-bold" style="border-radius:10px;">
                    <i class="bi bi-shop me-2"></i>Seguir comprando
                </a>
                <a href="/" class="btn btn-outline-secondary px-4 py-2 fw-bold" style="border-radius:10px;">
                    <i class="bi bi-house me-2"></i>Ir al inicio
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes bounce {
    0%   { transform: scale(.5); opacity: 0; }
    70%  { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}
</style>
