<?php $this->layout('shared::layout', ['title' => $title]) ?>

<div class="container py-5 catalog-list-page">
    <div class="row">
        <!-- Sidebar de Filtros -->
        <div class="col-md-3 mb-4 mb-md-0 catalog-filter-column">
            <div class="card border-0 shadow-sm p-4 catalog-filter-card">
                <button type="button" class="catalog-filter-toggle" aria-expanded="false" aria-controls="catalogFilterBody">
                    <span><i class="bi bi-sliders me-2"></i>Filtros</span>
                    <i class="bi bi-chevron-down"></i>
                </button>
                <div class="catalog-filter-body" id="catalogFilterBody">
                    <h4 class="fw-bold mb-3 catalog-filter-title" style="font-size: 1.2rem;">Filtros</h4>
                
                    <!-- Categorías -->
                    <div class="mb-4 catalog-filter-group">
                        <h5 class="fw-bold mb-2" style="font-size: 1rem; color: var(--green-700);">Categorías</h5>
                        <ul class="list-unstyled">
                            <li><a href="/productos" class="text-decoration-none text-dark d-block py-1">Todos</a></li>
                            <?php foreach ($categoriesList as $cat): ?>
                                <li><a href="/productos?categoria=<?= $this->e($cat['slug']) ?>" class="text-decoration-none text-dark d-block py-1"><?= $this->e($cat['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Condiciones de salud -->
                    <div class="catalog-filter-group">
                        <h5 class="fw-bold mb-2" style="font-size: 1rem; color: var(--green-700);">Condiciones de Salud</h5>
                        <ul class="list-unstyled">
                            <?php foreach ($conditionsList as $cond): ?>
                                <li><a href="/productos?condicion=<?= $this->e($cond['slug']) ?>" class="text-decoration-none text-dark d-block py-1"><?= $this->e($cond['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados de Productos -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0" style="font-size: 1.5rem;">
                    <?= empty($filterName) ? 'Todos los Productos' : $this->e($filterName) ?>
                </h2>
                <span class="text-muted"><?= count($products) ?> productos encontrados</span>
            </div>

            <div class="row g-4">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search fs-1 text-muted"></i>
                        <p class="text-muted mt-3">No se encontraron productos que coincidan con tu búsqueda o filtro.</p>
                        <a href="/productos" class="btn btn-primary-custom btn-sm mt-2">Ver todo el catálogo</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-6 col-md-4">
                            <div class="product-card">
                                <div class="product-img-wrapper">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?= $this->e($product['image_url']) ?>" alt="<?= $this->e($product['name']) ?>" class="product-img">
                                    <?php else: ?>
                                        <i class="bi bi-box-seam fs-1" style="color:var(--green-700);opacity:.45"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="product-body">
                                    <span class="product-category"><?= $this->e($product['category_name']) ?></span>
                                    <h3 class="product-title">
                                        <a href="/producto/<?= $this->e($product['slug']) ?>" class="text-decoration-none text-dark">
                                            <?= $this->e($product['name']) ?>
                                        </a>
                                    </h3>
                                    <div class="product-price">S/. <?= $this->e(number_format($product['price'], 2)) ?></div>
                                    <div class="d-grid">
                                        <form action="/carrito/agregar" method="POST">
                                            <input type="hidden" name="product_id" value="<?= $this->e($product['id']) ?>">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
