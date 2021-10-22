<?php
if (!defined('BASE_DIR')) exit;
$sitename = sitename();
$siteslogan = get_option('site_slogan');
$sitedescription = get_option('site_description');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo $siteslogan . ' - ' . $sitename; ?></title>
    <meta name="description" content="<?php echo $sitedescription ?>">

    <!-- facebook -->
    <meta name="og:sitename" content="<?php echo $sitename; ?>">
    <meta name="og:title" content="<?php echo $siteslogan . ' - ' . $sitename; ?>">
    <meta name="og:description" content="<?php echo $sitedescription; ?>">
    <meta name="og:type" content="website">

    <!-- twitter -->
    <meta name="twitter:title" content="<?php echo $siteslogan . ' - ' . $sitename; ?>">
    <meta name="twitter:description" content="<?php echo $sitedescription; ?>">

    <link rel="icon" href="<?php echo BASE_URL; ?>favicon.png" type="image/png">

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sweetalert.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <link rel="preconnect" href="//www.paypal.com">
    <link rel="preconnect" href="//www.sandbox.paypal.com">
    <link rel="preconnect" href="//static.addtoany.com">
    <link rel="preconnect" href="//www.google.com">

    <script src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
    <?php include_once 'includes/ga.php'; ?>
    <?php include_once 'includes/gtm_head.php'; ?>
</head>

<body class="bg-light my-5 pt-2">
    <?php include_once 'includes/gtm_body.php'; ?>
    <div class="container-lg bg-white rounded-bottom shadow">
        <header id="header">
            <nav class="navbar container-lg navbar-expand-lg navbar-dark fixed-top bg-custom shadow">
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>"><?php echo sitename(); ?></a>
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
                        if (is_admin() || filter_var(get_option('anonymous_generator'), FILTER_VALIDATE_BOOLEAN) && !filter_var(get_option('disable_gsharer'), FILTER_VALIDATE_BOOLEAN)) : ?>
                            <li class="nav-item">
                                <a class="nav-link hide" href="<?php echo BASE_URL; ?>sharer/">
                                    <i class="fa fa-google"></i>
                                    <span class="ml-1">Bypass Limit</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php $user = current_user(); ?>
                        <li class="nav-item">
                            <a class="nav-link hide" href="<?php echo BASE_URL; ?>administrator/">
                                <i class="fa fa-<?php echo $user ? 'user' : 'sign-in'; ?>"></i>
                                <span class="ml-1">
                                    <?php echo $user ? 'Dashboard' : 'Login'; ?>
                                </span>
                            </a>
                        </li>
                        <?php if (!$user && !filter_var(get_option('disable_registration'), FILTER_VALIDATE_BOOLEAN)) : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>administrator/?go=register">
                                    <i class="fa fa-user-plus mr-1"></i>
                                    <span>Register</span>
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
        <main id="main" role="main">
