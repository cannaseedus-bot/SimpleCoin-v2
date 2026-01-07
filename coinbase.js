// Helpers for Coinbase checkout metadata (order IDs and email capture)

function generateOrderId() {
    var existing = sessionStorage.getItem('current-order-id');
    if (existing) {
        return existing;
    }
    var newId = 'order-' + Date.now() + '-' + Math.floor(Math.random() * 1e6);
    sessionStorage.setItem('current-order-id', newId);
    return newId;
}

function getCheckoutEmail() {
    var stored = sessionStorage.getItem('checkout-email');
    if (stored) {
        return stored;
    }

    var input = document.getElementById('checkoutEmail');
    if (input && input.value) {
        var trimmed = input.value.trim();
        sessionStorage.setItem('checkout-email', trimmed);
        return trimmed;
    }

    return '';
}
