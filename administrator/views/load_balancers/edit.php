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

// cek id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (empty($id)) {
    include_once 'views/404.php';
    exit;
}

$error  = [];
$data   = $_POST;
$lb  = new \load_balancers();
if (!empty($data)) {
    $update = $lb->update($data);
    $error = $lb->get_errors();
}
$get = $lb->get($id);
?>
<div class="row py-3">
    <div class="col-12">
        <h1 class="h4 mb-3">Edit Load Balancer</h1>
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
                Load balancer updated successfully.</span></div>';
            }
            echo $alert;
        }
        ?>
        <form action="./admin.php?go=load_balancers/edit&id=<?php echo $id; ?>" method="post" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo strip_tags($get['name']); ?>" required>
                        <div class="invalid-feedback">Must be filled!</div>
                    </div>
                    <div class="form-group">
                        <label for="link">Link</label>
                        <input type="url" name="link" id="link" class="form-control" value="<?php echo filter_var($get['link'], FILTER_SANITIZE_URL); ?>" required>
                        <div class="invalid-feedback">Must be valid!</div>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="custom-select" required>
                            <option value="0">Inactive</option>
                            <option value="1" <?php echo $get['status'] == 1 ? 'selected' : ''; ?>>Active</option>
                        </select>
                        <div class="invalid-feedback">Must choose one!</div>
                    </div>
                    <div class="form-group">
                        <label for="public">Public</label>
                        <select name="public" id="public" class="custom-select" required>
                            <option value="0">Hide</option>
                            <option value="1" <?php echo $get['public'] == 1 ? 'selected' : ''; ?>>Show</option>
                        </select>
                        <small class="form-text text-muted">Show/hide the video player generator to the public.</small>
                        <div class="invalid-feedback">Must choose one!</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="button" class="btn btn-secondary" onclick="location.href='admin.php?go=load_balancers'">
                        <i class="fa fa-arrow-left mr-2"></i>
                        <span>Back</span>
                    </button>
                    <button name="simpan" type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-2"></i>
                        <span>Save</span>
                    </button>
                    <input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
                </div>
            </div>
        </form>
    </div>
</div>
