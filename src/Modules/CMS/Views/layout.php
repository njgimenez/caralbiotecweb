<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'Caral Biotec — Equilibrio Vital') ?></title>
    <meta name="description" content="<?= $this->e($metaDescription ?? 'Caral Biotec: productos nutracéuticos, bienestar y rehabilitación de alta calidad para mejorar tu salud y calidad de vida.') ?>">
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <link rel="apple-touch-icon" href="/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>

<!-- ── 1. Announcement Bar ── -->
<div class="announcement-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex gap-4 align-items-center">
            <span><i class="bi bi-truck me-1"></i>Envíos a todo el Perú</span>
            <span class="d-none d-md-inline"><i class="bi bi-patch-check me-1"></i>Calidad y respaldo garantizado</span>
            <span><i class="bi bi-headset me-1"></i>Atención personalizada</span>
        </div>
        <div class="d-none d-md-flex align-items-center gap-3">
            <span>Síguenos:</span>
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="Instagram" class="ms-1"><i class="bi bi-instagram"></i></a>
            <?php if ($this->isLoggedIn() && $this->getUserEmail() === 'operador@caralbiotec.com'): ?>
            <span style="width:1px;height:14px;background:rgba(255,255,255,.3);display:inline-block;"></span>
            <a href="/admin/pos" class="d-inline-flex align-items-center gap-1 text-decoration-none"
               style="color:var(--white);font-weight:700;font-size:.78rem;letter-spacing:.01em;
                      padding:.2rem .65rem;background:rgba(255,255,255,.15);border-radius:20px;
                      border:1px solid rgba(255,255,255,.3);transition:background .2s;"
               onmouseover="this.style.background='rgba(255,255,255,.25)'" onmouseout="this.style.background='rgba(255,255,255,.15)'">
                <i class="bi bi-calculator-fill" style="font-size:.75rem;"></i>
                Ir a POS
            </a>
            <?php elseif ($this->isLoggedIn() && in_array($this->getUserRole(), ['Super Administrador', 'Marketing', 'Operaciones', 'Editor'])): ?>
            <span style="width:1px;height:14px;background:rgba(255,255,255,.3);display:inline-block;"></span>
            <a href="<?= $this->getUserRole() === 'Editor' ? '/admin/blog' : '/admin' ?>" class="d-inline-flex align-items-center gap-1 text-decoration-none"
               style="color:var(--white);font-weight:700;font-size:.78rem;letter-spacing:.01em;
                      padding:.2rem .65rem;background:rgba(255,255,255,.15);border-radius:20px;
                      border:1px solid rgba(255,255,255,.3);transition:background .2s;"
               onmouseover="this.style.background='rgba(255,255,255,.25)'" onmouseout="this.style.background='rgba(255,255,255,.15)'">
                <i class="bi bi-gear-fill" style="font-size:.75rem;"></i>
                <?= $this->getUserRole() === 'Editor' ? 'Editar blog' : 'Administrar tienda' ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── 2. Header Principal ── -->
<header class="header-main">
    <div class="container">
        <div class="row align-items-center g-2">

            <!-- Logo -->
            <div class="col-6 col-md-4 col-lg-3 d-flex align-items-center gap-2">
                <button type="button" class="mobile-menu-toggle d-md-none" id="mobileMenuToggle" aria-label="Abrir menu" aria-controls="mobileNavMenu" aria-expanded="false">
                    <i class="bi bi-list"></i>
                </button>
                <a href="/" class="text-decoration-none d-block">
                    <img src="/images/logo.png" alt="Caral Biotec" class="site-logo">
                </a>
            </div>

            <!-- Buscador -->
            <div class="col-12 col-md-4 col-lg-4 order-3 order-md-2">
                <form action="/productos" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control search-input"
                           placeholder="Buscar productos..." aria-label="Buscar productos"
                           value="<?= $this->e($_GET['search'] ?? '') ?>">
                    <button type="submit" class="search-btn" aria-label="Buscar">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>

            <!-- Acciones del header -->
            <div class="col-6 col-md-4 col-lg-5 order-2 order-md-3 d-flex justify-content-end align-items-center gap-2 gap-lg-3">

                <!-- WhatsApp -->
                <a href="https://wa.me/51947123456" target="_blank"
                   class="d-none d-xl-flex align-items-center text-decoration-none gap-1">
                    <i class="bi bi-whatsapp header-action-icon" style="color:var(--green-700)"></i>
                    <div>
                        <span class="d-block fw-bold" style="font-size:.75rem;color:var(--green-700)">WhatsApp</span>
                        <span class="text-muted" style="font-size:.7rem">+51 947 123 456</span>
                    </div>
                </a>



                <!-- Mi Cuenta -->
                <div class="header-action-item">
                    <div class="header-action-box">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="header-action-text">
                        <?php if ($this->isLoggedIn()): ?>
                            <span class="header-action-title">
                                <?= $this->e(explode('@', $this->getUserEmail())[0]) ?>
                            </span>
                            <a href="/logout" class="header-action-subtitle text-decoration-none">Salir</a>
                        <?php else: ?>
                            <span class="header-action-title">Mi cuenta</span>
                            <a href="/login" class="header-action-subtitle text-decoration-none">Iniciar sesión</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Carrito -->
                <a href="/carrito" class="header-action-item header-action-link">
                    <div class="header-action-box">
                        <i class="bi bi-cart3"></i>
                        <span class="cart-badge"><?= $this->e($this->getCartCount()) ?></span>
                    </div>
                    <div class="header-action-text d-none d-md-block">
                        <span class="header-action-title">Carrito</span>
                        <span class="header-action-subtitle">Ver detalle</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- ── 3. Navegación ── -->
<nav class="nav-menu d-none d-md-block" id="mobileNavMenu">
    <div class="container">
        <ul class="navbar-nav flex-row gap-0 justify-content-between">
            <li class="nav-item"><a class="nav-link nav-link-custom" href="/">Inicio</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="/productos?categoria=nutraceuticos">Nutracéuticos</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="/productos?categoria=bienestar">Bienestar</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="/productos?categoria=rehabilitacion">Rehabilitación</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="/productos?categoria=apoyo-al-paciente">Apoyo Paciente</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    Condiciones de Salud
                </a>
                <ul class="dropdown-menu border-0 shadow">
                    <li><a class="dropdown-item py-2" href="/productos?condicion=cancer"><i class="bi bi-ribbon me-2" style="color:var(--green-700)"></i>Cáncer</a></li>
                    <li><a class="dropdown-item py-2" href="/productos?condicion=diabetes"><i class="bi bi-droplet me-2" style="color:var(--green-700)"></i>Diabetes</a></li>
                    <li><a class="dropdown-item py-2" href="/productos?condicion=osteoporosis"><i class="bi bi-capsule me-2" style="color:var(--green-700)"></i>Osteoporosis</a></li>
                    <li><a class="dropdown-item py-2" href="/productos?condicion=cardiologicos"><i class="bi bi-heart me-2" style="color:var(--green-700)"></i>Cardiológicos</a></li>
                    <li><a class="dropdown-item py-2" href="/productos?condicion=adulto-mayor"><i class="bi bi-person-wheelchair me-2" style="color:var(--green-700)"></i>Adulto Mayor</a></li>
                    <li><a class="dropdown-item py-2" href="/productos?condicion=rehabilitacion-condicion"><i class="bi bi-activity me-2" style="color:var(--green-700)"></i>Rehabilitación</a></li>
                </ul>
            </li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="/productos">Ofertas</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="/blog">Blog</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="#">Nosotros</a></li>
            <li class="nav-item"><a class="nav-link nav-link-custom" href="#">Contacto</a></li>
        </ul>
    </div>
</nav>

<!-- ── 4. Contenido principal ── -->
<main>
    <?= $this->section('content') ?>
</main>

<!-- ── 5. Footer ── -->
<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4 col-lg-4">
                <a href="/" class="text-decoration-none d-block mb-3">
                    <img src="/images/logo.png" alt="Caral Biotec" class="site-logo site-logo-footer bg-white p-2 rounded-2">
                </a>
                <p class="mb-3" style="line-height:1.6">Productos de alta calidad diseñados para tu salud, bienestar y recuperación integral.</p>
                <p class="mb-1"><i class="bi bi-geo-alt-fill me-2 footer-contact-icon"></i>Lima, Perú</p>
                <p class="mb-3"><i class="bi bi-envelope-fill me-2 footer-contact-icon"></i>contacto@caralbiotec.com</p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
            <div class="col-6 col-md-2 col-lg-2">
                <h5>Tienda</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/">Inicio</a></li>
                    <li class="mb-2"><a href="/productos">Productos</a></li>
                    <li class="mb-2"><a href="/blog">Blog</a></li>
                    <li class="mb-2"><a href="/login">Mi cuenta</a></li>
                    <li class="mb-2"><a href="/carrito">Carrito</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3 col-lg-3">
                <h5>Categorías</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="/productos?categoria=nutraceuticos">Nutracéuticos</a></li>
                    <li class="mb-2"><a href="/productos?categoria=bienestar">Bienestar</a></li>
                    <li class="mb-2"><a href="/productos?categoria=rehabilitacion">Rehabilitación</a></li>
                    <li class="mb-2"><a href="/productos?categoria=apoyo-al-paciente">Apoyo al Paciente</a></li>
                </ul>
            </div>
            <div class="col-md-3 col-lg-3">
                <h5>Newsletter</h5>
                <p>Recibe ofertas y consejos de salud.</p>
                <div class="input-group mb-3">
                    <input type="email" class="form-control" placeholder="Tu correo electrónico"
                           style="border-color:rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:var(--white)">
                    <button class="btn" type="button" style="background:linear-gradient(135deg,var(--green-700),var(--green-500));color:#fff;border:none">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
                <p style="font-size:.75rem;color:#4b5563">Sin spam. Cancela cuando quieras.</p>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span>© 2026 Caral Biotec. Todos los derechos reservados.</span>
            <span>Desarrollado para <strong style="color:var(--green-500)">Banahosting</strong></span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('mobileMenuToggle');
    var menu = document.getElementById('mobileNavMenu');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', function () {
        var isOpen = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.querySelector('i').className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
    });

    menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth >= 768) return;
            if (link.classList.contains('dropdown-toggle')) return;
            menu.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.querySelector('i').className = 'bi bi-list';
        });
    });

    document.querySelectorAll('.catalog-filter-card').forEach(function (card) {
        var filterToggle = card.querySelector('.catalog-filter-toggle');
        if (!filterToggle) return;

        filterToggle.addEventListener('click', function () {
            var isOpen = card.classList.toggle('is-open');
            filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });
});
</script>
</body>
</html>
