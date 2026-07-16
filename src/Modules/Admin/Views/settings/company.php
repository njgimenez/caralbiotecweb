<?php $this->layout('admin::layout', ['title' => 'Configuracion de Empresa | Admin', 'pageTitle' => 'Configuracion de Empresa']) ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger flash-alert d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-exclamation-circle-fill"></i> <?= $this->e($error) ?>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <form method="POST" action="/admin/configuracion/empresa" class="admin-form-card">
            <h6 class="fw-bold mb-4"><i class="bi bi-building-gear me-2 text-success"></i>Datos fiscales y comerciales</h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Razon social <span class="text-danger">*</span></label>
                    <input type="text" name="business_name" class="form-control" required value="<?= $this->e($settings['business_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nombre comercial</label>
                    <input type="text" name="trade_name" class="form-control" value="<?= $this->e($settings['trade_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">RUC de la empresa</label>
                    <input type="text" name="ruc" class="form-control" maxlength="11" inputmode="numeric" value="<?= $this->e($settings['ruc'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">IGV (%)</label>
                    <input type="number" name="igv_percent" class="form-control" min="0" max="100" step="0.01" value="<?= $this->e($settings['igv_percent'] ?? '18') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Direccion fiscal</label>
                    <input type="text" name="address" class="form-control" value="<?= $this->e($settings['address'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telefono</label>
                    <input type="text" name="phone" class="form-control" value="<?= $this->e($settings['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Correo</label>
                    <input type="email" name="email" class="form-control" value="<?= $this->e($settings['email'] ?? '') ?>">
                </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3"><i class="bi bi-credit-card-2-front me-2 text-success"></i>Pasarela Izipay</h6>
            <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                <label class="form-label d-block mb-2">Modo para nuevos pagos</label>
                <div class="btn-group" role="group" aria-label="Modo Izipay">
                    <input type="radio" class="btn-check" name="izipay_mode" id="izipay_mode_test" value="test" autocomplete="off" <?= ($settings['izipay_mode'] ?? 'test') !== 'production' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-primary" for="izipay_mode_test">Desarrollo / prueba</label>

                    <input type="radio" class="btn-check" name="izipay_mode" id="izipay_mode_prod" value="production" autocomplete="off" <?= ($settings['izipay_mode'] ?? 'test') === 'production' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-success" for="izipay_mode_prod">Produccion</label>
                </div>
                <p class="text-muted mb-0 mt-3" style="font-size:.85rem;line-height:1.5">
                    Este switch decide que credenciales se usan al crear nuevos pagos. La URL IPN puede ser la misma para prueba y produccion:
                    <code>https://caralbiotec.com/checkout/izipay/ipn</code>.
                </p>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn-admin-save py-2 px-4 rounded-3">
                    <i class="bi bi-floppy me-1"></i>Guardar configuracion
                </button>
                <a href="/admin" class="btn-admin-cancel py-2 px-4 rounded-3">Cancelar</a>
            </div>
        </form>
    </div>

    <div class="col-12 col-lg-4">
        <div class="admin-form-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-receipt me-2 text-success"></i>Uso en comprobantes</h6>
            <p class="text-muted mb-3" style="font-size:.9rem;line-height:1.6">
                Estos datos se imprimiran en boletas, facturas y reportes POS. El IGV configurado se usa para calcular el valor gravado e impuesto incluido en el total de venta.
            </p>
            <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.85rem">
                <div class="fw-bold"><?= $this->e($settings['trade_name'] ?: $settings['business_name']) ?></div>
                <div class="text-muted"><?= $this->e($settings['business_name']) ?></div>
                <?php if (!empty($settings['ruc'])): ?><div>RUC: <?= $this->e($settings['ruc']) ?></div><?php endif; ?>
                <div>IGV: <?= $this->e(number_format((float)$settings['igv_percent'], 2)) ?>%</div>
                <div>Izipay: <?= ($settings['izipay_mode'] ?? 'test') === 'production' ? 'Produccion' : 'Desarrollo / prueba' ?></div>
            </div>
        </div>
    </div>
</div>