/* =====================================================================
   Ruwanpura Gems - Checkout page behaviour
   ===================================================================== */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var empty = document.getElementById('checkoutEmpty');
        var wrap  = document.getElementById('checkoutWrap');
        if (!empty || !wrap || !window.RuwanpuraCart) return;

        function renderSummary() {
            var cart = RuwanpuraCart.get();
            if (!cart.length) {
                empty.style.display = 'block';
                wrap.style.display  = 'none';
                return;
            }
            empty.style.display = 'none';
            wrap.style.display  = 'grid';

            RuwanpuraCart.renderList('#cartItems', {
                removable: false,
                onChange: function (items) {
                    var subtotalEl = document.getElementById('cartSubtotal');
                    if (subtotalEl) {
                        var total = items.reduce(function (sum, it) { return sum + (it.line_total || 0); }, 0);
                        subtotalEl.textContent = '$' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    }
                    var input = document.getElementById('cartDataInput');
                    if (input) input.value = JSON.stringify(items.map(function (it) { return { id: it.id, qty: it.qty }; }));

                    if (!items.length) {
                        empty.style.display = 'block';
                        wrap.style.display  = 'none';
                    }
                }
            });
        }

        renderSummary();

        /* Login / Register tab toggle */
        document.querySelectorAll('.account-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.account-tab').forEach(function (t) { t.classList.remove('active'); });
                document.querySelectorAll('.account-tab-panel').forEach(function (p) { p.classList.remove('active'); });
                tab.classList.add('active');
                var panel = document.getElementById('tab-' + tab.getAttribute('data-tab'));
                if (panel) panel.classList.add('active');
            });
        });

        /* Populate cart_data right before the order is placed, in case the
           background summary fetch hasn't resolved yet */
        var placeOrderForm = document.getElementById('placeOrderForm');
        if (placeOrderForm) {
            placeOrderForm.addEventListener('submit', function (e) {
                var input = document.getElementById('cartDataInput');
                var cartItems = RuwanpuraCart.get();
                if (input && (!input.value || input.value === '[]')) {
                    input.value = JSON.stringify(cartItems.map(function (i) { return { id: i.id, qty: i.qty }; }));
                }
                if (!cartItems.length) {
                    e.preventDefault();
                    alert('Your cart is empty.');
                }
            });
        }
    });
})();
