<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'Panel de Administración | Caral Biotec') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --admin-dark: #0f172a;
            --admin-darker: #070e1b;
            --admin-sidebar: #1e293b;
            --admin-accent: #6f5add;
            --admin-accent-soft: rgba(111,90,221,0.12);
            --admin-border: rgba(255,255,255,0.07);
            --admin-text: #cbd5e1;
            --admin-text-muted: #64748b;
            --sidebar-width: 260px;
        }
        .text-success { color: var(--admin-accent) !important; }
        .bg-success { background-color: var(--admin-accent) !important; }
        .border-success { border-color: var(--admin-accent) !important; }
        .btn-success {
            background: linear-gradient(135deg, #4b2bb0, #6f5add);
            border-color: #4b2bb0 !important;
            color: #fff !important;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #28135d, #4b2bb0);
            border-color: #28135d !important;
            color: #fff !important;
        }
        .alert-success {
            background: #f6f3ff;
            color: #1f113c;
            border-color: #e7e0ff;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            margin: 0;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .admin-sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--admin-sidebar);
            z-index: 100;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            transition: transform .3s;
        }
        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid var(--admin-border);
            display: flex; align-items: center; gap: .75rem;
            text-decoration: none;
        }
        .sidebar-brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #4b2bb0, #6f5add);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.25rem; flex-shrink: 0;
        }
        .sidebar-brand-text { color: white; font-weight: 700; font-size: 1rem; line-height: 1.2; }
        .sidebar-brand-sub { color: var(--admin-text-muted); font-size: .7rem; font-weight: 400; }

        .sidebar-section { padding: .75rem 1rem .25rem; }
        .sidebar-section-label {
            color: var(--admin-text-muted);
            font-size: .65rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .sidebar-nav { list-style: none; margin: 0; padding: .25rem 0; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: .75rem;
            padding: .6rem 1.25rem;
            color: var(--admin-text);
            text-decoration: none; font-size: .875rem; font-weight: 500;
            border-radius: 8px; margin: 1px .5rem;
            transition: all .2s;
        }
        .sidebar-nav a:hover { background: var(--admin-accent-soft); color: var(--admin-accent); }
        .sidebar-nav a.active { background: var(--admin-accent-soft); color: var(--admin-accent); }
        .sidebar-nav a .nav-icon { font-size: 1rem; width: 1.1rem; flex-shrink: 0; }
        .sidebar-nav a .nav-badge {
            margin-left: auto;
            background: var(--admin-accent); color: white;
            font-size: .65rem; font-weight: 700;
            padding: .15rem .4rem; border-radius: 20px;
        }
        .sidebar-footer {
            margin-top: auto; padding: 1rem 1.25rem;
            border-top: 1px solid var(--admin-border);
        }
        .sidebar-user {
            display: flex; align-items: center; gap: .75rem;
        }
        .sidebar-user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #4b2bb0, #6f5add);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: .85rem; flex-shrink: 0;
        }
        .sidebar-user-name { color: white; font-size: .8rem; font-weight: 600; }
        .sidebar-user-role { color: var(--admin-text-muted); font-size: .7rem; }
        .sidebar-user-logout {
            margin-left: auto; color: var(--admin-text-muted);
            font-size: 1rem; text-decoration: none;
            transition: color .2s;
        }
        .sidebar-user-logout:hover { color: #ef4444; }

        /* ── Topbar ── */
        .admin-topbar {
            position: fixed; top: 0;
            left: var(--sidebar-width);
            right: 0; height: 64px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center;
            padding: 0 1.5rem; z-index: 99;
            gap: 1rem;
        }
        .topbar-title { font-weight: 600; color: #0f172a; font-size: 1.1rem; }
        .topbar-right { margin-left: auto; display: flex; align-items: center; gap: 1rem; }
        .topbar-btn {
            background: none; border: none;
            color: #64748b; font-size: 1.1rem;
            cursor: pointer; padding: .4rem;
            border-radius: 6px; transition: all .2s;
        }
        .topbar-btn:hover { background: #f1f5f9; color: #0f172a; }
        .topbar-view-btn {
            background: linear-gradient(135deg, #4b2bb0, #6f5add);
            color: white; border: none;
            padding: .45rem 1rem; border-radius: 8px;
            font-size: .8rem; font-weight: 600;
            text-decoration: none; display: flex; align-items: center; gap: .4rem;
            transition: opacity .2s;
        }
        .topbar-view-btn:hover { opacity: .85; color: white; }

        /* ── Content ── */
        .admin-content {
            margin-left: var(--sidebar-width);
            margin-top: 64px;
            padding: 1.75rem;
            min-height: calc(100vh - 64px);
        }

        /* ── Cards ── */
        .stat-card {
            background: white; border-radius: 14px;
            padding: 1.25rem 1.5rem;
            border: 1px solid #e2e8f0;
            transition: box-shadow .2s, transform .2s;
        }
        .stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-2px); }
        .stat-card-icon {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; margin-bottom: .75rem;
        }
        .stat-card-value { font-size: 1.75rem; font-weight: 700; color: #0f172a; }
        .stat-card-label { color: #64748b; font-size: .8rem; font-weight: 500; }
        .stat-card-delta { font-size: .78rem; margin-top: .25rem; }
        .delta-up { color: #6f5add; } .delta-down { color: #ef4444; }

        /* ── Tables ── */
        .admin-table-card {
            background: white; border-radius: 14px;
            border: 1px solid #e2e8f0; overflow: hidden;
        }
        .admin-table-head {
            padding: 1rem 1.5rem;
            display: flex; align-items: center; gap: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .admin-table-head-title { font-weight: 600; color: #0f172a; font-size: .95rem; }
        .admin-table thead th {
            background: #f8fafc; color: #64748b;
            font-size: .75rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .5px;
            border: none; padding: .75rem 1.25rem;
        }
        .admin-table tbody td { padding: .85rem 1.25rem; vertical-align: middle; font-size: .875rem; border-color: #f1f5f9; }
        .admin-table tbody tr:hover { background: #f8fafc; }

        /* Status badges */
        .badge-active { background: #e7e0ff; color: #4b2bb0; font-size: .75rem; font-weight: 600; }
        .badge-inactive { background: #fee2e2; color: #dc2626; font-size: .75rem; font-weight: 600; }

        /* Forms */
        .admin-form-card {
            background: white; border-radius: 14px;
            border: 1px solid #e2e8f0; padding: 1.75rem;
        }
        .form-label { font-weight: 500; font-size: .85rem; color: #374151; }
        .form-control, .form-select {
            border-radius: 8px; border-color: #e2e8f0;
            font-size: .875rem; padding: .55rem .85rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6f5add;
            box-shadow: 0 0 0 3px rgba(111,90,221,.15);
        }
        .btn-admin-save {
            background: linear-gradient(135deg, #4b2bb0, #6f5add);
            color: white; border: none; padding: .6rem 1.5rem;
            border-radius: 8px; font-weight: 600; font-size: .875rem;
            transition: opacity .2s;
        }
        .btn-admin-save:hover { opacity: .85; color: white; }
        .btn-admin-cancel {
            background: #f1f5f9; color: #374151; border: none;
            padding: .6rem 1.25rem; border-radius: 8px;
            font-weight: 600; font-size: .875rem;
            text-decoration: none; display: inline-block;
            transition: background .2s;
        }
        .btn-admin-cancel:hover { background: #e2e8f0; color: #0f172a; }
        .btn-admin-danger {
            background: #fee2e2; color: #dc2626; border: none;
            padding: .35rem .75rem; border-radius: 6px;
            font-size: .8rem; font-weight: 600; cursor: pointer;
            transition: background .2s;
        }
        .btn-admin-danger:hover { background: #fecaca; }


        /* Media picker */
        .media-picker-shell { display:grid; gap:.5rem; }
        .media-picker-control { display:flex; gap:.5rem; align-items:stretch; }
        .media-picker-control .form-control { flex:1 1 auto; min-width:0; }
        .media-picker-btn {
            border:1px solid #dbe3ec; background:#fff; color:#374151;
            border-radius:8px; padding:.55rem .75rem; font-size:.82rem; font-weight:600;
            display:inline-flex; align-items:center; gap:.35rem; white-space:nowrap;
        }
        .media-picker-btn:hover { background:#f6f3ff; border-color:#b0a8ff; color:#4b2bb0; }
        .media-picker-preview {
            display:none; width:100%; max-width:260px; aspect-ratio:16/9;
            border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; background:#f8fafc;
        }
        .media-picker-preview img { width:100%; height:100%; object-fit:cover; display:block; }
        .media-gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:.75rem; max-height:360px; overflow:auto; padding-right:.25rem; }
        .media-gallery-item { border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; background:#fff; text-align:left; padding:0; cursor:pointer; }
        .media-gallery-item:hover { border-color:#6f5add; box-shadow:0 0 0 3px rgba(111,90,221,.12); }
        .media-gallery-item img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; background:#f1f5f9; }
        .media-gallery-item span { display:block; padding:.45rem .55rem; font-size:.72rem; color:#475569; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        /* Alert flash */
        .flash-alert {
            border-radius: 10px; font-size: .875rem; font-weight: 500;
        }

        /* Mobile overlay */
        @media (max-width: 767px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-topbar { left: 0; }
            .admin-content { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>
<?php
$isPosOperator = ($this->getUserEmail() === 'operador@caralbiotec.com');
$isEditorOnly = ($this->getUserRole() === 'Editor');
?>

<!-- ── Sidebar ── -->
<aside class="admin-sidebar" id="adminSidebar">
    <a href="<?= $isPosOperator ? '/admin/pos' : ($isEditorOnly ? '/admin/blog' : '/admin') ?>" class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-flower1"></i></div>
        <div>
            <div class="sidebar-brand-text">Caral Biotec</div>
            <div class="sidebar-brand-sub"><?= $isPosOperator ? 'Punto de Venta' : ($isEditorOnly ? 'Editor de Blog' : 'Panel de Administración') ?></div>
        </div>
    </a>

    <?php if ($isPosOperator): ?>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Operación</div>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="/admin/pos" class="<?= (($_SERVER['REQUEST_URI'] ?? '') === '/admin/pos') ? 'active' : '' ?>">
                <i class="bi bi-calculator nav-icon"></i> Punto de Venta
            </a>
        </li>
        <li>
            <a href="/admin/pos/ventas" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/pos/ventas') ? 'active' : '' ?>">
                <i class="bi bi-journal-text nav-icon"></i> Ventas realizadas
            </a>
        </li>
        <li>
            <a href="/" target="_blank">
                <i class="bi bi-shop nav-icon"></i> Ver tienda
            </a>
        </li>
    </ul>
    <?php elseif ($isEditorOnly): ?>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Contenido</div>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="/admin/blog" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/blog') ? 'active' : '' ?>">
                <i class="bi bi-newspaper nav-icon"></i> Blog
            </a>
        </li>
        <li>
            <a href="/blog" target="_blank">
                <i class="bi bi-eye nav-icon"></i> Ver blog
            </a>
        </li>
        <li>
            <a href="/" target="_blank">
                <i class="bi bi-shop nav-icon"></i> Ver tienda
            </a>
        </li>
    </ul>
    <?php else: ?>
    <div class="sidebar-section">
        <div class="sidebar-section-label">Principal</div>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="/admin" class="<?= (($_SERVER['REQUEST_URI'] ?? '') === '/admin') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 nav-icon"></i> Dashboard
            </a>
        </li>
    </ul>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Catálogo</div>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="/admin/productos" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/producto') ? 'active' : '' ?>">
                <i class="bi bi-box-seam nav-icon"></i> Productos
            </a>
        </li>
        <li>
            <a href="/admin/categorias" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/categor') ? 'active' : '' ?>">
                <i class="bi bi-tags nav-icon"></i> Categorías
            </a>
        </li>
        <li>
            <a href="/admin/ordenes" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/ordenes') ? 'active' : '' ?>">
                <i class="bi bi-receipt nav-icon"></i> Órdenes
            </a>
        </li>
    </ul>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Contenido</div>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="/admin/cms" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/cms') ? 'active' : '' ?>">
                <i class="bi bi-layout-text-window-reverse nav-icon"></i> Páginas (CMS)
            </a>
        </li>
        <li>
            <a href="/admin/blog" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/blog') ? 'active' : '' ?>">
                <i class="bi bi-newspaper nav-icon"></i> Blog
            </a>
        </li>
    </ul>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Sistema</div>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="/admin/configuracion/empresa" class="<?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/configuracion') ? 'active' : '' ?>">
                <i class="bi bi-building-gear nav-icon"></i> Empresa
            </a>
        </li>
        <li>
            <a href="/" target="_blank">
                <i class="bi bi-shop nav-icon"></i> Ver tienda
            </a>
        </li>
    </ul>
    <?php endif; ?>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?= strtoupper(substr($this->getUserEmail() ?? 'A', 0, 1)) ?></div>
            <div>
                <div class="sidebar-user-name"><?= $this->e($this->getUserEmail() ?? 'Admin') ?></div>
                <div class="sidebar-user-role"><?= $this->e($this->getUserRole() ?? 'Administrador') ?></div>
            </div>
            <a href="/logout" class="sidebar-user-logout" title="Cerrar sesión"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</aside>

<!-- ── Topbar ── -->
<div class="admin-topbar">
    <button class="topbar-btn d-md-none" onclick="document.getElementById('adminSidebar').classList.toggle('open')">
        <i class="bi bi-list fs-5"></i>
    </button>
    <span class="topbar-title"><?= $this->e($pageTitle ?? 'Panel') ?></span>
    <div class="topbar-right">
        <a href="/" target="_blank" class="topbar-view-btn"><i class="bi bi-eye"></i> Ver tienda</a>
    </div>
</div>

<!-- ── Main Content ── -->
<main class="admin-content">
    <?php if (($flash = \Caral\Core\Session::get('flash_success'))): \Caral\Core\Session::remove('flash_success'); ?>
        <div class="alert alert-success flash-alert d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-check-circle-fill"></i> <?= $this->e($flash) ?>
        </div>
    <?php endif; ?>
    <?php if (($flashErr = \Caral\Core\Session::get('flash_error'))): \Caral\Core\Session::remove('flash_error'); ?>
        <div class="alert alert-danger flash-alert d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-circle-fill"></i> <?= $this->e($flashErr) ?>
        </div>
    <?php endif; ?>

    <?= $this->section('content') ?>
</main>


<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-images me-2 text-success"></i>Seleccionar imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-3 p-3 h-100">
                            <h6 class="fw-bold mb-3">Usar URL externa</h6>
                            <input type="url" class="form-control mb-2" id="mediaPickerUrl" placeholder="https://... o /uploads/imagen.jpg">
                            <button type="button" class="btn-admin-save w-100" id="mediaPickerUseUrl"><i class="bi bi-check2 me-1"></i> Usar esta URL</button>
                            <hr>
                            <h6 class="fw-bold mb-3">Subir desde mi PC</h6>
                            <input type="file" class="form-control" id="mediaPickerFile" accept="image/jpeg,image/png,image/webp">
                            <div class="small text-muted mt-2">Formatos: JPG, PNG o WebP. Maximo 5 MB.</div>
                            <div class="alert alert-danger py-2 px-3 mt-3 d-none" id="mediaPickerError"></div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-8">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                            <h6 class="fw-bold mb-0">Galeria del servidor</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="mediaPickerReload"><i class="bi bi-arrow-clockwise me-1"></i> Actualizar</button>
                        </div>
                        <div id="mediaPickerGallery" class="media-gallery-grid">
                            <div class="text-muted small">Cargando imagenes...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function () {
    const modalEl = document.getElementById('mediaPickerModal');
    if (!modalEl || !window.bootstrap) return;

    const modal = new bootstrap.Modal(modalEl);
    const urlInput = document.getElementById('mediaPickerUrl');
    const fileInput = document.getElementById('mediaPickerFile');
    const useUrlBtn = document.getElementById('mediaPickerUseUrl');
    const reloadBtn = document.getElementById('mediaPickerReload');
    const galleryEl = document.getElementById('mediaPickerGallery');
    const errorEl = document.getElementById('mediaPickerError');
    let activeInput = null;
    let activeCallback = null;
    let galleryLoaded = false;

    function showError(message) {
        errorEl.textContent = message || '';
        errorEl.classList.toggle('d-none', !message);
    }

    function isLikelyImage(url) {
        return /\.(jpe?g|png|webp|gif|avif|svg)(\?.*)?$/i.test(url) || url.startsWith('/uploads/') || url.startsWith('/images/');
    }

    function refreshPreview(input) {
        const shell = input.closest('.media-picker-shell');
        if (!shell) return;
        const clearBtn = shell.querySelector('.media-picker-clear');
        if (clearBtn) clearBtn.disabled = input.value.trim() === '';
        const preview = shell.querySelector('.media-picker-preview');
        if (!preview) return;
        const url = input.value.trim();
        if (!url || !isLikelyImage(url)) {
            preview.style.display = 'none';
            preview.innerHTML = '';
            return;
        }
        preview.innerHTML = '<img src="' + url.replace(/"/g, '&quot;') + '" alt="Vista previa">';
        preview.style.display = 'block';
    }

    function selectUrl(url) {
        if (!url) return;
        if (activeInput) {
            activeInput.value = url;
            activeInput.dispatchEvent(new Event('input', { bubbles:true }));
            activeInput.dispatchEvent(new Event('change', { bubbles:true }));
            refreshPreview(activeInput);
        }
        if (typeof activeCallback === 'function') activeCallback(url);
        modal.hide();
    }

    async function loadGallery(force) {
        if (galleryLoaded && !force) return;
        galleryEl.innerHTML = '<div class="text-muted small">Cargando imagenes...</div>';
        try {
            const response = await fetch('/admin/galeria-imagenes', { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            const items = Array.isArray(data.items) ? data.items : [];
            galleryLoaded = true;
            if (!items.length) {
                galleryEl.innerHTML = '<div class="text-muted small">Aun no hay imagenes en la galer?a.</div>';
                return;
            }
            galleryEl.innerHTML = items.map(item => `
                <button type="button" class="media-gallery-item" data-url="${String(item.url).replace(/"/g, '&quot;')}">
                    <img src="${String(item.url).replace(/"/g, '&quot;')}" alt="">
                    <span title="${String(item.name || item.url).replace(/"/g, '&quot;')}">${item.name || item.url}</span>
                </button>
            `).join('');
        } catch (error) {
            galleryEl.innerHTML = '<div class="text-danger small">No se pudo cargar la galer?a.</div>';
        }
    }

    function openPicker(options) {
        activeInput = options && options.input ? options.input : null;
        activeCallback = options && options.onSelect ? options.onSelect : null;
        urlInput.value = activeInput ? activeInput.value : '';
        fileInput.value = '';
        showError('');
        loadGallery(false);
        modal.show();
        setTimeout(() => urlInput.focus(), 180);
    }

    function enhanceInput(input) {
        if (!input || input.dataset.mediaEnhanced === '1') return;
        input.dataset.mediaEnhanced = '1';
        const shell = document.createElement('div');
        shell.className = 'media-picker-shell';
        const control = document.createElement('div');
        control.className = 'media-picker-control';
        input.parentNode.insertBefore(shell, input);
        shell.appendChild(control);
        control.appendChild(input);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'media-picker-btn';
        btn.innerHTML = '<i class="bi bi-folder2-open"></i><span>Seleccionar</span>';
        btn.addEventListener('click', () => openPicker({ input }));
        control.appendChild(btn);

        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'media-picker-btn media-picker-clear';
        clearBtn.title = 'Eliminar imagen seleccionada';
        clearBtn.setAttribute('aria-label', 'Eliminar imagen seleccionada');
        clearBtn.innerHTML = '<i class="bi bi-trash"></i><span>Eliminar</span>';
        clearBtn.addEventListener('click', () => {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles:true }));
        });
        control.appendChild(clearBtn);

        const preview = document.createElement('div');
        preview.className = 'media-picker-preview';
        shell.appendChild(preview);
        input.addEventListener('input', () => refreshPreview(input));
        input.addEventListener('change', () => refreshPreview(input));
        refreshPreview(input);
    }

    useUrlBtn.addEventListener('click', () => selectUrl(urlInput.value.trim()));
    urlInput.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            selectUrl(urlInput.value.trim());
        }
    });
    reloadBtn.addEventListener('click', () => loadGallery(true));
    galleryEl.addEventListener('click', event => {
        const item = event.target.closest('[data-url]');
        if (item) selectUrl(item.dataset.url);
    });
    fileInput.addEventListener('change', async () => {
        if (!fileInput.files || !fileInput.files[0]) return;
        showError('');
        const form = new FormData();
        form.append('image_file', fileInput.files[0]);
        try {
            const response = await fetch('/admin/upload-imagen', { method:'POST', body:form, headers:{ 'Accept':'application/json' } });
            const data = await response.json();
            if (!response.ok || !data.url) throw new Error(data.error || 'No se pudo subir la imagen.');
            galleryLoaded = false;
            selectUrl(data.url);
        } catch (error) {
            showError(error.message || 'No se pudo subir la imagen.');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.js-media-picker').forEach(enhanceInput);
    });

    window.CaralMediaPicker = { open: openPicker, enhance: enhanceInput, refreshPreview: refreshPreview };
})();
</script>

</body>
</html>
