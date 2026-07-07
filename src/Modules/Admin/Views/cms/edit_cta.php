<?php $this->layout('admin::layout', ['title' => 'Configurar Banner de Llamado a la Acción (CTA) | Admin', 'pageTitle' => 'Configurar CTA']) ?>

<div class="mb-4">
    <a href="/admin/cms" class="btn-admin-cancel py-2 px-3 rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Volver al listado
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="admin-form-card">
            <h6 class="fw-700 mb-4" style="color: #0f172a;">
                <i class="bi bi-megaphone me-2 text-success"></i>
                Editar Banner de Llamado a la Acción (CTA)
            </h6>

            <form action="/admin/cms/home_cta/actualizar" method="POST">
                
                <div class="mb-4 form-check form-switch p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                    <div class="ms-3">
                        <label class="form-check-label fw-bold d-block text-dark" for="is_active">Activar este bloque CTA</label>
                        <small class="text-muted">Si se desactiva, el banner inferior de la tienda no se mostrará.</small>
                    </div>
                    <input class="form-check-input me-2" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                           <?= $block['is_active'] ? 'checked' : '' ?> style="width: 2.5em; height: 1.25em;">
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Título del Banner</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?= $this->e($content['title'] ?? '') ?>" placeholder="Ej: ¿Necesitas asesoría personalizada?">
                </div>

                <div class="mb-3">
                    <label for="subtitle" class="form-label">Subtítulo o texto descriptivo</label>
                    <textarea id="subtitle" name="subtitle" class="form-control" rows="3" placeholder="Ej: Nuestro equipo de especialistas está listo para ayudarte..."><?= $this->e($content['subtitle'] ?? '') ?></textarea>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3 text-success"><i class="bi bi-link-45deg"></i> Configuración del Botón Principal</h6>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="btn_text" class="form-label">Texto del Botón</label>
                        <input type="text" id="btn_text" name="btn_text" class="form-control" 
                               value="<?= $this->e($content['btn_text'] ?? '') ?>" placeholder="Ej: Chatear por WhatsApp">
                    </div>
                    <div class="col-md-6">
                        <label for="btn_url" class="form-label">Enlace del Botón (URL)</label>
                        <input type="text" id="btn_url" name="btn_url" class="form-control" 
                               value="<?= $this->e($content['btn_url'] ?? '') ?>" placeholder="Ej: https://wa.me/51947123456">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="btn_icon" class="form-label">Icono de Bootstrap para el botón</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-<?= $this->e($content['btn_icon'] ?? 'chat') ?> text-success" id="btn-icon-preview"></i></span>
                        <input type="text" id="btn_icon" name="btn_icon" class="form-control" 
                               value="<?= $this->e($content['btn_icon'] ?? 'chat') ?>" placeholder="ej: whatsapp, chat, envelope"
                               oninput="document.getElementById('btn-icon-preview').className = 'bi bi-' + this.value + ' text-success'">
                    </div>
                    <small class="text-muted">Nombre del icono de Bootstrap Icons.</small>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn-admin-save px-4 py-2 rounded-3">
                        <i class="bi bi-floppy"></i> Guardar cambios
                    </button>
                    <a href="/admin/cms" class="btn-admin-cancel py-2 px-4 rounded-3">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
