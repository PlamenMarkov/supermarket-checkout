var Home = (() => {
    let elements = {
        form: null,
        input: null,
        result: null,
        errorBox: null,
    };

    function renderResult(order) {
        if (!elements.result) return;
        if (!order) {
            elements.result.innerHTML = '';
            return;
        }

        const currency = order.currency;

        const itemsRows = (order.items || []).map(i => {
            return `
                <tr>
                  <td>${i.sku}</td>
                  <td>${i.product_name ?? ''}</td>
                  <td class="num">${i.qty}</td>
                  <td class="num">${Common.currencyFormat(i.unit_price_cents, i.currency)}</td>
                  <td class="num">${i.bundle_count ?? 0}</td>
                  <td class="num">${Common.currencyFormat(i.bundle_price_cents, i.currency)}</td>
                  <td class="num">${Common.currencyFormat(i.line_total_cents, i.currency)}</td>
                  <td>${i.currency}</td>
                </tr>
            `;
        }).join('');

        elements.result.innerHTML = `
          <div class="results">
            <h3>Order #${order.id} (${order.status})</h3>
            <table class="results-table">
              <thead>
                <tr>
                  <th>SKU</th>
                  <th>Product</th>
                  <th class="num">Qty</th>
                  <th class="num">Unit</th>
                  <th class="num">Bundles</th>
                  <th class="num">Bundle Price</th>
                  <th class="num">Line Total</th>
                  <th>Currency</th>
                </tr>
              </thead>
              <tbody>
                ${itemsRows}
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="6" class="num">Total</td>
                  <td class="num">${Common.currencyFormat(order.total_cents, currency)}</td>
                  <td>${currency}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        `;
    }

    function showError(message) {
        if (!elements.errorBox) return;
        elements.errorBox.style.display = 'block';
        elements.errorBox.textContent = message;
    }

    function clearError() {
        if (!elements.errorBox) return;
        elements.errorBox.style.display = 'none';
        elements.errorBox.textContent = '';
    }

    function wire() {
        if (!elements.form) return;
        elements.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearError();
            if (elements.result) elements.result.innerHTML = '';

            const skus = (elements.input ? elements.input.value : '').trim();

            elements.input.value = '';

            try {
                const order = await Api.createCheckoutOrder({skus});
                renderResult(order);
            } catch (err) {
                showError((err && err.message ? err.message : 'Request failed') + '. ' + Object.values(err?.body ?? {}).flat().join(', '));
            }
        });
    }

    function init() {
        elements.form = document.getElementById('checkout-form');
        elements.input = document.getElementById('skus');
        elements.result = document.getElementById('result');
        elements.errorBox = document.getElementById('error');

        wire();
    }

    return {
        init,
    };
})();