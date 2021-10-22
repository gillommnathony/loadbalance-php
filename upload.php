<?php
session_write_close();
header('X-Frame-Options: SAMEORIGIN');
header('content-type: application/json; charset=UTF-8');
header("Developed-By: GDPlayer.top");

require_once 'includes/config.php';
require_once BASE_DIR . 'administrator/includes/conn.php';
require_once BASE_DIR . 'administrator/includes/functions.php';
require_once BASE_DIR . 'administrator/includes/classes/login.class.php';

$login = new \login();
$userLogin = $login->cek_login();
$uid = $userLogin ? $userLogin['id'] : 1;

define('UPLOAD_DIR', BASE_DIR . 'subtitles/');
define('KB', 1024);
define('MB', 1048576);
define('GB', 1073741824);
define('TB', 1099511627776);

if ($_FILES['media']['error'] == UPLOAD_ERR_FORM_SIZE) {
    echo json_encode([
        'status' => 'fail',
        'result' => 'File size too large! A maximum of 5MB is allowed.'
    ]);
    exit;
} elseif ($_FILES['media']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'status' => 'fail',
        'result' => 'Uploading failed!'
    ]);
    exit;
} else {
    $media    = $_FILES['media'];
    $size    = $_FILES['media']['size'];

    $fileName = preg_replace('/[^A-Z0-9._-]/i', '_', $media['name']);
    $parts  = pathinfo($fileName);
    $fname  = $parts['filename'];
    $ext    = $parts['extension'];
    $type   = $media['type'];

    $isSrt  = ($type === 'text/plain' || $type === 'application/octet-stream') && $ext === 'srt';
    $isVtt  = ($type === 'text/vtt' || $type === 'application/octet-stream') && $ext === 'vtt';

    if (!$isSrt && !$isVtt) {
        echo json_encode([
            'status' => 'fail',
            'result' => 'Unsupported file type!'
        ]);
        exit;
    } else {
        if ($size <= 5 * MB) {
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            if (!is_dir(UPLOAD_DIR)) {
                mkdir(BASE_DIR . 'subtitles', 0755, true);
                chmod(BASE_DIR . 'subtitles', 0755);
            }

            // mencegah overwrite filename
            $fileName = substr($fname, 0, 200) . '-' . substr(str_shuffle($permitted_chars), 0, 15) . '.' . $ext;
            $i = 0;
            while (file_exists(UPLOAD_DIR . $fileName)) {
                $i++;
                $fileName = $fname . '-' . $i . '.' . $ext;
            }

            $success = move_uploaded_file($media['tmp_name'], UPLOAD_DIR . $fileName);
            if ($success) {
                // ubah file permission
                chmod(UPLOAD_DIR . $fileName, 0644);
                // simpan ke database
                try {
                    $saveFile = $db->prepare('INSERT INTO `tb_subtitle_manager`(`file_name`, `file_size`, `file_type`, `added`, `uid`, `host`) VALUES (?, ?, ?, ?, ?, ?)');
                    $saveFile->execute([$fileName, $size, $type, time(), $uid, BASE_URL]);
                    echo json_encode([
                        'status' => 'ok',
                        'result' => BASE_URL . 'subtitles/' . $fileName
                    ]);
                    exit;
                } catch (PDOException $e) {
                    echo json_encode([
                        'status' => 'fail',
                        'result' => $e->getMessage()
                    ]);
                    exit;
                }
            }
        } else {
            echo json_encode([
                'status' => 'fail',
                'result' => 'File size too large! The maximum file size allowed is 5MB.'
            ]);
            exit;
        }
    }
}
