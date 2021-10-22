<?php
if (!defined('BASE_DIR')) {
    http_response_code(403);
    exit;
}

// cek apakah super admin atau tidak
// hanya super admin yang bisa menambah user baru
$login = new login();
$userLogin = $login->cek_login();
if (!$userLogin || !is_admin()) {
    include_once 'views/403.php';
    exit;
}
?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">User List</h1>
        <div class="mb-3">
            <a href="./admin.php?go=users/new" class="btn btn-success btn-sm">
                <i class="fa fa-plus-circle"></i>
                <span class="ml-2">Add New</span>
            </a>
            <button type="button" class="btn btn-info btn-sm" onclick="users.list()">
                <i class="fa fa-refresh"></i>
                <span class="ml-2">Reload</span>
            </button>
        </div>
        <table id="tbUsers" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Added On</th>
                    <th>Updated On</th>
                    <th>Role</th>
                    <th>Links</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Added On</th>
                    <th>Updated On</th>
                    <th>Role</th>
                    <th>Links</th>
                    <th>Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
