<?php $this->layout('shared::layout', ['title' => 'Mi Carrito de Compras - Caral Biotec']) ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">Mi Carrito de Compras</h1>

    <?php if (empty($items)): ?>
        <div class="card border-0 shadow-sm p-5 text-center">
            <div class="card-body">
                <i class="bi bi-cart-x fs-1 text-muted" style="font-size: 4rem;"></i>
                <h3 class="fw-bold mt-4">Tu carrito está vacío</h3>
                <p class="text-muted">Aún no has agregado ningún producto a tu carrito de compras.</p>
                <a href="/productos" class="btn btn-primary-custom mt-3">Explorar catálogo</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Listado de Items -->
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm p-4">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Producto</th>
                                    <th scope="col" class="text-center">Precio</th>
                                    <th scope="col" class="text-center" style="width: 150px;">Cantidad</th>
                                    <th scope="col" class="text-end">Total</th>
                                    <th scope="col" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <a href="/producto/<?= $this->e($item['slug']) ?>" class="text-decoration-none text-dark d-flex align-items-center gap-3">
                                                <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                    <i class="bi bi-box-seam text-success"></i>
                                                </div>
                                                <div>
                                                    <span class="d-block fw-bold"><?= $this->e($item['name']) ?></span>
                                                    <small class="text-muted">SKU: <?= $this->e($item['sku']) ?></small>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="text-center">S/. <?= $this->e(number_format($item['price'], 2)) ?></td>
                                        <td class="text-center">
                                            <form action="/carrito/actualizar" method="POST" class="d-flex align-items-center justify-content-center gap-1">
                                                <input type="hidden" name="product_id" value="<?= $this->e($item['product_id']) ?>">
                                                <input type="number" name="quantity" class="form-control text-center form-control-sm" value="<?= $this->e($item['quantity']) ?>" min="1" style="width: 60px;" onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold">S/. <?= $this->e(number_format($item['price'] * $item['quantity'], 2)) ?></td>
                                        <td class="text-center">
                                            <a href="/carrito/eliminar/<?= $this->e($item['product_id']) ?>" class="btn btn-outline-danger btn-sm border-0">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen de Compra -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm p-4">
                    <h3 class="fw-bold mb-4" style="font-size: 1.25rem;">Resumen de compra</h3>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>S/. <?= $this->e(number_format($total, 2)) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Envío</span>
                        <span class="text-success fw-bold">Gratis</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-success fs-4">S/. <?= $this->e(number_format($total, 2)) ?></span>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/checkout" class="btn btn-primary-custom py-2 fw-bold">PROCEDER AL PAGO</a>
                        <a href="/productos" class="btn btn-link text-success text-decoration-none text-center btn-sm mt-2">Seguir comprando</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
