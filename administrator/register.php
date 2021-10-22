<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/functions.php';

if (isset($_POST['submit'])) {
    $sitename = sitename();
    $recaptchValidation = recaptcha_validate($_POST['captcha-response']);
    if ($recaptchValidation) {
        $user = new \users();
        $checkEmail = $user->check_email($_POST['email']);
        $checkUser = $user->check_username($_POST['user']);

        // validasi
        if ($checkEmail) {
            create_alert('warning', 'The email is already registered, please use another one!', BASE_URL . 'administrator/?go=register');
        } elseif ($checkUser) {
            create_alert('warning', 'The username is already registered, please use another one!', BASE_URL . 'administrator/?go=register');
        } elseif ($_POST['password'] !== $_POST['retype_password']) {
            create_alert('warning', 'Confirm password must be the same as the password!', BASE_URL . 'administrator/?go=register');
        } elseif (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $message = @file_get_contents(BASE_DIR . 'administrator/templates/registration-email.php');
            $exp = time() + 600;
            if (!filter_var(get_option('disable_confirm'), FILTER_VALIDATE_BOOLEAN) && !empty(get_option('smtp_email')) && !empty(get_option('smtp_password'))) {
                if ($message) {
                    $link = BASE_URL . 'administrator/register.php?token=' . encode($_POST['email'] . '|' . $exp);
                    $message = strtr(
                        $message,
                        [
                            '{recepient_name}' => $_POST['name'],
                            '{email_confirmation_link}' => $link,
                            '{sitename}' => $sitename
                        ]
                    );

                    $_POST['status'] = 2;
                    $_POST['role']   = 1;

                    unset($_POST['submit']);
                    unset($_POST['g-recaptcha-response']);
                    unset($_POST['action']);

                    try {
                        $mail = new \mailer();
                        $send = $mail->send([
                            'sendto' => [
                                'name' => $_POST['name'],
                                'email' => $_POST['email']
                            ],
                            'subject' => 'Confirmation email (' . $sitename . ') | ' . $_POST['name'],
                            'message' => $message
                        ]);
                        if ($send) {
                            $insert = $user->insert($_POST);
                            if ($insert) {
                                create_alert('success', 'Registration successful! Please check the confirmation email in the inbox / spam folder in your email.', BASE_URL . 'administrator/?go=register');
                            } else {
                                $msg = '';
                                foreach ($user->get_errors() as $err) {
                                    $msg .= $err . '<br>';
                                }
                                create_alert('danger', $msg, BASE_URL . 'administrator/?go=register');
                            }
                        } else {
                            create_alert('warning', $mail->get_errors(), BASE_URL . 'administrator/?go=register');
                        }
                    } catch (Exception $e) {
                        create_alert('danger', $e->getMessage(), BASE_URL . 'administrator/?go=register');
                    }
                }
            } else {
                $_POST['status'] = 1;
                $_POST['role']   = 1;

                unset($_POST['submit']);
                unset($_POST['g-recaptcha-response']);
                unset($_POST['action']);

                $insert = $user->insert($_POST);
                if ($insert) {
                    create_alert('success', 'Registration successful!', BASE_URL . 'administrator/?go=login');
                } else {
                    $msg = '';
                    foreach ($user->get_errors() as $err) {
                        $msg .= $err . '<br>';
                    }
                    create_alert('danger', $msg, BASE_URL . 'administrator/?go=register');
                }
            }
        } else {
            create_alert('warning', 'Incorrect email format!', BASE_URL . 'administrator/?go=register');
        }
    } else {
        create_alert('danger', 'The security code entered is incorrect!', BASE_URL . 'administrator/?go=register');
    }
} elseif (!empty($_GET['token'])) {
    $token = explode('|', decode($_GET['token']));
    $email = $token[0];
    $expired = (int) end($token);
    if (time() < $expired) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $get = $db->prepare('UPDATE `tb_users` SET `status`=?, `updated`=? WHERE `email`=?');
                $get->execute(array(1, time(), $email));
                create_alert('success', 'Registration is successful! Now you can login.', BASE_URL . 'administrator/?go=login');
            } catch (PDOException $e) {
                create_alert('danger', $e->getMessage(), BASE_URL . 'administrator/?go=register');
            }
        } else {
            create_alert('warning', 'Your email is invalid! Please enter a valid email.', BASE_URL . 'administrator/?go=register');
        }
    } else {
        $newExp = time() + 600;
        $resendLink = BASE_URL . 'administrator/register.php?token=' . encode($email . '|' . $newExp);
        create_alert('warning', 'The email confirmation link has expired, <a href="' . $resendLink . '">resend the link</a>.', BASE_URL . 'administrator/?go=register');
    }
} else {
    create_alert('danger', 'Please registeration from the register form provided!', BASE_URL . 'administrator/?go=register');
}
