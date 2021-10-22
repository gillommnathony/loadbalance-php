<?php
if (!defined('BASE_DIR')) exit();

$gtm_id = get_option('google_tag_manager_id');
if (!empty($gtm_id)) :
?>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm_id; ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
