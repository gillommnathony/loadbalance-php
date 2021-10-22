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
?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">Subtitle List</h1>
        <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm" onclick="videos.modalSubtitle()">
                <i class="fa fa-upload"></i>
                <span class="ml-2 d-none d-md-inline">Upload</span>
            </button>
            <button type="button" class="btn btn-info btn-sm" onclick="subtitles.list()">
                <i class="fa fa-refresh"></i>
                <span class="ml-2 d-none d-md-inline">Reload</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="subtitles.deleteChecked()" title="Delete">
                <i class="fa fa-trash"></i>
                <span class="ml-2 d-none d-md-inline">Delete</span>
            </button>
        </div>
        <table id="tbSubtitles" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="max-width:20px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAllSubtitles">
                            <label class="custom-control-label" for="ckAllSubtitles"></label>
                        </div>
                    </th>
                    <th>File Name</th>
                    <th>Language</th>
                    <th>User</th>
                    <th>Location</th>
                    <th>Added On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th style="max-width:20px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAllSubtitles1">
                            <label class="custom-control-label" for="ckAllSubtitles1"></label>
                        </div>
                    </th>
                    <th>File Name</th>
                    <th>Language</th>
                    <th>User</th>
                    <th>Location</th>
                    <th>Added On</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
