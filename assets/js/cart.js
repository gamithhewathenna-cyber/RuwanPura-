/* =====================================================================
   Ruwanpura Gems - Cart (localStorage ids, server-priced)
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

    /* Fetch server-computed pricing/availability for the given product ids.
       Price is never trusted from localStorage — this is the single source of truth. */
    function fetchPricing(ids, cb) {
        if (!ids.length) { cb([]); return; }
        fetch('cart-data.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids })
        }).then(function (r) { return r.json(); })
          .then(function (data) { cb(data.items || []); })
          .catch(function () { cb([]); });
    }

    /* Render a cart-item list (used by both cart.php and checkout.php's summary).
       options.removable: show a remove (x) button per row.
       options.onChange(items): called after each render with the server item list. */
    function renderList(containerSelector, options) {
        options = options || {};
        var container = document.querySelector(containerSelector);
        if (!container) return;

        var ids = getCart().map(function (i) { return i.id; });
        fetchPricing(ids, function (items) {
            // Auto-prune anything the server says is gone/unavailable
            var stillGood = items.filter(function (it) { return it.found && it.available; });
            var goodIds = stillGood.map(function (it) { return it.id; });
            var removedAny = items.some(function (it) { return !it.found || !it.available; });
            if (removedAny) {
                saveCart(getCart().filter(function (i) { return goodIds.indexOf(i.id) !== -1; }));
                updateBadge();
            }

            container.innerHTML = '';
            stillGood.forEach(function (item) {
                var row = document.createElement('div');
                row.className = 'cart-item';
                var priceHtml = '';
                if (item.has_discount) {
                    priceHtml = '<span class="price-was">' + item.price_display + '</span> <span class="price-now">' + item.final_display + '</span>';
                } else if (item.final_display) {
                    priceHtml = '<span class="price-now">' + item.final_display + '</span>';
                }
                row.innerHTML =
                    '<div class="cart-item-img">' + (item.image ? '<img src="' + item.image + '" alt="">' : '') + '</div>' +
                    '<div class="cart-item-info"><h4></h4><p class="cart-item-meta"></p><p class="cart-item-price">' + priceHtml + '</p></div>' +
                    (options.removable ? '<button type="button" class="cart-item-remove" aria-label="Remove">&times;</button>' : '');
                row.querySelector('h4').textContent = item.name || 'Gemstone';
                var meta = [];
                if (item.weight) meta.push(item.weight + ' ct');
                if (item.shape) meta.push(item.shape);
                row.querySelector('.cart-item-meta').textContent = meta.join(' · ');
                if (options.removable) {
                    row.querySelector('.cart-item-remove').addEventListener('click', function () {
                        removeFromCart(item.id);
                        renderList(containerSelector, options);
                    });
                }
                container.appendChild(row);
            });

            if (options.onChange) options.onChange(stillGood);
        });
    }

    window.RuwanpuraCart = { get: getCart, add: addToCart, remove: removeFromCart, updateBadge: updateBadge, renderList: renderList, fetchPricing: fetchPricing };

    document.addEventListener('DOMContentLoaded', function () {
        updateBadge();

        /* Add to Cart buttons (product detail / catalogue cards) */
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

        /* Cart page rendering (cart.php only — checkout.php has its own
           #checkoutEmpty/#checkoutWrap ids and renders itself via checkout.js) */
        var empty = document.getElementById('cartEmpty');
        var wrap  = document.getElementById('cartWrap');
        if (!empty || !wrap) return;

        function render() {
            var cart = getCart();

            if (!cart.length) {
                if (empty) empty.style.display = 'block';
                if (wrap) wrap.style.display = 'none';
                return;
            }
            if (empty) empty.style.display = 'none';
            if (wrap) wrap.style.display = 'grid';

            renderList('#cartItems', {
                removable: true,
                onChange: function (items) {
                    var subtotalEl = document.getElementById('cartSubtotal');
                    if (subtotalEl) {
                        var total = items.reduce(function (sum, it) { return sum + (it.final_price || 0); }, 0);
                        subtotalEl.textContent = '$' + total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    }
                    if (!items.length) {
                        if (empty) empty.style.display = 'block';
                        if (wrap) wrap.style.display = 'none';
                    }
                }
            });
        }

        render();
    });
})();
