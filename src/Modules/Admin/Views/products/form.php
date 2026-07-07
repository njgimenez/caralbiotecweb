<?php
$isEdit = isset($product);
$title = $isEdit ? "Editar: {$product['name']}" : 'Nuevo Producto';
$pageTitle = $isEdit ? 'Editar Producto' : 'Nuevo Producto';
?>
<?php $this->layout('admin::layout', ['title' => "$title | Admin Caral Biotec", 'pageTitle' => $pageTitle]) ?>

<!-- Cropper.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

<style>
/* ── Image Widget ── */
.img-widget-tabs { display:flex; gap:.5rem; margin-bottom:1rem; }
.img-widget-tab {
    flex:1; padding:.55rem 1rem; border:1.5px solid #e2e8f0; border-radius:8px;
    background:#f8fafc; cursor:pointer; font-size:.85rem; font-weight:600;
    color:#64748b; text-align:center; transition:all .18s;
}
.img-widget-tab.active {
    border-color:var(--admin-accent); background:rgba(111,90,221,.08); color:#4b2bb0;
}
.img-widget-pane { display:none; }
.img-widget-pane.active { display:block; }

/* Drop zone */
.img-dropzone {
    border:2px dashed #cbd5e1; border-radius:10px;
    padding:2rem 1rem; text-align:center; cursor:pointer;
    transition:all .2s; background:#f8fafc;
}
.img-dropzone:hover, .img-dropzone.drag-over {
    border-color:var(--admin-accent); background:rgba(111,90,221,.05);
}
.img-dropzone i { font-size:2rem; color:#94a3b8; margin-bottom:.5rem; display:block; }
.img-dropzone p { margin:0; color:#64748b; font-size:.85rem; }
.img-dropzone input[type=file] { display:none; }

/* Preview */
.img-preview-wrap {
    margin-top:1rem; border-radius:10px; overflow:hidden;
    border:1.5px solid #e2e8f0; background:#000;
    display:none; position:relative;
}
.img-preview-wrap.visible { display:block; }
.img-preview-wrap img { width:100%; max-height:260px; object-fit:contain; display:block; }
.img-preview-actions {
    position:absolute; top:.5rem; right:.5rem; display:flex; gap:.4rem;
}
.img-action-btn {
    width:34px; height:34px; border-radius:8px;
    border:none; cursor:pointer; display:flex; align-items:center; justify-content:center;
    font-size:.9rem; transition:all .15s;
    background:rgba(15,23,42,.75); color:#fff; backdrop-filter:blur(4px);
}
.img-action-btn:hover { background:rgba(111,90,221,.9); }

/* Cropper modal */
.cropper-modal-overlay {
    display:none; position:fixed; inset:0; z-index:1055;
    background:rgba(0,0,0,.75); backdrop-filter:blur(4px);
    align-items:center; justify-content:center;
}
.cropper-modal-overlay.open { display:flex; }
.cropper-modal-box {
    background:#1e293b; border-radius:16px; width:min(760px,96vw);
    overflow:hidden; display:flex; flex-direction:column; box-shadow:0 24px 64px rgba(0,0,0,.5);
}
.cropper-modal-header {
    padding:1rem 1.5rem; border-bottom:1px solid rgba(255,255,255,.07);
    display:flex; align-items:center; justify-content:space-between;
    color:#fff; font-weight:700; font-size:1rem;
}
.cropper-modal-body { padding:1.25rem; }
.cropper-container-wrap { max-height:55vh; overflow:hidden; border-radius:8px; }
#cropperImage { display:block; max-width:100%; }
.cropper-modal-footer {
    padding:1rem 1.5rem; border-top:1px solid rgba(255,255,255,.07);
    display:flex; align-items:center; gap:.6rem; flex-wrap:wrap;
}
.crop-btn {
    padding:.45rem .9rem; border-radius:8px; border:none; cursor:pointer;
    font-size:.82rem; font-weight:600; transition:all .15s; display:inline-flex;
    align-items:center; gap:.35rem;
}
.crop-btn-primary { background:var(--admin-accent); color:#fff; }
.crop-btn-primary:hover { background:#4b2bb0; }
.crop-btn-secondary { background:rgba(255,255,255,.1); color:#cbd5e1; }
.crop-btn-secondary:hover { background:rgba(255,255,255,.18); }
.crop-btn-danger { background:rgba(239,68,68,.15); color:#f87171; }
.crop-btn-danger:hover { background:rgba(239,68,68,.25); }
.ratio-group { display:flex; gap:.4rem; margin-left:auto; }
.ratio-btn {
    padding:.35rem .7rem; border-radius:6px; border:1.5px solid rgba(255,255,255,.15);
    background:transparent; color:#94a3b8; font-size:.75rem; font-weight:600; cursor:pointer;
    transition:all .15s;
}
.ratio-btn.active, .ratio-btn:hover { border-color:var(--admin-accent); color:#8a7fff; }

/* Upload progress */
.img-upload-status {
    margin-top:.6rem; padding:.5rem .75rem; border-radius:8px;
    font-size:.8rem; display:none; align-items:center; gap:.5rem;
}
.img-upload-status.uploading { display:flex; background:rgba(59,130,246,.1); color:#60a5fa; }
.img-upload-status.success   { display:flex; background:rgba(111,90,221,.1);  color:#8a7fff; }
.img-upload-status.error     { display:flex; background:rgba(239,68,68,.1);  color:#f87171; }

/* URL preview */
.url-preview-box {
    margin-top:1rem; border-radius:10px; overflow:hidden;
    border:1.5px solid #e2e8f0; display:none; background:#f8fafc;
}
.url-preview-box.visible { display:block; }
.url-preview-box img { width:100%; max-height:200px; object-fit:contain; display:block; }
</style>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="/admin/productos" class="btn-admin-cancel rounded-3 py-2 px-3">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
</div>

<form id="productForm"
      action="<?= $isEdit ? "/admin/productos/{$product['id']}/actualizar" : '/admin/productos/guardar' ?>"
      method="POST" enctype="multipart/form-data">

    <!-- Hidden field that stores the final image URL -->
    <input type="hidden" name="image_url" id="image_url_hidden"
           value="<?= $this->e($product['image_url'] ?? $_POST['image_url'] ?? '') ?>">

    <div class="row g-4">
        <!-- ── Col principal ── -->
        <div class="col-12 col-lg-8">
            <div class="admin-form-card mb-4">
                <h6 class="fw-700 mb-4" style="color:#0f172a"><i class="bi bi-info-circle me-2 text-success"></i>Información general</h6>

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre del producto <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                           value="<?= $this->e($product['name'] ?? $_POST['name'] ?? '') ?>" required>
                    <?php if (!empty($errors['name'])): ?>
                        <div class="invalid-feedback"><?= $this->e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                        <input type="text" id="sku" name="sku" class="form-control <?= !empty($errors['sku']) ? 'is-invalid' : '' ?>"
                               value="<?= $this->e($product['sku'] ?? $_POST['sku'] ?? '') ?>" required>
                        <?php if (!empty($errors['sku'])): ?>
                            <div class="invalid-feedback"><?= $this->e($errors['sku']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="slug" class="form-label">Slug (URL)</label>
                        <input type="text" id="slug" name="slug" class="form-control"
                               value="<?= $this->e($product['slug'] ?? $_POST['slug'] ?? '') ?>"
                               placeholder="auto-generado-del-nombre">
                    </div>
                </div>

                <div class="mt-3">
                    <label for="short_description" class="form-label">Descripción corta</label>
                    <textarea id="short_description" name="short_description" class="form-control" rows="2"><?= $this->e($product['short_description'] ?? $_POST['short_description'] ?? '') ?></textarea>
                </div>

                <div class="mt-3">
                    <label for="description" class="form-label">Descripción completa</label>
                    <textarea id="description" name="description" class="form-control" rows="5"><?= $this->e($product['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- ── Imagen ── -->
            <div class="admin-form-card">
                <h6 class="fw-700 mb-4" style="color:#0f172a"><i class="bi bi-image me-2 text-success"></i>Imagen del producto</h6>

                <!-- Tabs -->
                <div class="img-widget-tabs">
                    <button type="button" class="img-widget-tab active" id="tabFile" onclick="switchTab('file')">
                        <i class="bi bi-upload me-1"></i> Subir archivo
                    </button>
                    <button type="button" class="img-widget-tab" id="tabUrl" onclick="switchTab('url')">
                        <i class="bi bi-link-45deg me-1"></i> URL externa
                    </button>
                </div>

                <!-- Pane: File upload -->
                <div class="img-widget-pane active" id="paneFile">
                    <div class="img-dropzone" id="dropzone" onclick="document.getElementById('fileInput').click()"
                         ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p><strong>Arrastra una imagen aquí</strong> o haz clic para seleccionar</p>
                        <p style="font-size:.75rem;color:#94a3b8;margin-top:.3rem">JPG, PNG, WebP · Máx. 5 MB</p>
                        <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <!-- Preview + actions -->
                    <div class="img-preview-wrap" id="filePreviewWrap">
                        <img id="filePreviewImg" src="" alt="Vista previa">
                        <div class="img-preview-actions">
                            <button type="button" class="img-action-btn" title="Editar / Recortar" onclick="openCropper()">
                                <i class="bi bi-crop"></i>
                            </button>
                            <button type="button" class="img-action-btn" title="Quitar imagen" onclick="clearFileImage()" style="background:rgba(239,68,68,.75)">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="img-upload-status" id="uploadStatus">
                        <i class="bi bi-arrow-repeat spin"></i>
                        <span id="uploadStatusText">Subiendo imagen…</span>
                    </div>
                </div>

                <!-- Pane: URL -->
                <div class="img-widget-pane" id="paneUrl">
                    <label class="form-label">URL de imagen externa</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#f8fafc">
                            <i class="bi bi-link-45deg"></i>
                        </span>
                        <input type="url" id="urlInput" class="form-control"
                               placeholder="https://ejemplo.com/imagen.jpg"
                               value="<?= (isset($product['image_url']) && str_starts_with($product['image_url'] ?? '', 'http')) ? $this->e($product['image_url']) : '' ?>">
                        <button type="button" class="btn btn-outline-secondary" onclick="previewUrl()">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted">Ingresa la URL completa de la imagen y presiona Vista previa.</small>

                    <div class="url-preview-box" id="urlPreviewBox">
                        <img id="urlPreviewImg" src="" alt="Vista previa URL">
                    </div>
                </div>

                <!-- Current image indicator -->
                <?php $currentImg = $product['image_url'] ?? ''; ?>
                <?php if (!empty($currentImg)): ?>
                <div class="mt-3 p-3 rounded-3" style="background:#f6f3ff;border:1.5px solid #d4ccff;">
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= $this->e($currentImg) ?>" alt="Imagen actual"
                             style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #d1fae5;">
                        <div>
                            <div style="font-size:.8rem;font-weight:600;color:#4b2bb0;margin-bottom:.15rem">
                                <i class="bi bi-check-circle-fill me-1"></i>Imagen actual
                            </div>
                            <code style="font-size:.75rem;color:#475569"><?= $this->e($currentImg) ?></code>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Col lateral ── -->
        <div class="col-12 col-lg-4">
            <!-- Precio y stock -->
            <div class="admin-form-card mb-4">
                <h6 class="fw-700 mb-4" style="color:#0f172a"><i class="bi bi-currency-dollar me-2 text-success"></i>Precio y Stock</h6>
                <div class="mb-3">
                    <label for="price" class="form-label">Precio (S/.) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:#f8fafc;font-weight:600">S/.</span>
                        <input type="number" id="price" name="price" class="form-control <?= !empty($errors['price']) ? 'is-invalid' : '' ?>"
                               step="0.01" min="0"
                               value="<?= $this->e($product['price'] ?? $_POST['price'] ?? '') ?>" required>
                        <?php if (!empty($errors['price'])): ?>
                            <div class="invalid-feedback"><?= $this->e($errors['price']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock disponible <span class="text-danger">*</span></label>
                    <input type="number" id="stock" name="stock" class="form-control" min="0"
                           value="<?= $this->e($product['stock'] ?? $_POST['stock'] ?? 0) ?>" required>
                </div>
            </div>

            <!-- Categoría -->
            <div class="admin-form-card mb-4">
                <h6 class="fw-700 mb-4" style="color:#0f172a"><i class="bi bi-tags me-2 text-success"></i>Organización</h6>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                    <select id="category_id" name="category_id" class="form-select <?= !empty($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Seleccionar categoría...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $this->e($cat['id']) ?>"
                                <?= (($product['category_id'] ?? $_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= $this->e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['category_id'])): ?>
                        <div class="invalid-feedback"><?= $this->e($errors['category_id']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estado -->
            <div class="admin-form-card">
                <h6 class="fw-700 mb-3" style="color:#0f172a"><i class="bi bi-toggles me-2 text-success"></i>Publicación</h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                           <?= (($product['is_active'] ?? $_POST['is_active'] ?? 1)) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Producto activo (visible en tienda)</label>
                </div>
                <div class="mt-4 d-grid gap-2">
                    <button type="submit" class="btn-admin-save py-2 rounded-3">
                        <i class="bi bi-floppy me-1"></i>
                        <?= $isEdit ? 'Guardar cambios' : 'Crear producto' ?>
                    </button>
                    <a href="/admin/productos" class="btn-admin-cancel text-center py-2 rounded-3">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- ════════════════════════════════════════
     CROPPER.JS MODAL
════════════════════════════════════════ -->
<div class="cropper-modal-overlay" id="cropperModal">
    <div class="cropper-modal-box">
        <div class="cropper-modal-header">
            <span><i class="bi bi-crop me-2" style="color:var(--admin-accent)"></i>Editar imagen</span>
            <button type="button" class="img-action-btn" onclick="closeCropper()" style="background:rgba(255,255,255,.1)">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="cropper-modal-body">
            <div class="cropper-container-wrap">
                <img id="cropperImage" src="" alt="Editor">
            </div>
        </div>
        <div class="cropper-modal-footer">
            <!-- Actions -->
            <button type="button" class="crop-btn crop-btn-secondary" onclick="cropRotate(-90)" title="Rotar izquierda">
                <i class="bi bi-arrow-counterclockwise"></i> 90°
            </button>
            <button type="button" class="crop-btn crop-btn-secondary" onclick="cropRotate(90)" title="Rotar derecha">
                <i class="bi bi-arrow-clockwise"></i> 90°
            </button>
            <button type="button" class="crop-btn crop-btn-secondary" onclick="cropFlip('x')" title="Voltear horizontal">
                <i class="bi bi-symmetry-vertical"></i>
            </button>
            <button type="button" class="crop-btn crop-btn-secondary" onclick="cropZoom(0.1)">
                <i class="bi bi-zoom-in"></i>
            </button>
            <button type="button" class="crop-btn crop-btn-secondary" onclick="cropZoom(-0.1)">
                <i class="bi bi-zoom-out"></i>
            </button>

            <!-- Aspect ratios -->
            <div class="ratio-group">
                <button type="button" class="ratio-btn active" onclick="setRatio(NaN, this)">Libre</button>
                <button type="button" class="ratio-btn" onclick="setRatio(1, this)">1:1</button>
                <button type="button" class="ratio-btn" onclick="setRatio(4/3, this)">4:3</button>
                <button type="button" class="ratio-btn" onclick="setRatio(16/9, this)">16:9</button>
            </div>

            <!-- Confirm / cancel -->
            <button type="button" class="crop-btn crop-btn-danger ms-auto" onclick="closeCropper()">
                <i class="bi bi-x-circle"></i> Cancelar
            </button>
            <button type="button" class="crop-btn crop-btn-primary" onclick="applyCrop()">
                <i class="bi bi-check2-circle"></i> Aplicar y subir
            </button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<style>
@keyframes spin { to { transform:rotate(360deg); } }
.spin { display:inline-block; animation:spin .8s linear infinite; }
</style>
<script>
// ── State ──────────────────────────────────────────────────────────
let cropper = null;
let currentBlob = null; // raw File selected by user (for reopening cropper)
let activeTab = 'file';

// ── Tabs ────────────────────────────────────────────────────────────
function switchTab(tab) {
    activeTab = tab;
    document.getElementById('tabFile').classList.toggle('active', tab === 'file');
    document.getElementById('tabUrl').classList.toggle('active', tab === 'url');
    document.getElementById('paneFile').classList.toggle('active', tab === 'file');
    document.getElementById('paneUrl').classList.toggle('active', tab === 'url');
}

// ── Drag & Drop ─────────────────────────────────────────────────────
function handleDragOver(e) {
    e.preventDefault();
    document.getElementById('dropzone').classList.add('drag-over');
}
function handleDragLeave(e) {
    document.getElementById('dropzone').classList.remove('drag-over');
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone').classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) loadFileIntoPreview(file);
}

// ── File input ──────────────────────────────────────────────────────
document.getElementById('fileInput').addEventListener('change', function () {
    if (this.files[0]) loadFileIntoPreview(this.files[0]);
});

function loadFileIntoPreview(file) {
    currentBlob = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('filePreviewImg').src = e.target.result;
        document.getElementById('filePreviewWrap').classList.add('visible');
        setStatus('', '');
    };
    reader.readAsDataURL(file);
}

function clearFileImage() {
    document.getElementById('filePreviewImg').src = '';
    document.getElementById('filePreviewWrap').classList.remove('visible');
    document.getElementById('fileInput').value = '';
    document.getElementById('image_url_hidden').value = '';
    setStatus('', '');
    currentBlob = null;
}

// ── URL preview ─────────────────────────────────────────────────────
function previewUrl() {
    const url = document.getElementById('urlInput').value.trim();
    if (!url) return;

    const img = document.getElementById('urlPreviewImg');
    const box = document.getElementById('urlPreviewBox');

    img.onerror = () => { box.classList.remove('visible'); alert('No se pudo cargar la imagen desde esa URL.'); };
    img.onload  = () => {
        box.classList.add('visible');
        document.getElementById('image_url_hidden').value = url;
    };
    img.src = url;
}

// ── Cropper ─────────────────────────────────────────────────────────
function openCropper() {
    const previewSrc = document.getElementById('filePreviewImg').src;
    if (!previewSrc) return;

    const cropImg = document.getElementById('cropperImage');
    cropImg.src = previewSrc;

    document.getElementById('cropperModal').classList.add('open');

    // Destroy previous instance
    if (cropper) { cropper.destroy(); cropper = null; }

    cropper = new Cropper(cropImg, {
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 0.9,
        responsive: true,
        restore: false,
        checkCrossOrigin: false,
        checkOrientation: false,
        movable: true,
        zoomable: true,
        rotatable: true,
        scalable: true,
    });
}

function closeCropper() {
    document.getElementById('cropperModal').classList.remove('open');
    if (cropper) { cropper.destroy(); cropper = null; }
}

function cropRotate(deg) { if (cropper) cropper.rotate(deg); }
function cropFlip(axis)   { if (!cropper) return; axis === 'x' ? cropper.scaleX(-cropper.getData().scaleX || -1) : cropper.scaleY(-cropper.getData().scaleY || -1); }
function cropZoom(ratio)  { if (cropper) cropper.zoom(ratio); }

function setRatio(ratio, btn) {
    document.querySelectorAll('.ratio-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (cropper) cropper.setAspectRatio(ratio);
}

async function applyCrop() {
    if (!cropper) return;
    setStatus('uploading', 'Procesando y subiendo imagen…');

    const canvas = cropper.getCroppedCanvas({ maxWidth: 1200, maxHeight: 1200, imageSmoothingQuality: 'high' });
    const dataUrl = canvas.toDataURL('image/jpeg', 0.88);

    closeCropper();

    // Show cropped image as preview immediately
    document.getElementById('filePreviewImg').src = dataUrl;
    document.getElementById('filePreviewWrap').classList.add('visible');

    // Upload to server
    try {
        const response = await fetch('/admin/upload-imagen', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image_data: dataUrl }),
        });
        const data = await response.json();
        if (!response.ok || data.error) throw new Error(data.error || 'Error del servidor');

        document.getElementById('image_url_hidden').value = data.url;
        setStatus('success', 'Imagen guardada: ' + data.url);
    } catch (err) {
        setStatus('error', '✗ Error al subir: ' + err.message);
    }
}

// ── Status helper ────────────────────────────────────────────────────
function setStatus(type, text) {
    const el = document.getElementById('uploadStatus');
    el.className = 'img-upload-status';
    el.querySelector('#uploadStatusText').textContent = text;
    const icon = el.querySelector('i');
    if (type === 'uploading') { el.classList.add('uploading'); icon.className = 'bi bi-arrow-repeat spin'; }
    else if (type === 'success') { el.classList.add('success'); icon.className = 'bi bi-check-circle-fill'; }
    else if (type === 'error')   { el.classList.add('error');   icon.className = 'bi bi-x-circle-fill'; }
}

// ── Form submit guard ─────────────────────────────────────────────────
// If user selected a file but didn't crop/apply, upload the raw file first
document.getElementById('productForm').addEventListener('submit', async function (e) {
    const urlHidden = document.getElementById('image_url_hidden').value;
    const previewSrc = document.getElementById('filePreviewImg').src;
    const hasNewFile = previewSrc && !previewSrc.startsWith('http') && !urlHidden.startsWith('/uploads/');

    if (hasNewFile && currentBlob) {
        e.preventDefault();
        setStatus('uploading', 'Subiendo imagen sin recortar…');

        const formData = new FormData();
        formData.append('image_file', currentBlob);

        try {
            const response = await fetch('/admin/upload-imagen', { method: 'POST', body: formData });
            const data = await response.json();
            if (!response.ok || data.error) throw new Error(data.error);

            document.getElementById('image_url_hidden').value = data.url;
            setStatus('success', 'Imagen guardada. Enviando formulario...');
            setTimeout(() => this.submit(), 400);
        } catch (err) {
            setStatus('error', '✗ Error al subir imagen: ' + err.message);
        }
    }
});

// ── URL input: set hidden field on Enter ─────────────────────────────
document.getElementById('urlInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); previewUrl(); }
});

// ── Slug auto-gen ────────────────────────────────────────────────────
document.getElementById('name').addEventListener('input', function () {
    const slugField = document.getElementById('slug');
    if (!slugField.dataset.edited) {
        slugField.value = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-');
    }
});
document.getElementById('slug').addEventListener('input', function () { this.dataset.edited = true; });

// ── Init: if current image_url exists and is a local upload, show in file preview ──
(function () {
    const existingUrl = '<?= addslashes($product['image_url'] ?? '') ?>';
    if (existingUrl && !existingUrl.startsWith('http')) {
        document.getElementById('filePreviewImg').src = existingUrl;
        document.getElementById('filePreviewWrap').classList.add('visible');
    } else if (existingUrl && existingUrl.startsWith('http')) {
        switchTab('url');
        document.getElementById('urlInput').value = existingUrl;
        document.getElementById('urlPreviewImg').src = existingUrl;
        document.getElementById('urlPreviewBox').classList.add('visible');
    }
})();
</script>
