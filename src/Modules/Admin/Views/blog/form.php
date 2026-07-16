<?php $this->layout('admin::layout', ['title' => 'Editor de blog | Admin', 'pageTitle' => $mode === 'create' ? 'Nueva entrada' : 'Editar entrada']); ?>

<?php
$publishedValue = '';
if (!empty($post['published_at'])) {
    $publishedValue = date('Y-m-d\TH:i', strtotime($post['published_at']));
}
?>

<style>
    .editor-layout { display:grid; grid-template-columns:minmax(0, 1fr) 340px; gap:1.25rem; align-items:start; }
    .editor-toolbar { display:flex; flex-wrap:wrap; gap:.35rem; padding:.65rem; border:1px solid #e2e8f0; border-bottom:0; border-radius:8px 8px 0 0; background:#f8fafc; }
    .editor-toolbar button, .editor-toolbar select { border:1px solid #dbe3ec; background:#fff; border-radius:6px; min-height:34px; padding:.25rem .55rem; font-size:.82rem; color:#0f172a; }
    .editor-toolbar button:hover { background:#ecfdf5; border-color:#b0a8ff; color:#37207a; }
    .rich-editor { min-height:520px; border:1px solid #e2e8f0; border-radius:0 0 8px 8px; padding:1.25rem; background:#fff; outline:none; line-height:1.75; color:#1f2937; }
    .rich-editor:focus { border-color:#6f5add; box-shadow:0 0 0 3px rgba(111,90,221,.12); }
    .rich-editor h2, .rich-editor h3 { font-weight:800; color:#0f172a; }
    .rich-editor blockquote { border-left:4px solid #6f5add; background:#f6f3ff; padding:1rem; color:#1f113c; }
    .rich-editor figure { margin:1rem 0; text-align:center; }
    .rich-editor figure img { max-width:100%; height:auto; }
    .rich-editor img.is-selected { outline:3px solid #6f5add; outline-offset:3px; }
    .image-context-toolbar { display:flex; align-items:center; flex-wrap:wrap; gap:.5rem; padding:.55rem .7rem; border:1px solid #c4b5fd; border-bottom:0; background:#f6f3ff; color:#37207a; font-size:.8rem; }
    .image-context-toolbar select { width:auto; min-width:150px; }
    .rich-editor .blog-video-embed { position:relative; width:100%; aspect-ratio:16/9; margin:1rem 0; border-radius:8px; overflow:hidden; background:#0f172a; }
    .rich-editor .blog-video-embed iframe, .rich-editor .blog-video-embed video { position:absolute; inset:0; width:100%; height:100%; border:0; object-fit:contain; }
    .editor-side { position:sticky; top:84px; }
    .mini-help { font-size:.78rem; color:#64748b; }
    .seo-count { font-size:.72rem; color:#64748b; text-align:right; }
    @media (max-width: 991px) { .editor-layout { grid-template-columns:1fr; } .editor-side { position:static; } }
</style>

<form action="<?= $this->e($action) ?>" method="POST" id="blogForm">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1"><?= $mode === 'create' ? 'Nueva entrada' : 'Editar entrada' ?></h1>
            <p class="text-muted mb-0">Diseña contenido claro, escaneable y optimizado para SEO.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/blog" class="btn-admin-cancel">Cancelar</a>
            <button type="submit" class="btn-admin-save"><i class="bi bi-save me-1"></i> Guardar</button>
        </div>
    </div>

    <div class="editor-layout">
        <div class="admin-form-card">
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" id="titleInput" class="form-control" value="<?= $this->e($post['title']) ?>" required maxlength="220">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Slug SEO</label>
                    <input type="text" name="slug" id="slugInput" class="form-control" value="<?= $this->e($post['slug']) ?>" maxlength="220" placeholder="se-genera-automatico">
                </div>
                <div class="col-12">
                    <label class="form-label">Extracto</label>
                    <textarea name="excerpt" id="excerptInput" class="form-control" rows="3" maxlength="320" placeholder="Resumen breve que se mostrará en el listado y buscadores."><?= $this->e($post['excerpt']) ?></textarea>
                    <div class="seo-count"><span id="excerptCount">0</span>/320</div>
                </div>
            </div>

            <label class="form-label">Contenido</label>
            <div class="editor-toolbar" aria-label="Herramientas de edición">
                <select id="formatBlock" title="Formato">
                    <option value="p">Párrafo</option>
                    <option value="h2">Título H2</option>
                    <option value="h3">Título H3</option>
                    <option value="h4">Título H4</option>
                </select>
                <button type="button" data-cmd="bold" title="Negrita"><i class="bi bi-type-bold"></i></button>
                <button type="button" data-cmd="italic" title="Cursiva"><i class="bi bi-type-italic"></i></button>
                <button type="button" data-cmd="underline" title="Subrayado"><i class="bi bi-type-underline"></i></button>
                <button type="button" data-cmd="insertUnorderedList" title="Lista"><i class="bi bi-list-ul"></i></button>
                <button type="button" data-cmd="insertOrderedList" title="Lista numerada"><i class="bi bi-list-ol"></i></button>
                <button type="button" id="quoteBtn" title="Cita"><i class="bi bi-quote"></i></button>
                <button type="button" id="linkBtn" title="Enlace"><i class="bi bi-link-45deg"></i></button>
                <button type="button" id="imageBtn" title="Imagen"><i class="bi bi-image"></i></button>
                <button type="button" id="videoLinkBtn" title="Insertar video desde enlace"><i class="bi bi-play-btn"></i></button>
                <button type="button" id="videoUploadBtn" title="Subir video"><i class="bi bi-camera-video"></i></button>
                <button type="button" id="tableBtn" title="Tabla"><i class="bi bi-table"></i></button>
                <button type="button" id="hrBtn" title="Separador"><i class="bi bi-dash-lg"></i></button>
                <button type="button" id="tipBtn" title="Bloque destacado"><i class="bi bi-lightbulb"></i></button>
            </div>
            <div id="imageContextToolbar" class="image-context-toolbar d-none" aria-live="polite">
                <strong><i class="bi bi-image me-1"></i>Imagen seleccionada</strong>
                <label for="imageSizeSelect" class="ms-auto">Tamaño</label>
                <select id="imageSizeSelect" class="form-select form-select-sm">
                    <option value="auto">Automático</option>
                    <option value="25">25%</option>
                    <option value="50">50%</option>
                    <option value="75">75%</option>
                    <option value="100">100%</option>
                </select>
                <button type="button" id="removeContentImageBtn" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Eliminar imagen
                </button>
            </div>
            <input type="file" id="videoFileInput" class="d-none" accept="video/mp4,video/webm,video/ogg">
            <div id="editor" class="rich-editor" contenteditable="true"><?= $post['content_html'] ?: '<h2>Subtitulo de la entrada</h2><p>Escribe aqui el contenido principal. Usa subtitulos, listas, citas e imagenes para crear una lectura clara.</p>' ?></div>
            <div class="mini-help mt-2">Haz clic en una imagen para cambiar su tamaño o eliminarla. También puedes subir videos MP4/WebM/OGG (máximo 100 MB) o pegar en una línea independiente un enlace de YouTube, Vimeo, video o imagen.</div>
            <div id="videoUploadStatus" class="mini-help mt-1" role="status" aria-live="polite"></div>
            <textarea name="content_html" id="contentHtml" class="d-none"></textarea>
        </div>

        <aside class="editor-side">
            <div class="admin-form-card mb-3">
                <h2 class="h6 fw-bold mb-3">Publicación</h2>
                <label class="form-label">Estado</label>
                <select name="status" class="form-select mb-3">
                    <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Borrador</option>
                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Publicado</option>
                </select>
                <label class="form-label">Fecha de publicación</label>
                <input type="datetime-local" name="published_at" class="form-control" value="<?= $this->e($publishedValue) ?>">
                <div class="mini-help mt-2">Si publicas sin fecha, se usará la fecha actual.</div>
            </div>

            <div class="admin-form-card mb-3">
                <h2 class="h6 fw-bold mb-3">Imagen destacada</h2>
                <input type="text" id="featuredImageInput" name="featured_image_url" class="form-control mb-2 js-media-picker" value="<?= $this->e($post['featured_image_url']) ?>" placeholder="/uploads/imagen.jpg o www.ejemplo.com/imagen.jpg">
                <div class="mini-help">Usa una imagen horizontal y liviana. Recomendado: 1200 x 675 px.</div>
            </div>

            <div class="admin-form-card mb-3">
                <h2 class="h6 fw-bold mb-3">SEO</h2>
                <label class="form-label">Meta título</label>
                <input type="text" name="meta_title" id="metaTitleInput" class="form-control" maxlength="220" value="<?= $this->e($post['meta_title']) ?>">
                <div class="seo-count"><span id="metaTitleCount">0</span>/220</div>
                <label class="form-label mt-2">Meta descripción</label>
                <textarea name="meta_description" id="metaDescriptionInput" class="form-control" rows="3" maxlength="320"><?= $this->e($post['meta_description']) ?></textarea>
                <div class="seo-count"><span id="metaDescriptionCount">0</span>/320</div>
                <label class="form-label mt-2">Etiquetas</label>
                <input type="text" name="tags" class="form-control" value="<?= $this->e($post['tags']) ?>" placeholder="nutrición, bienestar, rehabilitación">
            </div>
        </aside>
    </div>
</form>

<script>
const editor = document.getElementById('editor');
const form = document.getElementById('blogForm');
const contentHtml = document.getElementById('contentHtml');
const titleInput = document.getElementById('titleInput');
const slugInput = document.getElementById('slugInput');
const videoFileInput = document.getElementById('videoFileInput');
const videoUploadStatus = document.getElementById('videoUploadStatus');
const imageContextToolbar = document.getElementById('imageContextToolbar');
const imageSizeSelect = document.getElementById('imageSizeSelect');
const featuredImageInput = document.getElementById('featuredImageInput');
let savedEditorRange = null;
let selectedContentImage = null;

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[character]);
}

function rememberEditorSelection() {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) return;
    const range = selection.getRangeAt(0);
    const node = range.commonAncestorContainer;
    if (node === editor || editor.contains(node.nodeType === Node.TEXT_NODE ? node.parentNode : node)) {
        savedEditorRange = range.cloneRange();
    }
}

function insertEditorHtml(html) {
    editor.focus();
    const selection = window.getSelection();
    let range = savedEditorRange;
    if (!range || !editor.contains(range.commonAncestorContainer)) {
        range = document.createRange();
        range.selectNodeContents(editor);
        range.collapse(false);
    }

    range.deleteContents();
    const fragment = range.createContextualFragment(html);
    const lastNode = fragment.lastChild;
    range.insertNode(fragment);
    if (lastNode) {
        range.setStartAfter(lastNode);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
        savedEditorRange = range.cloneRange();
    }
}

function selectContentImage(image) {
    if (selectedContentImage) selectedContentImage.classList.remove('is-selected');
    selectedContentImage = image || null;
    imageContextToolbar.classList.toggle('d-none', !selectedContentImage);
    if (!selectedContentImage) return;

    selectedContentImage.classList.add('is-selected');
    const width = selectedContentImage.style.width.match(/^(25|50|75|100)%$/)?.[1] || 'auto';
    imageSizeSelect.value = width;
}

function normalizeMediaUrl(value) {
    const url = String(value || '').trim();
    if (!url || /^(?:https?:\/\/|\/)/i.test(url)) return url;
    if (url.startsWith('//')) return 'https:' + url;
    if (/^[a-z][a-z0-9+.-]*:/i.test(url)) return '';
    if (/^(?:www\.)?[a-z0-9-]+(?:\.[a-z0-9-]+)+(?:[\/:?#].*)?$/i.test(url)) return 'https://' + url;
    if (/^(?:uploads|images)\//i.test(url)) return '/' + url;
    return url;
}

function youtubeId(url) {
    try {
        const parsed = new URL(url);
        const host = parsed.hostname.replace(/^www\./, '').toLowerCase();
        let id = '';
        if (host === 'youtu.be') id = parsed.pathname.split('/').filter(Boolean)[0] || '';
        if (host === 'youtube.com' || host === 'm.youtube.com') {
            id = parsed.searchParams.get('v') || '';
            if (!id) {
                const parts = parsed.pathname.split('/').filter(Boolean);
                if (['embed', 'shorts'].includes(parts[0])) id = parts[1] || '';
            }
        }
        return /^[a-zA-Z0-9_-]{11}$/.test(id) ? id : null;
    } catch (error) {
        return null;
    }
}

function videoEmbedHtml(url) {
    const safeUrl = escapeHtml(url);
    const ytId = youtubeId(url);
    if (ytId) return '<div class="blog-video-embed"><iframe src="https://www.youtube-nocookie.com/embed/' + ytId + '" title="Video de YouTube" loading="lazy" allowfullscreen></iframe></div><p><br></p>';
    const vimeoMatch = String(url).match(/^https?:\/\/(?:www\.)?(?:player\.)?vimeo\.com\/(?:video\/)?([0-9]+)/i);
    if (vimeoMatch) return '<div class="blog-video-embed"><iframe src="https://player.vimeo.com/video/' + vimeoMatch[1] + '" title="Video de Vimeo" loading="lazy" allowfullscreen></iframe></div><p><br></p>';
    if (/\.(mp4|webm|ogv|ogg)(?:[?#].*)?$/i.test(url)) return '<div class="blog-video-embed blog-video-file"><video controls preload="metadata" playsinline src="' + safeUrl + '"></video></div><p><br></p>';
    return null;
}

function slugify(value) {
    return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
        .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 200);
}

titleInput.addEventListener('input', () => {
    if (!slugInput.dataset.touched) slugInput.value = slugify(titleInput.value);
});
slugInput.addEventListener('input', () => slugInput.dataset.touched = '1');

document.querySelectorAll('[data-cmd]').forEach(btn => {
    btn.addEventListener('click', () => {
        editor.focus();
        document.execCommand(btn.dataset.cmd, false, null);
    });
});

document.getElementById('formatBlock').addEventListener('change', event => {
    editor.focus();
    document.execCommand('formatBlock', false, event.target.value);
});

document.getElementById('quoteBtn').addEventListener('click', () => {
    editor.focus();
    document.execCommand('formatBlock', false, 'blockquote');
});

document.getElementById('linkBtn').addEventListener('click', () => {
    const url = prompt('URL del enlace');
    if (url) document.execCommand('createLink', false, url);
});

document.getElementById('imageBtn').addEventListener('click', () => {
    rememberEditorSelection();
    if (window.CaralMediaPicker) {
        window.CaralMediaPicker.open({
            onSelect: url => insertEditorHtml('<figure><img src="' + escapeHtml(url) + '" alt="" loading="lazy"></figure><p><br></p>')
        });
        return;
    }
    const url = prompt('URL de la imagen');
    if (url) insertEditorHtml('<figure><img src="' + escapeHtml(url) + '" alt="" loading="lazy"></figure><p><br></p>');
});

editor.addEventListener('click', event => {
    const image = event.target.closest('img');
    selectContentImage(image && editor.contains(image) ? image : null);
});

imageSizeSelect.addEventListener('change', () => {
    if (!selectedContentImage) return;
    if (imageSizeSelect.value === 'auto') {
        selectedContentImage.style.removeProperty('width');
        selectedContentImage.style.removeProperty('margin-left');
        selectedContentImage.style.removeProperty('margin-right');
        return;
    }
    selectedContentImage.style.width = imageSizeSelect.value + '%';
    selectedContentImage.style.height = 'auto';
    selectedContentImage.style.marginLeft = 'auto';
    selectedContentImage.style.marginRight = 'auto';
});

document.getElementById('removeContentImageBtn').addEventListener('click', () => {
    if (!selectedContentImage) return;
    const removable = selectedContentImage.closest('figure') || selectedContentImage;
    selectContentImage(null);
    removable.remove();
    editor.focus();
});

featuredImageInput.addEventListener('blur', () => {
    featuredImageInput.value = normalizeMediaUrl(featuredImageInput.value);
    if (window.CaralMediaPicker) window.CaralMediaPicker.refreshPreview(featuredImageInput);
});

document.getElementById('videoLinkBtn').addEventListener('click', () => {
    rememberEditorSelection();
    const url = prompt('URL del video (YouTube, Vimeo, MP4, WebM u OGG)');
    if (!url) return;
    const embed = videoEmbedHtml(url.trim());
    if (!embed) {
        alert('Usa un enlace de YouTube, Vimeo o un archivo MP4, WebM u OGG.');
        return;
    }
    insertEditorHtml(embed);
});

document.getElementById('videoUploadBtn').addEventListener('click', () => {
    rememberEditorSelection();
    videoFileInput.click();
});

videoFileInput.addEventListener('change', async () => {
    const file = videoFileInput.files && videoFileInput.files[0];
    if (!file) return;

    videoUploadStatus.textContent = 'Subiendo video...';
    const payload = new FormData();
    payload.append('video_file', file);

    try {
        const response = await fetch('/admin/upload-video', {
            method: 'POST',
            body: payload,
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        if (!response.ok || !data.url) throw new Error(data.error || 'No se pudo subir el video.');
        insertEditorHtml(videoEmbedHtml(data.url));
        videoUploadStatus.textContent = 'Video insertado correctamente.';
    } catch (error) {
        videoUploadStatus.textContent = error.message || 'No se pudo subir el video.';
    } finally {
        videoFileInput.value = '';
    }
});

document.getElementById('tableBtn').addEventListener('click', () => {
    editor.focus();
    document.execCommand('insertHTML', false, '<table><tbody><tr><th>Concepto</th><th>Detalle</th></tr><tr><td>Dato</td><td>Descripcion</td></tr></tbody></table>');
});

document.getElementById('hrBtn').addEventListener('click', () => {
    editor.focus();
    document.execCommand('insertHTML', false, '<hr>');
});

document.getElementById('tipBtn').addEventListener('click', () => {
    editor.focus();
    document.execCommand('insertHTML', false, '<blockquote><strong>Consejo:</strong> escribe aqui una recomendacion importante para el lector.</blockquote>');
});

editor.addEventListener('keyup', rememberEditorSelection);
editor.addEventListener('mouseup', rememberEditorSelection);
editor.addEventListener('focus', rememberEditorSelection);
editor.addEventListener('paste', event => {
    const text = event.clipboardData?.getData('text/plain')?.trim() || '';
    const embed = text ? videoEmbedHtml(text) : null;
    if (!embed) return;
    event.preventDefault();
    rememberEditorSelection();
    insertEditorHtml(embed);
});
document.addEventListener('selectionchange', () => {
    const selection = window.getSelection();
    const anchor = selection?.anchorNode;
    if (anchor && (anchor === editor || editor.contains(anchor))) rememberEditorSelection();
});

function updateCount(id, targetId) {
    const el = document.getElementById(id);
    const target = document.getElementById(targetId);
    const sync = () => target.textContent = el.value.length;
    el.addEventListener('input', sync);
    sync();
}
updateCount('excerptInput', 'excerptCount');
updateCount('metaTitleInput', 'metaTitleCount');
updateCount('metaDescriptionInput', 'metaDescriptionCount');

form.addEventListener('submit', () => {
    featuredImageInput.value = normalizeMediaUrl(featuredImageInput.value);
    selectContentImage(null);
    contentHtml.value = editor.innerHTML;
});
</script>
