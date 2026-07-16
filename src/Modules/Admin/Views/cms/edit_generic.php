<?php
$prettyJson = json_encode($content ?? new stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$prettyJson = $prettyJson !== false ? $prettyJson : '{}';
?>
<?php $this->layout('admin::layout', ['title' => 'Configurar bloque CMS | Admin', 'pageTitle' => 'Configurar bloque CMS']) ?>

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
                <?= $this->e($block['title']) ?>
            </h6>

            <form action="/admin/cms/<?= $this->e($block['block_key']) ?>/actualizar" method="POST">
                <div class="mb-4 form-check form-switch p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                    <div class="ms-3">
                        <label class="form-check-label fw-bold d-block text-dark" for="is_active">Mostrar este bloque en la tienda</label>
                        <small class="text-muted">Activa o desactiva la visibilidad de esta seccion.</small>
                    </div>
                    <input class="form-check-input me-2" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                           <?= $block['is_active'] ? 'checked' : '' ?> style="width: 2.5em; height: 1.25em;">
                </div>

                <div class="mb-3">
                    <label for="block_title" class="form-label">Nombre visible en el CMS</label>
                    <input type="text" id="block_title" name="block_title" class="form-control"
                           value="<?= $this->e($block['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Clave del bloque</label>
                    <input type="text" class="form-control" value="<?= $this->e($block['block_key']) ?>" disabled>
                </div>

                <div class="mb-4">
                    <label for="content_json" class="form-label">Contenido del bloque</label>
                    <textarea id="content_json" name="content_json" class="form-control font-monospace" rows="14" spellcheck="false"><?= $this->e($prettyJson) ?></textarea>
                    <small class="text-muted">Usa formato JSON valido. Puedes dejar <code>{}</code> si este bloque solo controla visibilidad.</small>
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