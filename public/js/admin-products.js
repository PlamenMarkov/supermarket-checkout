var AdminProducts = (() => {
    let elements = {
        productsTbody: null,
        productModal: null,
        productForm: null,
        productModalTitle: null,
        productModalClose: null,
        addProductBtn: null,
    };
    let editingProductId = null;

    async function loadProducts() {
        if (!elements.productsTbody) return;
        try {
            const products = await Api.listProducts();
            const rows = (products || []).map(p => {
                const promos = Array.isArray(p.promotions) ? p.promotions : [];
                const promosText = promos.length ? promos.map(pr => {
                    if (pr.type === 'n_for_price') {
                        return `${pr.n_qty} for ${Common.currencyFormat(pr.special_price_cents, null)}`;
                    }
                    return pr.type;
                }).join(', ') : '—';
                return `
                    <tr>
                      <td>${p.id}</td>
                      <td>${p.sku}</td>
                      <td>${p.name}</td>
                      <td class="num">${Common.currencyFormat(p.unit_price_cents, null)}</td>
                      <td>${promosText}</td>
                      <td>
                        <button class="btn" data-edit-id="${p.id}">Update</button>
                        <button class="btn" data-delete-id="${p.id}">Remove</button>
                      </td>
                    </tr>
                  `;
            }).join('');
            elements.productsTbody.innerHTML = rows || '<tr><td colspan="6">No products</td></tr>';

            document.querySelectorAll('[data-edit-id]').forEach(btn => btn.addEventListener('click', () => openProductModal(btn.getAttribute('data-edit-id'))));
            document.querySelectorAll('[data-delete-id]').forEach(btn => btn.addEventListener('click', async () => {
                if (!confirm('Delete this product?')) return;

                const id = btn.getAttribute('data-delete-id');

                try {
                    await Api.deleteProduct(id);
                    await loadProducts();
                } catch (e) {
                    alert(e.message);
                }
            }));
        } catch (err) {
            alert('Failed to load products: ' + (err.message || err));
        }
    }

    function openProductModal(id) {
        editingProductId = id || null;
        elements.productForm.reset();
        if (editingProductId) {
            elements.productModalTitle.textContent = 'Update Product';
            const promoFields = document.getElementById('promo_fields');
            const promoTypeSel = document.getElementById('promo_type_select');
            Api.getProduct(editingProductId).then(p => {
                elements.productForm.sku.value = p.sku || '';
                elements.productForm.name.value = p.name || '';
                elements.productForm.unit_price_cents.value = p.unit_price_cents || 0;
                elements.productModal.style.display = 'block';
                if (p.promotions.length > 0) {
                    promoFields.style.display = 'block';
                    promoTypeSel.value = 'n_for_price';
                    const promotion = p.promotions.pop();
                    elements.productForm.promo_n_qty_input.value = promotion.n_qty;
                    elements.productForm.promo_price_cents_input.value = promotion.special_price_cents;
                } else {
                    promoFields.style.display = 'none';
                }

            }).catch(e => alert(e.message));
        } else {
            elements.productModalTitle.textContent = 'Add Product';
            elements.productModal.style.display = 'block';
        }
    }

    function closeProductModal() {
        if (elements.productModal) elements.productModal.style.display = 'none';
    }

    function wire() {
        if (elements.addProductBtn) elements.addProductBtn.addEventListener('click', () => openProductModal());
        if (elements.productModalClose) elements.productModalClose.addEventListener('click', closeProductModal);
        if (elements.productModal) elements.productModal.addEventListener('click', (e) => {
            if (e.target === elements.productModal) closeProductModal();
        });

        const promoTypeSel = document.getElementById('promo_type_select');
        const promoFields = document.getElementById('promo_fields');
        if (promoTypeSel && promoFields) {
            promoTypeSel.addEventListener('change', () => {
                promoFields.style.display = promoTypeSel.value === 'n_for_price' ? 'block' : 'none';
            });
        }

        if (elements.productForm) {
            elements.productForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const payload = {
                    sku: elements.productForm.sku.value.trim(),
                    name: elements.productForm.name.value.trim(),
                    unit_price_cents: parseInt(elements.productForm.unit_price_cents.value, 10) || 0,
                };
                try {
                    let productId;
                    if (editingProductId) {
                        await Api.updateProduct(editingProductId, payload);
                        productId = editingProductId;
                    } else {
                        const created = await Api.createProduct(payload);
                        productId = created && created.id;
                    }

                    const typeSel = document.getElementById('promo_type_select');
                    if (productId && typeSel && typeSel.value === 'n_for_price') {
                        const nQty = parseInt(document.getElementById('promo_n_qty_input').value, 10) || 1;
                        const special = parseInt(document.getElementById('promo_price_cents_input').value, 10) || 0;
                        await Api.createPromotion({
                            product_id: productId,
                            type: 'n_for_price',
                            n_qty: nQty,
                            special_price_cents: special
                        });
                    }

                    closeProductModal();

                    await loadProducts();
                } catch (err) {
                    alert(err.message + '. ' + Object.values(err?.body ?? {}).flat().join(', '));
                }
            });
        }
    }

    function init(options) {
        const opts = options || {};
        elements.productsTbody = document.getElementById(opts.tbodyId || 'products-tbody');
        elements.productModal = document.getElementById(opts.modalId || 'product-modal');
        elements.productForm = document.getElementById(opts.formId || 'product-form');
        elements.productModalTitle = document.getElementById(opts.modalTitleId || 'product-modal-title');
        elements.productModalClose = document.getElementById(opts.modalCloseId || 'product-modal-close');
        elements.addProductBtn = document.getElementById(opts.addBtnId || 'add-product-btn');

        wire();

        return loadProducts();
    }

    return {
        init,
        reload: loadProducts,
        openModal: openProductModal,
        closeModal: closeProductModal,
    };
})();
