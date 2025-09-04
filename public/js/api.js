var Api = (() => {
    const DEFAULT_HEADERS = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    async function sendRequest(url, options = {}) {
        const {json, headers, ...rest} = options;
        const init = {
            ...rest,
            headers: {...DEFAULT_HEADERS, ...(headers || {})},
        };
        if (json !== undefined) {
            init.body = JSON.stringify(json);
        }

        const res = await fetch(url, init);
        if (res.status === 204) return null;

        let data;
        const text = await res.text();
        try {
            data = text ? JSON.parse(text) : null;
        } catch (e) {
            data = text;
        }

        if (!res.ok) {
            const err = new Error(
                `Request failed ${res.status} ${res.statusText}.`
            );
            err.status = res.status;
            err.statusText = res.statusText;
            err.url = url;
            err.body = data;
            throw err;
        }

        return data;
    }

    function listProducts() {
        return sendRequest('/api/admin/products', {method: 'GET'});
    }

    function getProduct(id) {
        return sendRequest(`/api/admin/products/${id}`, {method: 'GET'});
    }

    function createProduct(payload) {
        return sendRequest('/api/admin/products', {method: 'POST', json: payload});
    }

    function updateProduct(id, payload) {
        return sendRequest(`/api/admin/products/${id}`, {method: 'PUT', json: payload});
    }

    function deleteProduct(id) {
        return sendRequest(`/api/admin/products/${id}`, {method: 'DELETE'});
    }

    function listOrders() {
        return sendRequest('/api/admin/orders', {method: 'GET'});
    }

    function getOrder(id) {
        return sendRequest(`/api/admin/orders/${id}`, {method: 'GET'});
    }

    function completeOrder(id) {
        return sendRequest(`/api/admin/orders/${id}/complete`, {method: 'POST'});
    }

    function cancelOrder(id) {
        return sendRequest(`/api/admin/orders/${id}/cancel`, {method: 'POST'});
    }

    function createCheckoutOrder(payload) {
        return sendRequest('/api/checkout/orders', {method: 'POST', json: payload});
    }

    function listPromotions() {
        return sendRequest('/api/admin/promotions', {method: 'GET'});
    }

    function getPromotion(id) {
        return sendRequest(`/api/admin/promotions/${id}`, {method: 'GET'});
    }

    function createPromotion(payload) {
        return sendRequest('/api/admin/promotions', {method: 'POST', json: payload});
    }

    function updatePromotion(id, payload) {
        return sendRequest(`/api/admin/promotions/${id}`, {method: 'PUT', json: payload});
    }

    function deletePromotion(id) {
        return sendRequest(`/api/admin/promotions/${id}`, {method: 'DELETE'});
    }


    return {
        listProducts,
        getProduct,
        createProduct,
        updateProduct,
        deleteProduct,
        listOrders,
        getOrder,
        completeOrder,
        cancelOrder,
        createCheckoutOrder,
        listPromotions,
        getPromotion,
        createPromotion,
        updatePromotion,
        deletePromotion,
    };
})();
