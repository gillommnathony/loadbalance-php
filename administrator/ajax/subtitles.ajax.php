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
        case 'delete_db_only':
            $getFile = $db->prepare('SELECT `host`, `file_name` FROM tb_subtitle_manager WHERE id=?');
            $getFile->execute([$data['id']]);
            $field = $getFile->fetch(PDO::FETCH_ASSOC);
            if (is_array($field) && !empty($field['host']) && !empty($field['file_name'])) {
                $file = $field['host'] . 'subtitles/' . $field['file_name'];
                try {
                    $delete = $db->prepare('DELETE FROM tb_subtitle_manager WHERE id=?;DELETE FROM tb_subtitles WHERE link=?');
                    $delete->execute([$data['id'], $file]);
                    echo json_encode([
                        'status' => 'ok',
                        'message' => 'Data successfully deleted.'
                    ]);
                } catch (\PDOException $e) {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Subtitle not found!'
                ]);
            }
            break;

        case 'delete':
            try {
                $getFile = $db->prepare('SELECT `host`, `file_name` FROM tb_subtitle_manager WHERE id=?');
                $getFile->execute([$data['id']]);
                $field = $getFile->fetch(PDO::FETCH_ASSOC);
                if (is_array($field) && !empty($field['host']) && !empty($field['file_name'])) {
                    if (parse_url(BASE_URL, PHP_URL_HOST) === parse_url($field['host'], PHP_URL_HOST)) {
                        $file = BASE_DIR . 'subtitles/' . $field['file_name'];
                        if (file_exists($file)) {
                            try {
                                $deleteFile = @unlink($file);
                                if ($deleteFile) {
                                    $delete = $db->prepare('DELETE FROM tb_subtitle_manager WHERE id=?');
                                    $delete->execute([$data['id']]);
                                    echo json_encode([
                                        'status' => 'ok',
                                        'message' => 'Data successfully deleted.'
                                    ]);
                                }
                            } catch (\PDOException $e) {
                                echo json_encode([
                                    'status' => 'fail',
                                    'message' => $e->getMessage()
                                ]);
                            } catch (\Exception $e) {
                                echo json_encode([
                                    'status' => 'fail',
                                    'message' => $e->getMessage()
                                ]);
                            }
                        }
                    } else {
                        $curl = curl_init();
                        curl_setopt_array(
                            $curl,
                            array(
                                CURLOPT_URL => $field['host'] . 'administrator/api.php',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 30,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => 'POST',
                                CURLOPT_POSTFIELDS => http_build_query(array(
                                    'token' => $_COOKIE['adv_token'],
                                    'action' => 'delete_subtitle',
                                    'ip' => $_SERVER['REMOTE_ADDR'],
                                    'ua' => $_SERVER['HTTP_USER_AGENT']
                                ))
                            )
                        );
                        session_write_close();
                        $response = curl_exec($curl);
                        $errCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        curl_close($curl);
                        if ($errCode !== 404) {
                            echo $response;
                        } else {
                            echo json_encode([
                                'status' => 'fail',
                                'message' => 'Failed! Not getting any response from the ' . $field['host'] . ' server.'
                            ]);
                        }
                    }
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Subtitle not found!'
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
