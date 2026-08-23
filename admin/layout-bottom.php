        </div><!-- /content -->
    </div><!-- /main -->
</div><!-- /admin -->

<script>
(function(){
    var t=document.getElementById('mobileToggle');
    var s=document.getElementById('sidebar');
    var o=document.getElementById('overlay');
    if(t){t.addEventListener('click',function(){s.classList.toggle('open');o.classList.toggle('show');});}
    if(o){o.addEventListener('click',function(){s.classList.remove('open');o.classList.remove('show');});}
})();

// Home Page sections accordion
(function(){
    var toggle = document.getElementById('homeAccordionToggle');
    var menu = document.getElementById('homeAccordionMenu');
    if (!toggle || !menu) return;

    if (!menu.classList.contains('open')) {
        try {
            if (localStorage.getItem('admin_homeNavOpen') === '1') {
                menu.classList.add('open');
                toggle.setAttribute('aria-expanded', 'true');
            }
        } catch (e) {}
    }

    toggle.addEventListener('click', function () {
        var open = menu.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        try { localStorage.setItem('admin_homeNavOpen', open ? '1' : '0'); } catch (e) {}
    });
})();

// image preview on file select
document.querySelectorAll('input[type=file][data-preview]').forEach(function(inp){
    inp.addEventListener('change',function(){
        var target=document.getElementById(inp.getAttribute('data-preview'));
        if(target && inp.files && inp.files[0]){
            var url=URL.createObjectURL(inp.files[0]);
            target.innerHTML='<img src="'+url+'">';
        }
    });
});
</script>
</body>
</html>
