<?php
if (!defined('BASE_DIR')) {
    http_response_code(403);
    exit;
}

$login = new login();
$userLogin = $login->cek_login();
if ($userLogin && $userLogin['user'] === 'demo') {
    include_once 'views/402.php';
    exit;
}

$error  = [];
$data   = $_POST;
if (!empty($data)) {
    $users  = new users();
    $update = $users->update_profile($data);
    if ($update) {
        $userLogin = $login->cek_login();
    } else {
        $error = $users->get_errors();
        $userLogin = $data;
    }
}
?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">My Profile</h1>
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
                $alert = '<div class="alert alert-success"><i class="fa fa-check"></i><span class="ml-2">Profile updated successfully.</span></div>';
            }
            echo $alert;
        }
        ?>
        <form action="./admin.php?go=users/profile" method="post" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" value="<?php if (!empty($userLogin['name'])) echo htmlspecialchars($userLogin['name']); ?>" class="form-control" placeholder="Your name" required>
                        <div class="invalid-feedback">Must be filled!</div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button class="btn btn-secondary" type="button" id="edit-email" data-toggle="tooltip" title="Change Email" onclick="$('#email').removeAttr('readonly')">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </div>
                            <input type="email" name="email" id="email" value="<?php if (!empty($userLogin['email'])) echo htmlspecialchars($userLogin['email']); ?>" class="form-control" placeholder="Your email" required readonly>
                            <div class="input-group-append">
                                <button class="btn btn-info rounded-right" type="button" data-toggle="tooltip" title="Save" onclick="users.changeEmail($('#email').val())">
                                    <i class="fa fa-save"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Must be valid!</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user">Username</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button class="btn btn-secondary" type="button" id="edit-user" data-toggle="tooltip" title="Edit Username" onclick="$('#user').removeAttr('readonly')">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </div>
                            <input type="text" name="user" id="user" value="<?php if (!empty($userLogin['user'])) echo htmlspecialchars($userLogin['user']); ?>" class="form-control" placeholder="Your username" required readonly>
                            <div class="input-group-append">
                                <button class="btn btn-info rounded-right" type="button" data-toggle="tooltip" title="Save" onclick="users.changeUsername($('#user').val())">
                                    <i class="fa fa-save"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Must be filled!</div>
                        </div>
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
                            <input type="password" name="password" id="password" class="form-control" placeholder="Your new password" onchange="if($(this).val() !== '') $('#retype_password').attr('required', 'true'); else $('#retype_password').removeAttr('required');">
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
                            <input type="password" name="retype_password" id="retype_password" class="form-control" placeholder="Retype your new password" onchange="if($(this).val() !== $('#password').val()) $(this).removeClass('is-valid').addClass('is-invalid'); else $(this).removeClass('is-invalid').addClass('is-valid');">
                            <div class="invalid-feedback">Must match the new password!</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button name="simpan" type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-2"></i>
                        <span>Save</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
