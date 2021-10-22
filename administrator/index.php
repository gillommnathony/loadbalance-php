<?php
session_write_close();
header('X-Frame-Options: SAMEORIGIN');
header('Developed-By: GDPlayer.top');

require_once "../vendor/autoload.php";
require_once "../includes/config.php";
require_once "../includes/functions.php";
require_once "includes/functions.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

newUpdate();
autoupdateProxy();

if (!empty($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']);
    list($email, $expired) = explode('|', decode($_GET['token']), 2);
    if (time() > $expired) {
        header('location: ' . BASE_URL . 'administrator/?go=reset-password');
        exit;
    }
} else {
    $token = '';
    $email = '';
    $expired = 0;
}

$login = new login();
if ($login->cek_login()) {
    //bawa ke halaman admin
    header("location: " . BASE_URL . "administrator/admin.php?go=videos");
    exit();
}
include_once 'header.php';

if (!empty($_GET['go']) && $_GET['go'] === 'register' && !filter_var(get_option('disable_registration'), FILTER_VALIDATE_BOOLEAN)) :
?>
    <div class="row py-5">
        <div class="col-12 col-md-6 col-lg-4 mx-auto">
            <h1 class="h3 my-3 text-center">Register</h1>
            <?php echo show_alert(); ?>
            <form id="frm" action="<?php echo BASE_URL . 'administrator/register.php'; ?>" method="post" class="needs-validation" novalidate>
                <div class="form-group">
                    <label for="username">Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Your name" required>
                    <div class="invalid-feedback">Must be filled!</div>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="user" name="user" class="form-control" placeholder="Your username" required>
                    <div class="invalid-feedback">Must be filled!</div>
                </div>
                <div class="form-group">
                    <label for="username">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Your email" required>
                    <div class="invalid-feedback">Must be filled!</div>
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" autocomplete="false" id="password" name="password" class="form-control" placeholder="New Password" onchange="if($(this).val() === $('#retype_password').val()) $('#retype_password').removeClass('is-invalid').addClass('is-valid'); else $('#retype_password').removeClass('is-valid').addClass('is-invalid');" required>
                    <div class="invalid-feedback">Must be filled!</div>
                </div>
                <div class="form-group">
                    <label for="password">Confirm New Password</label>
                    <input type="password" autocomplete="false" id="retype_password" name="retype_password" class="form-control" placeholder="Confirm New Password" onchange="if($(this).val() !== $('#password').val()) $(this).removeClass('is-valid').addClass('is-invalid'); else $(this).removeClass('is-invalid').addClass('is-valid');" required>
                    <div class="invalid-feedback">The new password confirmation must be the same as the new password!</div>
                </div>
                <div class="form-group text-center">
                    <div class="row">
                        <div class="col-6">
                            <a href="<?php echo BASE_URL . 'administrator/'; ?>" class="btn btn-block btn-secondary">
                                <i class="fa fa-arrow-left"></i><span class="ml-2">Login</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <?php
                            $recaptcha_site_key = get_option('recaptcha_site_key');
                            if ($recaptcha_site_key) :
                            ?>
                                <div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>" data-size="invisible" data-callback="gCallback"></div>
                            <?php
                            endif;
                            ?>
                            <input type="hidden" id="captcha-response" name="captcha-response" />
                            <button type="submit" name="submit" class="btn btn-block btn-custom">
                                <i class="fa fa-check"></i><span class="ml-2">Register</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
elseif (!empty($_GET['go']) && $_GET['go'] === 'reset-password') :
    if (!empty($token)) :
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && $expired > time()) :
    ?>
            <div class="row py-5">
                <div class="col-12 col-md-6 col-lg-4 mx-auto">
                    <h1 class="h3 my-3 text-center">Reset Password</h1>
                    <?php echo show_alert(); ?>
                    <form id="frm" action="<?php echo BASE_URL . 'administrator/reset-password.php?save=true'; ?>" method="post" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" autocomplete="false" id="password" name="password" class="form-control" placeholder="New Password" onchange="if($(this).val() === $('#retype_password').val()) $('#retype_password').removeClass('is-invalid').addClass('is-valid'); else $('#retype_password').removeClass('is-valid').addClass('is-invalid');" required>
                            <div class="invalid-feedback">Must be filled!</div>
                        </div>
                        <div class="form-group">
                            <label for="password">Confirm New Password</label>
                            <input type="password" autocomplete="false" id="retype_password" name="retype_password" class="form-control" placeholder="Confirm New Password" onchange="if($(this).val() !== $('#password').val()) $(this).removeClass('is-valid').addClass('is-invalid'); else $(this).removeClass('is-invalid').addClass('is-valid');" required>
                            <div class="invalid-feedback">The new password confirmation must be the same as the new password!</div>
                        </div>
                        <div class="form-group text-center">
                            <div class="row">
                                <div class="col-12">
                                    <?php
                                    $recaptcha_site_key = get_option('recaptcha_site_key');
                                    if ($recaptcha_site_key) :
                                    ?>
                                        <div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>" data-size="invisible" data-callback="gCallback"></div>
                                    <?php
                                    endif;
                                    ?>
                                    <input type="hidden" id="captcha-response" name="captcha-response" />
                                    <input type="hidden" name="email" value="<?php echo $email; ?>">
                                    <input type="hidden" name="expired" value="<?php echo $expired; ?>">
                                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                                    <button type="submit" name="submit" class="btn btn-custom btn-block send-button">
                                        <i class="fa fa-refresh"></i><span class="ml-2">Reset Password</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php
        endif;
    else :
        ?>
        <div class="row py-5">
            <div class="col-12 col-md-6 col-lg-4 mx-auto">
                <h1 class="h3 my-3 text-center">Reset Password</h1>
                <?php echo show_alert(); ?>
                <form id="frm" action="<?php echo BASE_URL . 'administrator/reset-password.php'; ?>" method="post" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="username">Username / Email</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username / email address" required>
                        <div class="invalid-feedback">Must be filled!</div>
                    </div>
                    <div class="form-group text-center">
                        <div class="row">
                            <div class="col-6">
                                <?php
                                $recaptcha_site_key = get_option('recaptcha_site_key');
                                if ($recaptcha_site_key) :
                                ?>
                                    <div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>" data-size="invisible" data-callback="gCallback"></div>
                                <?php
                                endif;
                                ?>
                                <input type="hidden" id="captcha-response" name="captcha-response" />
                                <button type="submit" name="submit" class="btn btn-custom btn-block send-button">
                                    <i class="fa fa-refresh"></i><span class="ml-2">Reset Password</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo BASE_URL; ?>administrator/?go=login" class="btn btn-block btn-secondary">
                                    <span>Login</span>
                                    <i class="fa fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php
    endif;
else :
    ?>
    <div class="row py-5">
        <div class="col-12 col-md-6 col-lg-4 mx-auto">
            <h1 class="h3 my-3 text-center">Login</h1>
            <?php echo show_alert(); ?>
            <form id="frm" action="<?php echo BASE_URL . 'administrator/login.php'; ?>" method="post" class="needs-validation" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username / email address" required>
                    <div class="invalid-feedback">Must be filled!</div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" autocomplete="false" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    <div class="invalid-feedback">Must be filled!</div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="rmb" name="remember" value="1">
                        <label class="custom-control-label" for="rmb">Remember Me</label>
                    </div>
                </div>
                <div class="form-group text-center">
                    <div class="row">
                        <div class="col">
                            <?php
                            $recaptcha_site_key = get_option('recaptcha_site_key');
                            if ($recaptcha_site_key) :
                            ?>
                                <div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>" data-size="invisible" data-callback="gCallback"></div>
                            <?php
                            endif;
                            ?>
                            <input type="hidden" id="captcha-response" name="captcha-response" />
                            <button type="submit" name="submit" class="btn btn-custom btn-block send-button">
                                <i class="fa fa-lock"></i><span class="ml-2">Login</span>
                            </button>
                        </div>
                        <?php if (!filter_var(get_option('disable_registration'), FILTER_VALIDATE_BOOLEAN)) : ?>
                            <div class="col">
                                <a href="<?php echo BASE_URL; ?>administrator/?go=register" class="btn btn-block btn-secondary">
                                    <span>Register</span>
                                    <i class="fa fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
endif;

include_once 'footer.php';

ob_end_flush();
