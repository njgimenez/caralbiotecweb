<?php $this->layout('admin::layout', ['title' => 'Configurar Hero Principal | Admin', 'pageTitle' => 'Configurar Hero Principal']) ?>

<div class="mb-4">
    <a href="/admin/cms" class="btn-admin-cancel py-2 px-3 rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Volver al listado
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="admin-form-card">
            <h6 class="fw-700 mb-4" style="color: #0f172a;">
                <i class="bi bi-layout-text-window-reverse me-2 text-success"></i>
                Editar Banner Hero Principal
            </h6>

            <form action="/admin/cms/home_hero/actualizar" method="POST">
                
                <div class="mb-4 form-check form-switch p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                    <div class="ms-3">
                        <label class="form-check-label fw-bold d-block text-dark" for="is_active">Activar este bloque</label>
                        <small class="text-muted">Si se desactiva, el banner principal no aparecerá en el inicio de la tienda.</small>
                    </div>
                    <input class="form-check-input me-2" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                           <?= $block['is_active'] ? 'checked' : '' ?> style="width: 2.5em; height: 1.25em;">
                </div>

                <div class="mb-3">
                    <label for="tag_text" class="form-label">Texto de etiqueta superior</label>
                    <input type="text" id="tag_text" name="tag_text" class="form-control" 
                           value="<?= $this->e($content['tag_text'] ?? '') ?>" placeholder="Ej: Productos certificados · Lima, Perú">
                    <small class="text-muted">Pequeño texto con check verde que se muestra arriba del título.</small>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="title_part1" class="form-label">Título: Parte 1</label>
                        <input type="text" id="title_part1" name="title_part1" class="form-control" 
                               value="<?= $this->e($content['title_part1'] ?? '') ?>" placeholder="Ej: Soluciones integrales para tu">
                    </div>
                    <div class="col-md-4">
                        <label for="title_accent" class="form-label">Título: Palabra Destacada (Color)</label>
                        <input type="text" id="title_accent" name="title_accent" class="form-control" 
                               value="<?= $this->e($content['title_accent'] ?? '') ?>" placeholder="Ej: bienestar">
                        <small class="text-muted">Esta palabra tendrá el color verde de resalte.</small>
                    </div>
                    <div class="col-md-4">
                        <label for="title_part2" class="form-label">Título: Parte 2</label>
                        <input type="text" id="title_part2" name="title_part2" class="form-control" 
                               value="<?= $this->e($content['title_part2'] ?? '') ?>" placeholder="Ej: y recuperación">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="subtitle" class="form-label">Subtítulo (Párrafo explicativo)</label>
                    <textarea id="subtitle" name="subtitle" class="form-control" rows="3" placeholder="Descripción del banner..."><?= $this->e($content['subtitle'] ?? '') ?></textarea>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-3 text-success"><i class="bi bi-link-45deg"></i> Botón Principal (Verde con sombra)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="btn_primary_text" class="form-label">Texto del Botón</label>
                        <input type="text" id="btn_primary_text" name="btn_primary_text" class="form-control" 
                               value="<?= $this->e($content['btn_primary_text'] ?? '') ?>" placeholder="Ej: Comprar ahora">
                    </div>
                    <div class="col-md-6">
                        <label for="btn_primary_url" class="form-label">Enlace del Botón (URL)</label>
                        <input type="text" id="btn_primary_url" name="btn_primary_url" class="form-control" 
                               value="<?= $this->e($content['btn_primary_url'] ?? '') ?>" placeholder="Ej: /productos">
                    </div>
                </div>

                <h6 class="fw-bold mb-3 text-success"><i class="bi bi-link-45deg"></i> Botón Secundario (Borde y fondo blanco)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="btn_secondary_text" class="form-label">Texto del Botón</label>
                        <input type="text" id="btn_secondary_text" name="btn_secondary_text" class="form-control" 
                               value="<?= $this->e($content['btn_secondary_text'] ?? '') ?>" placeholder="Ej: Ver categorías">
                    </div>
                    <div class="col-md-6">
                        <label for="btn_secondary_url" class="form-label">Enlace del Botón (URL)</label>
                        <input type="text" id="btn_secondary_url" name="btn_secondary_url" class="form-control" 
                               value="<?= $this->e($content['btn_secondary_url'] ?? '') ?>" placeholder="Ej: #categorias">
                    </div>
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
