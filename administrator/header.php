<?php
if (!defined('BASE_DIR')) exit();

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Control Panel - <?php echo sitename(); ?></title>

    <link rel="icon" href="<?php echo BASE_URL; ?>favicon.png" type="image/png">

    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/sweetalert.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/multi-select.dist.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/vendor/datatables/datatables.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/vendor/select2/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/select2-bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/vendor/jquery-wheelcolorpicker/css/wheelcolorpicker.css">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <script src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
    <?php include_once '../includes/ga.php'; ?>
    <?php include_once '../includes/gtm_head.php'; ?>
</head>

<body class="h-100">
    <?php include_once '../includes/gtm_body.php'; ?>
    <div class="container-fluid d-flex flex-column h-100">
        <header id="header">
            <nav class="navbar container-fluid navbar-expand-lg navbar-dark fixed-top bg-custom shadow">
                <a class="navbar-brand" href="<?php echo BASE_URL . 'administrator/'; ?>">Control Panel</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>">
                                <i class="fa fa-home"></i>
                                <span class="ml-1">Home</span>
                            </a>
                        </li>
                        <?php
                        $userLogin = $login->cek_login();
                        if ($userLogin) :
                            $go = !empty($_GET['go']) ? htmlentities($_GET['go']) : '';
                        ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?php echo strpos($go, 'videos') !== FALSE || strpos($go, 'subtitles') !== FALSE ? 'active' : ''; ?>" href="#" id="ndVideos" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-film"></i>
                                    <span class="ml-1">Video</span>
                                </a>
                                <div class="dropdown-menu shadow border-0" aria-labelledby="ndVideos">
                                    <a class="dropdown-item" href="admin.php?go=videos/new">
                                        <i class="fa fa-plus-circle fa-lg"></i>
                                        <span class="ml-1">Add New Video</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="admin.php?go=videos">
                                        <i class="fa fa-film"></i>
                                        <span class="ml-1">List</span>
                                    </a>
                                    <a class="dropdown-item" href="admin.php?go=subtitles">
                                        <i class="fa fa-copy"></i>
                                        <span class="ml-1">Subtitles</span>
                                    </a>
                                </div>
                            </li>
                            <?php
                            if (is_admin()) :
                            ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle <?php echo strpos($go, 'gdrive_') !== FALSE ? 'active' : ''; ?>" href="#" id="gdMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-google"></i>
                                        <span class="ml-1">Google Drive</span>
                                    </a>
                                    <div class="dropdown-menu shadow border-0" aria-labelledby="gdMenu">
                                        <a class="dropdown-item" href="admin.php?go=gdrive_accounts/new">
                                            <i class="fa fa-plus-circle fa-lg"></i>
                                            <span class="ml-1">Add New Account</span>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="admin.php?go=gdrive_accounts">
                                            <i class="fa fa-google"></i>
                                            <span class="ml-1">Accounts</span>
                                        </a>
                                        <a class="dropdown-item" href="admin.php?go=gdrive_files">
                                            <i class="fa fa-film"></i>
                                            <span class="ml-1">Videos</span>
                                        </a>
                                    </div>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle <?php echo strpos($go, 'users') !== FALSE ? 'active' : ''; ?>" href="#" id="gdUser" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-users"></i>
                                        <span class="ml-1">User</span>
                                    </a>
                                    <div class="dropdown-menu shadow border-0" aria-labelledby="gdUser">
                                        <a class="dropdown-item" href="admin.php?go=users/new">
                                            <i class="fa fa-plus-circle fa-lg"></i>
                                            <span class="ml-1">Add New User</span>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="admin.php?go=users">
                                            <i class="fa fa-users"></i>
                                            <span class="ml-1">List</span>
                                        </a>
                                        <a class="dropdown-item" href="admin.php?go=users_sessions">
                                            <i class="fa fa-history"></i>
                                            <span class="ml-1">Sessions</span>
                                        </a>
                                    </div>
                                </li>
                                <li class="nav-item dropdown">
                                    <?php
                                    $mSettings = '';
                                    if (strpos($go, 'settings') !== FALSE || strpos($go, 'balancers') !== FALSE) {
                                        $mSettings = 'active';
                                    }
                                    ?>
                                    <a class="nav-link dropdown-toggle <?php echo $mSettings; ?>" href="#" id="ndSettings" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-cogs"></i>
                                        <span class="ml-1">App</span>
                                    </a>
                                    <div class="dropdown-menu shadow border-0" aria-labelledby="ndSettings">
                                        <a class="dropdown-item" href="admin.php?go=load_balancers">
                                            <i class="fa fa-refresh"></i>
                                            <span class="ml-1">Load Balancers</span>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="admin.php?go=settings">
                                            <i class="fa fa-cog"></i>
                                            <span class="ml-1">Settings</span>
                                        </a>
                                    </div>
                                </li>
                            <?php
                            endif;
                            ?>
                            <?php if ($userLogin['user'] !== 'demo') : ?>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo strpos($go, 'profile') !== FALSE ? 'active' : ''; ?>" href="admin.php?go=users/profile">
                                        <i class="fa fa-user"></i>
                                        <span class="ml-1">Profile</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">
                                    <i class="fa fa-sign-out"></i>
                                    <span class="ml-1">Logout</span>
                                </a>
                            </li>
                        <?php else : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL . 'administrator/'; ?>">
                                    <i class="fa fa-sign-in"></i>
                                    <span class="ml-1">Login</span>
                                </a>
                            </li>
                            <?php if (!filter_var(get_option('disable_registration'), FILTER_VALIDATE_BOOLEAN)) : ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo BASE_URL . 'administrator/?go=register'; ?>">
                                        <i class="fa fa-user-plus"></i>
                                        <span class="ml-1">Register</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL . 'administrator/?go=reset-password'; ?>">
                                    <i class="fa fa-refresh"></i>
                                    <span class="ml-1">Reset Password</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (!filter_var(get_option('production_mode'), FILTER_VALIDATE_BOOLEAN)) : ?>
                            <li class="nav-item">
                                <a href="https://p-store.net/user/adis0308" class="btn btn-green btn-block" target="_blank">
                                    <i class="fa fa-shopping-basket"></i>
                                    <span class="ml-1">Buy</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </header>
        <main id="main" class="mt-2 pt-5 flex-grow-1" role="main">
