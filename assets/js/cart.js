/* =====================================================================
   Ruwanpura Gems - Cart (localStorage ids + quantities, server-priced)
   ===================================================================== */
(function () {
    var STORAGE_KEY = 'ruwanpura_cart';

    function getCart() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            var cart = raw ? JSON.parse(raw) : [];
            if (!Array.isArray(cart)) return [];
            // Normalize older/partial entries so every item always has a numeric qty >= 1
            return cart.map(function (i) {
                var qty = parseInt(i.qty, 10);
                return Object.assign({}, i, { qty: (qty > 0 ? qty : 1) });
            });
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
        var count = getCart().reduce(function (sum, i) { return sum + i.qty; }, 0);
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }

    /* item: {id, name, weight, shape, image, qty}. If already in the cart, increases
       quantity instead of no-op'ing (capped at item.maxQty when provided). */
    function addToCart(item) {
        var cart = getCart();
        var qtyToAdd = parseInt(item.qty, 10) > 0 ? parseInt(item.qty, 10) : 1;
        var maxQty = parseInt(item.maxQty, 10) > 0 ? parseInt(item.maxQty, 10) : null;
        var existing = cart.filter(function (i) { return i.id === item.id; })[0];

        if (existing) {
            var newQty = existing.qty + qtyToAdd;
            if (maxQty) newQty = Math.min(newQty, maxQty);
            if (newQty === existing.qty) return false; // already at max
            existing.qty = newQty;
        } else {
            var toAdd = Object.assign({}, item);
            delete toAdd.maxQty;
            toAdd.qty = maxQty ? Math.min(qtyToAdd, maxQty) : qtyToAdd;
            cart.push(toAdd);
        }
        saveCart(cart);
        updateBadge();
        return true;
    }

    function removeFromCart(id) {
        saveCart(getCart().filter(function (i) { return i.id !== id; }));
        updateBadge();
    }

    /* Set an item's quantity directly (removes the line if qty <= 0) */
    function updateQty(id, qty) {
        qty = parseInt(qty, 10);
        var cart = getCart();
        if (qty <= 0) {
            cart = cart.filter(function (i) { return i.id !== id; });
        } else {
            cart = cart.map(function (i) { return i.id === id ? Object.assign({}, i, { qty: qty }) : i; });
        }
        saveCart(cart);
        updateBadge();
    }

    /* Fetch server-computed pricing/availability/stock for the given product ids.
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
       options.removable: show a remove (x) button + quantity stepper per row.
       options.onChange(items): called after each render with the server item list
       (each item carries the cart's qty merged in as item.qty, clamped to stock). */
    function renderList(containerSelector, options) {
        options = options || {};
        var container = document.querySelector(containerSelector);
        if (!container) return;

        var cart = getCart();
        var ids = cart.map(function (i) { return i.id; });
        fetchPricing(ids, function (serverItems) {
            var cartById = {};
            cart.forEach(function (i) { cartById[i.id] = i; });

            // Auto-prune anything the server says is gone/unavailable, and clamp
            // quantity down to current stock if it dropped since the item was added.
            var stillGood = [];
            var clampedAny = false;
            var removedAny = false;
            serverItems.forEach(function (it) {
                if (!it.found || !it.available) { removedAny = true; return; }
                var qty = cartById[it.id] ? cartById[it.id].qty : 1;
                var stock = it.stock > 0 ? it.stock : 1;
                if (qty > stock) { qty = stock; clampedAny = true; }
                it.qty = qty;
                it.line_total = (it.final_price || 0) * qty;
                stillGood.push(it);
            });
            if (removedAny || clampedAny) {
                var updated = stillGood.map(function (it) { return { id: it.id, name: it.name, weight: it.weight, shape: it.shape, image: it.image, qty: it.qty }; });
                saveCart(updated);
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

                var infoEl = row.querySelector('.cart-item-info');
                if (options.removable && item.stock > 1) {
                    var qtyWrap = document.createElement('div');
                    qtyWrap.className = 'cart-item-qty';
                    qtyWrap.innerHTML =
                        '<button type="button" class="qty-minus" aria-label="Decrease quantity">&minus;</button>' +
                        '<span>' + item.qty + '</span>' +
                        '<button type="button" class="qty-plus" aria-label="Increase quantity">+</button>' +
                        '<span class="qty-max-note">(' + item.stock + ' in stock)</span>';
                    qtyWrap.querySelector('.qty-minus').addEventListener('click', function () {
                        updateQty(item.id, item.qty - 1);
                        renderList(containerSelector, options);
                    });
                    qtyWrap.querySelector('.qty-plus').addEventListener('click', function () {
                        updateQty(item.id, Math.min(item.qty + 1, item.stock));
                        renderList(containerSelector, options);
                    });
                    infoEl.appendChild(qtyWrap);
                } else if (item.qty > 1) {
                    var qtyNote = document.createElement('p');
                    qtyNote.className = 'cart-item-meta';
                    qtyNote.textContent = 'Qty: ' + item.qty;
                    infoEl.appendChild(qtyNote);
                }

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

    window.RuwanpuraCart = {
        get: getCart, add: addToCart, remove: removeFromCart, updateQty: updateQty,
        updateBadge: updateBadge, renderList: renderList, fetchPricing: fetchPricing
    };

    document.addEventListener('DOMContentLoaded', function () {
        updateBadge();

        /* Add to Cart buttons (product detail / catalogue cards) */
        document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (btn.disabled) return;

                var maxQty = parseInt(btn.getAttribute('data-max-qty'), 10) || 1;
                var qty = 1;
                var qtyInputId = btn.getAttribute('data-qty-input');
                if (qtyInputId) {
                    var qtyInput = document.getElementById(qtyInputId);
                    if (qtyInput) {
                        qty = parseInt(qtyInput.value, 10) || 1;
                        qty = Math.max(1, Math.min(qty, maxQty));
                    }
                }

                var item = {
                    id: parseInt(btn.getAttribute('data-id'), 10),
                    name: btn.getAttribute('data-name') || '',
                    weight: btn.getAttribute('data-weight') || '',
                    shape: btn.getAttribute('data-shape') || '',
                    image: btn.getAttribute('data-image') || '',
                    qty: qty,
                    maxQty: maxQty
                };
                var added = addToCart(item);
                var original = btn.textContent;
                btn.textContent = added ? 'Added ✓' : 'Already at max quantity';
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
                        var total = items.reduce(function (sum, it) { return sum + (it.line_total || 0); }, 0);
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
