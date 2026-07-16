<?php $this->layout('shared::layout', ['title' => $product['name'] . ' - Caral Biotec']) ?>
<?php
function productVideoEmbed(string $url): string {
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]+)~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return $url;
}
?>
<style>
.product-media-stage{border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#f8fafc;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center}.product-media-stage img,.product-media-stage video,.product-media-stage iframe{width:100%;height:100%;object-fit:contain;border:0}.product-media-zoom{cursor:zoom-in}.product-media-thumbs{display:flex;gap:.6rem;margin-top:.8rem;overflow:auto}.product-media-thumb{width:74px;height:74px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden}.product-media-thumb img{width:100%;height:100%;object-fit:cover}.zoom-modal{position:fixed;inset:0;background:rgba(15,23,42,.88);display:none;align-items:center;justify-content:center;z-index:2000;padding:2rem}.zoom-modal.open{display:flex}.zoom-modal img{max-width:96vw;max-height:92vh;object-fit:contain}.zoom-modal button{position:absolute;top:1rem;right:1rem;border:0;background:#fff;color:#0f172a;border-radius:8px;padding:.6rem .85rem}
</style>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="/" class="text-decoration-none" style="color:var(--green-700)">Inicio</a></li><li class="breadcrumb-item"><a href="/productos" class="text-decoration-none" style="color:var(--green-700)">Productos</a></li><li class="breadcrumb-item active"><?= $this->e($product['name']) ?></li></ol></nav>

    <div class="row mb-5">
        <div class="col-md-6 mb-4 mb-md-0">
            <?php if (!empty($media)): ?>
            <div id="productMediaCarousel" class="carousel slide" data-bs-interval="false">
                <div class="carousel-inner">
                    <?php foreach ($media as $i => $item): $type = $item['media_type'] ?? 'image'; $url = $item['url'] ?? ''; ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <div class="product-media-stage">
                            <?php if ($type === 'video'): ?>
                                <?php if (preg_match('~youtube\.com|youtu\.be~', $url)): ?><iframe src="<?= $this->e(productVideoEmbed($url)) ?>" allowfullscreen loading="lazy"></iframe><?php else: ?><video controls src="<?= $this->e($url) ?>"></video><?php endif; ?>
                            <?php else: ?>
                                <img src="<?= $this->e($url) ?>" alt="<?= $this->e($item['title'] ?: $product['name']) ?>" class="product-media-zoom" onclick="openProductZoom(this.src)">
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($media) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productMediaCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productMediaCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                <?php endif; ?>
            </div>
            <div class="product-media-thumbs">
                <?php foreach ($media as $i => $item): ?>
                <button class="product-media-thumb" type="button" data-bs-target="#productMediaCarousel" data-bs-slide-to="<?= $i ?>">
                    <?php if (($item['media_type'] ?? 'image') === 'video'): ?><i class="bi bi-play-circle fs-2" style="color:var(--green-700)"></i><?php else: ?><img src="<?= $this->e($item['url']) ?>" alt=""><?php endif; ?>
                </button>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="product-media-stage"><i class="bi bi-box-seam" style="font-size:8rem;color:var(--green-700);opacity:.4"></i></div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <span class="badge mb-2 px-3 py-2" style="background:var(--green-100);color:var(--green-700)"><?= $this->e($product['category_name']) ?></span>
            <h1 class="fw-bold mb-2"><?= $this->e($product['name']) ?></h1>
            <p class="text-muted mb-4">SKU: <strong><?= $this->e($product['sku']) ?></strong></p>
            <div class="price-box mb-4 py-3 px-4 rounded" style="background:var(--green-50)"><span class="text-muted d-block" style="font-size:.85rem">Precio al publico</span><span class="fw-bold" style="font-size:2.2rem;color:var(--green-700)">S/. <?= $this->e(number_format($product['price'], 2)) ?></span></div>
            <p class="lead text-muted mb-4"><?= $this->e($product['short_description']) ?></p>
            <div class="d-flex align-items-center gap-2 mb-4"><i class="bi bi-check-circle-fill fs-5" style="color:var(--green-700)"></i><span>Stock disponible: <strong><?= $this->e($product['stock']) ?> unidades</strong></span></div>
            <form action="/carrito/agregar" method="POST" class="row g-3 align-items-center"><input type="hidden" name="product_id" value="<?= $this->e($product['id']) ?>"><div class="col-auto"><div class="input-group"><button type="button" class="btn btn-outline-secondary" onclick="decQty()">-</button><input type="number" name="quantity" id="quantity" class="form-control text-center" value="1" min="1" max="<?= $this->e($product['stock']) ?>" style="width:60px"><button type="button" class="btn btn-outline-secondary" onclick="incQty()">+</button></div></div><div class="col"><button type="submit" class="btn btn-primary-custom w-100 d-flex align-items-center justify-content-center gap-2">ANADIR AL CARRITO <i class="bi bi-cart-plus fs-5"></i></button></div></form>
        </div>
    </div>

    <div class="row mb-5"><div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5"><h3 class="fw-bold mb-3 pb-2 border-bottom" style="color:var(--green-700)">Ficha Tecnica y Descripcion</h3><p class="text-muted" style="line-height:1.8;font-size:1.05rem"><?= nl2br($this->e($product['description'])) ?></p></div></div></div></div>

    <?php if (!empty($relatedProducts)): ?><div class="related-products-section"><h3 class="fw-bold mb-4">Productos Recomendados</h3><div class="row g-4"><?php foreach ($relatedProducts as $rel): ?><div class="col-6 col-md-3"><div class="product-card"><div class="product-img-wrapper"><?php if (!empty($rel['image_url'])): ?><img src="<?= $this->e($rel['image_url']) ?>" alt="<?= $this->e($rel['name']) ?>" class="product-img"><?php else: ?><i class="bi bi-box-seam fs-2" style="color:var(--green-700);opacity:.45"></i><?php endif; ?></div><div class="product-body"><span class="product-category"><?= $this->e($rel['category_name']) ?></span><h4 class="product-title" style="font-size:.95rem"><a href="/producto/<?= $this->e($rel['slug']) ?>" class="text-decoration-none text-dark"><?= $this->e($rel['name']) ?></a></h4><div class="product-price" style="font-size:1.1rem">S/. <?= $this->e(number_format($rel['price'], 2)) ?></div><form action="/carrito/agregar" method="POST"><input type="hidden" name="product_id" value="<?= $this->e($rel['id']) ?>"><input type="hidden" name="quantity" value="1"><button type="submit" class="btn btn-primary-custom btn-sm w-100 d-flex align-items-center justify-content-center gap-2">Anadir <i class="bi bi-cart-plus"></i></button></form></div></div></div><?php endforeach; ?></div></div><?php endif; ?>
</div>

<div class="zoom-modal" id="productZoom"><button type="button" onclick="closeProductZoom()"><i class="bi bi-x-lg"></i></button><img src="" alt="Zoom producto"></div>
<script>
function incQty(){var qty=document.getElementById('quantity');var max=parseInt(qty.max);var val=parseInt(qty.value);if(val<max)qty.value=val+1}function decQty(){var qty=document.getElementById('quantity');var val=parseInt(qty.value);if(val>1)qty.value=val-1}function openProductZoom(src){var modal=document.getElementById('productZoom');modal.querySelector('img').src=src;modal.classList.add('open')}function closeProductZoom(){document.getElementById('productZoom').classList.remove('open')}document.getElementById('productZoom').addEventListener('click',function(e){if(e.target===this)closeProductZoom()});
</script>