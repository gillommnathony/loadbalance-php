<?php
if (!defined('BASE_DIR')) die('access denied!');

$msg = '';
$link = '';
$linkBypass = '';
$disableSharer = !is_admin() && filter_var(get_option('disable_gsharer'), FILTER_VALIDATE_BOOLEAN);
if (!empty($_POST['link']) && !$disableSharer) {
    $link = htmlspecialchars($_POST['link']);
    // recaptcha validation
    $recaptchaValidate = recaptcha_validate($_POST['captcha-response']);
    if ($recaptchaValidate) {
        $gd = new \gdrive_auth();
        $gd->set_id($link);
        $mirror = $gd->copy_files(true);
        if ($mirror) {
            $linkBypass = download_link($mirror['webContentLink']);
        } else {
            $msg = '<div class="alert alert-danger">Can\'t bypass limit! Please try again later.</div>';
        }
    }
}

if (!$disableSharer) :
?>
    <div class="row py-5 bg-custom text-center">
        <div class="col">
            <h1 class="h3">Bypass Limit Google Drive</h1>
            <p>This tool is used to bypass the Google Drive limit without having to log in.</p>
        </div>
    </div>
    <div class="row py-5">
        <div class="col-12 mb-3"><?php echo htmlspecialchars_decode(get_option('sh_banner_top')); ?></div>
        <div class="col-12">
            <?php echo $msg; ?>
            <form id="frm" action="<?php echo BASE_URL; ?>sharer/" method="post">
                <div class="form-group">
                    <input type="text" name="link" id="link" class="form-control" placeholder="Google Drive Link! Example: https://drive.google.com/file/d/1DY5QWnXCdWnAWDXuwOT5pOW6OyTL8cIa/view" value="<?php echo $link; ?>" required>
                </div>
                <div class="form-group text-center">
                    <?php
                    $recaptcha_site_key = get_option('recaptcha_site_key');
                    if ($recaptcha_site_key) :
                    ?>
                        <div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>" data-size="invisible" data-callback="gCallback"></div>
                    <?php
                    endif;
                    ?>
                    <input type="hidden" id="captcha-response" name="captcha-response" />
                    <button id="submit" type="submit" class="btn btn-custom btn-block">
                        <i class="fa fa-cog"></i>
                        <span class="ml-2">Bypass Limit</span>
                    </button>
                </div>
            </form>
            <div class="form-group">
                <label for="result">Bypass Limit Link</label>
                <input type="text" onfocus="this.select()" value="<?php echo $linkBypass; ?>" class="form-control" placeholder="The bypass limit link is here!" readonly>
            </div>
        </div>
        <div class="col-12 mb-3"><?php echo htmlspecialchars_decode(get_option('sh_banner_bottom')); ?></div>
    </div>
<?php
else :
    echo error(403);
endif;
