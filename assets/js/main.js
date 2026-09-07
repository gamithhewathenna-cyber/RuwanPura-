/* =====================================================================
   Ruwanpura Gems - Front-end interactions
   ===================================================================== */

/* Fallback page fade-in (only relevant where @view-transition isn't supported —
   see style.css). Runs immediately rather than waiting on DOMContentLoaded,
   since this script sits at the end of <body> and the element already exists
   by the time it executes — keeps the invisible flash as short as possible. */
document.body.classList.add('page-loaded');

document.addEventListener('DOMContentLoaded', function () {

    /* ---- Mobile menu toggle ---- */
    var toggle = document.querySelector('.menu-toggle');
    var menu = document.querySelector('.nav-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            menu.classList.toggle('open');
        });
        menu.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () { menu.classList.remove('open'); });
        });
    }

    /* ---- Transparent header over the hero video (home page only) ----
       Starts transparent/overlaid on the video; switches to the normal solid
       sticky header once the user scrolls past the hero, so nav stays usable
       for the rest of the (long) home page. */
    (function () {
        var header = document.querySelector('.site-header--transparent');
        var hero = document.querySelector('.hero');
        if (!header || !hero) return;

        function update() {
            var threshold = hero.offsetHeight - header.offsetHeight;
            header.classList.toggle('is-scrolled', window.scrollY > threshold);
        }
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    })();

    /* Collection ("Explore Our Gemstones") is a pure CSS infinite marquee now — see .collection-track in style.css */

    /* ---- Testimonials slider (auto-advances, loops back to the start) ---- */
    (function () {
        var track = document.querySelector('.testi-track');
        if (!track) return;
        var prev = document.querySelector('.testi-nav .prev');
        var next = document.querySelector('.testi-nav .next');
        var bar = document.querySelector('.testi-progress .bar');
        var timer;

        function step() {
            var card = track.querySelector('.testi-card');
            return card ? card.offsetWidth + 24 : 320;
        }
        function updateBar() {
            var max = track.scrollWidth - track.clientWidth;
            var pct = max > 0 ? (track.scrollLeft / max) * 100 : 0;
            if (bar) bar.style.width = Math.max(15, pct) + '%';
        }
        function atEnd() {
            return track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
        }
        function goNext() {
            if (atEnd()) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: step(), behavior: 'smooth' });
            }
        }
        function goPrev() {
            track.scrollBy({ left: -step(), behavior: 'smooth' });
        }
        function restartAuto() {
            clearInterval(timer);
            if (track.scrollWidth - track.clientWidth <= 0) return; // nothing to scroll
            timer = setInterval(goNext, 5000);
        }

        if (next) next.addEventListener('click', function () { goNext(); restartAuto(); });
        if (prev) prev.addEventListener('click', function () { goPrev(); restartAuto(); });

        track.addEventListener('scroll', updateBar);
        updateBar();
        restartAuto();
    })();

    /* ---- Gemstone search-as-you-type (catalogue page only) ---- */
    (function () {
        var input = document.getElementById('gemSearchInput');
        var panel = document.getElementById('gemSearchResults');
        if (!input || !panel) return;

        var debounceTimer;
        var requestSeq = 0;

        function closePanel() {
            // Only hide it — don't clear the markup here. If this fires while a tap
            // on a result link is still being processed (timing varies by browser),
            // wiping the DOM out from under that link could stop the navigation.
            panel.classList.remove('open');
        }

        function renderResults(items) {
            panel.innerHTML = '';
            if (!items.length) {
                var empty = document.createElement('div');
                empty.className = 'search-empty';
                empty.textContent = 'No gemstones found';
                panel.appendChild(empty);
            } else {
                items.forEach(function (item) {
                    var a = document.createElement('a');
                    a.href = item.url;
                    a.className = 'search-result-item';

                    var thumb = document.createElement('span');
                    thumb.className = 'search-result-thumb';
                    if (item.thumb) {
                        var img = document.createElement('img');
                        img.src = item.thumb;
                        img.alt = '';
                        thumb.appendChild(img);
                    }

                    var name = document.createElement('span');
                    name.className = 'search-result-name';
                    name.textContent = item.name;

                    a.appendChild(thumb);
                    a.appendChild(name);
                    panel.appendChild(a);
                });
            }
            panel.classList.add('open');
        }

        function runSearch(query) {
            var seq = ++requestSeq;
            fetch('search-products.php?q=' + encodeURIComponent(query), { credentials: 'same-origin' })
                .then(function (res) { return res.ok ? res.json() : Promise.reject(new Error('bad response')); })
                .then(function (data) {
                    if (seq !== requestSeq) return; // a newer request started meanwhile — ignore this stale one
                    renderResults(Array.isArray(data) ? data : []);
                })
                .catch(function () {
                    if (seq !== requestSeq) return;
                    panel.innerHTML = '';
                    var err = document.createElement('div');
                    err.className = 'search-empty';
                    err.textContent = 'Search is unavailable right now — please try again.';
                    panel.appendChild(err);
                    panel.classList.add('open');
                });
        }

        input.addEventListener('input', function () {
            var query = input.value.trim();
            clearTimeout(debounceTimer);
            if (query.length === 0) {
                requestSeq++; // invalidate any in-flight request
                closePanel();
                return;
            }
            debounceTimer = setTimeout(function () { runSearch(query); }, 250);
        });

        input.addEventListener('focus', function () {
            if (input.value.trim().length > 0 && panel.innerHTML !== '') {
                panel.classList.add('open');
            }
        });

        // Close the dropdown on outside clicks. Clicks on a result <a> still
        // navigate normally — this only hides the panel, it doesn't block them.
        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== input) {
                closePanel();
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePanel();
        });
    })();

    /* ---- Wishlist: lightweight client-side "save" heart on product cards ----
       localStorage only, no account required — mirrors the cart's storage
       pattern. sync() re-applies saved state to whatever cards are currently
       in the DOM, so it can be re-run after each AJAX results swap. */
    (function () {
        var STORAGE_KEY = 'ruwanpura_wishlist';

        function getWishlist() {
            try {
                var raw = localStorage.getItem(STORAGE_KEY);
                var list = raw ? JSON.parse(raw) : [];
                return Array.isArray(list) ? list : [];
            } catch (e) { return []; }
        }
        function saveWishlist(list) {
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(list)); } catch (e) {}
        }
        function isSaved(id) { return getWishlist().indexOf(id) !== -1; }
        function toggle(id) {
            var list = getWishlist();
            var idx = list.indexOf(id);
            if (idx === -1) { list.push(id); } else { list.splice(idx, 1); }
            saveWishlist(list);
            return idx === -1; // now active
        }
        function sync(root) {
            (root || document).querySelectorAll('.product-card-wishlist').forEach(function (heart) {
                var active = isSaved(heart.getAttribute('data-id'));
                heart.classList.toggle('is-active', active);
                heart.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }

        window.RuwanpuraWishlist = { isSaved: isSaved, toggle: toggle, sync: sync };
        sync();
    })();

    /* ---- Gemstone catalogue filters: drawer + instant no-reload updates ----
       Checking a filter fetches just the results fragment (count + grid +
       pagination) and swaps it in via the Fetch API, updating the URL with
       the History API so the page never actually reloads — while still
       working as plain links/forms if JS fails. Desktop applies the instant
       a checkbox changes; mobile keeps the drawer's deliberate "pick several,
       then tap Show Products" flow, since instant-apply on the very first tap
       would make it impossible to select more than one filter there. */
    (function () {
        var form = document.getElementById('filterForm');
        var resultsEl = document.getElementById('catalogueResults');
        if (!form || !resultsEl) return;

        var toggleBtn = document.getElementById('filtersToggleBtn');
        var closeBtn = document.getElementById('filtersCloseBtn');
        var drawer = document.getElementById('catalogueFilters');
        var backdrop = document.getElementById('filtersBackdrop');

        function openDrawer() {
            if (!drawer) return;
            drawer.classList.add('open');
            if (backdrop) backdrop.classList.add('open');
            document.body.classList.add('drawer-open');
        }
        function closeDrawer() {
            if (!drawer) return;
            drawer.classList.remove('open');
            if (backdrop) backdrop.classList.remove('open');
            document.body.classList.remove('drawer-open');
        }
        if (toggleBtn) toggleBtn.addEventListener('click', openDrawer);
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (backdrop) backdrop.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDrawer();
        });

        function updateBadge() {
            var count = form.querySelectorAll('input[type="checkbox"]:checked').length;
            var badge = toggleBtn ? toggleBtn.querySelector('.filter-count-badge') : null;
            if (count > 0) {
                if (!badge && toggleBtn) {
                    badge = document.createElement('span');
                    badge.className = 'filter-count-badge';
                    toggleBtn.appendChild(badge);
                }
                if (badge) badge.textContent = count;
            } else if (badge) {
                badge.remove();
            }
        }

        function applyFilters(queryString) {
            var url = 'gemstones.php' + (queryString ? '?' + queryString : '');
            resultsEl.classList.add('is-loading');
            fetch('filter-products.php' + (queryString ? '?' + queryString : ''), { credentials: 'same-origin' })
                .then(function (res) { return res.ok ? res.text() : Promise.reject(new Error('bad response')); })
                .then(function (html) {
                    resultsEl.innerHTML = html;
                    resultsEl.querySelectorAll('.reveal, .reveal-fade').forEach(function (el) {
                        el.classList.add('in-view');
                    });
                    if (window.RuwanpuraWishlist) window.RuwanpuraWishlist.sync(resultsEl);
                    window.history.pushState({ gemFilters: true }, '', url);
                    updateBadge();
                    closeDrawer();
                })
                .catch(function () {
                    window.location = url; // JS/network failure — fall back to a normal navigation
                })
                .then(function () {
                    resultsEl.classList.remove('is-loading');
                });
        }

        function formQueryString() {
            return new URLSearchParams(new FormData(form)).toString();
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            applyFilters(formQueryString());
        });

        form.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (window.innerWidth > 980) applyFilters(formQueryString());
            });
        });

        document.querySelectorAll('.catalogue-clear, .filters-clear-mobile').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                form.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
                var catInput = document.getElementById('categoryFilterInput');
                if (catInput) catInput.value = '';
                form.querySelectorAll('.cat-accordion-select.is-active, .subcat-select.is-active').forEach(function (btn) {
                    btn.classList.remove('is-active');
                });
                applyFilters('');
            });
        });

        /* Category / sub-category selection — click to filter instantly (no checkboxes).
           Clicking the already-active one again clears it and shows everything. */
        form.addEventListener('click', function (e) {
            var btn = e.target.closest('.cat-accordion-select, .subcat-select');
            if (!btn) return;
            var catInput = document.getElementById('categoryFilterInput');
            if (!catInput) return;

            var id = btn.getAttribute('data-id');
            var nowActive = catInput.value !== id;
            catInput.value = nowActive ? id : '';

            form.querySelectorAll('.cat-accordion-select.is-active, .subcat-select.is-active').forEach(function (el) {
                el.classList.remove('is-active');
            });
            if (nowActive) btn.classList.add('is-active');

            applyFilters(formQueryString());
        });

        resultsEl.addEventListener('click', function (e) {
            var link = e.target.closest('.catalogue-pagination a');
            if (!link) return;
            e.preventDefault();
            applyFilters(link.getAttribute('href').replace(/^\?/, ''));
            resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        /* Sort dropdown lives inside the results fragment (so it can show the
           active value after each swap) but is form-associated via form="filterForm" —
           always auto-applies, on both desktop and mobile, since it isn't part of
           the drawer's "pick several then apply" flow. */
        resultsEl.addEventListener('change', function (e) {
            if (e.target.id !== 'sortSelect') return;
            applyFilters(formQueryString());
        });

        /* Wishlist heart — never navigates the card link, just toggles the save state */
        resultsEl.addEventListener('click', function (e) {
            var heart = e.target.closest('.product-card-wishlist');
            if (!heart || !window.RuwanpuraWishlist) return;
            e.preventDefault();
            e.stopPropagation();
            var active = window.RuwanpuraWishlist.toggle(heart.getAttribute('data-id'));
            heart.classList.toggle('is-active', active);
            heart.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        resultsEl.addEventListener('keydown', function (e) {
            var heart = e.target.closest('.product-card-wishlist');
            if (!heart || (e.key !== 'Enter' && e.key !== ' ')) return;
            e.preventDefault();
            heart.click();
        });

        window.addEventListener('popstate', function () {
            applyFilters(window.location.search.replace(/^\?/, ''));
        });
    })();

    /* ---- Category accordion chevron: expand/collapse only ----
       Selecting a category/sub-category to filter by is handled separately
       (see the click delegation on #filterForm above) — this button only
       ever reveals or hides the sub-category list, it never applies a filter. */
    (function () {
        document.querySelectorAll('.cat-accordion-toggle').forEach(function (toggleBtn) {
            var item = toggleBtn.closest('.cat-accordion-item');
            var panel = item ? item.querySelector('.cat-accordion-panel') : null;
            if (!panel) return;

            if (panel.classList.contains('open')) panel.style.maxHeight = 'none';

            toggleBtn.addEventListener('click', function () {
                var isOpen = panel.classList.contains('open');
                if (isOpen) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    requestAnimationFrame(function () { panel.style.maxHeight = '0px'; });
                    panel.classList.remove('open');
                    item.classList.remove('is-open');
                    toggleBtn.setAttribute('aria-expanded', 'false');
                } else {
                    panel.classList.add('open');
                    item.classList.add('is-open');
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    panel.addEventListener('transitionend', function handler(e) {
                        if (e.propertyName !== 'max-height') return;
                        panel.style.maxHeight = 'none';
                        panel.removeEventListener('transitionend', handler);
                    });
                }
            });
        });
    })();

    /* ---- Filter accordion (Availability / Carat Weight / Shape & Cut / Origin /
       Treatment / Price) — same measured-height smooth expand/collapse as the
       category accordion above, just on its own header/panel pair. ---- */
    (function () {
        document.querySelectorAll('.filter-acc-item').forEach(function (item) {
            var header = item.querySelector('.filter-acc-header');
            var panel = item.querySelector('.filter-acc-panel');
            if (!header || !panel) return;

            if (item.classList.contains('is-open')) {
                panel.style.maxHeight = 'none';
                header.setAttribute('aria-expanded', 'true');
            }

            header.addEventListener('click', function () {
                var isOpen = item.classList.contains('is-open');
                if (isOpen) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    requestAnimationFrame(function () { panel.style.maxHeight = '0px'; });
                    item.classList.remove('is-open');
                    header.setAttribute('aria-expanded', 'false');
                } else {
                    item.classList.add('is-open');
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    header.setAttribute('aria-expanded', 'true');
                    panel.addEventListener('transitionend', function handler(e) {
                        if (e.propertyName !== 'max-height') return;
                        panel.style.maxHeight = 'none';
                        panel.removeEventListener('transitionend', handler);
                    });
                }
            });
        });
    })();

    /* ---- Sitewide scroll reveal: .reveal / .reveal-fade / .timeline-item ---- */
    (function () {
        var items = document.querySelectorAll('.reveal, .reveal-fade, .reveal-left, .reveal-right, .timeline-item');
        if (items.length === 0) return;

        if (typeof IntersectionObserver === 'undefined') {
            // Unsupported browser — just show everything, no animation.
            items.forEach(function (item) { item.classList.add('in-view'); });
            return;
        }

        // Stagger delay based on position among reveal-siblings sharing the same parent,
        // so each section cascades in on its own rather than accumulating one long delay.
        var groupCounts = new Map();
        items.forEach(function (item) {
            var parent = item.parentElement;
            var n = groupCounts.get(parent) || 0;
            item.style.transitionDelay = Math.min(n * 90, 450) + 'ms';
            groupCounts.set(parent, n + 1);
        });

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        items.forEach(function (item) { observer.observe(item); });
    })();

    /* ---- Subtle parallax on select feature images (desktop only) ---- */
    (function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (window.innerWidth < 768) return;

        var els = Array.prototype.slice.call(document.querySelectorAll('.parallax'));
        if (!els.length) return;

        var ticking = false;
        function update() {
            var vh = window.innerHeight;
            els.forEach(function (el) {
                var rect = el.getBoundingClientRect();
                if (rect.bottom < -100 || rect.top > vh + 100) return; // well off-screen, skip
                var speed = parseFloat(el.getAttribute('data-speed')) || 0.15;
                var offset = (rect.top + rect.height / 2 - vh / 2) * speed;
                el.style.transform = 'scale(1.12) translateY(' + offset.toFixed(1) + 'px)';
            });
            ticking = false;
        }
        window.addEventListener('scroll', function () {
            if (!ticking) { requestAnimationFrame(update); ticking = true; }
        }, { passive: true });
        update();
    })();

});
