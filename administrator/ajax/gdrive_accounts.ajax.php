<?php
session_write_close();

require_once "../../vendor/autoload.php";
require_once "../../includes/config.php";
require_once "../../includes/functions.php";

header('content-type:application/json');

$login = new \login();
$userLogin = $login->cek_login();
if (!$userLogin) {
    echo json_encode([
        'status' => 'fail',
        'message' => 'You must login first!'
    ]);
    exit;
} elseif ($userLogin && !is_admin()) {
    echo json_encode([
        'status' => 'fail',
        'message' => 'You are not authorized to access this feature!'
    ]);
    exit;
}

$data = $_POST;
if (!empty($data['action'])) {
    switch ($data['action']) {
        case 'delete':
            if (!empty($data['id'])) {
                $opt = new \gdauth();
                $opt->delete($data['id']);
                echo json_encode([
                    'status' => 'ok',
                    'message' => 'Data successfully deleted.'
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Invalid parameter!'
                ]);
                exit;
            }
            break;
        default:
            echo json_encode([
                'status' => 'fail',
                'message' => 'What do you want, man?'
            ]);
            exit;
            break;
    }
} else {
    echo json_encode([
        'status' => 'fail',
        'message' => 'What do you want, man?'
    ]);
    exit;
}
