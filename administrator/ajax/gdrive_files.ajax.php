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
            if (!empty($data['id']) && !empty($data['email'])) {
                $gdauth = new \gdrive_auth($data['id'], $data['email']);
                $delete = $gdauth->delete_file();
                if ($delete) {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => 'Data successfully deleted.'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'The file cannot be deleted.'
                    ]);
                }
                exit;
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Invalid parameter!'
                ]);
                exit;
            }
            break;

        case 'public':
            if (!empty($data['id']) && !empty($data['email'])) {
                $gdauth = new \gdrive_auth($data['id'], $data['email']);
                $update = $gdauth->insert_permissions();
                if ($update) {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => 'Now the file is publicly accessible!'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Failed to change the status of the file!'
                    ]);
                }
                exit;
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Invalid parameter!'
                ]);
                exit;
            }
            break;

        case 'private':
            if (!empty($data['id']) && !empty($data['email'])) {
                $gdauth = new \gdrive_auth($data['id'], $data['email']);
                $delete = $gdauth->delete_permissions();
                if ($delete) {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => 'Now the file is not accessible to the public!'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Failed to change the status of the file!'
                    ]);
                }
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
