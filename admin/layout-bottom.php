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

// Show/hide password fields (works for any current or future .password-field)
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.password-toggle');
    if (!btn) return;
    var wrap = btn.closest('.password-field');
    var input = wrap ? wrap.querySelector('input') : null;
    if (!input) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.classList.toggle('is-visible', show);
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
});

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

// If a full <iframe> embed snippet gets pasted into the map or video embed fields,
// extract just the src URL before the form is submitted — some hosts block
// requests whose body contains raw <iframe>/<script> tags.
document.querySelectorAll('input[name="content[contact_map_embed]"], input[name="content[video_url]"]').forEach(function(inp){
    var form = inp.closest('form');
    if (!form) return;
    form.addEventListener('submit', function(){
        var val = inp.value || '';
        if (val.indexOf('<iframe') !== -1) {
            var m = val.match(/src=["']([^"']+)["']/i);
            if (m) inp.value = m[1];
        }
    });
});
</script>
</body>
</html>
