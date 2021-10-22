<?php
if (!defined('BASE_DIR')) die('access denied!');

if (is_anonymous() || current_user()) :
    $embedUrl   = '';
    $dlUrl      = '';
    $reqUrl     = '';
    $jsonUrl    = '';
    $field      = $_POST;
    $query      = $field;
    if (!empty($query['id'])) {
        // ambil id video dari url
        if (filter_var($query['id'], FILTER_VALIDATE_URL)) {
            $xxx = getHostId(filter_var($query['id'], FILTER_SANITIZE_URL));
            $query['host'] = $xxx['host'];
            $query['id'] = $xxx['host_id'];
        }
        if (filter_var($query['aid'], FILTER_VALIDATE_URL)) {
            $xxx = getHostId(filter_var($query['aid'], FILTER_SANITIZE_URL));
            $query['ahost'] = $xxx['host'];
            $query['aid'] = $xxx['host_id'];
        }
        // recaptcha validation
        $recaptchaValidate = recaptcha_validate($query['captcha-response']);
        if ($recaptchaValidate) {
            unset($query['captcha-response']);
            unset($query['g-recaptcha-response']);
            $host = parse_url(BASE_URL, PHP_URL_HOST);
            $subs = [];
            foreach ($query['sub'] as $i => $sub) {
                // get filename
                $file = explode('/', $sub);
                $file = end($file);
                // update language
                $update = $db->prepare("UPDATE `tb_subtitle_manager` SET `language` = ? WHERE `file_name` = ? AND `host` LIKE ?");
                $update->execute(array($query['lang'][$i], $file, "%$host%"));
                // get id
                $man = $db->prepare("SELECT `id` FROM `tb_subtitle_manager` WHERE `file_name` = ? AND `host` LIKE ?");
                $man->execute(array($file, "%$host%"));
                $row = $man->fetch(\PDO::FETCH_ASSOC);
                if ($row) $subs[] = $row['id'];
                else $subs[] = $sub;
            }
            // vast ads aktif jika tanpa admin panel
            // atau jika dengan admin panel maka hanya bisa dipakai oleh admin saja
            if (!is_admin()) {
                unset($query['client']);
                unset($query['vast']);
                unset($query['offset']);
                unset($query['adskip']);
            }
            $query['sub'] = implode('~', $subs);
            $query['lang'] = implode('~', $query['lang']);
            // buat query string
            $qry = http_build_query($query);
            $qryencode = encode($qry);
            // embed url
            $embedUrl = filter_var(BASE_URL . 'embed/?' . $qryencode, FILTER_SANITIZE_URL);
            // downlod url
            $dlUrl = filter_var(BASE_URL . 'download/?' . $qryencode, FILTER_SANITIZE_URL);
            // request url
            $reqUrl = filter_var(BASE_URL . 'embed2/?' . $qry . '&onlylink=no', FILTER_SANITIZE_URL);
            // json url
            $jsonUrl = filter_var(BASE_URL . 'api.php?' . $qry, FILTER_SANITIZE_URL);
        }
    } else {
        newUpdate();
        autoupdateProxy();

        $field['id'] = '';
        $field['aid'] = '';
        $field['sub'] = [];
        $field['lang'] = [];
        $field['client'] = 'vast';
        $field['vast'] = '';
        $field['adskip'] = '5';
        $field['offset'] = 'pre';
        $field['poster'] = get_option('poster');
    }
?>
    <div class="row py-5 bg-custom text-center">
        <div class="col">
            <h1 class="h3"><?php echo sitename(); ?></h1>
            <p><?php echo get_option('site_description'); ?></p>
        </div>
    </div>
    <div class="row mt-5 mb-3">
        <div class="col">
            <form id="frm" action="<?php echo BASE_URL; ?>" method="post">
                <div class="form-group">
                    <div class="input-group">
                        <input type="url" id="id" name="id" class="form-control" placeholder="Main Video Link" value="<?php echo filter_var($field['id'], FILTER_SANITIZE_URL); ?>" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-info" data-tooltip="true" title="Example Link Format">
                                <i class="fa fa-info"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <input type="url" id="aid" name="aid" class="form-control" placeholder="Alternative Video Link" value="<?php echo filter_var($field['aid'], FILTER_SANITIZE_URL); ?>">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-info" data-tooltip="true" title="Example Link Format">
                                <i class="fa fa-info"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="poster" name="poster" class="form-control" placeholder="Poster Link (.jpg/.jpeg/.png/.webp)" value="<?php echo filter_var($field['poster'], FILTER_SANITIZE_URL); ?>">
                </div>
                <div id="contSubs">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <?php echo subtitle_languages('lang[]', (!empty($field['lang'][0]) ? $field['lang'][0] : '')); ?>
                            </div>
                            <input type="text" name="sub[]" class="form-control subtitle" placeholder="Subtitle Link (.srt/.vtt)" value="<?php echo !empty($field['sub'][0]) ? filter_var($field['sub'][0], FILTER_SANITIZE_URL) : ''; ?>">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" data-tooltip="true" title="Upload Subtitle" onclick="openModalSub($(this))">
                                    <i class="fa fa-upload"></i>
                                </button>
                                <button type="button" class="btn btn-warning" data-tooltip="true" title="Add Subtitle" onclick="addSubtitle()">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($field['sub']) && !empty($field['lang'])) : foreach ($field['sub'] as $i => $sub) : if ($i > 0) : ?>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <?php echo subtitle_languages('lang[]', (!empty($field['lang'][$i]) ? $field['lang'][$i] : '')); ?>
                                        </div>
                                        <input type="text" name="sub[]" class="form-control subtitle" placeholder="Subtitle Link (.srt/.vtt)" value="<?php echo !empty($field['sub'][$i]) ? filter_var($field['sub'][$i], FILTER_SANITIZE_URL) : ''; ?>">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" data-tooltip="true" title="Upload Subtitle" onclick="openModalSub($(this))">
                                                <i class="fa fa-upload"></i>
                                            </button>
                                            <button type="button" class="btn btn-warning" data-tooltip="true" title="Add Subtitle" onclick="addSubtitle()">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" data-tooltip="true" title="Remove Subtitle" onclick="removeSubtitle($(this))">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                    <?php endif;
                        endforeach;
                    endif; ?>
                </div>
                <?php if (is_admin()) : ?>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend" style="max-width:136px">
                                <?php
                                $adClient = !empty($field['client']) ? $field['client'] : '';
                                ?>
                                <select id="client" name="client" class="form-control">
                                    <option value="vast" <?php echo $adClient === 'vast' ? 'selected' : ''; ?>>VAST</option>
                                    <option value="googima" <?php echo $adClient === 'googima' ? 'selected' : ''; ?>>Google IMA</option>
                                </select>
                            </div>
                            <input type="text" name="vast" id="vast" class="form-control" placeholder="VAST Link (.xml)" value="<?php echo !empty($field['vast']) ? filter_var($field['vast'], FILTER_SANITIZE_URL) : ''; ?>">
                            <div class="input-group-append">
                                <?php
                                $adOffset = !empty($field['offset']) ? $field['offset'] : '';
                                ?>
                                <select id="offset" name="offset" class="form-control">
                                    <option value="pre" <?php echo $adOffset === 'pre' ? 'selected' : ''; ?>>Start</option>
                                    <option value="50%" <?php echo $adOffset === '50%' ? 'selected' : ''; ?>>Middle</option>
                                    <option value="post" <?php echo $adOffset === 'post' ? 'selected' : ''; ?>>End</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="number" id="adskip" name="adskip" class="form-control" placeholder="Time to skip ads (seconds)" value="<?php echo !empty($field['adskip']) ? intval($field['adskip']) : ''; ?>" min="0">
                            <div class="input-group-append">
                                <span class="input-group-text">Seconds</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-group text-center mb-0">
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
                        <span class="ml-2">Create Player</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="row py-3">
        <div class="col">
            <ul class="nav nav-pills nav-justified mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="url-tab" data-toggle="tab" href="#turl" role="tab" aria-controls="turl" aria-selected="true">Embed Link</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="embed-tab" data-toggle="tab" href="#tembed" role="tab" aria-controls="tembed" aria-selected="false">Embed Code</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="dl-tab" data-toggle="tab" href="#tdl" role="tab" aria-controls="tdl" aria-selected="false">Download Link</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="req-tab" data-toggle="tab" href="#treq" role="tab" aria-controls="requrl" aria-selected="true">Request Link</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="json-tab" data-toggle="tab" href="#tjson" role="tab" aria-controls="jsonurl" aria-selected="true">RESTful API Request</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="turl" role="tabpanel" aria-labelledby="url-tab">
                    <textarea onfocus="this.select()" style="font-size:13px" id="txtEmbed" cols="30" rows="6" class="form-control" readonly><?php echo $embedUrl; ?></textarea>
                </div>
                <div class="tab-pane fade" id="tembed" role="tabpanel" aria-labelledby="embed-tab">
                    <textarea onfocus="this.select()" style="font-size:13px" id="txtEmbedCode" cols="30" rows="6" class="form-control" readonly><?php echo htmlentities('<iframe src="' . $embedUrl . '" frameborder="0" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" width="640" height="320"></iframe>'); ?></textarea>
                </div>
                <div class="tab-pane fade" id="tdl" role="tabpanel" aria-labelledby="dl-tab">
                    <textarea onfocus="this.select()" style="font-size:13px" id="txtDl" cols="30" rows="6" class="form-control" readonly><?php echo $dlUrl; ?></textarea>
                </div>
                <div class="tab-pane fade" id="treq" role="tabpanel" aria-labelledby="req-tab">
                    <textarea onfocus="this.select()" style="font-size:13px" id="txtReq" cols="30" rows="6" class="form-control" readonly><?php echo $reqUrl; ?></textarea>
                </div>
                <div class="tab-pane fade" id="tjson" role="tabpanel" aria-labelledby="json-tab">
                    <p>You can access video sources using the RESTful API in various programming languages. For example, you can see the following link <a href="https://bit.ly/3b1XSCd" target="_blank">https://bit.ly/3b1XSCd</a>.</p>
                    <textarea onfocus="this.select()" style="font-size:13px" id="txtJson" cols="30" rows="6" class="form-control" readonly><?php echo $jsonUrl; ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($embedUrl)) : ?>
        <div class="row py-3 text-center">
            <div class="col-12">
                <iframe src="<?php echo $embedUrl ?>" frameborder="0" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" style="width:100%;height:90vh"></iframe>
            </div>
        </div>
    <?php endif; ?>
    <?php include_once 'includes/link_format.php'; ?>
    <?php if (file_exists('includes/disqus.php')) include_once 'includes/disqus.php'; ?>
<?php
else :
    echo error(403);
endif;
