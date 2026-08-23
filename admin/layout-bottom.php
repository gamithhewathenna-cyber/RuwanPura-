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
