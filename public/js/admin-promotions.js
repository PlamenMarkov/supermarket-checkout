var AdminPromotions = (() => {
    let elements = {
        tbody: null,
        modal: null,
        form: null,
        title: null,
        closeBtn: null,
        addBtn: null,
        productSelect: null,
        typeSelect: null,
        nQty: null,
        special: null,
    };

    let editingId = null;

    async function loadProductsOptions(selectedId) {
        const products = await Api.listProducts();
        elements.productSelect.innerHTML = (products || []).map(p => `
            <option value="${p.id}" ${selectedId && p.id === selectedId ? 'selected' : ''}>${p.sku} — ${p.name}</option>
        `).join('');
    }

    async function loadPromotions() {
        const promos = await Api.listPromotions();
        const rows = (promos || []).map(pr => {
            const prod = pr.product;
            return `
                <tr>
                  <td>${pr.id}</td>
                  <td>${prod ? (prod.sku + ' — ' + prod.name) : ''}</td>
                  <td>${pr.type}</td>
                  <td class="num">${pr.n_qty}</td>
                  <td class="num">${Common.currencyFormat(pr.special_price_cents, prod.currency)}</td>
                  <td>
                    <button class="btn" data-edit-id="${pr.id}">Update</button>
                    <button class="btn" data-delete-id="${pr.id}">Remove</button>
                  </td>
                </tr>
              `;
        }).join('');

        elements.tbody.innerHTML = rows || '<tr><td colspan="6">No promotions</td></tr>';

        document.querySelectorAll('[data-edit-id]').forEach(btn => btn.addEventListener('click', () => openModal(btn.getAttribute('data-edit-id'))));
        document.querySelectorAll('[data-delete-id]').forEach(btn => btn.addEventListener('click', async () => {
            if (!confirm('Delete this promotion?')) return;
            const id = btn.getAttribute('data-delete-id');
            try {
                await Api.deletePromotion(id);
                await loadPromotions();
            } catch (e) {
                alert(e.message);
            }
        }));
    }

    function openModal(id) {
        editingId = id ? parseInt(id, 10) : null;
        elements.form.reset();
        if (editingId) {
            elements.title.textContent = 'Update Promotion';
            Api.getPromotion(editingId).then(async pr => {
                await loadProductsOptions(pr.product?.id);
                elements.typeSelect.value = pr.type || 'n_for_price';
                elements.nQty.value = pr.n_qty || 1;
                elements.special.value = pr.special_price_cents || 0;
                elements.modal.style.display = 'block';
            }).catch(e => alert(e.message));
        } else {
            loadProductsOptions();

            elements.title.textContent = 'Add Promotion';
            elements.typeSelect.value = 'n_for_price';
            elements.nQty.value = 1;
            elements.special.value = 0;
            elements.modal.style.display = 'block';
        }
    }

    function closeModal() {
        if (elements.modal) elements.modal.style.display = 'none';
    }

    function wire() {
        if (elements.addBtn) elements.addBtn.addEventListener('click', () => openModal());
        if (elements.closeBtn) elements.closeBtn.addEventListener('click', closeModal);
        if (elements.modal) elements.modal.addEventListener('click', (e) => {
            if (e.target === elements.modal) closeModal();
        });

        if (elements.form) elements.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                product_id: parseInt(elements.productSelect.value, 10),
                type: elements.typeSelect.value,
                n_qty: parseInt(elements.nQty.value, 10) || 1,
                special_price_cents: parseInt(elements.special.value, 10) || 0,
            };
            try {
                if (editingId) await Api.updatePromotion(editingId, payload);
                else await Api.createPromotion(payload);
                closeModal();
                await loadPromotions();
            } catch (err) {
                alert(err.message + '. ' + Object.values(err?.body ?? {}).flat().join(', '));
            }
        });
    }

    async function init() {
        elements.tbody = document.getElementById('promotions-tbody');
        elements.modal = document.getElementById('promo-modal');
        elements.form = document.getElementById('promo-form');
        elements.title = document.getElementById('promo-modal-title');
        elements.closeBtn = document.getElementById('promo-modal-close');
        elements.addBtn = document.getElementById('add-promo-btn');
        elements.productSelect = document.getElementById('promo_product');
        elements.typeSelect = document.getElementById('promo_type');
        elements.nQty = document.getElementById('promo_n_qty');
        elements.special = document.getElementById('promo_special');

        wire();

        await loadPromotions();
    }

    return {init, reload: loadPromotions};
})();