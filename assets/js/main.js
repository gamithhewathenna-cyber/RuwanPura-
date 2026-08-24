/* =====================================================================
   Ruwanpura Gems - Front-end interactions
   ===================================================================== */
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

    /* ---- Hero slider: image + text slide together as one unit ---- */
    (function () {
        var slides = document.querySelectorAll('.hero-slide');
        var textSlides = document.querySelectorAll('.hero-text-slide');
        var heroText = document.querySelector('.hero-text');
        if (slides.length === 0) return;
        var idx = 0;

        function setHeroTextHeight() {
            var active = textSlides[idx];
            if (heroText && active) heroText.style.height = active.offsetHeight + 'px';
        }

        function place(el, parked) {
            el.classList.remove('active', 'park-left');
            if (parked) el.classList.add('park-left');
        }

        function go(newIdx, direction) {
            var oldIdx = idx;
            if (newIdx === oldIdx) return;

            [slides, textSlides].forEach(function (group) {
                var oldEl = group[oldIdx];
                var newEl = group[newIdx];
                if (!oldEl || !newEl) return;

                // Put the incoming slide at its correct starting side with no
                // transition first (so it doesn't visibly fly in from wherever
                // it was last left), then flip both slides on the next frame
                // so the move actually animates.
                newEl.style.transition = 'none';
                place(newEl, direction === 'prev');
                void newEl.offsetWidth;
                newEl.style.transition = '';

                requestAnimationFrame(function () {
                    place(oldEl, direction === 'next');
                    place(newEl, false);
                    newEl.classList.add('active');
                });
            });

            idx = newIdx;
            setHeroTextHeight();
        }

        function next() { go((idx + 1) % slides.length, 'next'); }
        function prev() { go((idx - 1 + slides.length) % slides.length, 'prev'); }

        setHeroTextHeight();
        window.addEventListener('resize', setHeroTextHeight);

        var timer;
        function restartAuto() {
            clearInterval(timer);
            if (slides.length > 1) timer = setInterval(next, 6000);
        }

        document.querySelectorAll('.hero-arrow.next').forEach(function (b) { b.addEventListener('click', function () { next(); restartAuto(); }); });
        document.querySelectorAll('.hero-arrow.prev').forEach(function (b) { b.addEventListener('click', function () { prev(); restartAuto(); }); });
        restartAuto();
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

    /* ---- Legacy stats: on mobile, auto-looping carousel showing two at a time ----
       Desktop/tablet keeps the static 4-column grid — this only takes effect once
       the CSS switches .legacy-grid to a horizontally-scrolling flex row (see the
       max-width:980px rules in style.css). */
    (function () {
        var track = document.querySelector('.legacy-grid');
        if (!track || window.innerWidth > 980) return;

        function atEnd() {
            return track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
        }
        function next() {
            if (atEnd()) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: track.clientWidth, behavior: 'smooth' });
            }
        }

        if (track.scrollWidth - track.clientWidth > 0) {
            setInterval(next, 3500);
        }
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
                applyFilters('');
            });
        });

        resultsEl.addEventListener('click', function (e) {
            var link = e.target.closest('.catalogue-pagination a');
            if (!link) return;
            e.preventDefault();
            applyFilters(link.getAttribute('href').replace(/^\?/, ''));
            resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        window.addEventListener('popstate', function () {
            applyFilters(window.location.search.replace(/^\?/, ''));
        });
    })();

    /* ---- Sitewide scroll reveal: .reveal / .reveal-fade / .timeline-item ---- */
    (function () {
        var items = document.querySelectorAll('.reveal, .reveal-fade, .timeline-item');
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
