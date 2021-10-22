<?php
session_write_close();

require_once "../../vendor/autoload.php";
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
require_once "../includes/conn.php";
require_once "../includes/functions.php";

header('content-type:application/json');

$login = new \login();
$userLogin = $login->cek_login();
if (!$userLogin || empty($userLogin)) {
    echo json_encode([
        'status' => 'fail',
        'message' => 'You must login first!'
    ]);
    exit;
}

if (!is_admin()) {
    echo json_encode([
        'status' => 'fail',
        'message' => 'You are not authorized to use this feature!'
    ]);
    exit;
}

$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : '';
switch ($action) {
    case 'clear_cache':
        try {
            $cleared[] = deleteDir(BASE_DIR . 'cookies');
            $cleared[] = deleteDir(BASE_DIR . 'cache/embed');
            $cleared[] = deleteDir(BASE_DIR . 'cache/playlist');
            $cleared[] = deleteDir(BASE_DIR . 'cache/streaming');
            $cleared[] = deleteDir(BASE_DIR . 'cache');
            $cleared[] = deleteDir(BASE_DIR . 'tmp');
            if (extension_loaded('mongodb') || extension_loaded('redis')) {
                $cleared[] = $InstanceCache->clear();
            }

            $result = [];
            $lbs = $db->prepare("SELECT link FROM tb_loadbalancers WHERE `status` = 1");
            $lbs->execute();
            $rows = $lbs->fetchAll(\PDO::FETCH_ASSOC);
            if ($rows) {
                $data['action'] = 'clear_cache';
                $data['token'] = $_COOKIE['adv_token'];

                $links = array_column($rows, 'link');
                $mh = curl_multi_init();
                $ch = [];
                foreach ($links as $i => $link) {
                    $link = rtrim($link, '/');
                    $ch[$i] = curl_init($link . '/administrator/api.php');
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch[$i], CURLOPT_ENCODING, "");
                    curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch[$i], CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch[$i], CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_multi_add_handle($mh, $ch[$i]);
                }

                $active = null;
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);

                while ($active && $mrc == CURLM_OK) {
                    if (curl_multi_select($mh) == -1) {
                        usleep(10);
                    }
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }

                foreach ($links as $i => $link) {
                    $status = curl_getinfo($ch[$i], CURLINFO_HTTP_CODE);
                    if ($status > 0 && $status < 400) {
                        $response = curl_multi_getcontent($ch[$i]);
                        $json = json_decode($response, true);
                        $result[] = [
                            'server' => $link,
                            'status' => $json['status'],
                            'message' => $json['message']
                        ];
                        error_log($link . ' => ' . $json['message']);
                    } else {
                        $result[] = [
                            'server' => $link,
                            'status' => 'fail',
                            'message' => 'Failed! Not getting any response from the load balancer server.'
                        ];
                        error_log($link . ' => Failed! Not getting any response from the load balancer server.');
                    }
                    curl_multi_remove_handle($mh, $ch[$i]);
                }
                curl_multi_close($mh);
            }

            if (in_array('success', array_column($result, 'status')) && !in_array(false, $cleared)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Cache cleared successfully!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Cache cannot be cleared properly! Please check the error.log file for more information.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
        exit;
        break;
    case 'clear_video_info_cache':
        try {
            $cleared[] = $InstanceCache->deleteItemsByTags(['mime_info', 'video_length']);

            $result = [];
            $lbs = $db->prepare("SELECT link FROM tb_loadbalancers WHERE `status`=1");
            $lbs->execute();
            $rows = $lbs->fetchAll(\PDO::FETCH_ASSOC);
            if ($rows) {
                $data['action'] = 'clear_video_info_cache';
                $data['token'] = $_COOKIE['adv_token'];
                $data['ip'] = $_SERVER['REMOTE_ADDR'];
                $data['ua'] = $_SERVER['HTTP_USER_AGENT'];

                $links = array_column($rows, 'link');
                $mh = curl_multi_init();
                $ch = [];
                foreach ($links as $i => $link) {
                    $link = rtrim($link, '/');
                    $ch[$i] = curl_init($link . '/administrator/api.php');
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch[$i], CURLOPT_ENCODING, "");
                    curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch[$i], CURLOPT_POST, 1);
                    curl_setopt($ch[$i], CURLOPT_POSTFIELDS, http_build_query($data));
                    if (defined('CURLOPT_TCP_FASTOPEN')) {
                        curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                    }
                    curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                    curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                    curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                    curl_multi_add_handle($mh, $ch[$i]);
                }

                $active = null;
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);

                while ($active && $mrc == CURLM_OK) {
                    if (curl_multi_select($mh) == -1) {
                        usleep(10);
                    }
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }

                foreach ($links as $i => $link) {
                    $status = curl_getinfo($ch[$i], CURLINFO_HTTP_CODE);
                    if ($status > 0 && $status < 400) {
                        $response = curl_multi_getcontent($ch[$i]);
                        $json = json_decode($response, true);
                        $result[] = [
                            'server' => $link,
                            'status' => $json['status'],
                            'message' => $json['message']
                        ];
                        error_log($link . ' => ' . $json['message']);
                    } else {
                        $result[] = [
                            'server' => $link,
                            'status' => 'fail',
                            'message' => 'Failed! Not getting any response from the load balancer server.'
                        ];
                        error_log($link . ' => Failed! Not getting any response from the load balancer server.');
                    }
                    curl_multi_remove_handle($mh, $ch[$i]);
                }
                curl_multi_close($mh);
            }

            if (in_array('success', array_column($result, 'status')) && !in_array(false, $cleared)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Cache cleared successfully!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Cache cannot be cleared properly! Please check the error.log file for more information.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
        exit;
        break;
    case 'reset_host':
        try {
            $settings = new \settings();
            $update = $settings->delete('bypass_host');

            $core = new \core();
            $hosts = $core->bypass_host();
            set_option('bypass_host', $hosts);

            echo json_encode([
                'status' => 'success',
                'message' => 'Bypassed host reset was successful!',
                'result' => $hosts
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
        break;
    case 'delete_videos_blacklisted':
        try {
            $blacklisted = explode("\n", strtr(strtolower(get_option('word_blacklisted')), ["\r\n" => "\n"]));
            $blacklisted = array_unique($blacklisted);
            $blacklisted = array_filter($blacklisted, function ($a) {
                return !empty(trim($a));
            });
            sort($blacklisted);
            set_option('word_blacklisted', implode("\n", $blacklisted));
            $where = '';
            foreach ($blacklisted as $word) {
                $where .= " LOWER(`title`) LIKE '%" . addslashes(htmlspecialchars($word)) . "%' OR";
            }
            $where = rtrim($where, 'OR');
            $deleted = $db->query("DELETE FROM `tb_videos` WHERE $where");
            if ($deleted) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Blacklisted videos have been successfully deleted!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Failed to delete blacklisted videos!'
                ]);
            }
        } catch (\PDOException | \Exception $e) {
            echo json_encode([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
        break;
    default:
        break;
}
