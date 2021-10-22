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
        <h1 class="h4 mb-3">Session List</h1>
        <div class="mb-3">
            <button type="button" class="btn btn-danger btn-sm" onclick="sessions.deleteChecked()">
                <i class="fa fa-trash"></i><span class="ml-2">Delete</span>
            </button>
            <button type="button" class="btn btn-info btn-sm" onclick="sessions.reload()">
                <i class="fa fa-refresh"></i>
                <span class="ml-2">Reload</span>
            </button>
        </div>
        <table id="tbSessions" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="max-width:20px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAllSessions">
                            <label class="custom-control-label" for="ckAllSessions"></label>
                        </div>
                    </th>
                    <th>Username</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Log On</th>
                    <th>Expires On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th style="max-width:20px">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="ckAllSessions1">
                            <label class="custom-control-label" for="ckAllSessions1"></label>
                        </div>
                    </th>
                    <th>Username</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Log On</th>
                    <th>Expires On</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
