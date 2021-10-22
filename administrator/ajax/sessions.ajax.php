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
}

$data = $_POST;
if (!empty($data['action'])) {
    switch ($data['action']) {
        case 'delete':
            try {
                if (!empty($data['id'])) {
                    $delete = $db->prepare("DELETE FROM tb_sessions WHERE id=?");
                    $deleted = $delete->execute(array(intval($data['id'])));
                    if($deleted){
                        $hasData = (int) $db->query("SELECT COUNT(id) FROM tb_sessions")->fetchColumn();
                        if ($hasData == 0) {
                            $db->query("ALTER TABLE `tb_sessions` AUTO_INCREMENT=1");
                        }
                        echo json_encode([
                            'status' => 'ok',
                            'message' => 'Data successfully deleted.'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Session not found!'
                    ]);
                }
                exit;
            } catch (PDOException $e) {
                echo json_encode([
                    'status' => 'fail',
                    'message' => $e->getMessage()
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
