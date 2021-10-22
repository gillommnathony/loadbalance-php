<?php
if (!defined('BASE_DIR')) exit();
$disqus_shortname = get_option('disqus_shortname');
if (!empty($disqus_shortname)) :
?>
    <div class="row mt-4">
        <div class="col-12">
            <div id="disqus_thread"></div>
        </div>
    </div>
    <script>
        var disqus_config = function() {
            this.page.url = '<?php echo BASE_URL; ?>';
            this.page.identifier = '<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>';
        };
        (function() {
            var d = document,
                s = d.createElement('script');
            s.src = 'https://<?php echo trim($disqus_shortname); ?>.disqus.com/embed.js';
            s.setAttribute('data-timestamp', +new Date());
            (d.head || d.body).appendChild(s);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
<?php
endif;
?>
