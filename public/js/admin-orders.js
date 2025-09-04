var AdminOrders = (() => {
    let ordersTbody;

    async function loadOrders() {
        if (!ordersTbody) return;
        try {
            const orders = await Api.listOrders();
            const rows = (orders || []).map(o => {
                const itemsCount = (o.items || []).reduce((s, i) => s + (i.qty || 0), 0);
                const statusSelect = `
                  <select data-order-id="${o.id}" class="status-select">
                    <option value="created" ${o.status === 'created' ? 'selected' : ''}>created</option>
                    <option value="completed" ${o.status === 'completed' ? 'selected' : ''}>completed</option>
                    <option value="cancelled" ${o.status === 'cancelled' ? 'selected' : ''}>cancelled</option>
                  </select>
                `;
                return `
                  <tr>
                    <td>${o.id}</td>
                    <td>${new Date(o.created_at).toLocaleString()}</td>
                    <td class="num">${itemsCount}</td>
                    <td class="num">${Common.currencyFormat(o.total_cents, o.currency)}</td>
                    <td>${statusSelect}</td>
                  </tr>
                `;
            }).join('');

            ordersTbody.innerHTML = rows || '<tr><td colspan="6">No orders yet</td></tr>';

            document.querySelectorAll('.status-select').forEach(sel => {
                sel.addEventListener('change', async () => {
                    const id = sel.getAttribute('data-order-id');
                    const val = sel.value;
                    try {
                        if (val === 'completed') await Api.completeOrder(id);
                        else if (val === 'cancelled') await Api.cancelOrder(id);
                        else {
                            await Api.getOrder(id);
                            alert('Cannot set status back to created.');
                        }
                        await loadOrders();
                    } catch (err) {
                        alert(err.message);
                    }
                });
            });
        } catch (err) {
            alert('Failed to load orders: ' + (err.message || err));
        }
    }

    function init(options) {
        const opts = options || {};
        ordersTbody = document.getElementById(opts.tbodyId || 'orders-tbody');
        return loadOrders();
    }

    return {
        init,
        reload: loadOrders,
    };
})();
