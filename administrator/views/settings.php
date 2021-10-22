<?php
if (!defined('BASE_DIR')) {
    http_response_code(403);
    exit;
}

// cek apakah super admin atau tidak
$login = new login();
$userLogin = $login->cek_login();
if (!$userLogin || !is_admin()) {
    include_once 'views/402.php';
    exit;
}

$error  = [];
$data   = $_POST;
$error  = '';
$setting  = new \settings();
if (!empty($data)) {
    $update = $setting->update($data);
}
$opt = $setting->get();
?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">Settings</h1>
        <?php
        if (!empty($data)) {
            if (!empty($error)) {
                $alert = '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><span class="ml-2">' . $error . '</span></div>';
            } else {
                $alert = '<div class="alert alert-success"><i class="fa fa-check"></i><span class="ml-2">Settings updated successfully!</span></div>';
            }
            echo $alert;
        }
        ?>
        <form action="./admin.php?go=settings" method="post" class="needs-validation" autocomplete="off" novalidate>
            <div class="row">
                <div class="col-12 col-lg-7 mr-auto">
                    <ul class="nav nav-pills nav-justified mb-3" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">General</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="vplayer-tab" data-toggle="tab" href="#vplayer" role="tab" aria-controls="vplayer" aria-selected="false">Video Player</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="ads-tab" data-toggle="tab" href="#ads" role="tab" aria-controls="ads" aria-selected="false">Ads</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Other</a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" id="shortlink-tab" data-toggle="tab" href="#shortlink" role="tab" aria-controls="shortlink" aria-selected="false">URL Shortener</a>
                                <a class="dropdown-item" id="smtp-tab" data-toggle="tab" href="#smtp" role="tab" aria-controls="smtp" aria-selected="false">SMTP</a>
                                <a class="dropdown-item" id="other-tab" data-toggle="tab" href="#other" role="tab" aria-controls="other" aria-selected="false">Miscellaneous</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane pt-3 fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="site_name">Site Name</label>
                                <input type="text" name="opt[site_name]" id="site_name" placeholder="Your site name" class="form-control" value="<?php echo !empty($opt['site_name']) ? $opt['site_name'] : 'GDPlayer'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="site_slogan">Slogan</label>
                                <input type="text" name="opt[site_slogan]" id="site_slogan" placeholder="Your site slogan" class="form-control" value="<?php echo !empty($opt['site_slogan']) ? $opt['site_slogan'] : 'Google Drive Video Player'; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="site_description">Site Description</label>
                                <textarea name="opt[site_description]" id="site_description" placeholder="Your site description" class="form-control" required><?php echo !empty($opt['site_description']) ? $opt['site_description'] : 'Google Drive Video Player'; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="main_site">Main Site</label>
                                <input type="url" name="opt[main_site]" id="main_site" placeholder="Main site" class="form-control" value="<?php echo !empty($opt['main_site']) ? $opt['main_site'] : BASE_URL; ?>" required>
                                <small class="form-text text-muted">The main site as a public video player generator. If someone accesses the video player generator from the load balancer site then they will be directed to this main site.</small>
                            </div>
                            <div class="form-group">
                                <label for="production_mode">
                                    <span class="mr-2">Production Mode</span>
                                    <i class="fa fa-info-circle" data-toggle="tooltip" title="Serves to secure your video direct link."></i>
                                </label>
                                <select name="opt[production_mode]" id="production_mode" class="custom-select">
                                    <option value="false">Disable</option>
                                    <option value="true" <?php echo !empty($opt['production_mode']) && $opt['production_mode'] === 'true' ? 'selected' : ''; ?>>Enable</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="load_balancer_methods">Load Balancer Method</label>
                                <select name="opt[load_balancer_methods]" id="load_balancer_methods" class="custom-select">
                                    <option value="direct">Direct</option>
                                    <option value="redirect" <?php echo !empty($opt['load_balancer_methods']) && $opt['load_balancer_methods'] === 'redirect' ? 'selected' : ''; ?>>Redirect</option>
                                </select>
                                <small class="form-text text-muted"><strong>Redirect</strong> means the embed page will be redirected to the load balancer embed page. <strong>Direct</strong> means only the video playback files will be fetched from the load balancer.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="save_public_video" name="opt[save_public_video]" value="true" <?php echo !empty($opt['save_public_video']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="save_public_video">Save Public Videos Automatically</label>
                                </div>
                                <small class="form-text text-muted">All videos embed from the public generator will be saved in the video list.</small>
                            </div>
                            <div class="form-group">
                                <label for="public_video_user">Save Public Videos As User</label>
                                <select name="opt[public_video_user]" id="public_video_user" class="form-control">
                                    <?php
                                    $selUser = !empty($opt['public_video_user']) ? $opt['public_video_user'] : '';
                                    $list = $db->prepare("SELECT `id`, `name` FROM `tb_users`");
                                    $list->execute();
                                    $rows = $list->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($rows as $user) {
                                        echo '<option value="' . $user['id'] . '" ' . ($selUser === $user['id'] ? 'selected' : '') . '>' . $user['name'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">Public videos will be saved as that user.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="disable_gsharer" name="opt[disable_gsharer]" value="true" <?php echo !empty($opt['disable_gsharer']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="disable_gsharer">Disable Google Drive Sharer</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="gdrive_copy" name="opt[gdrive_copy]" value="true" <?php echo !empty($opt['gdrive_copy']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="gdrive_copy">Always copy Google Drive videos</label>
                                </div>
                                <small class="form-text text-muted">Always copy Google Drive videos that are entered by the user or public into your unlimited Google Drive account.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="gdrive_copy_all" name="opt[gdrive_copy_all]" value="true" <?php echo !empty($opt['gdrive_copy_all']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="gdrive_copy_all">Copy Files to All Google Drive Accounts</label>
                                </div>
                                <small class="form-text text-muted">Check if you want to copy files to all Google Drive accounts at the same time. By default, files are copied to a random Google Drive account.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="disable_registration" name="opt[disable_registration]" value="true" <?php echo !empty($opt['disable_registration']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="disable_registration">Disable Registration Page</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="google_analytics_id">Google Analytics ID</label>
                                <input type="text" class="form-control" name="opt[google_analytics_id]" id="google_analytics_id" placeholder="Enter the Google Analytics ID. Example: UA-123456" value="<?php echo !empty($opt['google_analytics_id']) ? htmlspecialchars($opt['google_analytics_id']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="google_tag_manager">
                                    <span>Google Tag Manager ID</span>
                                    <i class="fa fa-info-circle ml-2" data-toggle="tooltip" title="Used specifically for JW Player."></i>
                                </label>
                                <input type="text" class="form-control" name="opt[google_tag_manager]" id="google_tag_manager" placeholder="Enter the Google Tag Manager. Example: GTM-123456" value="<?php echo !empty($opt['google_tag_manager']) ? htmlspecialchars($opt['google_tag_manager']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="histats_id">Histats.com SID</label>
                                <input type="text" class="form-control" name="opt[histats_id]" id="histats_id" placeholder="Your Histats.com SID" value="<?php echo !empty($opt['histats_id']) ? htmlspecialchars($opt['histats_id']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="recaptcha_site_key">Google ReCaptcha Site Key</label>
                                <input type="text" class="form-control" name="opt[recaptcha_site_key]" id="recaptcha_site_key" placeholder="Enter the Google ReCaptcha Site Key" value="<?php echo !empty($opt['recaptcha_site_key']) ? htmlspecialchars($opt['recaptcha_site_key']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="recaptcha_secret_key">Google ReCaptcha Secret Key</label>
                                <input type="text" class="form-control" name="opt[recaptcha_secret_key]" id="recaptcha_secret_key" placeholder="Enter the Google ReCaptcha Secret Key" value="<?php echo !empty($opt['recaptcha_secret_key']) ? htmlspecialchars($opt['recaptcha_secret_key']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="disqus_shortname">Disqus Shortname</label>
                                <div class="input-group">
                                    <input type="text" name="opt[disqus_shortname]" id="disqus_shortname" placeholder="Your disqus shortname" class="form-control" value="<?php echo !empty($opt['disqus_shortname']) ? $opt['disqus_shortname'] : ''; ?>">
                                    <div class="input-group-append">
                                        <div class="input-group-text">.disqus.com</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="chat_widget">Chat Widget Script</label>
                                <textarea name="opt[chat_widget]" id="chat_widget" cols="30" rows="5" class="form-control" placeholder="Enter the Chat Widget javascript code (with the <script> tag)"><?php echo !empty($opt['chat_widget']) ? trim(htmlspecialchars($opt['chat_widget'])) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane pt-3 fade" id="vplayer" role="tabpanel" aria-labelledby="vplayer-tab">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="row">
                                <div class="form-group col-12 col-md-6">
                                    <label for="player">Video Player</label>
                                    <select name="opt[player]" id="player" class="custom-select">
                                        <option value="jwplayer">JW Player</option>
                                    </select>
                                </div>
                                <div class="form-group col-12 col-md-6">
                                    <label for="player_skin">Skin</label>
                                    <select name="opt[player_skin]" id="player_skin" class="custom-select">
                                        <option value="">Default</option>
                                        <?php
                                        $skinDir = BASE_DIR . 'assets/css/skin/' . (!empty($opt['player']) ? $opt['player'] : 'jwplayer');
                                        if (is_dir($skinDir)) {
                                            $dir = new RecursiveDirectoryIterator($skinDir, RecursiveDirectoryIterator::SKIP_DOTS);
                                            foreach (new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST) as $filename => $file) {
                                                $skin = strtr(basename($filename), ['.min.css' => '.css', '.css' => '']);
                                                echo '<option value="' . $skin . '" ' . (!empty($opt['player_skin']) && $skin === $opt['player_skin'] ? 'selected' : '') . '>' . ucfirst($skin) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-12 col-md-6">
                                    <label for="player_color">Video Player Color</label>
                                    <input type="text" name="opt[player_color]" id="player_color" class="form-control" placeholder="Video Player Color" value="<?php echo !empty($opt['player_color']) ? $opt['player_color'] : '673AB7'; ?>" data-wheelcolorpicker>
                                </div>
                                <div class="form-group col-12 col-md-6">
                                    <label for="subtitle_color">Subtitle Font Color</label>
                                    <input type="text" name="opt[subtitle_color]" id="subtitle_color" class="form-control" placeholder="Video Player Color" value="<?php echo !empty($opt['subtitle_color']) ? $opt['subtitle_color'] : 'ffff00'; ?>" data-wheelcolorpicker>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php $stretching = !empty($opt['stretching']) ? $opt['stretching'] : ''; ?>
                                <label for="stretching">Position</label>
                                <select name="opt[stretching]" id="stretching" class="custom-select">
                                    <option value="uniform">uniform</option>
                                    <option value="exactfit" <?php echo $stretching === 'exactfit' ? 'selected' : ''; ?>>Exact Fit</option>
                                    <option value="fill" <?php echo $stretching === 'fill' ? 'selected' : ''; ?>>Fill</option>
                                    <option value="none" <?php echo $stretching === 'none' ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="autoplay" name="opt[autoplay]" value="true" <?php echo !empty($opt['autoplay']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="autoplay">Autoplay</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="mute" name="opt[mute]" value="true" <?php echo !empty($opt['mute']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="mute">Mute</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="repeat" name="opt[repeat]" value="true" <?php echo !empty($opt['repeat']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="repeat">Repeat</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="display_title" name="opt[display_title]" value="true" <?php echo !empty($opt['display_title']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="display_title">Show Title</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="playback_rate" name="opt[playback_rate]" value="true" <?php echo !empty($opt['playback_rate']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="playback_rate">Show Playback Rate Controls</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="enable_share_button" name="opt[enable_share_button]" value="true" <?php echo !empty($opt['enable_share_button']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="enable_share_button">Show Share Button</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="enable_download_button" name="opt[enable_download_button]" value="true" <?php echo !empty($opt['enable_download_button']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="enable_download_button">Show Download Button</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="anonymous_generator" name="opt[anonymous_generator]" value="true" <?php echo !empty($opt['anonymous_generator']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="anonymous_generator">Enable Public Video Generator</label>
                                </div>
                                <small class="form-text text-muted">Public video generators can be accessed directly or not.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="embed_page_direct" name="opt[embed_page_direct]" value="true" <?php echo !empty($opt['embed_page_direct']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="embed_page_direct">Enable Direct Access to the Embed Page</label>
                                </div>
                                <small class="form-text text-muted">If direct access to the embed page is disabled then the embed page must be accessed using an iframe.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="enable_download_page" name="opt[enable_download_page]" value="true" <?php echo !empty($opt['enable_download_page']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="enable_download_page">Enable Download Page</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="hide_sub_download" name="opt[hide_sub_download]" value="true" <?php echo !empty($opt['hide_sub_download']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="hide_sub_download">Hide Subtitles on Download Page</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="memory_friendly" name="opt[memory_friendly]" value="true" <?php echo !empty($opt['memory_friendly']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="memory_friendly">Enable memory friendly</label>
                                </div>
                                <small class="form-text text-muted">Will reduce memory usage but may reduce smooth video playback.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="p2p" name="opt[p2p]" value="true" <?php echo !empty($opt['p2p']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="p2p">Enable p2p</label>
                                </div>
                                <small class="form-text text-muted">Enable the p2p feature on the hls / m3u8 video to reduce bandwidth usage on your server.</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="poster">Poster Image URL</label>
                                <input type="url" name="opt[poster]" id="poster" class="form-control" placeholder="Enter poster url (.jpg/.jpeg/.png/.webp)" value="<?php echo !empty($opt['poster']) ? $opt['poster'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="small_logo_file">Small Logo URL</label>
                                <input type="url" name="opt[small_logo_file]" id="small_logo_file" class="form-control" placeholder="The small logo will be displayed on the video player control" value="<?php echo !empty($opt['small_logo_file']) ? $opt['small_logo_file'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="small_logo_link">Visited Link</label>
                                <input type="url" name="opt[small_logo_link]" id="small_logo_link" class="form-control" placeholder="Enter the URL visited when the small logo is clicked" value="<?php echo !empty($opt['small_logo_link']) ? $opt['small_logo_link'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <h2 class="h5">Watermark</h2>
                            </div>
                            <div class="form-group">
                                <label for="logo_file">Image URL</label>
                                <input type="url" name="opt[logo_file]" id="logo_file" class="form-control" placeholder="Enter the watermark image url" value="<?php echo !empty($opt['logo_file']) ? $opt['logo_file'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="logo_open_link">Visited Link</label>
                                <input type="url" name="opt[logo_open_link]" id="logo_open_link" class="form-control" placeholder="Enter the URL visited when the watermark image is clicked" value="<?php echo !empty($opt['logo_open_link']) ? $opt['logo_open_link'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <?php $logo_position = !empty($opt['logo_position']) ? $opt['logo_position'] : ''; ?>
                                <label for="logo_position">Position</label>
                                <select name="opt[logo_position]" id="logo_position" class="custom-select">
                                    <option value="top-right" <?php echo $logo_position === 'top-right' ? 'selected' : ''; ?>>Top-right</option>
                                    <option value="bottom-right" <?php echo $logo_position === 'bottom-right' ? 'selected' : ''; ?>>Bottom-right</option>
                                    <option value="bottom-left" <?php echo $logo_position === 'bottom-left' ? 'selected' : ''; ?>>Bottom-left</option>
                                    <option value="top-left" <?php echo $logo_position === 'top-left' ? 'selected' : ''; ?>>Top-left</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="logo_margin">Margin</label>
                                <input type="number" name="opt[logo_margin]" id="logo_margin" class="form-control" placeholder="Enter the distance between the logo and the edge of the screen (px)" value="<?php echo !empty($opt['logo_margin']) ? $opt['logo_margin'] : 8; ?>">
                            </div>
                            <div class="form-group">
                                <?php $logo_hide = !empty($opt['logo_hide']) ? $opt['logo_hide'] : ''; ?>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="logo_hide" name="opt[logo_hide]" value="true" <?php echo filter_var($logo_hide, FILTER_VALIDATE_BOOLEAN) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="logo_hide">Hide</label>
                                </div>
                                <small class="form-text text-muted">When this option is checked, the logo will automatically show and hide along with the other player controls </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane pt-3 fade" id="ads" role="tabpanel" aria-labelledby="ads-tab">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="vast_client">Publisher</label>
                                <?php
                                $adClient = !empty($opt['vast_client']) ? $opt['vast_client'] : '';
                                ?>
                                <select name="opt[vast_client]" id="vast_client" class="custom-select">
                                    <option value="vast" <?php echo $adClient === 'vast' ? 'selected' : ''; ?>>VAST</option>
                                    <option value="googima" <?php echo $adClient === 'googima' ? 'selected' : ''; ?>>Google IMA</option>
                                </select>
                                <small class="form-text text-muted">
                                    JW Player Ad Rule Reference: <a href="https://developer.jwplayer.com/jwplayer/docs/jw8-define-ad-rules" target="_blank">https://developer.jwplayer.com/jwplayer/docs/jw8-define-ad-rules</a>
                                </small>
                                <small class="form-text text-muted">
                                    Create a custom VAST XML: <a href="https://developer.jwplayer.com/jwplayer/docs/jw8-create-a-custom-vast-xml-ad-tag" target="_blank">https://developer.jwplayer.com/jwplayer/docs/jw8-create-a-custom-vast-xml-ad-tag</a>
                                </small>
                            </div>
                            <div id="vastWrapper">
                                <?php
                                $vast_xml = !empty($opt['vast_xml']) ? json_decode($opt['vast_xml']) : [];
                                $vast_offset = !empty($opt['vast_offset']) ? json_decode($opt['vast_offset']) : [];
                                $first_xml = !empty($vast_xml) ? $vast_xml[0] : '';
                                $first_offset = !empty($vast_offset) ? $vast_offset[0] : '';
                                ?>
                                <div class="form-group" data-index="0">
                                    <div class="input-group">
                                        <div class="input-group-prepend" style="max-width:110px">
                                            <input type="text" placeholder="Ad Position" name="opt[vast_offset][]" id="vast_offset-0" class="form-control" value="<?php echo $first_offset; ?>">
                                        </div>
                                        <input type="url" name="opt[vast_xml][]" id="vast_xml-0" placeholder="VAST Link (.xml)" class="form-control" value="<?php echo $first_xml; ?>">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success" onclick="settings.addVastHTML()">
                                                <i class="fa fa-plus-circle"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($vast_xml)) :
                                    for ($i = 1; $i < count($vast_xml); $i++) :
                                ?>
                                        <div class=" form-group" data-index="<?php echo $i; ?>">
                                            <div class="input-group">
                                                <div class="input-group-prepend" style="max-width:110px">
                                                    <input type="text" placeholder="Ad Position" name="opt[vast_offset][]" id="vast_offset-<?php echo $i; ?>" class="form-control" value="<?php echo $vast_offset[$i]; ?>">
                                                </div>
                                                <input type="url" name="opt[vast_xml][]" id="vast_xml-<?php echo $i; ?>" placeholder="VAST Link (.xml)" class="form-control" value="<?php echo $vast_xml[$i]; ?>">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger" onclick="settings.removeVastHTML(<?php echo $i; ?>)">
                                                        <i class="fa fa-minus-circle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                <?php
                                    endfor;
                                endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="vast_skip">Skip Ads After</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="opt[vast_skip]" id="vast_skip" placeholder="Skip ads after (seconds)" value="<?php echo !empty($opt['vast_skip']) ? intval($opt['vast_skip']) : 0; ?>">
                                    <div class="input-group-append">
                                        <div class="input-group-text">Seconds</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dl_banner_top">Top Banner on Download Page</label>
                                <textarea name="opt[dl_banner_top]" id="dl_banner_top" cols="30" rows="3" class="form-control" placeholder="HTML/Javascript Here"><?php echo !empty($opt['dl_banner_top']) ? htmlspecialchars($opt['dl_banner_top']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="dl_banner_bottom">Bottom Banner on Download Page</label>
                                <textarea name="opt[dl_banner_bottom]" id="dl_banner_bottom" cols="30" rows="3" class="form-control" placeholder="HTML/Javascript Here"><?php echo !empty($opt['dl_banner_bottom']) ? htmlspecialchars($opt['dl_banner_bottom']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="sh_banner_top">Top Banner on Sharer Page</label>
                                <textarea name="opt[sh_banner_top]" id="sh_banner_top" cols="30" rows="3" class="form-control" placeholder="HTML/Javascript Here"><?php echo !empty($opt['sh_banner_top']) ? htmlspecialchars($opt['sh_banner_top']) : ''; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="sh_banner_bottom">Bottom Banner on Sharer Page</label>
                                <textarea name="opt[sh_banner_bottom]" id="sh_banner_bottom" cols="30" rows="3" class="form-control" placeholder="HTML/Javascript Here"><?php echo !empty($opt['sh_banner_bottom']) ? htmlspecialchars($opt['sh_banner_bottom']) : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="block_adblocker">Block AdBlocker</label>
                                <select name="opt[block_adblocker]" id="block_adblocker" class="custom-select">
                                    <option value="false">No</option>
                                    <option value="true" <?php echo !empty($opt['block_adblocker']) && filter_var($opt['block_adblocker'], FILTER_VALIDATE_BOOLEAN) ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="direct_ads_link">Direct Link Ads</label>
                                <input type="url" class="form-control" name="opt[direct_ads_link]" id="direct_ads_link" placeholder="Enter the direct link ads" value="<?php echo !empty($opt['direct_ads_link']) ? filter_var($opt['direct_ads_link'], FILTER_SANITIZE_URL) : ''; ?>">
                                <small class="form-text text-muted">The direct link will open when the download button is clicked.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="visitads_onplay" name="opt[visitads_onplay]" value="true" <?php echo !empty($opt['visitads_onplay']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="visitads_onplay">Visit Direct Link Ads on Play</label>
                                </div>
                                <small class="form-text text-muted">Visit the direct link ad when the play button is clicked for the first time.</small>
                            </div>
                            <div class="form-group">
                                <label for="popup_ads_link">Popup Ads Link</label>
                                <input type="url" class="form-control" name="opt[popup_ads_link]" id="popup_ads_link" placeholder="Enter the Popup Ads Link (.js)" value="<?php echo !empty($opt['popup_ads_link']) ? filter_var($opt['popup_ads_link'], FILTER_SANITIZE_URL) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="popup_ads_code">Popup Ad Code</label>
                                <textarea name="opt[popup_ads_code]" id="popup_ads_code" cols="30" rows="10" class="form-control" placeholder="Enter the popup ad's javascript code (with the <script> tag)"><?php echo !empty($opt['popup_ads_code']) ? trim(htmlspecialchars($opt['popup_ads_code'])) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane pt-3 fade" id="shortlink" role="tabpanel" aria-labelledby="shortlink-tab">
                    <div class="row">
                        <div class="col">
                            <div class="row">
                                <div class="form-group col-12 col-md-6">
                                    <label for="main_url_shortener">Main URL Shortener (bitly.com)</label>
                                    <input type="text" name="opt[main_url_shortener]" id="main_url_shortener" class="form-control" placeholder="Enter the access token from bitly.com" value="<?php echo !empty($opt['main_url_shortener']) ? trim(htmlspecialchars($opt['main_url_shortener'])) : ''; ?>">
                                    <small class="form-text text-muted">
                                        You can find out how to get a bitly.com access token from the following link <a href="https://support.bitly.com/hc/en-us/articles/230647907-How-do-I-find-my-OAuth-access-token-" target="_blank">https://support.bitly.com/hc/en-us/articles/230647907-How-do-I-find-my-OAuth-access-token-</a>
                                    </small>
                                </div>
                                <div class="form-group col-12 col-md-6">
                                    <label for="additional_url_shortener">Select Additional URL Shortener</label>
                                    <?php echo earnmoney_website('opt[additional_url_shortener]', (!empty($opt['additional_url_shortener']) ? trim(htmlspecialchars($opt['additional_url_shortener'])) : '')); ?>
                                    <small class="form-text text-muted">Additional URL shorteners are used to earn money online and will be used on the download page.</small>
                                </div>
                            </div>
                            <div class="row">
                                <?php
                                $urlShortener = earnmoney_website('', '', false, true);
                                $urlShortener = json_decode($urlShortener, TRUE);
                                foreach ($urlShortener as $key => $value) :
                                    if ($key !== 'random') :
                                        $name = 'additional_url_shortener_' . $key;
                                ?>
                                        <div class="form-group col-12 col-md-6">
                                            <label for="<?php echo $key; ?>">
                                                <a href="http://<?php echo $key; ?>" target="_blank"><?php echo $value; ?></a> API Key
                                            </label>
                                            <input type="text" name="opt[<?php echo $name; ?>]" id="<?php echo $key; ?>" class="form-control" placeholder="Enter <?php echo $key; ?> API Key" value="<?php echo !empty($opt[$name]) ? trim(htmlspecialchars($opt[$name])) : ''; ?>">
                                        </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane pt-3 fade" id="smtp" role="tabpanel" aria-labelledby="smtp-tab">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="disable_confirm" name="opt[disable_confirm]" value="true" <?php echo !empty($opt['disable_confirm']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="disable_confirm">Disable registration confirmation email / reset password</label>
                                </div>
                                <small class="form-text text-muted">Warning! If you tick this feature then the email entered by the user will not be validated.</small>
                            </div>
                            <div class="form-group">
                                <?php
                                $smtp_provider = !empty($opt['smtp_provider']) ? $opt['smtp_provider'] : '';
                                ?>
                                <label for="smtp_provider">Provider</label>
                                <select name="opt[smtp_provider]" id="smtp_provider" class="custom-select" onchange="settings.smtp()">
                                    <option value="">-- Select Email Provider --</option>
                                    <option value="gmail" <?php if ($smtp_provider === 'gmail') echo 'selected'; ?>>Gmail</option>
                                    <option value="ymail" <?php if ($smtp_provider === 'ymail') echo 'selected'; ?>>Yahoo!</option>
                                    <option value="outlook" <?php if ($smtp_provider === 'outlook') echo 'selected'; ?>>Outlook</option>
                                    <option value="other" <?php if ($smtp_provider === 'other') echo 'selected'; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="smtp_host">Host</label>
                                <input type="text" name="opt[smtp_host]" id="smtp_host" class="form-control" placeholder="SMTP Host" value="<?php echo !empty($opt['smtp_host']) ? $opt['smtp_host'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="smtp_port">Port</label>
                                <input type="number" name="opt[smtp_port]" id="smtp_port" class="form-control" placeholder="SMTP Port" value="<?php echo !empty($opt['smtp_port']) ? $opt['smtp_port'] : ''; ?>">
                            </div>
                            <?php
                            $smtp_tls = !empty($opt['smtp_tls']) ? filter_var($opt['smtp_tls'], FILTER_VALIDATE_BOOLEAN) : FALSE;
                            ?>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="smtp_tls" name="opt[smtp_tls]" <?php echo $smtp_tls ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="smtp_tls">Use TLS</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="smtp_email">Email</label>
                                <input type="email" name="opt[smtp_email]" id="smtp_email" class="form-control" placeholder="Email" value="<?php echo !empty($opt['smtp_email']) ? $opt['smtp_email'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="smtp_password">Password</label>
                                <input type="password" name="opt[smtp_password]" id="smtp_password" class="form-control" placeholder="Password" value="<?php echo !empty($opt['smtp_password']) ? $opt['smtp_password'] : ''; ?>" autocomplete="off">
                            </div>
                            <div class="form-group">
                                <label for="smtp_sender">Sender Name</label>
                                <input type="text" name="opt[smtp_sender]" id="smtp_sender" class="form-control" placeholder="Sender Name" value="<?php echo !empty($opt['smtp_sender']) ? $opt['smtp_sender'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="smtp_reply_email">Reply To Email</label>
                                <input type="email" name="opt[smtp_reply_email]" id="smtp_reply_email" class="form-control" placeholder="Reply To Email" value="<?php echo !empty($opt['smtp_reply_email']) ? $opt['smtp_reply_email'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label for="smtp_reply_name">Reply To Recipient</label>
                                <input type="text" name="opt[smtp_reply_name]" id="smtp_reply_name" class="form-control" placeholder="Reply To Recipient" value="<?php echo !empty($opt['smtp_reply_name']) ? $opt['smtp_reply_name'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane pt-3 fade" id="other" role="tabpanel" aria-labelledby="other-tab">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group d-none">
                                <input type="text" class="form-control" name="opt[anti_captcha]" id="anti_captcha" value="<?php echo !empty($opt['anti_captcha']) ? $opt['anti_captcha'] : ''; ?>" placeholder="Anti Captcha API Key">
                                <small class="form-text text-muted">https://anti-captcha.com/clients/settings/apisetup</small>
                            </div>
                            <div class="form-group">
                                <label for="uptobox_api">Uptobox Token</label>
                                <input type="text" class="form-control" name="opt[uptobox_api]" id="uptobox_api" value="<?php echo !empty($opt['uptobox_api']) ? $opt['uptobox_api'] : ''; ?>" placeholder="Uptobox Token">
                            </div>
                            <div class="form-group">
                                <label for="bypass_host">Bypassed Hosts</label>
                                <select multiple="multiple" id="bypass_host" name="opt[bypass_host][]">
                                    <?php
                                    $bypassed = !empty($opt['bypass_host']) ? json_decode($opt['bypass_host'], true) : ['anonfile', 'bayfiles', 'clicknupload', 'clipwatching', 'dropbox', 'indishare', 'fembed', 'filerio', 'gdrive', 'googlephotos', 'hxfile', 'mixdropto', 'okru', 'playtube', 'senditcloud', 'streamtape', 'uploadsmobi', 'uqload', 'userscloud', 'vidlox', 'vidmoly', 'vidoza', 'yadisk', 'yourupload', 'upstream', 'supervideo', 'streamwire', 'vupto', 'tiktok'];
                                    $host = json_decode(vidhost_supported('', '', false, true), true);
                                    foreach ($host as $k => $v) {
                                        echo '<option value="' . $k . '" ' . (in_array($k, $bypassed) ? 'selected' : '') . '>' . $v . '</option>';
                                    }
                                    ?>
                                </select>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-warning btn-sm" id="resetHost" onclick="settings.resetHost()">Reset Hosts</button>
                                </div>
                                <small class="form-text text-muted">The bypassed hosts will use your VPS bandwidth to stream the video. The bypassed hosts are in the right or bottom column. After you change this setting, please clear the embed cache.</small>
                            </div>
                            <div class="form-group">
                                <label for="disable_host">Disabled Hosts</label>
                                <select multiple="multiple" id="disable_host" name="opt[disable_host][]">
                                    <?php
                                    $disabled = !empty($opt['disable_host']) ? json_decode($opt['disable_host'], true) : [];
                                    $host = json_decode(vidhost_supported('', '', false, true), true);
                                    foreach ($host as $k => $v) {
                                        echo '<option value="' . $k . '" ' . (in_array($k, $disabled) ? 'selected' : '') . '>' . $v . '</option>';
                                    }
                                    ?>
                                </select>
                                <small class="form-text text-muted">The disabled hosts are in the right or bottom column.</small>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="disable_proxy" name="opt[disable_proxy]" value="true" <?php echo !empty($opt['disable_proxy']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="disable_proxy">Disable proxies</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="free_proxy" name="opt[free_proxy]" value="true" <?php echo !empty($opt['free_proxy']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="free_proxy">Free Proxies Excluded</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="delete_unused_proxy" name="opt[delete_unused_proxy]" value="true" <?php echo !empty($opt['delete_unused_proxy']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="delete_unused_proxy">Delete Unused Proxies</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="proxy_list">
                                    <span class="mr-2">Proxy List</span>
                                    <i class="fa fa-info-circle" data-toggle="tooltip" title="The proxy list is used to retrieve the Google Drive direct link."></i>
                                </label>
                                <textarea name="opt[proxy_list]" id="proxy_list" cols="30" rows="5" class="form-control" placeholder="Enter the proxy line. Format: ip:port,username:password,socks5 OR ip:port,username:password OR ip:port,socks5 OR ip:port"><?php echo !empty($opt['proxy_list']) ? trim(htmlspecialchars($opt['proxy_list'])) : ''; ?></textarea>
                                <button type="button" class="btn btn-primary btn-sm mt-3" id="checkProxy" onclick="settings.checkProxy()">Proxy Checker</button>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label for="word_blacklisted">Blacklisted Words</label>
                                <textarea name="opt[word_blacklisted]" id="word_blacklisted" cols="30" rows="5" class="form-control" placeholder="Enter the words you blacklisted (per line). Example: porn"><?php echo !empty($opt['word_blacklisted']) ? trim(htmlspecialchars($opt['word_blacklisted'])) : ''; ?></textarea>
                                <p><small class="form-text text-muted">Videos will not play if the title has blacklisted words.</small></p>
                                <button type="button" class="btn btn-danger btn-sm" onclick="settings.deleteVideosWithWords()" id="deleteVideosBlacklisted"><i class="fa fa-trash"></i>&nbsp; Delete Videos With Blacklisted Words</button>
                            </div>
                            <div class="form-group">
                                <label for="domain_whitelisted">Whitelisted Domains/IP Addesses</label>
                                <textarea name="opt[domain_whitelisted]" id="domain_whitelisted" cols="30" rows="5" class="form-control" placeholder="Enter a domain / ip you trust (per line). Example: userweb.com"><?php echo !empty($opt['domain_whitelisted']) ? trim(htmlspecialchars($opt['domain_whitelisted'])) : ''; ?></textarea>
                                <small class="form-text text-muted">Only whitelisted websites can access this tool. If the whitelist is left blank, all websites can access this tool.</small>
                            </div>
                            <div class="form-group">
                                <label for="domain_blacklisted">Blacklisted Domains/IP Addresses</label>
                                <textarea name="opt[domain_blacklisted]" id="domain_blacklisted" cols="30" rows="5" class="form-control" placeholder="Enter the domain / ip you blacklisted (per line). Example: userweb.com"><?php echo !empty($opt['domain_blacklisted']) ? trim(htmlspecialchars($opt['domain_blacklisted'])) : ''; ?></textarea>
                                <small class="form-text text-muted">Blacklisted websites cannot access this tool. If left blank, all websites can access this tool.</small>
                            </div>
                            <div class="form-group">
                                <label for="link_blacklisted">Blacklisted Referers</label>
                                <textarea name="opt[link_blacklisted]" id="link_blacklisted" cols="30" rows="5" class="form-control" placeholder="Enter the link you blacklisted (per line). Example: http://userweb.com/movie1/"><?php echo !empty($opt['link_blacklisted']) ? trim(htmlspecialchars($opt['link_blacklisted'])) : ''; ?></textarea>
                                <small class="form-text text-muted">Blacklisted referers cannot access this tool. If left blank, all referers can access this tool.</small>
                            </div>
                            <div class="form-group">
                                <button type="button" id="clearCache" class="btn btn-danger" onclick="settings.clearCache()"><i class="fa fa-erase"></i>&nbsp; Clear Cache</button>
                                <button type="button" id="clearVideoInfo" class="btn btn-warning" onclick="settings.clearVideoInfoCache()"><i class="fa fa-erase"></i>&nbsp; Clear Video Info Cache</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="button" class="btn btn-secondary" onclick="location.href='admin.php?go=videos'">
                        <i class="fa fa-arrow-left mr-2"></i>
                        <span>Back</span>
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-2"></i>
                        <span>Update</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
