<?php
require_once '../vendor/autoload.php';
require_once 'config.php';
require_once 'functions.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

header('content-type: application/json');

function proxy_checker()
{
    $disableProxy = get_option('disable_proxy');
    $freeProxy = get_option('free_proxy');
    $proxyServer = explode("\n", strtr(trim(get_option('proxy_list')), ["\r\n" => "\n"]));

    $proxyType = ['socks4', 'socks4a', 'socks5'];
    $proxyReplace = [CURLPROXY_SOCKS4, CURLPROXY_SOCKS4A, CURLPROXY_SOCKS5];

    if (!filter_var($disableProxy, FILTER_VALIDATE_BOOLEAN)) {
        if (!filter_var($freeProxy, FILTER_VALIDATE_BOOLEAN) || empty($proxyServer)) {
            $fplnet = free_proxy_list_net();
            $fpcz = free_proxy_cz();
            $pdcom = proxy_docker_com();
            $proxyServer = array_unique(array_merge($proxyServer, $fplnet, $fpcz, $pdcom), SORT_REGULAR);
        }

        if (!empty($proxyServer)) {
            $mh = curl_multi_init();
            $ch = [];
            foreach ($proxyServer as $i => $proxy) {
                $pr = explode(',', $proxy);
                $ch[$i] = curl_init('https://drive.google.com/file/d/1cwus-hJ4iWy_-KSMd3y78yvMzvFYMjEu/view');
                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch[$i], CURLOPT_ENCODING, "");
                curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($ch[$i], CURLOPT_TIMEOUT, 5);
                if (defined('CURLOPT_TCP_FASTOPEN')) {
                    curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                }
                curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                curl_setopt($ch[$i], CURLOPT_HEADER, 1);
                curl_setopt($ch[$i], CURLOPT_NOBODY, 1);
                if (!empty($pr)) {
                    curl_setopt($ch[$i], CURLOPT_PROXY, $pr[0]);
                    if (!empty($pr[1])) {
                        if (in_array(strtolower($pr[1]), $proxyType)) {
                            $key = array_search(strtolower($pr[1]), $proxyType);
                            curl_setopt($ch[$i], CURLOPT_PROXYTYPE, $proxyReplace[$key]);
                        } else {
                            $usrpwd = strpos($pr[1], '@') !== FALSE ? strtr($pr[1], ['@' => ':']) : $pr[1];
                            curl_setopt($ch[$i], CURLOPT_PROXYUSERPWD, $usrpwd);
                        }
                    }
                    if (!empty($pr[2])) {
                        $key = array_search(strtolower($pr[2]), $proxyType);
                        curl_setopt($ch[$i], CURLOPT_PROXYTYPE, $proxyReplace[$key]);
                    }
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

            $prx = [];
            foreach ($proxyServer as $i => $proxy) {
                $response = curl_multi_getcontent($ch[$i]);
                $status = curl_getinfo($ch[$i], CURLINFO_HTTP_CODE);
                if ($status >= 200 && $status <= 320 && strpos($response, 'recaptcha') == FALSE) {
                    $prx[] = $proxy;
                } else {
                    error_log('Proxy checker ' . $proxy . ' => status ' . $status);
                }
                curl_multi_remove_handle($mh, $ch[$i]);
            }
            curl_multi_close($mh);

            if (!empty($prx)) {
                $prx = implode("\n", array_filter($prx));
                if (function_exists('set_option')) {
                    set_option('proxy_list', $prx);
                } else {
                    $file = @fopen(BASE_DIR . 'includes/proxy.txt', 'w+');
                    fwrite($file, $prx);
                    fclose($file);
                }
                return json_encode([
                    'status' => 'success',
                    'message' => 'Proxy has been successfully validated and can be used.',
                    'result' => $prx
                ]);
            } else {
                return json_encode([
                    'status' => 'fail',
                    'message' => 'Failed to retrieve validated proxy status. If there is a proxy in the proxy list column, the proxy is validated and can be used.'
                ]);
            }
        }
    }
    return json_encode([
        'status' => 'fail',
        'message' => 'No proxy can be used.'
    ]);
}

if (is_admin()) {
    echo proxy_checker();
    exit();
} else {
    $username = '';
    $password = '';
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];
    } elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    } elseif (!empty($_GET['username']) && !empty($_GET['password'])) {
        $username = htmlspecialchars($_GET['username']);
        $password = htmlspecialchars($_GET['password']);
    }

    if (!empty($username) && !empty($password)) {
        //step 1 : cek apakah username ada di tabel 
        $cek = $db->prepare("SELECT * FROM tb_users WHERE user = ? OR email = ?");
        $cek->execute(array(
            $username, $username
        ));
        $row = $cek->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            //username ada, tangkap password yg ada di database
            $password_db = $row['password'];
            $is_admin = intval($row['role']) === 0;

            if (password_verify($password, $password_db) && $is_admin) {
                echo proxy_checker();
                exit();
            }
        }
    }
}

http_response_code(403);
echo json_encode([
    'status' => 'fail',
    'message' => 'You are not authorized to access this page!'
]);
exit();
