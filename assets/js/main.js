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
        if (slides.length === 0) return;
        var idx = 0;
        function show(i) {
            slides.forEach(function (s, k) { s.classList.toggle('active', k === i); });
        }
        function next() { idx = (idx + 1) % slides.length; show(idx); }
        function prev() { idx = (idx - 1 + slides.length) % slides.length; show(idx); }
        show(0);
        var np = document.querySelector('.hero-arrow.next');
        var pp = document.querySelector('.hero-arrow.prev');
        if (np) np.addEventListener('click', next);
        if (pp) pp.addEventListener('click', prev);
        if (slides.length > 1) setInterval(next, 6000);
    })();

    /* ---- Collection carousel (shifts visible window on small counts) ---- */
    (function () {
        var track = document.querySelector('.collection-track');
        if (!track) return;
        var cards = Array.prototype.slice.call(track.children);
        if (cards.length <= 4) return; // no need to slide
        var start = 0;
        var perView = 4;
        function render() {
            cards.forEach(function (c, i) {
                var visible = i >= start && i < start + perView;
                c.style.display = visible ? '' : 'none';
            });
        }
        var next = document.querySelector('.collection-arrow.next');
        var prev = document.querySelector('.collection-arrow.prev');
        if (next) next.addEventListener('click', function () {
            if (start + perView < cards.length) { start++; render(); }
        });
        if (prev) prev.addEventListener('click', function () {
            if (start > 0) { start--; render(); }
        });
        render();
    })();

    /* ---- Testimonials slider ---- */
    (function () {
        var track = document.querySelector('.testi-track');
        if (!track) return;
        var prev = document.querySelector('.testi-nav .prev');
        var next = document.querySelector('.testi-nav .next');
        var bar = document.querySelector('.testi-progress .bar');
        function step() {
            var card = track.querySelector('.testi-card');
            return card ? card.offsetWidth + 24 : 320;
        }
        function updateBar() {
            var max = track.scrollWidth - track.clientWidth;
            var pct = max > 0 ? (track.scrollLeft / max) * 100 : 0;
            if (bar) bar.style.width = Math.max(15, pct) + '%';
        }
        if (next) next.addEventListener('click', function () {
            track.scrollBy({ left: step(), behavior: 'smooth' });
        });
        if (prev) prev.addEventListener('click', function () {
            track.scrollBy({ left: -step(), behavior: 'smooth' });
        });
        track.addEventListener('scroll', updateBar);
        updateBar();
    })();

});
