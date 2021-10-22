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

$data = $_POST;
$error = [];
if (!empty($data)) {
    $users  = new users();
    $insert = $users->insert($data);
    if (!$insert) {
        $error = $users->get_errors();
    }
}
?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">New User</h1>
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
                $alert = '<div class="alert alert-success"><i class="fa fa-check"></i><span class="ml-2">
                New user added successfully.</span></div>';
            }
            echo $alert;
        }
        ?>
        <form action="./admin.php?go=users/new" method="post" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Your name" required>
                        <div class="invalid-feedback">Must be filled!</div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Your email address" required>
                        <div class="invalid-feedback">Must be valid!</div>
                    </div>
                    <div class="form-group">
                        <label for="user">Username</label>
                        <input type="text" name="user" id="user" class="form-control" placeholder="Your username" required>
                        <div class="invalid-feedback">Must be filled!</div>
                    </div>
                    <div class="form-group">
                        <label for="name">User Role</label>
                        <select name="role" id="role" class="custom-select" required>
                            <option value="">-- Select User Role --</option>
                            <option value="0">Administrator</option>
                            <option value="1">User</option>
                        </select>
                        <div class="invalid-feedback">Must choose one!</div>
                    </div>
                    <div class="form-group">
                        <label for="name">Status</label>
                        <select name="status" id="status" class="custom-select" required>
                            <option value="">-- Select Status --</option>
                            <option value="0">Inactive</option>
                            <option value="1">Active</option>
                            <option value="2">Need Approval</option>
                        </select>
                        <div class="invalid-feedback">Must choose one!</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button class="btn btn-secondary" type="button" data-toggle="tooltip" title="Show/hide New Password" onclick="if($('#password').attr('type') === 'password'){ $('#password').attr('type', 'text');$(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');} else{ $('#password').attr('type', 'password');$(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');}">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Your new password" onchange="if($(this).val() === $('#retype_password').val()) $('#retype_password').removeClass('is-invalid').addClass('is-valid'); else $('#retype_password').removeClass('is-valid').addClass('is-invalid');" required>
                            <div class="invalid-feedback">Must be filled!</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Retype New Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button class="btn btn-secondary" type="button" data-toggle="tooltip" title="Show/hide retype new password" onclick="if($('#retype_password').attr('type') === 'password'){ $('#retype_password').attr('type', 'text');$(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash'); }else{ $('#retype_password').attr('type', 'password');$(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');}">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                            <input type="password" name="retype_password" id="retype_password" class="form-control" placeholder="Retype your new password" onchange="if($(this).val() !== $('#password').val()) $(this).removeClass('is-valid').addClass('is-invalid'); else $(this).removeClass('is-invalid').addClass('is-valid');" required>
                            <div class="invalid-feedback">Must match the new password!</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="button" class="btn btn-secondary" onclick="location.href='admin.php?go=users'">
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
