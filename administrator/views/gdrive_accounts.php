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
        <h1 class="h4 mb-3">Google Drive Accounts</h1>
        <p>You can add a Google Drive account from this menu without having to manually create a json file.</p>
        <div class="row mb-3">
            <div class="col-12 col-md-8">
                <a href="./admin.php?go=gdrive_accounts/new" class="btn btn-success btn-sm">
                    <i class="fa fa-plus-circle"></i>
                    <span class="ml-2">Add New</span>
                </a>
                <button type="button" class="btn btn-danger btn-sm" onclick="gdrive_accounts.deleteChecked();">
                    <i class="fa fa-trash"></i><span class="ml-2">Delete</span>
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="gdrive_accounts.reload();">
                    <i class="fa fa-refresh"></i><span class="ml-2">Reload</span>
                </button>
            </div>
        </div>
        <table id="tbGDAccounts" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="width:60px;max-width:60px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAllGDA">
                            <label class="custom-control-label" for="ckAllGDA"></label>
                        </div>
                    </th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Added On</th>
                    <th>Updated On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th style="width:60px;max-width:60px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAllGDA1">
                            <label class="custom-control-label" for="ckAllGDA1"></label>
                        </div>
                    </th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Added On</th>
                    <th>Updated On</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
