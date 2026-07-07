<?php $this->layout('admin::layout', ['title' => 'Punto de Venta | Caral Biotec', 'pageTitle' => 'Punto de Venta']) ?>

<style>
.pos-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 1.25rem;
    min-height: calc(100vh - 130px);
}
.pos-panel {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
}
.pos-toolbar {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    display: grid;
    grid-template-columns: minmax(220px, 1fr) 220px;
    gap: .75rem;
}
.pos-search,
.pos-select,
.pos-input {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: .7rem .85rem;
    font-size: .9rem;
    outline: none;
}
.pos-search:focus,
.pos-select:focus,
.pos-input:focus {
    border-color: #6f5add;
    box-shadow: 0 0 0 3px rgba(111,90,221,.12);
}
.pos-grid {
    padding: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: .9rem;
}
.pos-product {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    overflow: hidden;
    cursor: pointer;
    text-align: left;
    transition: border-color .18s, box-shadow .18s, transform .18s;
}
.pos-product:hover {
    border-color: #6f5add;
    box-shadow: 0 8px 24px rgba(15,23,42,.08);
    transform: translateY(-2px);
}
.pos-product-img {
    height: 120px;
    background: linear-gradient(135deg, #f6f3ff, #f1ecff);
    display: flex;
    align-items: center;
    justify-content: center;
}
.pos-product-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: .75rem;
}
.pos-product-img i {
    color: #4b2bb0;
    opacity: .45;
    font-size: 2.4rem;
}
.pos-product-body {
    padding: .85rem;
}
.pos-product-name {
    color: #0f172a;
    font-size: .86rem;
    font-weight: 700;
    line-height: 1.25;
    min-height: 2.2rem;
}
.pos-product-meta {
    color: #64748b;
    font-size: .72rem;
    margin-top: .35rem;
}
.pos-product-bottom {
    display: flex;
    align-items: end;
    justify-content: space-between;
    margin-top: .75rem;
    gap: .5rem;
}
.pos-price {
    color: #4b2bb0;
    font-weight: 800;
    font-size: 1rem;
}
.pos-stock {
    color: #64748b;
    font-size: .7rem;
    white-space: nowrap;
}
.pos-cart {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 130px);
}
.pos-cart-head {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.pos-cart-title {
    font-weight: 800;
    color: #0f172a;
}
.pos-clear-btn {
    border: 0;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 8px;
    padding: .45rem .7rem;
    font-weight: 700;
    font-size: .78rem;
}
.pos-cart-items {
    padding: .75rem 1rem;
    overflow-y: auto;
    flex: 1;
}
.pos-empty {
    color: #64748b;
    text-align: center;
    padding: 3rem 1rem;
    font-size: .9rem;
}
.pos-line {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: .75rem;
    padding: .85rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.pos-line-name {
    font-weight: 700;
    color: #0f172a;
    font-size: .84rem;
    line-height: 1.3;
}
.pos-line-sub {
    color: #64748b;
    font-size: .72rem;
    margin-top: .25rem;
}
.pos-qty {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    margin-top: .55rem;
}
.pos-qty button {
    width: 28px;
    height: 28px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 8px;
    font-weight: 800;
}
.pos-qty span {
    min-width: 24px;
    text-align: center;
    font-weight: 700;
}
.pos-line-total {
    text-align: right;
    font-weight: 800;
    color: #4b2bb0;
}
.pos-remove {
    margin-top: .6rem;
    border: 0;
    background: transparent;
    color: #dc2626;
    font-size: .78rem;
}
.pos-summary {
    padding: 1rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.pos-summary-row {
    display: flex;
    justify-content: space-between;
    color: #475569;
    font-size: .9rem;
    margin-bottom: .55rem;
}
.pos-summary-row strong {
    color: #0f172a;
}
.pos-total-row {
    font-size: 1.25rem;
    font-weight: 900;
    color: #0f172a;
    padding-top: .7rem;
    border-top: 1px solid #e2e8f0;
}
.pos-pay-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .65rem;
    margin-top: .85rem;
}
.pos-document-box {
    margin-top: .85rem;
    padding: .8rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}
.pos-document-title {
    color: #0f172a;
    font-size: .78rem;
    font-weight: 800;
    margin-bottom: .55rem;
}
.pos-document-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .65rem;
}
.pos-document-grid.hidden {
    display: none;
}
.pos-checkout-btn {
    width: 100%;
    border: 0;
    border-radius: 10px;
    padding: .85rem 1rem;
    background: linear-gradient(135deg, #4b2bb0, #6f5add);
    color: white;
    font-weight: 800;
    margin-top: .85rem;
}
.pos-checkout-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
}
.pos-sale-message {
    display: none;
    margin-top: .85rem;
    padding: .8rem;
    border-radius: 10px;
    font-size: .82rem;
    line-height: 1.45;
}
.pos-sale-message.visible {
    display: block;
}
.pos-sale-message.success {
    background: #e7e0ff;
    border: 1px solid #b0a8ff;
    color: #28135d;
}
.pos-sale-message.error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.pos-sale-message strong {
    display: block;
    margin-bottom: .25rem;
}
@media (max-width: 1100px) {
    .pos-shell { grid-template-columns: 1fr; }
    .pos-cart { min-height: auto; }
}
@media (max-width: 640px) {
    .pos-toolbar { grid-template-columns: 1fr; }
    .pos-grid { grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)); }
}
</style>

<?php
$productsPayload = array_map(static function (array $product): array {
    return [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'sku' => $product['sku'],
        'category' => $product['category_name'],
        'price' => (float)$product['price'],
        'stock' => (int)$product['stock'],
        'image_url' => $product['image_url'] ?? '',
    ];
}, $products);
?>

<div class="pos-shell">
    <section class="pos-panel">
        <div class="pos-toolbar">
            <input id="posSearch" class="pos-search" type="search" placeholder="Buscar por producto o SKU">
            <select id="posCategory" class="pos-select">
                <option value="">Todas las categorías</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $this->e($category) ?>"><?= $this->e($category) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="pos-grid" id="posGrid"></div>
    </section>

    <aside class="pos-panel pos-cart">
        <div class="pos-cart-head">
            <div>
                <div class="pos-cart-title">Venta actual</div>
                <div style="color:#64748b;font-size:.76rem">Operador: <?= $this->e($this->getUserEmail()) ?></div>
            </div>
            <button type="button" class="pos-clear-btn" onclick="clearCart()">Limpiar</button>
        </div>

        <div class="pos-cart-items" id="posCartItems">
            <div class="pos-empty">Selecciona productos para iniciar una venta.</div>
        </div>

        <div class="pos-summary">
            <div class="pos-summary-row">
                <span>Subtotal</span>
                <strong id="subtotalText">S/. 0.00</strong>
            </div>
            <div class="pos-summary-row">
                <span>Descuento</span>
                <strong id="discountText">S/. 0.00</strong>
            </div>
            <div class="pos-summary-row">
                <span>Gravada</span>
                <strong id="taxableText">S/. 0.00</strong>
            </div>
            <div class="pos-summary-row">
                <span>IGV <?= $this->e(number_format((float)($companySettings['igv_percent'] ?? 18), 2)) ?>%</span>
                <strong id="igvText">S/. 0.00</strong>
            </div>
            <div class="pos-pay-grid">
                <input id="discountInput" class="pos-input" type="number" min="0" step="0.1" value="0" placeholder="Descuento S/.">
                <select id="paymentMethod" class="pos-select">
                    <option value="efectivo">Efectivo</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                    <option value="tarjeta">Tarjeta</option>
                </select>
                <input id="receivedInput" class="pos-input" type="number" min="0" step="0.1" value="0" placeholder="Recibido S/.">
                <div class="pos-input" style="background:white;color:#475569">
                    Vuelto: <strong id="changeText">S/. 0.00</strong>
                </div>
            </div>
            <div class="pos-document-box">
                <div class="pos-document-title">Comprobante</div>
                <select id="documentType" class="pos-select" style="width:100%">
                    <option value="boleta">Boleta</option>
                    <option value="factura">Factura</option>
                </select>
                <div id="invoiceFields" class="pos-document-grid hidden" style="margin-top:.65rem">
                    <input id="rucInput" class="pos-input" type="text" inputmode="numeric" maxlength="11" placeholder="RUC 11 dígitos">
                    <input id="businessNameInput" class="pos-input" type="text" placeholder="Razón social">
                </div>
            </div>
            <div class="pos-summary-row pos-total-row">
                <span>Total</span>
                <span id="totalText">S/. 0.00</span>
            </div>
            <button type="button" id="checkoutBtn" class="pos-checkout-btn" disabled>
                Completar venta
            </button>
            <div id="saleMessage" class="pos-sale-message" role="status" aria-live="polite"></div>
        </div>
    </aside>
</div>

<script>
const products = <?= json_encode($productsPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const igvPercent = <?= json_encode((float)($companySettings['igv_percent'] ?? 18)) ?>;
let cart = new Map();

const currency = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' });
const grid = document.getElementById('posGrid');
const cartItems = document.getElementById('posCartItems');
const searchInput = document.getElementById('posSearch');
const categoryInput = document.getElementById('posCategory');
const discountInput = document.getElementById('discountInput');
const receivedInput = document.getElementById('receivedInput');
const paymentMethodInput = document.getElementById('paymentMethod');
const checkoutBtn = document.getElementById('checkoutBtn');
const saleMessage = document.getElementById('saleMessage');
const documentTypeInput = document.getElementById('documentType');
const invoiceFields = document.getElementById('invoiceFields');
const rucInput = document.getElementById('rucInput');
const businessNameInput = document.getElementById('businessNameInput');

function money(value) {
    return currency.format(Number(value || 0)).replace('PEN', 'S/.');
}

function renderProducts() {
    const query = searchInput.value.trim().toLowerCase();
    const category = categoryInput.value;
    const visible = products.filter(product => {
        const matchesQuery = !query || product.name.toLowerCase().includes(query) || product.sku.toLowerCase().includes(query);
        const matchesCategory = !category || product.category === category;
        return matchesQuery && matchesCategory;
    });

    grid.innerHTML = visible.map(product => `
        <button type="button" class="pos-product" onclick="addToCart(${product.id})">
            <div class="pos-product-img">
                ${product.image_url ? `<img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.name)}">` : '<i class="bi bi-box-seam"></i>'}
            </div>
            <div class="pos-product-body">
                <div class="pos-product-name">${escapeHtml(product.name)}</div>
                <div class="pos-product-meta">${escapeHtml(product.category)} · ${escapeHtml(product.sku)}</div>
                <div class="pos-product-bottom">
                    <span class="pos-price">${money(product.price)}</span>
                    <span class="pos-stock">${product.stock} disp.</span>
                </div>
            </div>
        </button>
    `).join('') || '<div class="pos-empty">No hay productos para este filtro.</div>';
}

function refreshProductStock(items) {
    items.forEach(item => {
        const product = products.find(productItem => productItem.id === item.id);
        if (product) {
            product.stock = Math.max(product.stock - item.quantity, 0);
        }
    });
}

function addToCart(id) {
    const product = products.find(item => item.id === id);
    if (!product || product.stock <= 0) return;

    const current = cart.get(id) || { ...product, quantity: 0 };
    if (current.quantity >= product.stock) return;
    current.quantity += 1;
    cart.set(id, current);
    hideSaleMessage();
    renderCart();
}

function updateQty(id, delta) {
    const item = cart.get(id);
    if (!item) return;
    item.quantity += delta;
    if (item.quantity <= 0) cart.delete(id);
    else if (item.quantity > item.stock) item.quantity = item.stock;
    renderCart();
}

function removeItem(id) {
    cart.delete(id);
    renderCart();
}

function clearCart() {
    cart.clear();
    discountInput.value = '0';
    receivedInput.value = '0';
    documentTypeInput.value = 'boleta';
    rucInput.value = '';
    businessNameInput.value = '';
    toggleDocumentFields();
    hideSaleMessage();
    renderCart();
}

function renderCart() {
    const items = Array.from(cart.values());
    if (!items.length) {
        cartItems.innerHTML = '<div class="pos-empty">Selecciona productos para iniciar una venta.</div>';
    } else {
        cartItems.innerHTML = items.map(item => `
            <div class="pos-line">
                <div>
                    <div class="pos-line-name">${escapeHtml(item.name)}</div>
                    <div class="pos-line-sub">${escapeHtml(item.sku)} · ${money(item.price)}</div>
                    <div class="pos-qty">
                        <button type="button" onclick="updateQty(${item.id}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button type="button" onclick="updateQty(${item.id}, 1)">+</button>
                    </div>
                </div>
                <div>
                    <div class="pos-line-total">${money(item.price * item.quantity)}</div>
                    <button type="button" class="pos-remove" onclick="removeItem(${item.id})">Quitar</button>
                </div>
            </div>
        `).join('');
    }

    updateTotals();
}

function updateTotals() {
    const subtotal = Array.from(cart.values()).reduce((sum, item) => sum + item.price * item.quantity, 0);
    const discount = Math.min(Number(discountInput.value || 0), subtotal);
    const total = Math.max(subtotal - discount, 0);
    const received = Number(receivedInput.value || 0);
    const change = Math.max(received - total, 0);
    const taxable = total > 0 ? total / (1 + (igvPercent / 100)) : 0;
    const igv = Math.max(total - taxable, 0);

    document.getElementById('subtotalText').textContent = money(subtotal);
    document.getElementById('discountText').textContent = money(discount);
    document.getElementById('taxableText').textContent = money(taxable);
    document.getElementById('igvText').textContent = money(igv);
    document.getElementById('totalText').textContent = money(total);
    document.getElementById('changeText').textContent = money(change);
    checkoutBtn.disabled = cart.size === 0 || total <= 0;
}

async function finishSale() {
    const items = Array.from(cart.values());
    const subtotal = Array.from(cart.values()).reduce((sum, item) => sum + item.price * item.quantity, 0);
    const discount = Math.min(Number(discountInput.value || 0), subtotal);
    const total = Math.max(subtotal - discount, 0);
    const received = Number(receivedInput.value || 0);
    const paymentMethod = paymentMethodInput.value;

    if (!items.length || total <= 0) {
        showSaleMessage('error', '<strong>No se puede completar.</strong>Agrega al menos un producto con total mayor a cero.');
        return;
    }

    if (paymentMethod === 'efectivo' && received < total) {
        showSaleMessage('error', '<strong>Pago insuficiente.</strong>El monto recibido debe cubrir el total para pagos en efectivo.');
        receivedInput.focus();
        return;
    }

    if (documentTypeInput.value === 'factura') {
        const ruc = rucInput.value.replace(/\D+/g, '');
        if (!/^\d{11}$/.test(ruc)) {
            showSaleMessage('error', '<strong>RUC inválido.</strong>Ingresa un RUC de 11 dígitos para emitir factura.');
            rucInput.focus();
            return;
        }

        if (!businessNameInput.value.trim()) {
            showSaleMessage('error', '<strong>Razón social requerida.</strong>Ingresa la razón social para emitir factura.');
            businessNameInput.focus();
            return;
        }
    }

    checkoutBtn.disabled = true;
    checkoutBtn.textContent = 'Registrando venta...';
    hideSaleMessage();

    try {
        const response = await fetch('/admin/pos/venta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                items: items.map(item => ({ id: item.id, quantity: item.quantity })),
                discount,
                received,
                payment_method: paymentMethod,
                document_type: documentTypeInput.value,
                document_number: rucInput.value.replace(/\D+/g, ''),
                document_name: businessNameInput.value.trim(),
            }),
        });
        const data = await response.json();

        if (!response.ok || !data.ok) {
            throw new Error(data.error || 'No se pudo registrar la venta.');
        }

        const soldItems = items.map(item => ({ id: item.id, quantity: item.quantity }));
        const itemCount = soldItems.reduce((sum, item) => sum + item.quantity, 0);
        refreshProductStock(soldItems);
        cart.clear();
        discountInput.value = '0';
        receivedInput.value = '0';
        documentTypeInput.value = 'boleta';
        rucInput.value = '';
        businessNameInput.value = '';
        toggleDocumentFields();
        renderProducts();
        renderCart();
        showSaleMessage(
            'success',
            `<strong>Venta registrada: ${escapeHtml(data.order_number)}</strong>${itemCount} item(s) · ${money(data.total)} · ${escapeHtml(paymentMethodLabel(paymentMethod))}<br><a href="${escapeHtml(data.receipt_url)}" target="_blank" rel="noopener" style="color:#28135d;font-weight:800">Imprimir comprobante</a>`
        );
        window.open(data.receipt_url, '_blank', 'noopener');
    } catch (err) {
        showSaleMessage('error', `<strong>No se registró la venta.</strong>${escapeHtml(err.message)}`);
        updateTotals();
    } finally {
        checkoutBtn.textContent = 'Completar venta';
    }
}

function toggleDocumentFields() {
    invoiceFields.classList.toggle('hidden', documentTypeInput.value !== 'factura');
}

function showSaleMessage(type, html) {
    saleMessage.className = `pos-sale-message visible ${type}`;
    saleMessage.innerHTML = html;
}

function hideSaleMessage() {
    saleMessage.className = 'pos-sale-message';
    saleMessage.innerHTML = '';
}

function paymentMethodLabel(value) {
    const labels = {
        efectivo: 'Efectivo',
        yape: 'Yape',
        plin: 'Plin',
        tarjeta: 'Tarjeta',
    };
    return labels[value] || value;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

searchInput.addEventListener('input', renderProducts);
categoryInput.addEventListener('change', renderProducts);
discountInput.addEventListener('input', updateTotals);
receivedInput.addEventListener('input', updateTotals);
paymentMethodInput.addEventListener('change', updateTotals);
checkoutBtn.addEventListener('click', finishSale);
documentTypeInput.addEventListener('change', toggleDocumentFields);
rucInput.addEventListener('input', () => {
    rucInput.value = rucInput.value.replace(/\D+/g, '').slice(0, 11);
});

renderProducts();
toggleDocumentFields();
renderCart();
</script>
