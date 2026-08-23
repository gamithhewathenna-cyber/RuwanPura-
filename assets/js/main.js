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

    /* ---- Collection carousel (pages through 5 gemstones at a time, auto-advances) ---- */
    (function () {
        var track = document.querySelector('.collection-track');
        if (!track) return;
        var cards = Array.prototype.slice.call(track.children);
        if (cards.length === 0) return;
        var perView = 5;
        var start = 0;
        var next = document.querySelector('.collection-arrow.next');
        var prev = document.querySelector('.collection-arrow.prev');
        var timer;

        function render() {
            cards.forEach(function (c, i) {
                var visible = i >= start && i < start + perView;
                c.style.display = visible ? '' : 'none';
            });
            if (prev) prev.style.visibility = start > 0 ? 'visible' : 'hidden';
        }
        function goNext() {
            start = (start + perView < cards.length) ? start + perView : 0;
            render();
        }
        function goPrev() {
            start = Math.max(start - perView, 0);
            render();
        }
        function restartAuto() {
            clearInterval(timer);
            timer = setInterval(goNext, 5000);
        }

        if (next) next.addEventListener('click', function () { goNext(); restartAuto(); });
        if (prev) prev.addEventListener('click', function () { goPrev(); restartAuto(); });

        render();
        restartAuto();
    })();

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

});
