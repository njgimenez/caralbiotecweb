<?php
$isEdit = isset($product);
$title = $isEdit ? "Editar: {$product['name']}" : 'Nuevo Producto';
$pageTitle = $isEdit ? 'Editar Producto' : 'Nuevo Producto';
$mediaRows = $media ?? [];
if ($mediaRows === [] && !empty($product['image_url'])) {
    $mediaRows[] = ['media_type' => 'image', 'url' => $product['image_url'], 'title' => 'Imagen principal'];
}
while (count($mediaRows) < 6) { $mediaRows[] = ['media_type'=>'image','url'=>'','title'=>'']; }
?>
<?php $this->layout('admin::layout', ['title' => "$title | Admin Caral Biotec", 'pageTitle' => $pageTitle]) ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="/admin/productos" class="btn-admin-cancel rounded-3 py-2 px-3"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<form id="productForm" action="<?= $isEdit ? "/admin/productos/{$product['id']}/actualizar" : '/admin/productos/guardar' ?>" method="POST">
    <input type="hidden" name="image_url" id="image_url_hidden" value="<?= $this->e($product['image_url'] ?? $_POST['image_url'] ?? '') ?>">
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="admin-form-card mb-4">
                <h6 class="fw-700 mb-4"><i class="bi bi-info-circle me-2 text-success"></i>Informacion general</h6>
                <div class="mb-3"><label class="form-label">Nombre <span class="text-danger">*</span></label><input type="text" name="name" id="name" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>" value="<?= $this->e($product['name'] ?? $_POST['name'] ?? '') ?>" required><?php if (!empty($errors['name'])): ?><div class="invalid-feedback"><?= $this->e($errors['name']) ?></div><?php endif; ?></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">SKU <span class="text-danger">*</span></label><input type="text" name="sku" class="form-control <?= !empty($errors['sku']) ? 'is-invalid' : '' ?>" value="<?= $this->e($product['sku'] ?? $_POST['sku'] ?? '') ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Slug</label><input type="text" name="slug" id="slug" class="form-control" value="<?= $this->e($product['slug'] ?? $_POST['slug'] ?? '') ?>"></div>
                </div>
                <div class="mt-3"><label class="form-label">Descripcion corta</label><textarea name="short_description" class="form-control" rows="2"><?= $this->e($product['short_description'] ?? $_POST['short_description'] ?? '') ?></textarea></div>
                <div class="mt-3"><label class="form-label">Descripcion completa</label><textarea name="description" class="form-control" rows="6"><?= $this->e($product['description'] ?? $_POST['description'] ?? '') ?></textarea></div>
            </div>

            <div class="admin-form-card">
                <h6 class="fw-700 mb-3"><i class="bi bi-images me-2 text-success"></i>Galeria multimedia</h6>
                <p class="text-muted" style="font-size:.9rem">Agrega imagenes y videos. La primera imagen se usara como portada del producto.</p>
                <?php foreach ($mediaRows as $i => $row): ?>
                <div class="row g-2 align-items-end mb-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="col-md-2"><label class="form-label">Tipo</label><select name="media[<?= $i ?>][type]" class="form-select media-type"><option value="image" <?= ($row['media_type'] ?? 'image') === 'image' ? 'selected' : '' ?>>Imagen</option><option value="video" <?= ($row['media_type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option></select></div>
                    <div class="col-md-6"><label class="form-label">URL</label><input type="text" name="media[<?= $i ?>][url]" class="form-control media-url js-media-picker" value="<?= $this->e($row['url'] ?? '') ?>" placeholder="/uploads/imagen.jpg, https://youtu.be/... o mp4"></div>
                    <div class="col-md-3"><label class="form-label">Titulo</label><input type="text" name="media[<?= $i ?>][title]" class="form-control" value="<?= $this->e($row['title'] ?? '') ?>"></div>
                    <div class="col-md-1"><button type="button" class="btn btn-outline-secondary w-100" onclick="setCoverFromRow(this)" title="Usar como portada"><i class="bi bi-star"></i></button></div>
                </div>
                <?php endforeach; ?>
                <small class="text-muted">Para YouTube puedes pegar una URL normal. En la tienda se incrustara como video.</small>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="admin-form-card mb-4">
                <h6 class="fw-700 mb-4"><i class="bi bi-currency-dollar me-2 text-success"></i>Precio y stock</h6>
                <label class="form-label">Precio (S/.)</label><input type="number" name="price" class="form-control mb-3" step="0.01" min="0" value="<?= $this->e($product['price'] ?? $_POST['price'] ?? '') ?>" required>
                <label class="form-label">Stock</label><input type="number" name="stock" class="form-control" min="0" value="<?= $this->e($product['stock'] ?? $_POST['stock'] ?? 0) ?>" required>
            </div>
            <div class="admin-form-card mb-4">
                <h6 class="fw-700 mb-4"><i class="bi bi-tags me-2 text-success"></i>Organizacion</h6>
                <label class="form-label">Categoria</label><select name="category_id" class="form-select" required><option value="">Seleccionar...</option><?php foreach ($categories as $cat): ?><option value="<?= $this->e($cat['id']) ?>" <?= (($product['category_id'] ?? $_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>><?= $this->e($cat['name']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="admin-form-card">
                <h6 class="fw-700 mb-3"><i class="bi bi-toggles me-2 text-success"></i>Publicacion</h6>
                <div class="form-check form-switch mb-4"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= (($product['is_active'] ?? $_POST['is_active'] ?? 1)) ? 'checked' : '' ?>><label class="form-check-label" for="is_active">Producto activo</label></div>
                <button type="submit" class="btn-admin-save py-2 rounded-3 w-100"><i class="bi bi-floppy me-1"></i><?= $isEdit ? 'Guardar cambios' : 'Crear producto' ?></button>
            </div>
        </div>
    </div>
</form>

<script>
function setCoverFromRow(button) {
    const row = button.closest('.row');
    const type = row.querySelector('.media-type').value;
    const url = row.querySelector('.media-url').value.trim();
    if (type !== 'image' || !url) { alert('Selecciona una fila de imagen con URL.'); return; }
    document.getElementById('image_url_hidden').value = url;
    alert('Portada actualizada.');
}
document.getElementById('productForm').addEventListener('submit', function () {
    const cover = document.getElementById('image_url_hidden');
    if (!cover.value.trim()) {
        const firstImage = Array.from(document.querySelectorAll('.row')).find(row => row.querySelector('.media-type') && row.querySelector('.media-type').value === 'image' && row.querySelector('.media-url').value.trim());
        if (firstImage) cover.value = firstImage.querySelector('.media-url').value.trim();
    }
});
document.getElementById('name').addEventListener('input', function () {
    const slugField = document.getElementById('slug');
    if (!slugField.dataset.edited) {
        slugField.value = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
    }
});
document.getElementById('slug').addEventListener('input', function () { this.dataset.edited = true; });
</script>