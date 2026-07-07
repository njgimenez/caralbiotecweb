<?php $this->layout('shared::layout', ['title' => $product['name'] . ' - Caral Biotec']) ?>

<div class="container py-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none" style="color:var(--green-700)">Inicio</a></li>
            <li class="breadcrumb-item"><a href="/productos" class="text-decoration-none" style="color:var(--green-700)">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $this->e($product['name']) ?></li>
        </ol>
    </nav>

    <!-- Ficha del Producto -->
    <div class="row mb-5">
        <!-- Imagen -->
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="detail-img-wrap">
                <?php if (!empty($product['image_url'])): ?>
                    <img src="<?= $this->e($product['image_url']) ?>" alt="<?= $this->e($product['name']) ?>" class="detail-product-img">
                <?php else: ?>
                    <div class="text-center">
                        <i class="bi bi-box-seam" style="font-size: 8rem; color:var(--green-700); opacity:.4"></i>
                        <p class="text-muted mt-2">Imagen referencial del producto</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información y Compra -->
        <div class="col-md-6">
            <span class="badge mb-2 px-3 py-2 fs-7 uppercase"
                  style="background:var(--green-100);color:var(--green-700)"><?= $this->e($product['category_name']) ?></span>
            <h1 class="fw-bold mb-2"><?= $this->e($product['name']) ?></h1>
            <p class="text-muted mb-4">SKU: <strong><?= $this->e($product['sku']) ?></strong></p>

            <div class="price-box mb-4 py-3 px-4 rounded" style="background:var(--green-50)">
                <span class="text-muted d-block" style="font-size: 0.85rem;">Precio al público</span>
                <span class="fw-bold" style="font-size: 2.2rem; color:var(--green-700)">S/. <?= $this->e(number_format($product['price'], 2)) ?></span>
            </div>

            <p class="lead text-muted mb-4"><?= $this->e($product['short_description']) ?></p>

            <!-- Stock disponible -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill fs-5" style="color:var(--green-700)"></i>
                <span>Stock disponible: <strong><?= $this->e($product['stock']) ?> unidades</strong></span>
            </div>

            <!-- Formulario de Adición -->
            <form action="/carrito/agregar" method="POST" class="row g-3 align-items-center">
                <input type="hidden" name="product_id" value="<?= $this->e($product['id']) ?>">
                
                <div class="col-auto">
                    <label for="quantity" class="visually-hidden">Cantidad</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="decQty()">-</button>
                        <input type="number" name="quantity" id="quantity" class="form-control text-center" value="1" min="1" max="<?= $this->e($product['stock']) ?>" style="width: 60px;">
                        <button type="button" class="btn btn-outline-secondary" onclick="incQty()">+</button>
                    </div>
                </div>
                
                <div class="col">
                    <button type="submit" class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center gap-2">
                        AÑADIR AL CARRITO <i class="bi bi-cart-plus fs-5"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Descripción Detallada -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h3 class="fw-bold mb-3 pb-2 border-bottom" style="color:var(--green-700)">Ficha Técnica y Descripción</h3>
                    <p class="text-muted" style="line-height: 1.8; font-size: 1.05rem;"><?= nl2br($this->e($product['description'])) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Relacionados -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="related-products-section">
                        <h3 class="fw-bold mb-4">Productos Recomendados</h3>
            <div class="row g-4">
                <?php foreach ($relatedProducts as $rel): ?>
                    <div class="col-6 col-md-3">
                        <div class="product-card">
                            <div class="product-img-wrapper">
                                <?php if (!empty($rel['image_url'])): ?>
                                    <img src="<?= $this->e($rel['image_url']) ?>" alt="<?= $this->e($rel['name']) ?>" class="product-img">
                                <?php else: ?>
                                    <i class="bi bi-box-seam fs-2" style="color:var(--green-700);opacity:.45"></i>
                                <?php endif; ?>
                            </div>
                            <div class="product-body">
                                <span class="product-category"><?= $this->e($rel['category_name']) ?></span>
                                <h4 class="product-title" style="font-size: 0.95rem;">
                                    <a href="/producto/<?= $this->e($rel['slug']) ?>" class="text-decoration-none text-dark">
                                        <?= $this->e($rel['name']) ?>
                                    </a>
                                </h4>
                                <div class="product-price" style="font-size: 1.1rem;">S/. <?= $this->e(number_format($rel['price'], 2)) ?></div>
                                <div class="d-grid">
                                    <form action="/carrito/agregar" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $this->e($rel['id']) ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary-custom btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                            Añadir <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function incQty() {
    var qty = document.getElementById('quantity');
    var max = parseInt(qty.max);
    var val = parseInt(qty.value);
    if (val < max) qty.value = val + 1;
}
function decQty() {
    var qty = document.getElementById('quantity');
    var val = parseInt(qty.value);
    if (val > 1) qty.value = val - 1;
}
</script>
