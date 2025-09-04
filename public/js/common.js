var Common = (() => {
    function currencyFormat(cents, currency = null) {
        const amount = (cents || 0) / 100;
        return amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + (currency != null ? (' ' + currency) : '');
    }

    return {
        currencyFormat
    }
})();