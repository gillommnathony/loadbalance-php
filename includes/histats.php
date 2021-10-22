<?php
if (!defined('BASE_DIR')) exit();

$histats_id = get_option('histats_id');
if (!empty($histats_id)) :
?>
    <script>
        var _Hasync = _Hasync || [];
        _Hasync.push(['Histats.start', '1,<?php echo $histats_id; ?>,4,0,0,0,00010000']);
        _Hasync.push(['Histats.fasi', '1']);
        _Hasync.push(['Histats.track_hits', '']);
        (function() {
            var hs = document.createElement('script');
            hs.type = 'text/javascript';
            hs.async = true;
            hs.src = ('//s10.histats.com/js15_as.js');
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(hs);
        })();
    </script>
    <noscript><a href="/" target="_blank"><img src="//sstatic1.histats.com/0.gif?<?php echo $histats_id; ?>&amp;101" style="border:0"></a></noscript>
<?php endif; ?>
