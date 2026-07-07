<?php $this->layout('shared::layout', ['title' => $title ?? 'Entrada no encontrada | Caral Biotec']); ?>

<section class="py-5">
    <div class="container text-center" style="max-width: 680px;">
        <div class="display-6 fw-bold mb-3">Entrada no encontrada</div>
        <p class="text-muted mb-4">La entrada que buscas no existe o aún no está publicada.</p>
        <a href="/blog" class="btn btn-success">Volver al blog</a>
    </div>
</section>
