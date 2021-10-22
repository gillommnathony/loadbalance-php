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
        <h1 class="h4 mb-3">Video List</h1>
        <div class="mb-3">
            <a href="./admin.php?go=videos/new" class="btn btn-success btn-sm" title="Add New">
                <i class="fa fa-plus-circle"></i>
                <span class="ml-2 d-none d-md-inline">Add New</span>
            </a>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="collapse" data-target="#bulkLinkCollapse" title="Add Bulk Link">
                <i class="fa fa-plus-square"></i>
                <span class="ml-2 d-none d-md-inline">Add Bulk Link</span>
            </button>
            <button type="button" class="btn btn-warning btn-sm" onclick="videos.checker($(this))" title="Video Checker">
                <i class="fa fa-check-circle"></i>
                <span class="ml-2 d-none d-md-inline">Video Checker</span>
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="videos.clearCache()" title="Clear Cache">
                <i class="fa fa-eraser"></i>
                <span class="ml-2 d-none d-md-inline">Clear Cache</span>
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="videos.deleteChecked()" title="Delete">
                <i class="fa fa-trash"></i>
                <span class="ml-2 d-none d-md-inline">Delete</span>
            </button>
            <button type="button" class="btn btn-info btn-sm" onclick="videos.reload()" title="Reload">
                <i class="fa fa-refresh"></i>
                <span class="ml-2 d-none d-md-inline">Reload</span>
            </button>
        </div>
        <div class="collapse" id="bulkLinkCollapse">
            <form id="frmVideoBulkLink" class="mb-3">
                <div class="form-group">
                    <textarea name="links" id="links" cols="30" rows="8" class="form-control" placeholder="Add bulk link" required></textarea>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fa fa-save"></i>
                        <span class="ml-2">Save</span>
                    </button>
                    <button type="reset" class="btn btn-sm btn-secondary">
                        <i class="fa fa-refresh"></i>
                        <span class="ml-2">Reset</span>
                    </button>
                </div>
            </form>
            <div id="bulk-result"></div>
        </div>
        <table id="tbVideos" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="max-width:20px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAllVideos">
                            <label class="custom-control-label" for="ckAllVideos"></label>
                        </div>
                    </th>
                    <th>Title</th>
                    <th>Video Source(s)</th>
                    <th>Links</th>
                    <th>Subtitles</th>
                    <th>User</th>
                    <th>Added On</th>
                    <th>Updated On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th style="max-width:20px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAllVideos1">
                            <label class="custom-control-label" for="ckAllVideos1"></label>
                        </div>
                    </th>
                    <th>Title</th>
                    <th>Video Source(s)</th>
                    <th>Links</th>
                    <th>Subtitles</th>
                    <th>User</th>
                    <th>Added On</th>
                    <th>Updated On</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
