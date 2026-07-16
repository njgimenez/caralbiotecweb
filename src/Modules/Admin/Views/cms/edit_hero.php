<?php
$slides = $content['slides'] ?? null;
if (!is_array($slides) || $slides === []) {
    $slides = [[
        'tag_text' => $content['tag_text'] ?? 'Productos certificados - Lima, Peru',
        'title_part1' => $content['title_part1'] ?? 'Soluciones integrales para tu',
        'title_accent' => $content['title_accent'] ?? 'bienestar',
        'title_part2' => $content['title_part2'] ?? 'y recuperacion',
        'subtitle' => $content['subtitle'] ?? '',
        'image_url' => $content['image_url'] ?? '/images/hero-bg.png',
        'btn_primary_text' => $content['btn_primary_text'] ?? 'Comprar ahora',
        'btn_primary_url' => $content['btn_primary_url'] ?? '/productos',
        'btn_secondary_text' => $content['btn_secondary_text'] ?? 'Ver categorias',
        'btn_secondary_url' => $content['btn_secondary_url'] ?? '#categorias',
    ]];
}
while (count($slides) < 3) {
    $slides[] = ['tag_text'=>'','title_part1'=>'','title_accent'=>'','title_part2'=>'','subtitle'=>'','image_url'=>'','btn_primary_text'=>'','btn_primary_url'=>'','btn_secondary_text'=>'','btn_secondary_url'=>''];
}
?>
<?php $this->layout('admin::layout', ['title' => 'Configurar Hero Carrusel | Admin', 'pageTitle' => 'Configurar Hero Carrusel']) ?>

<div class="mb-4">
    <a href="/admin/cms" class="btn-admin-cancel py-2 px-3 rounded-3"><i class="bi bi-arrow-left me-1"></i> Volver al listado</a>
</div>

<form action="/admin/cms/home_hero/actualizar" method="POST" class="admin-form-card">
    <div class="mb-4 form-check form-switch p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
        <div class="ms-3">
            <label class="form-check-label fw-bold d-block text-dark" for="is_active">Activar carrusel principal</label>
            <small class="text-muted">Cada slide puede cambiar CTA, H1, textos e imagen.</small>
        </div>
        <input class="form-check-input me-2" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= $block['is_active'] ? 'checked' : '' ?> style="width:2.5em;height:1.25em;">
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <?php foreach ($slides as $i => $slide): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" type="button" data-bs-toggle="tab" data-bs-target="#hero-slide-<?= $i ?>">Slide <?= $i + 1 ?></button>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">
        <?php foreach ($slides as $i => $slide): ?>
        <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="hero-slide-<?= $i ?>">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Texto de etiqueta superior</label>
                    <input type="text" name="slides[<?= $i ?>][tag_text]" class="form-control" value="<?= $this->e($slide['tag_text'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">H1 parte 1</label>
                    <input type="text" name="slides[<?= $i ?>][title_part1]" class="form-control" value="<?= $this->e($slide['title_part1'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">H1 destacado</label>
                    <input type="text" name="slides[<?= $i ?>][title_accent]" class="form-control" value="<?= $this->e($slide['title_accent'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">H1 parte 2</label>
                    <input type="text" name="slides[<?= $i ?>][title_part2]" class="form-control" value="<?= $this->e($slide['title_part2'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Texto descriptivo</label>
                    <textarea name="slides[<?= $i ?>][subtitle]" class="form-control" rows="3"><?= $this->e($slide['subtitle'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Imagen de fondo o producto destacado</label>
                    <input type="text" name="slides[<?= $i ?>][image_url]" class="form-control js-media-picker" value="<?= $this->e($slide['image_url'] ?? '') ?>" placeholder="/images/hero-bg.png o https://...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">CTA principal - texto</label>
                    <input type="text" name="slides[<?= $i ?>][btn_primary_text]" class="form-control" value="<?= $this->e($slide['btn_primary_text'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">CTA principal - URL</label>
                    <input type="text" name="slides[<?= $i ?>][btn_primary_url]" class="form-control" value="<?= $this->e($slide['btn_primary_url'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">CTA secundario - texto</label>
                    <input type="text" name="slides[<?= $i ?>][btn_secondary_text]" class="form-control" value="<?= $this->e($slide['btn_secondary_text'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">CTA secundario - URL</label>
                    <input type="text" name="slides[<?= $i ?>][btn_secondary_url]" class="form-control" value="<?= $this->e($slide['btn_secondary_url'] ?? '') ?>">
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex gap-3 mt-4">
        <button type="submit" class="btn-admin-save px-4 py-2 rounded-3"><i class="bi bi-floppy"></i> Guardar carrusel</button>
        <a href="/admin/cms" class="btn-admin-cancel py-2 px-4 rounded-3">Cancelar</a>
    </div>
</form>