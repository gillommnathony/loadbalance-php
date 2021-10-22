<?php
session_write_close();

require_once "../../vendor/autoload.php";
require_once "../../includes/config.php";
require_once "../../includes/functions.php";

header('content-type:application/json');

if (!empty($_POST['action'])) {
    switch ($_POST['action']) {
        case "check_username":
            if (!empty($_POST['username'])) {
                $user = new \users();
                $data = $user->check_username($_POST['username']);
                if ($data) {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'The username is already registered, please use another one!'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => ''
                    ]);
                }
            }
            break;
        case "check_email":
            if (!empty($_POST['email'])) {
                $user = new \users();
                $data = $user->check_email($_POST['email']);
                if ($data) {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'The email is already registered, please use another one!'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => ''
                    ]);
                }
            }
            break;
        default:
            echo json_encode([
                'status' => 'fail',
                'message' => 'What do you want?'
            ]);
            break;
    }
    exit;
} else {
    echo json_encode([
        'status' => 'fail',
        'message' => 'What do you want?'
    ]);
    exit;
}
