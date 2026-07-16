<?php $this->layout('shared::layout', ['title' => 'Error de pago — Caral Biotec']) ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">

            <!-- ── Encabezado de error ── -->
            <div class="text-center mb-5">
                <div style="width:90px;height:90px;background:linear-gradient(135deg,#dc2626,#ef4444);
                            border-radius:50%;display:inline-flex;align-items:center;
                            justify-content:center;box-shadow:0 8px 24px rgba(220,38,38,.3);
                            margin-bottom:1.5rem;">
                    <i class="bi bi-x-lg text-white" style="font-size:2.5rem;"></i>
                </div>
                <h1 class="fw-bold mb-2" style="font-size:1.75rem;">Error al procesar el pago</h1>
                <p class="text-muted">No se realizó ningún cobro a tu tarjeta.</p>
            </div>

            <!-- ── Mensaje de error ── -->
            <div class="alert rounded-3 mb-4 p-4"
                 style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;">
                <div class="d-flex gap-3 align-items-start">
                    <i class="bi bi-exclamation-triangle-fill mt-1" style="font-size:1.3rem;flex-shrink:0;"></i>
                    <div>
                        <strong class="d-block mb-1">Motivo del rechazo:</strong>
                        <?= $this->e($errorMessage) ?>
                    </div>
                </div>
            </div>

            <!-- ── Acciones ── -->
            <div class="card border-0 shadow-sm mb-4 p-4" style="border-radius:16px;">
                <h6 class="fw-bold mb-3">¿Qué puedes hacer?</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex gap-2">
                        <i class="bi bi-arrow-repeat text-primary mt-1"></i>
                        <span>Intenta nuevamente con <strong>otra tarjeta</strong> o verifica los datos ingresados.</span>
                    </li>
                    <li class="mb-3 d-flex gap-2">
                        <i class="bi bi-bank text-primary mt-1"></i>
                        <span>Consulta con tu <strong>banco emisor</strong> para verificar que tu tarjeta esté habilitada para compras online.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-whatsapp text-success mt-1"></i>
                        <span>¿Necesitas ayuda? Escríbenos por <a href="<?= $this->e($this->companyWhatsappUrl()) ?>" target="_blank" class="text-success fw-bold">WhatsApp</a> y te asistimos.</span>
                    </li>
                </ul>
            </div>

            <!-- ── Botones ── -->
            <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                <a href="/checkout" class="btn btn-success px-4 py-2 fw-bold" style="border-radius:10px;">
                    <i class="bi bi-arrow-repeat me-2"></i>Intentar de nuevo
                </a>
                <a href="/carrito" class="btn btn-outline-secondary px-4 py-2 fw-bold" style="border-radius:10px;">
                    <i class="bi bi-cart me-2"></i>Ver carrito
                </a>
            </div>
        </div>
    </div>
</div>
