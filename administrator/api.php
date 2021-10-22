<?php
session_write_close();
header('content-type: application/json; charset=ut8');
header("Developed-By: GDPlayer.top");

require_once '../vendor/autoload.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once 'includes/conn.php';
require_once 'includes/functions.php';

if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
    $action = isset($_POST['action']) ? htmlspecialchars($_POST['action']) : '';
    $token = isset($_POST['token']) ? htmlspecialchars($_POST['token']) : '';
    $ip = isset($_POST['ip']) ? htmlspecialchars($_POST['ip']) : '';
    $useragent = isset($_POST['ua']) ? htmlspecialchars($_POST['ua']) : '';
    $now = time();

    $validate = $db->prepare("SELECT * FROM tb_sessions WHERE token = ? AND expired > $now AND stat <> 9");
    $validate->execute(array($token));
    $rows = $validate->fetchAll(\PDO::FETCH_ASSOC);
    if ($rows) {
        switch ($action) {
            case 'server_info':
                $memUsage = getServerMemoryUsage(false);
                $info = array(
                    'memory' => array(
                        'total' => getNiceFileSize($memUsage["total"]),
                        'free' => getNiceFileSize($memUsage["free"]),
                        'usage' => getNiceFileSize($memUsage["total"] - $memUsage["free"]),
                        'usage_percentage' => intval(getServerMemoryUsage(true)) . '%'
                    ),
                    'cpu' => array(
                        'total_threads' => getServerCpuThreads(),
                        'usage_percentage' => getServerCpuUsage() . '%'
                    )
                );
                if ($info) {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => '',
                        'result' => $info
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Cannot access the server!'
                    ]);
                }
                break;
            case 'delete_video_cache':
                if (isset($_POST['key'])) {
                    $key = htmlspecialchars($_POST['key']);
                    $deleted = delete_video_cache($key);
                    if ($deleted) {
                        echo json_encode([
                            'status' => 'ok',
                            'message' => 'Video cache cleared successfully.'
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'fail',
                            'message' => 'Cannot clear video cache.'
                        ]);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Invalid parameter.'
                    ]);
                }
                break;
            case 'clear_cache':
                $result[] = deleteDir(BASE_DIR . 'cookies');
                $result[] = deleteDir(BASE_DIR . 'cache/embed');
                $result[] = deleteDir(BASE_DIR . 'cache/playlist');
                $result[] = deleteDir(BASE_DIR . 'cache/streaming');
                $result[] = deleteDir(BASE_DIR . 'cache');
                $result[] = deleteDir(BASE_DIR . 'tmp');
                if (extension_loaded('mongodb') || extension_loaded('redis')) {
                    $result[] = $InstanceCache->clear();
                }
                if (!in_array(false, $result)) {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => 'Cache cleared successfully!'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Cannot clear cache.'
                    ]);
                }
                break;
            case 'delete_subtitle':
                if (isset($_POST['id'])) {
                    try {
                        $id = intval($_POST['id']);
                        $getFile = $db->prepare('SELECT `host`, `file_name` FROM tb_subtitle_manager WHERE id=?');
                        $getFile->execute(array($id));
                        $field = $getFile->fetch(PDO::FETCH_ASSOC);
                        if (is_array($field) && !empty($field['host']) && !empty($field['file_name'])) {
                            if (parse_url(BASE_URL, PHP_URL_HOST) === parse_url($field['host'], PHP_URL_HOST)) {
                                $file = BASE_DIR . 'subtitles/' . $field['file_name'];
                                if (file_exists($file)) {
                                    try {
                                        $deleteFile = @unlink($file);
                                        if ($deleteFile) {
                                            $delete = $db->prepare('DELETE FROM tb_subtitle_manager WHERE id=?');
                                            $delete->execute(array($id));
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
                                } else {
                                    echo json_encode([
                                        'status' => 'ok',
                                        'message' => 'Data successfully deleted.'
                                    ]);
                                }
                            } else {
                                echo json_encode([
                                    'status' => 'fail',
                                    'message' => 'You can only delete the subtitle from ' . $field['host']
                                ]);
                            }
                        } else {
                            echo json_encode([
                                'status' => 'fail',
                                'message' => 'Subtitle not found!'
                            ]);
                        }
                        exit;
                    } catch (\PDOException $e) {
                        echo json_encode([
                            'status' => 'fail',
                            'message' => $e->getMessage()
                        ]);
                        exit;
                    }
                }
                break;
            case 'clear_video_info_cache':
                $deleted = delete_video_info_cache();
                if($deleted) {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => 'Cache cleared successfully!'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Cannot clear cache.'
                    ]);
                }
                break;
            default:
                http_response_code(404);
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'API not found!'
                ]);
        }
    }
    exit;
}
http_response_code(403);
echo json_encode([
    'status' => 'fail',
    'message' => 'You are not authorized to access this page!'
]);
