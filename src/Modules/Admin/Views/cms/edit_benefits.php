<?php $this->layout('admin::layout', ['title' => 'Configurar Beneficios | Admin', 'pageTitle' => 'Configurar Beneficios']) ?>

<div class="mb-4">
    <a href="/admin/cms" class="btn-admin-cancel py-2 px-3 rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Volver al listado
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="admin-form-card">
            <h6 class="fw-700 mb-4" style="color: #0f172a;">
                <i class="bi bi-shield-check me-2 text-success"></i>
                Editar Barra de Beneficios (Trust Badges)
            </h6>

            <form action="/admin/cms/home_benefits/actualizar" method="POST">
                
                <div class="mb-4 form-check form-switch p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                    <div class="ms-3">
                        <label class="form-check-label fw-bold d-block text-dark" for="is_active">Activar esta barra de beneficios</label>
                        <small class="text-muted">Si se desactiva, no aparecerá en el inicio de la tienda.</small>
                    </div>
                    <input class="form-check-input me-2" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                           <?= $block['is_active'] ? 'checked' : '' ?> style="width: 2.5em; height: 1.25em;">
                </div>

                <p class="text-muted mb-4" style="font-size: .85rem;">Puedes configurar hasta 4 tarjetas informativas con sus respectivos iconos de Bootstrap Icons.</p>

                <div class="row g-4 mb-4">
                    <?php for ($i = 0; $i < 4; $i++): 
                        $item = $content[$i] ?? ['icon' => 'check-circle', 'title' => '', 'desc' => ''];
                    ?>
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="badge bg-success mb-3">Tarjeta #<?= $i + 1 ?></span>
                            
                            <div class="mb-3">
                                <label class="form-label">Icono de Bootstrap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-<?= $this->e($item['icon']) ?> text-success" id="icon-preview-<?= $i ?>"></i></span>
                                    <input type="text" name="icon_<?= $i ?>" class="form-control" 
                                           value="<?= $this->e($item['icon']) ?>" 
                                           placeholder="ej: truck, lock, headset, shield-check"
                                           oninput="document.getElementById('icon-preview-<?= $i ?>').className = 'bi bi-' + this.value + ' text-success'">
                                </div>
                                <small class="text-muted">Escribe el nombre del icono de <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a>.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Título</label>
                                <input type="text" name="title_<?= $i ?>" class="form-control" 
                                       value="<?= $this->e($item['title']) ?>" placeholder="Ej: Compra 100% segura">
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Subtexto / Descripción corta</label>
                                <input type="text" name="desc_<?= $i ?>" class="form-control" 
                                       value="<?= $this->e($item['desc']) ?>" placeholder="Ej: Tus datos protegidos">
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
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
