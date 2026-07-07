<?php $this->layout('shared::layout', ['title' => 'Caral Biotec — Soluciones integrales para tu bienestar']) ?>

<!-- ══════════════════════════════════════
     1. HERO SECTION (Customizable CMS)
══════════════════════════════════════ -->
<?php if ($hero['is_active']): ?>
<section class="hero-slider">
    <div class="container position-relative" style="z-index:1">
        <div class="row align-items-center">
            <!-- Texto -->
            <div class="col-12 col-lg-7">
                <?php if (!empty($hero['content']['tag_text'])): ?>
                <div class="hero-tag">
                    <i class="bi bi-patch-check-fill"></i>
                    <?= $this->e($hero['content']['tag_text']) ?>
                </div>
                <?php endif; ?>
                <h1 class="hero-title">
                    <?= str_replace(['&lt;br&gt;', '&lt;br /&gt;', '&lt;br/&gt;'], '<br>', $this->e($hero['content']['title_part1'] ?? '')) ?>
                    <?php if (!empty($hero['content']['title_accent'])): ?>
                        <span class="accent"><?= str_replace(['&lt;br&gt;', '&lt;br /&gt;', '&lt;br/&gt;'], '<br>', $this->e($hero['content']['title_accent'])) ?></span>
                    <?php endif; ?>
                    <?= str_replace(['&lt;br&gt;', '&lt;br /&gt;', '&lt;br/&gt;'], '<br>', $this->e($hero['content']['title_part2'] ?? '')) ?>
                </h1>
                <p class="hero-subtitle">
                    <?= $this->e($hero['content']['subtitle'] ?? '') ?>
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if (!empty($hero['content']['btn_primary_text'])): ?>
                    <a href="<?= $this->e($hero['content']['btn_primary_url'] ?? '/productos') ?>" class="btn-primary-custom d-flex align-items-center gap-2 text-decoration-none">
                        <i class="bi bi-cart3"></i> <?= $this->e($hero['content']['btn_primary_text']) ?>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($hero['content']['btn_secondary_text'])): ?>
                    <a href="<?= $this->e($hero['content']['btn_secondary_url'] ?? '#categorias') ?>" class="btn-outline-custom text-decoration-none">
                        <?= $this->e($hero['content']['btn_secondary_text']) ?>
                    </a>
                    <?php endif; ?>
                </div>
                <!-- Mini stats -->
                <div class="d-flex gap-5 mt-5 pt-3 justify-content-start">
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;color:#4b2bb0">+500</div>
                        <div style="font-size:.78rem;color:#64748b">Clientes felices</div>
                    </div>
                    <div style="width:1px;background:#e2e8f0"></div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;color:#4b2bb0">100%</div>
                        <div style="font-size:.78rem;color:#64748b">Calidad garantizada</div>
                    </div>
                    <div style="width:1px;background:#e2e8f0"></div>
                    <div>
                        <div style="font-size:1.6rem;font-weight:800;color:#4b2bb0">24h</div>
                        <div style="font-size:.78rem;color:#64748b">Envío express</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════
     2. TRUST BADGES (Customizable CMS)
══════════════════════════════════════ -->
<?php if ($benefits['is_active'] && !empty($benefits['content'])): ?>
<section class="trust-badges-section">
    <div class="container">
        <div class="row g-3">
            <?php foreach ($benefits['content'] as $b): ?>
            <div class="col-6 col-md-3">
                <div class="trust-badge-card">
                    <div class="trust-badge-icon-wrap">
                        <i class="bi bi-<?= $this->e($b['icon'] ?? 'check-circle') ?> trust-badge-icon"></i>
                    </div>
                    <div>
                        <h4 class="trust-badge-title"><?= $this->e($b['title'] ?? '') ?></h4>
                        <p class="trust-badge-desc"><?= $this->e($b['desc'] ?? '') ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════
     3. CATEGORÍAS
══════════════════════════════════════ -->
<section id="categorias" class="py-5" style="background:var(--slate-50)">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-1">
            <h2 class="section-title mb-0">Explora nuestras categorías</h2>
            <a href="/productos" class="d-none d-md-inline text-decoration-none"
               style="color:var(--green-700);font-weight:700;font-size:.875rem">
                Ver todo <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <p class="section-subtitle">Encuentra exactamente lo que necesitas</p>

        <?php
        $catIcons = ['nutraceuticos'=>'capsule','bienestar'=>'moon-stars','rehabilitacion'=>'activity','apoyo-al-paciente'=>'person-wheelchair'];
        $catBgs   = ['#e7e0ff','#dbeafe','#fef3c7','#fce7f3'];
        $i = 0;
        ?>
        <div class="row g-4">
            <?php foreach ($categories as $cat):
                $bg = $catBgs[$i % 4];
                $icon = $catIcons[$cat['slug']] ?? 'tag';
                $i++;
            ?>
            <div class="col-6 col-md-3">
                <a href="/productos?categoria=<?= $this->e($cat['slug']) ?>" class="text-decoration-none d-block h-100">
                    <div class="category-card h-100">
                        <div class="category-img" style="background:<?= $bg ?>">
                            <?php if (!empty($cat['image_url'])): ?>
                                <img src="<?= $this->e($cat['image_url']) ?>" alt="<?= $this->e($cat['name']) ?>" class="w-100 h-100 object-fit-cover position-absolute start-0 top-0">
                                <div class="position-absolute start-0 top-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,0.05), rgba(0,0,0,0.2));"></div>
                            <?php endif; ?>
                            <div class="category-icon-circle">
                                <i class="bi bi-<?= $icon ?>"></i>
                            </div>
                        </div>
                        <div class="category-body">
                            <h3 class="category-title"><?= $this->e($cat['name']) ?></h3>
                            <p class="category-desc"><?= $this->e($cat['description']) ?></p>
                            <span class="category-link">Ver productos <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     4. CONDICIONES DE SALUD
══════════════════════════════════════ -->
<section class="py-5" style="background:var(--white)">
    <div class="container">
        <h2 class="section-title">Compra según tu necesidad</h2>
        <p class="section-subtitle">Productos seleccionados para cada condición de salud</p>
        <div class="row g-3">
            <?php
            $condIcons = [
                'cancer'=>'ribbon','diabetes'=>'droplet','osteoporosis'=>'capsule',
                'cardiologicos'=>'heart-pulse','adulto-mayor'=>'person-wheelchair','rehabilitacion-condicion'=>'activity'
            ];
            foreach ($conditions as $cond):
                $icon = $condIcons[$cond['slug']] ?? 'heart';
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="/productos?condicion=<?= $this->e($cond['slug']) ?>" class="text-decoration-none d-block h-100">
                    <div class="condition-card">
                        <div class="condition-icon-wrap">
                            <?php if ($cond['slug'] === 'cancer'): ?>
                                <svg class="condition-icon" viewBox="0 0 30.476093 42.087807" width="1.35em" height="1.35em" style="display:inline-block; vertical-align:middle; pointer-events:none;">
                                    <g transform="translate(-252.37008,-156.12283)">
                                        <path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" d="m 258.44686,184.46488 -5.3721,7.09884 7.96221,5.85175 3.26163,-4.50873" />
                                        <path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" d="m 282.23755,191.37186 -8.25,6.13954 -18.61046,-24.94186 c -1.44452,-2.25907 -1.2599,-4.61249 -0.406,-7.12694 l 4.24321,-6.68702 c 2.08149,-2.39162 2.31011,-2.06892 5.55813,-2.13274 h 5.72772 c 4.66464,0.1448 5.04787,2.45148 6.46124,4.33914 3.09465,5.16122 4.82326,7.9436 3.54942,10.07267 l -7.00291,9.40117" />
                                        <path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" d="m 258.15907,160.96198 24.29433,30.84157" />
                                        <path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" d="m 267.75209,172.47361 c 0.38372,-0.28779 9.30523,-11.4157 9.30523,-11.4157" />
                                        <path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" d="m 260.69781,164.12768 13.98553,0.0959" />
                                    </g>
                                </svg>
                            <?php else: ?>
                                <i class="bi bi-<?= $icon ?> condition-icon"></i>
                            <?php endif; ?>
                        </div>
                        <h3 class="condition-title"><?= $this->e($cond['name']) ?></h3>
                        <p class="condition-desc"><?= $this->e($cond['description']) ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     5. PRODUCTOS DESTACADOS
══════════════════════════════════════ -->
<section class="py-5" style="background:var(--slate-50)">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-1">
            <h2 class="section-title mb-0">Productos Destacados</h2>
            <a href="/productos" class="d-none d-md-inline text-decoration-none"
               style="color:var(--green-700);font-weight:700;font-size:.875rem">
                Ver todo el catálogo <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <p class="section-subtitle">Los favoritos de nuestra comunidad</p>

        <div class="row g-4">
            <?php if (empty($featuredProducts)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">No hay productos disponibles aún.</p>
                </div>
            <?php else: ?>
                <?php foreach ($featuredProducts as $product): ?>
                <div class="col-6 col-md-3">
                    <div class="product-card">
                        <div class="product-img-wrapper">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?= $this->e($product['image_url']) ?>" alt="<?= $this->e($product['name']) ?>" class="product-img">
                            <?php else: ?>
                                <i class="bi bi-box-seam"></i>
                            <?php endif; ?>
                        </div>
                        <div class="product-body">
                            <span class="product-category"><?= $this->e($product['category_name']) ?></span>
                            <h3 class="product-title">
                                <a href="/producto/<?= $this->e($product['slug']) ?>"
                                   class="text-decoration-none text-dark">
                                    <?= $this->e($product['name']) ?>
                                </a>
                            </h3>
                            <div class="product-price">S/. <?= $this->e(number_format($product['price'], 2)) ?></div>
                            <form action="/carrito/agregar" method="POST">
                                <input type="hidden" name="product_id" value="<?= $this->e($product['id']) ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="product-add-btn">
                                    Añadir al carrito <i class="bi bi-cart-plus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════
     6. ÚLTIMAS ENTRADAS DEL BLOG
══════════════════════════════════════ -->
<?php if (!empty($latestPosts)): ?>
<section class="py-5" style="background:var(--white)">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-1">
            <h2 class="section-title mb-0">Descubre lo mejor para tu bienestar</h2>
            <a href="/blog" class="d-none d-md-inline text-decoration-none"
               style="color:var(--green-700);font-weight:700;font-size:.875rem">
                Ver blog <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <p class="section-subtitle">Descubre contenido útil para cuidar tu salud y bienestar cada día.</p>

        <div class="row g-4">
            <?php foreach ($latestPosts as $post): ?>
            <div class="col-md-4">
                <article class="h-100 bg-white border overflow-hidden" style="border-color:#e2e8f0!important;border-radius:8px;box-shadow:0 10px 28px rgba(15,23,42,.05)">
                    <a href="/blog/<?= $this->e($post['slug']) ?>" class="d-block text-decoration-none" style="aspect-ratio:16/9;background:#f1f5f9;overflow:hidden">
                        <?php if (!empty($post['featured_image_url'])): ?>
                            <img src="<?= $this->e($post['featured_image_url']) ?>" alt="<?= $this->e($post['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100 text-success">
                                <i class="bi bi-newspaper" style="font-size:2.2rem"></i>
                            </div>
                        <?php endif; ?>
                    </a>
                    <div class="p-4">
                        <div class="d-flex align-items-center gap-2 text-muted mb-2" style="font-size:.8rem">
                            <i class="bi bi-calendar3"></i>
                            <span><?= $this->e(date('d/m/Y', strtotime($post['published_at']))) ?></span>
                        </div>
                        <h3 class="fw-bold mb-2" style="font-size:1.08rem;line-height:1.35">
                            <a href="/blog/<?= $this->e($post['slug']) ?>" class="text-decoration-none text-dark">
                                <?= $this->e($post['title']) ?>
                            </a>
                        </h3>
                        <p class="text-muted mb-3" style="font-size:.9rem;line-height:1.6">
                            <?= $this->e($post['excerpt'] ?? '') ?>
                        </p>
                        <a href="/blog/<?= $this->e($post['slug']) ?>" class="text-decoration-none"
                           style="color:var(--green-700);font-weight:800;font-size:.86rem">
                            Leer más <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════
     7. CTA BANNER (Customizable CMS)
══════════════════════════════════════ -->
<?php if ($cta['is_active']): ?>
<section style="background:linear-gradient(135deg,#1f113c 0%,#4b2bb0 100%);padding:60px 0">
    <div class="container text-center">
        <h2 style="color:var(--white);font-weight:800;font-size:2rem;margin-bottom:.75rem">
            <?= $this->e($cta['content']['title'] ?? '') ?>
        </h2>
        <p style="color:rgba(255,255,255,.8);font-size:1rem;margin-bottom:2rem">
            <?= $this->e($cta['content']['subtitle'] ?? '') ?>
        </p>
        <a href="<?= $this->e($cta['content']['btn_url'] ?? '#') ?>" target="_blank"
           style="display:inline-flex;align-items:center;gap:.5rem;background:white;color:#4b2bb0;
                  padding:.85rem 2rem;border-radius:50px;font-weight:800;text-decoration:none;
                  box-shadow:0 4px 20px rgba(0,0,0,.2);transition:all .2s">
            <i class="bi bi-<?= $this->e($cta['content']['btn_icon'] ?? 'chat') ?>" style="font-size:1.2rem;color:var(--green-700)"></i>
            <?= $this->e($cta['content']['btn_text'] ?? '') ?>
        </a>
    </div>
</section>
<?php endif; ?>
