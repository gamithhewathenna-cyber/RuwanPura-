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

    /* ---- Hero slider ---- */
    (function () {
        var slides = document.querySelectorAll('.hero-slide');
        var textSlides = document.querySelectorAll('.hero-text-slide');
        if (slides.length === 0) return;
        var idx = 0;
        function show(i) {
            slides.forEach(function (s, k) { s.classList.toggle('active', k === i); });
            textSlides.forEach(function (t, k) { t.classList.toggle('active', k === i); });
        }
        function next() { idx = (idx + 1) % slides.length; show(idx); }
        function prev() { idx = (idx - 1 + slides.length) % slides.length; show(idx); }
        show(0);
        document.querySelectorAll('.hero-arrow.next').forEach(function (b) { b.addEventListener('click', next); });
        document.querySelectorAll('.hero-arrow.prev').forEach(function (b) { b.addEventListener('click', prev); });
        if (slides.length > 1) setInterval(next, 6000);
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
