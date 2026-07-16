<?php $this->layout('shared::layout', ['title' => 'Contacto - Caral Biotec']) ?>
<section class="py-5" style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
    <div class="container">
        <span class="badge mb-3" style="background:var(--green-100);color:var(--green-700)">Contacto</span>
        <h1 class="fw-bold mb-3" style="font-size:clamp(2rem,4vw,3.2rem);color:#0f172a">Estamos para ayudarte</h1>
        <p class="lead text-muted mb-0">Escribenos para resolver dudas sobre productos, delivery, recojo en tienda o estado de tu pedido.</p>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4"><div class="p-4 h-100 bg-white shadow-sm" style="border-radius:8px;border:1px solid #e2e8f0"><i class="bi bi-whatsapp fs-2" style="color:var(--green-700)"></i><h5 class="fw-bold mt-3">WhatsApp</h5><p class="text-muted"><?= $this->e($company['phone'] ?? '') ?></p><a class="btn btn-primary-custom" href="<?= $this->e($whatsappUrl) ?>" target="_blank">Escribir ahora</a></div></div>
            <div class="col-md-4"><div class="p-4 h-100 bg-white shadow-sm" style="border-radius:8px;border:1px solid #e2e8f0"><i class="bi bi-envelope fs-2" style="color:var(--green-700)"></i><h5 class="fw-bold mt-3">Correo</h5><p class="text-muted mb-0"><?= $this->e($company['email'] ?? '') ?></p></div></div>
            <div class="col-md-4"><div class="p-4 h-100 bg-white shadow-sm" style="border-radius:8px;border:1px solid #e2e8f0"><i class="bi bi-geo-alt fs-2" style="color:var(--green-700)"></i><h5 class="fw-bold mt-3">Direccion</h5><p class="text-muted mb-0"><?= $this->e($company['address'] ?? '') ?></p></div></div>
        </div>
    </div>
</section>