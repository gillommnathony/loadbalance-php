<?php
if (!defined('BASE_DIR')) {
    http_response_code(403);
    exit;
}

// cek super admin atau tidak
$login = new login();
$userLogin = $login->cek_login();
if (!$userLogin) {
    include_once 'views/403.php';
    exit;
} elseif (!is_admin()) {
    include_once 'views/402.php';
    exit;
}

// cek id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (empty($id)) {
    include_once 'views/404.php';
    exit;
}

$error  = [];
$data   = $_POST;
$user  = new users();
if (!empty($data)) {
    $update = $user->update($data);
    $error = $user->get_errors();
}
$profile = $user->get($id);

?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">User Detail</h1>
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
                $alert = '<div class="alert alert-success">
                <i class="fa fa-check"></i>
                <span class="ml-2">Updated successfully</span>
                </div>';
            }
            echo $alert;
        }
        ?>
        <form action="./admin.php?go=users/edit&id=<?php echo $id; ?>" method="post" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" value="<?php if (!empty($profile['name'])) echo htmlspecialchars($profile['name']); ?>" class="form-control" placeholder="Your name" required>
                        <div class="invalid-feedback">Must be filled!</div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?php if (!empty($profile['email'])) echo htmlspecialchars($profile['email']); ?>" class="form-control" placeholder="Your email address" required>
                        <div class="invalid-feedback">Must be valid!</div>
                    </div>
                    <div class="form-group">
                        <label for="user">Username</label>
                        <input type="text" name="user" id="user" value="<?php if (!empty($profile['user'])) echo htmlspecialchars($profile['user']); ?>" class="form-control" placeholder="Your username" required>
                        <div class="invalid-feedback">Must be filled!</div>
                    </div>
                    <div class="form-group">
                        <label for="name">User Role</label>
                        <select name="role" id="role" class="custom-select" required>
                            <?php
                            $role = !is_null($profile['role']) ? intval($profile['role']) : '';
                            ?>
                            <option value="">-- Select User Role --</option>
                            <option value="0" <?php echo $role === 0 ? 'selected' : ''; ?>>Administrator</option>
                            <option value="1" <?php echo $role === 1 ? 'selected' : ''; ?>>User</option>
                        </select>
                        <div class="invalid-feedback">Must choose one!</div>
                    </div>
                    <div class="form-group">
                        <label for="name">Status</label>
                        <select name="status" id="status" class="custom-select" required>
                            <?php
                            $status = !is_null($profile['status']) ? intval($profile['status']) : '';
                            ?>
                            <option value="">-- Select Status --</option>
                            <option value="0" <?php echo $status === 0 ? 'selected' : ''; ?>>Inactive</option>
                            <option value="1" <?php echo $status === 1 ? 'selected' : ''; ?>>Active</option>
                            <option value="2" <?php echo $status === 2 ? 'selected' : ''; ?>>Need Approval</option>
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
                    <button type="button" class="btn btn-secondary" onclick="location.href='admin.php?go=users'">
                        <i class="fa fa-arrow-left mr-2"></i>
                        <span>Back</span>
                    </button>
                    <button name="simpan" type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-2"></i>
                        <span>Update</span>
                    </button>
                    <input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
                </div>
            </div>
        </form>
    </div>
</div>
