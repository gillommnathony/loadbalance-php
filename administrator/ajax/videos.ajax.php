<?php
session_write_close();

require_once "../../vendor/autoload.php";
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
require_once "../includes/functions.php";

header('content-type:application/json');

$login = new login();
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
        case 'checker':
            $video = new \videos();
            $detail = $video->get($data['id']);
            if ($detail) {
                $links = [];
                if (!empty($detail['host_id']) && $detail['host'] !== 'fembed') $links[] = getDownloadLink($detail['host'], $detail['host_id']);
                else $links[] = strtr($detail['host_id'], ['/v/' => '/api/source/', '/f/' => '/api/source/']);
                if (!empty($detail['ahost_id']) && $detail['ahost'] !== 'fembed') $links[] = getDownloadLink($detail['ahost'], $detail['ahost_id']);
                else $links[] = strtr($detail['ahost_id'], ['/v/' => '/api/source/', '/f/' => '/api/source/']);

                $mh = curl_multi_init();
                $ch = [];
                // cek penggunaan proxy
                $proxy = proxy_rotator();
                foreach ($links as $i => $link) {
                    $host = parse_url($link, PHP_URL_HOST);
                    if (strpos($link, '/api/source') !== FALSE) $host = strtr($host, [$host => 'femax20.com']);
                    $port = parse_url($link, PHP_URL_PORT);
                    if (empty($port)) {
                        $port = parse_url($link, PHP_URL_SCHEME) == 'https' ? 443 : 80;
                    }
                    $ipv4 = gethostbyname($host);
                    $resolveHost = implode(':', array($host, $port, $ipv4));

                    $ch[$i] = curl_init($link);
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch[$i], CURLOPT_RESOLVE, [$resolveHost]);
                    curl_setopt($ch[$i], CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch[$i], CURLOPT_ENCODING, "");
                    curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                    if (strpos($link, '/api/source') === FALSE) {
                        curl_setopt($ch[$i], CURLOPT_HEADER, 1);
                        curl_setopt($ch[$i], CURLOPT_NOBODY, 1);
                    } else {
                        curl_setopt($ch[$i], CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch[$i], CURLOPT_POSTFIELDS, 'r=&d='. $host);
                    }
                    curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                    curl_setopt($ch[$i], CURLOPT_REFERER, $link);
                    curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
                        'host: '. $host,
                        'origin: https://'. $host
                    ));
                    if ($proxy) {
                        curl_setopt($ch[$i], CURLOPT_PROXY, $proxy['proxy']);
                        curl_setopt($ch[$i], CURLOPT_PROXYTYPE, $proxy['type']);
                        curl_setopt($ch[$i], CURLOPT_PROXYUSERPWD, $proxy['usrpwd']);
                    }
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

                $result = true;
                foreach ($links as $i => $link) {
                    if (strpos($link, '/api/source') === FALSE) {
                        $status = curl_getinfo($ch[$i], CURLINFO_HTTP_CODE);
                        if ($status >= 400) $result = false;
                    } else {
                        $content = json_decode(curl_multi_getcontent($ch[$i]), true);
                        if($content['success'] === false) $result = $content['success'];
                    }
                    curl_multi_remove_handle($mh, $ch[$i]);
                }
                curl_multi_close($mh);
                echo json_encode([
                    'status' => 'ok',
                    'message' => '',
                    'result' => $result
                ]);
                exit;
            }
            echo json_encode([
                'status' => 'fail',
                'message' => 'Failed to check video status.'
            ]);
            break;
        case 'delete_cache':
            $video = new \videos();
            $detail = $video->get($data['id']);
            $deleted = FALSE;
            if (!empty($detail['host_id'])) {
                $key = $detail['host'] . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $detail['host_id']);
                $deleted['local'] = delete_video_cache($key);
                $deleted['lb'] = delete_lb_video_cache($key, $_COOKIE['adv_token'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            }
            if (!empty($detail['ahost_id'])) {
                $key = $detail['ahost'] . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $detail['ahost_id']);
                $deleted['local'] = delete_video_cache($key);
                $deleted['lb'] = delete_lb_video_cache($key, $_COOKIE['adv_token'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
            }
            if ($deleted) {
                if (is_array($deleted['lb'])) {
                    $keys = array_keys(array_column($deleted['lb'], 'status'), 'fail');
                    if (!empty($keys)) {
                        $svr = [];
                        foreach ($keys as $key) {
                            $svr[] = $deleted['lb'][$key]['server'];
                        }
                        $servers = implode(', ', $svr);
                        echo json_encode([
                            'status' => 'fail',
                            'message' => 'Failed to clear the video cache of the following load balancer servers: ' . trim($servers, ', ') . '.'
                        ]);
                    } else {
                        echo json_encode([
                            'status' => 'ok',
                            'message' => 'Video cache cleared successfully.'
                        ]);
                    }
                } elseif (is_bool($deleted['lb']) && !$deleted['lb']) {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Failed to clear video cache from load balancer servers due to invalid parameters.'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => 'Video cache cleared successfully.'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Cannot clear video cache.'
                ]);
            }
            break;

        case 'get_host_id':
            if (!empty($data['url'])) {
                $data = getHostId($data['url']);
                if ($data) {
                    echo json_encode([
                        'status' => 'ok',
                        'message' => '',
                        'result' => $data
                    ]);
                    exit;
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Invalid URL or unknown video hosting.'
                    ]);
                    exit;
                }
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Enter video URL'
                ]);
                exit;
            }
            break;

        case 'save_bulk_link':
            if (!empty($data['links'])) {
                $result = [];
                $success = 0;
                $error = 0;
                $video = new \videos();
                foreach ($data['links'] as $url) {
                    $xdata = getHostId($url);
                    if ($xdata) {
                        $dl = getDownloadLink($xdata['host'], $xdata['host_id']);
                        if (filter_var($xdata['host_id'], FILTER_VALIDATE_URL)) {
                            $title = explode('/', rtrim($xdata['host_id']));
                            $title = end($title);
                        } else {
                            $title = $xdata['host_id'];
                        }
                        $xdata = array_merge($xdata, [
                            'title' => $title,
                            'ahost' => '',
                            'ahost_id' => '',
                            'subtitle' => ''
                        ]);
                        $video->insert($xdata);

                        $dt = [];
                        $dt['url']  = $url;
                        $dt['data'] = BASE_URL . 'embed.php?' . encode('host=' . $xdata['host'] . '&id=' . $xdata['host_id']);
                        $result[]   = $dt;

                        $success++;
                    } else {
                        $error++;
                    }
                }
                if ($success > 0) {
                    echo json_encode([
                        'status' => 'ok',
                        'error_count' => $error,
                        'success_count' => $success,
                        'message' => $success . ' links successfully added and ' . $error . ' links failed to be added.',
                        'results' => $result
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'All links are invalid!'
                    ]);
                }
                exit;
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'The link list is empty!'
                ]);
                exit;
            }
            break;

        case 'delete':
            $video = new \videos();
            $detail = $video->get($data['id']);
            $delete = $video->delete($data['id']);
            if ($delete) {
                $deleted = [];
                if (!empty($detail['host_id'])) {
                    $key = $detail['host'] . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $detail['host_id']);
                    $deleted['local'] = delete_video_cache($key);
                    $deleted['lb'] = delete_lb_video_cache($key, $_COOKIE['adv_token'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                }
                if (!empty($detail['ahost_id'])) {
                    $key = $detail['ahost'] . '~' . preg_replace('/[^A-Za-z0-9\-]/', '', $detail['ahost_id']);
                    $deleted['local'] = delete_video_cache($key);
                    $deleted['lb'] = delete_lb_video_cache($key, $_COOKIE['adv_token'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                }
                if (!empty($deleted)) {
                    if (is_array($deleted['lb'])) {
                        $keys = array_keys(array_column($deleted['lb'], 'status'), 'fail');
                        if (!empty($keys)) {
                            $svr = [];
                            foreach ($keys as $key) {
                                $svr[] = $deleted['lb'][$key]['server'];
                            }
                            $servers = implode(', ', $svr);
                            echo json_encode([
                                'status' => 'fail',
                                'message' => 'Failed to clear the video cache of the following load balancer servers: ' . trim($servers, ', ') . '.'
                            ]);
                            exit;
                        } else {
                            echo json_encode([
                                'status' => 'ok',
                                'message' => 'Data successfully deleted.'
                            ]);
                            exit;
                        }
                    } elseif (is_bool($deleted['lb']) && !$deleted['lb']) {
                        echo json_encode([
                            'status' => 'fail',
                            'message' => 'Failed to clear video cache from load balancer servers due to invalid parameters.'
                        ]);
                        exit;
                    } else {
                        echo json_encode([
                            'status' => 'ok',
                            'message' => 'Data successfully deleted.'
                        ]);
                        exit;
                    }
                }
                echo json_encode([
                    'status' => 'ok',
                    'message' => 'Data successfully deleted.'
                ]);
                exit;
            } else {
                echo json_encode([
                    'status' => 'fail',
                    'message' => $video->get_errors()
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
