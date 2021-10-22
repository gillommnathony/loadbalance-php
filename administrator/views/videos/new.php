<?php
if (!defined('BASE_DIR')) {
    http_response_code(403);
    exit;
}

// cek apakah super admin atau tidak
// hanya super admin yang bisa menambah user baru
$login = new login();
$userLogin = $login->cek_login();
if (!$userLogin) {
    include_once 'views/403.php';
    exit;
}

$data   = $_POST;
$error  = [];
if (!empty($data)) {
    $videos  = new videos();
    $insert = $videos->insert($data);
    if (!$insert) {
        $error = $videos->get_errors();
    }
}
?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">Add New Video</h1>
        <?php
        if (!empty($data)) {
            if (!empty($error)) {
                $alert = '<div class="alert alert-danger">';
                $cError = count($error);
                $i = 0;
                foreach ($error as $err) {
                    $alert .= '<i class="fa fa-exclamation-circle"></i><span class="ml-2">' . $err . '</span>';
                    if ($i < ($cError - 1)) $alert .= '<br>';
                    $i++;
                }
                $alert .= '</div>';
            } else {
                $alert = '<div class="alert alert-success"><i class="fa fa-check"></i><span class="ml-2">New video added successfully.</span></div>';
            }
            echo $alert;
        }
        ?>
        <form action="./admin.php?go=videos/new" method="post" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <div class="form-group">
                        <input type="text" name="title" id="title" class="form-control" placeholder="Enter a title">
                        <div class="invalid-feedback">Must be filled!</div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="url" id="host_id" name="host_id" class="form-control" placeholder="Main Video Link" value="<?php echo !empty($data['host_id']) ? $data['host_id'] : ''; ?>" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" data-tooltip="true" title="Example Link Format">
                                    <i class="fa fa-info"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="url" id="ahost_id" name="ahost_id" class="form-control" placeholder="Alternative Video Link" value="<?php echo !empty($data['ahost_id']) ? $data['ahost_id'] : ''; ?>">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-info" data-tooltip="true" title="Example Link Format">
                                    <i class="fa fa-info"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div id="subsWrapper">
                        <div class="form-group" data-index="0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <?php echo subtitle_languages('language[]'); ?>
                                </div>
                                <input type="url" name="subtitle[]" class="form-control" placeholder="Subtitle link (.srt/.vtt)">
                                <div class="input-group-append">
                                    <button type="button" title="Upload Subtitle" class="btn btn-primary" onclick="videos.modalSubtitle($(this))">
                                        <i class="fa fa-upload"></i>
                                    </button>
                                    <button type="button" title="Add Subtitle" class="btn btn-warning" onclick="videos.addSubtitleHTML()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
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
                    <button name="simpan" type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-2"></i>
                        <span>Save</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include_once BASE_DIR . 'includes/link_format.php' ?>
