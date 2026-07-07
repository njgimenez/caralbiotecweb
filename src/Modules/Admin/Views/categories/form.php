<?php
$isEdit = isset($category);
$title = $isEdit ? "Editar: {$category['name']}" : 'Nueva Categoría';
?>
<?php $this->layout('admin::layout', ['title' => "$title | Admin", 'pageTitle' => $title]) ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="/admin/categorias" class="btn-admin-cancel rounded-3 py-2 px-3">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="admin-form-card">
            <h6 class="fw-700 mb-4" style="color:#0f172a">
                <i class="bi bi-tags me-2 text-success"></i>
                <?= $isEdit ? 'Editar categoría' : 'Nueva categoría' ?>
            </h6>

            <form action="<?= $isEdit ? "/admin/categorias/{$category['id']}/actualizar" : '/admin/categorias/guardar' ?>" method="POST">

                <div class="mb-3">
                    <label for="cat_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="cat_name" name="name" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                           value="<?= $this->e($category['name'] ?? $_POST['name'] ?? '') ?>" required>
                    <?php if (!empty($errors['name'])): ?>
                        <div class="invalid-feedback"><?= $this->e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="cat_slug" class="form-label">Slug (URL)</label>
                    <input type="text" id="cat_slug" name="slug" class="form-control"
                           value="<?= $this->e($category['slug'] ?? $_POST['slug'] ?? '') ?>"
                           placeholder="se-genera-automaticamente">
                </div>

                <div class="mb-3">
                    <label for="cat_description" class="form-label">Descripción</label>
                    <textarea id="cat_description" name="description" class="form-control" rows="3"><?= $this->e($category['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="cat_image" class="form-label">URL de imagen</label>
                    <input type="text" id="cat_image" name="image_url" class="form-control"
                           value="<?= $this->e($category['image_url'] ?? $_POST['image_url'] ?? '') ?>"
                           placeholder="/uploads/categoria.jpg">
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn-admin-save px-4 py-2 rounded-3">
                        <i class="bi bi-floppy me-1"></i> <?= $isEdit ? 'Guardar cambios' : 'Crear categoría' ?>
                    </button>
                    <a href="/admin/categorias" class="btn-admin-cancel py-2 px-4 rounded-3">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('cat_name').addEventListener('input', function() {
    const slugField = document.getElementById('cat_slug');
    if (!slugField.dataset.edited) {
        slugField.value = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-');
    }
});
document.getElementById('cat_slug').addEventListener('input', function() {
    this.dataset.edited = true;
});
</script>
