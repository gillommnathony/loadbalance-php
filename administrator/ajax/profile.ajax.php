<?php
session_write_close();

require_once "../../vendor/autoload.php";
require_once "../../includes/config.php";
require_once "../../includes/functions.php";

header('content-type:application/json');

$login = new login();
$userLogin = $login->cek_login();
if (!$userLogin || empty($userLogin)) {
    echo json_encode([
        'status' => 'fail',
        'message' => 'You must login first!'
    ]);
    exit;
}

if ($userLogin && $userLogin['user'] === 'demo') {
    echo json_encode([
        'status' => 'fail',
        'message' => 'You are not authorized to use this feature!'
    ]);
    exit;
}

$data = $_POST;
if (!empty($data['action'])) {
    switch ($data['action']) {
        case 'editEmail':
            if (!empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $duplicate = $db->prepare("SELECT id FROM tb_users WHERE id <> ? AND email = ? LIMIT 1");
                $duplicate->execute(array(
                    $userLogin['id'], $data['email']
                ));
                $row = $duplicate->fetch(PDO::FETCH_ASSOC);
                if (!empty($row)) {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Email has already been used by another user.'
                    ]);
                    exit;
                } else {
                    try {
                        $update = $db->prepare("UPDATE tb_users SET email=? WHERE id=?");
                        $update->execute([$data['email'], $userLogin['id']]);
                        echo json_encode([
                            'status' => 'ok',
                            'message' => 'Email successfully updated.'
                        ]);
                    } catch (PDOException $e) {
                        echo json_encode([
                            'status' => 'fail',
                            'message' => $e->getMessage()
                        ]);
                    }
                    exit;
                }
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Email must be valid!'
                ]);
                exit;
            }
            break;

        case 'editUsername':
            if (!empty($data['user'])) {
                $duplicate = $db->prepare("SELECT id FROM tb_users WHERE id <> ? AND user = ? LIMIT 1");
                $duplicate->execute(array(
                    $userLogin['id'], $data['user']
                ));
                $row = $duplicate->fetch(PDO::FETCH_ASSOC);
                if (!empty($row)) {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'The username is already taken by another user!'
                    ]);
                    exit;
                } else {
                    try {
                        $update = $db->prepare("UPDATE tb_users SET user=? WHERE id=?");
                        $update->execute([$data['user'], $userLogin['id']]);
                        echo json_encode([
                            'status' => 'ok',
                            'message' => 'Username updated successfully.'
                        ]);
                    } catch (PDOException $e) {
                        echo json_encode([
                            'status' => 'fail',
                            'message' => $e->getMessage()
                        ]);
                    }
                    exit;
                }
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'The username must be filled in!'
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
