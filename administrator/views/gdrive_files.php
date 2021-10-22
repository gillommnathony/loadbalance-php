<?php
if (!defined('BASE_DIR')) {
    http_response_code(403);
    exit;
}

// cek apakah super admin atau tidak
// hanya super admin yang bisa menambah user baru
$login = new \login();
$userLogin = $login->cek_login();
if (!$userLogin || !is_admin()) {
    include_once 'views/403.php';
    exit;
}
?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">Google Drive Videos</h1>
        <p>List of videos found in your Google Drive account. If you add video files to your Google Drive account directly and from the anti-limit feature they will be displayed here.</p>
        <div class="row">
            <div class="col-12 col-md-4 mb-3">
                <select name="email" id="email" class="custom-select custom-select-sm">
                    <?php
                    $gda = new \gdrive_auth();
                    $accounts = $gda->get_accounts();
                    foreach ($accounts as $acc) {
                        echo '<option value="' . $acc['email'] . '">' . $acc['email'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-12 col-md-8 mb-3">
                <button type="button" class="btn btn-primary btn-sm" onclick="gdrive_files.publicChecked();">
                    <i class="fa fa-eye"></i><span class="d-none d-md-inline-block ml-2">Make Public</span>
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="gdrive_files.privateChecked();">
                    <i class="fa fa-eye-slash"></i><span class="d-none d-md-inline-block ml-2">Make Private</span>
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="gdrive_files.deleteChecked()">
                    <i class="fa fa-trash"></i><span class="d-none d-md-inline-block ml-2">Delete</span>
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="gdrive_files.reload();">
                    <i class="fa fa-refresh"></i><span class="d-none d-md-inline-block ml-2">Reload</span>
                </button>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="onlyPrivate" value="true" onchange="gdrive_files.reload()">
                    <label class="custom-control-label" for="onlyPrivate">Displays only private files</label>
                </div>
            </div>
        </div>
        <table id="tbGDFiles" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="width:60px;max-width:60px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAll">
                            <label class="custom-control-label" for="ckAll"></label>
                        </div>
                    </th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Links</th>
                    <th>Shared</th>
                    <th>Editable</th>
                    <th>Copyable</th>
                    <th>Created On</th>
                    <th>Modified On</th>
                    <th style="width:100px">Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th style="width:60px;max-width:60px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAll1">
                            <label class="custom-control-label" for="ckAll1"></label>
                        </div>
                    </th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Links</th>
                    <th>Shared</th>
                    <th>Editable</th>
                    <th>Copyable</th>
                    <th>Created On</th>
                    <th>Modified On</th>
                    <th style="width:100px">Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
