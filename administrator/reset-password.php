<?php

require_once '../vendor/autoload.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/functions.php';

if (isset($_POST['submit'])) {
    $sitename = sitename();
    $recaptchValidation = recaptcha_validate($_POST['captcha-response']);
    $token = !empty($_POST['token']) ? $_POST['token'] : '';
    if ($recaptchValidation) {
        if (!isset($_GET['save']) && !empty($_POST['username'])) {
            $user = new \users();
            $checkEmail = $user->check_email($_POST['username']);
            $checkUser = $user->check_username($_POST['username']);

            // validasi
            if ($checkEmail || $checkUser) {
                $data = $checkUser ? $checkUser : $checkEmail;
                $exp = time() + 600;
                if (!filter_var(get_option('disable_confirm'), FILTER_VALIDATE_BOOLEAN) && !empty(get_option('smtp_email')) && !empty(get_option('smtp_password'))) {
                    $message = @file_get_contents(BASE_DIR . 'administrator/templates/reset-password-email.php');
                    if ($message) {
                        try {
                            if (!empty($data)) {
                                $data = $user->get($data[0]['id']);

                                $link = BASE_URL . 'administrator/?go=reset-password&token=' . encode($data['email'] . '|' . $exp);
                                $message = strtr(
                                    $message,
                                    [
                                        '{recepient_name}' => $data['name'],
                                        '{reset_password_link}' => $link,
                                        '{sitename}' => $sitename
                                    ]
                                );

                                $mail = new \mailer();
                                $send = $mail->send([
                                    'sendto' => [
                                        'name' => $data['name'],
                                        'email' => $data['email']
                                    ],
                                    'subject' => 'Confirmation email (' . $sitename . ') | ' . $data['name'],
                                    'message' => $message
                                ]);
                                if ($send) {
                                    create_alert('success', 'A link to reset your password has been sent to your email.', BASE_URL . 'administrator/?go=reset-password');
                                } else {
                                    $msg = '';
                                    foreach ($mail->get_errors() as $err) {
                                        $msg .= $err . '<br>';
                                    }
                                    create_alert('warning', rtrim($err, '<br>'), BASE_URL . 'administrator/?go=reset-password');
                                }
                            } else {
                                create_alert('danger', 'User not found!', BASE_URL . 'administrator/?go=register');
                            }
                        } catch (\Exception $e) {
                            create_alert('danger', $e->getMessage(), BASE_URL . 'administrator/?go=reset-password');
                        }
                    }
                } else {
                    if (!empty($data)) {
                        $data = $user->get($data[0]['id']);
                        $token = encode($data['email'] . '|' . $exp);
                        create_alert('success', 'Please enter your new password!', BASE_URL . 'administrator/?go=reset-password&token=' . $token);
                    } else {
                        create_alert('danger', 'User not found!', BASE_URL . 'administrator/?go=register');
                    }
                }
            } else {
                create_alert('danger', 'Username or email not registered!', BASE_URL . 'administrator/?go=reset-password');
            }
        } else {
            if (empty($_POST['password']) || empty($_POST['retype_password'])) {
                create_alert('warning', 'Enter your new password!', BASE_URL . 'administrator/?go=reset-password&token=' . $token);
            } elseif ($_POST['password'] !== $_POST['retype_password']) {
                create_alert('warning', 'Confirm password must be the same as the password!', BASE_URL . 'administrator/?go=reset-password&token=' . $token);
            } else {
                try {
                    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    $user = $db->prepare('UPDATE `tb_users` SET `password`=?, `updated`=? WHERE `email`=?');
                    $user->execute(array($password, time(), $_POST['email']));
                    create_alert('success', 'Your password has been updated successfully!', BASE_URL . 'administrator/?go=login');
                } catch (Exception $e) {
                    create_alert('danger', $e->getMessage(), BASE_URL . 'administrator/?go=reset-password&token=' . $token);
                }
            }
        }
    }
}
