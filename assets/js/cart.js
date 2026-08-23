/* =====================================================================
   Ruwanpura Gems - Enquiry cart (localStorage, no server session)
   ===================================================================== */
(function () {
    var STORAGE_KEY = 'ruwanpura_cart';

    function getCart() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            var cart = raw ? JSON.parse(raw) : [];
            return Array.isArray(cart) ? cart : [];
        } catch (e) {
            return [];
        }
    }

    function saveCart(cart) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(cart)); } catch (e) {}
    }

    function updateBadge() {
        var badge = document.getElementById('cartCount');
        if (!badge) return;
        var count = getCart().length;
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }

    function addToCart(item) {
        var cart = getCart();
        if (cart.some(function (i) { return i.id === item.id; })) return false;
        cart.push(item);
        saveCart(cart);
        updateBadge();
        return true;
    }

    function removeFromCart(id) {
        saveCart(getCart().filter(function (i) { return i.id !== id; }));
        updateBadge();
    }

    window.RuwanpuraCart = { get: getCart, add: addToCart, remove: removeFromCart, updateBadge: updateBadge };

    document.addEventListener('DOMContentLoaded', function () {
        updateBadge();

        /* Add to Enquiry Cart buttons (product detail / catalogue cards) */
        document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (btn.disabled) return;
                var item = {
                    id: parseInt(btn.getAttribute('data-id'), 10),
                    name: btn.getAttribute('data-name') || '',
                    weight: btn.getAttribute('data-weight') || '',
                    shape: btn.getAttribute('data-shape') || '',
                    image: btn.getAttribute('data-image') || ''
                };
                var added = addToCart(item);
                var original = btn.textContent;
                btn.textContent = added ? 'Added ✓' : 'Already in Cart';
                setTimeout(function () { btn.textContent = original; }, 1600);
            });
        });

        /* Cart page rendering */
        var itemsWrap = document.getElementById('cartItems');
        if (!itemsWrap) return;

        function render() {
            var cart = getCart();
            var empty = document.getElementById('cartEmpty');
            var wrap = document.getElementById('cartWrap');

            if (!cart.length) {
                if (empty) empty.style.display = 'block';
                if (wrap) wrap.style.display = 'none';
                return;
            }
            if (empty) empty.style.display = 'none';
            if (wrap) wrap.style.display = 'block';

            itemsWrap.innerHTML = '';
            cart.forEach(function (item) {
                var row = document.createElement('div');
                row.className = 'cart-item';
                row.innerHTML =
                    '<div class="cart-item-img">' + (item.image ? '<img src="' + item.image + '" alt="">' : '') + '</div>' +
                    '<div class="cart-item-info"><h4></h4><p></p></div>' +
                    '<button type="button" class="cart-item-remove" aria-label="Remove">&times;</button>';
                row.querySelector('h4').textContent = item.name || 'Gemstone';
                var meta = [];
                if (item.weight) meta.push(item.weight + ' ct');
                if (item.shape) meta.push(item.shape);
                row.querySelector('p').textContent = meta.join(' · ');
                row.querySelector('.cart-item-remove').addEventListener('click', function () {
                    removeFromCart(item.id);
                    render();
                });
                itemsWrap.appendChild(row);
            });

            var input = document.getElementById('cartDataInput');
            if (input) input.value = JSON.stringify(cart);
        }

        render();

        var form = document.getElementById('enquiryForm');
        if (form) {
            form.addEventListener('submit', function () {
                var input = document.getElementById('cartDataInput');
                if (input) input.value = JSON.stringify(getCart());
            });
        }
    });
})();
