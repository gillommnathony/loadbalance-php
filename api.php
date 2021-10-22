<?php
session_write_close();
ini_set('max_execution_time', 0);
set_time_limit(0);

header('content-type: application/json');
header('Developed-By: GDPlayer.top');

require_once 'vendor/autoload.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';

use UAParser\Parser;

function userValidation($username = '')
{
    global $db;
    if (!empty($username)) {
        $cek = $db->prepare('SELECT `id`, `password`, `status`, `role` FROM `tb_users` WHERE `user` = ? OR `email` = ?');
        $cek->execute(array(
            $username, $username
        ));
        $row = $cek->fetch(PDO::FETCH_ASSOC);
        return $row;
    }
    return FALSE;
}

$referer = '';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
} elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
    $referer = $_SERVER['HTTP_ORIGIN'];
}

if (is_domain_blacklisted($referer) || is_referer_blacklisted($referer) && !is_domain_whitelisted($referer)) {
    http_response_code(403);
    echo json_encode([
        'status' => 'fail',
        'message' => 'Your site has been blacklisted. For more information, please contact Admin.'
    ]);
    exit();
} else {
    $qry = [];
    $username = '';
    $password = '';
    $is_admin = FALSE;
    $is_login = FALSE;

    // otentikasi
    if (strtolower($_SERVER['REQUEST_METHOD']) === 'get') {
        if (!empty($_GET['host']) && !empty($_GET['id']) && empty($_GET['origin'])) {
            // ambil data dari rest api
            parse_str($_SERVER['QUERY_STRING'], $qry);
        } else {
            // ambil data dari load balancer
            $input = decode($_SERVER['QUERY_STRING']);
            parse_str($input, $qry);
        }
    } elseif (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
        // ambil data dari rest api
        $input = @file_get_contents('php://input');
        if ($input) {
            $qry = @json_decode($input, true);
            if (!empty($qry['origin'])) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'Invalid parameter!'
                ]);
                exit();
            }
        }
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => 'fail',
            'message' => 'Invalid parameter!'
        ]);
        exit();
    }

    // validasi
    if (!empty($qry)) {
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
            $user = userValidation($username);
            if ($user) {
                //username ada, tangkap password yg ada di database
                $password_db = $user['password'];
                $status = intval($user['status']);
                // cek status user
                if ($status === 2) {
                    //status nonaktif
                    http_response_code(403);
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'User suspended! Please contact admin for more info.'
                    ]);
                    exit();
                } elseif ($status === 0) {
                    //status nonaktif
                    http_response_code(403);
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'User disabled! Please contact admin for more info.'
                    ]);
                    exit();
                } elseif (password_verify($password, $password_db)) {
                    $is_login = TRUE;
                    $is_admin = intval($user['role']) == 0;
                } else {
                    http_response_code(403);
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'You are not authorized to access this page!'
                    ]);
                    exit();
                }
            } else {
                http_response_code(403);
                echo json_encode([
                    'status' => 'fail',
                    'message' => 'You are not authorized to access this page!'
                ]);
                exit();
            }
        } elseif (!empty($qry['origin']) && !empty($referer) && strpos($referer, $qry['origin']) !== FALSE) {
            $is_login = TRUE;
            $is_admin = TRUE;
        } else {
            http_response_code(403);
            echo json_encode([
                'status' => 'fail',
                'message' => 'You are not authorized to access this page!'
            ]);
            exit();
        }

        if ($is_login) {
            $sQry = http_build_query($qry);
            $key = encode($sQry);
            if ($is_admin) {
                autoupdateProxy();

                $parser = Parser::create();
                $browser = !empty($_SERVER['HTTP_USER_AGENT']) ? $parser->parse($_SERVER['HTTP_USER_AGENT'])->toString() : 'bot';
                $remote_ip = $_SERVER['REMOTE_ADDR'];

                $parse = new \parse_sources($qry);
                $parse->remote_ip   = $remote_ip;
                $parse->user_agent  = $browser;
                $parse->qry_string  = $sQry;
                $parse->real_user_agent = $_SERVER['HTTP_USER_AGENT'];

                $download = isset($qry['download']) ? filter_var($qry['download'], FILTER_VALIDATE_BOOLEAN) : false;
                $config = $parse->get_config($download);
                if ($config) {
                    $result = [
                        'status' => 'ok',
                        'query' => $qry,
                        'key'   => $key,
                        'embed_link'    => BASE_URL . 'embed/?' . $key,
                        'download_link' => BASE_URL . 'download/?' . $key,
                        'request_link'  => BASE_URL . 'embed2/?' . $sQry,
                        'title'     => $config['title'],
                        'poster'    => $config['poster'],
                        'sources'   => $config['sources'],
                        'sources_alt' => $config['sources_alt'],
                        'tracks'    => $config['tracks']
                    ];
                    echo json_encode($result);
                    exit();
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'status' => 'fail',
                        'message' => 'Not found!'
                    ]);
                    exit();
                }
            } else {
                $result = [
                    'status' => 'ok',
                    'query' => $qry,
                    'key'   => $key,
                    'embed_link'    => BASE_URL . 'embed/?' . $key,
                    'download_link' => BASE_URL . 'download/?' . $key,
                    'request_link'  => BASE_URL . 'embed2/?' . $sQry
                ];
                echo json_encode($result);
                exit();
            }
        }
    } else {
        http_response_code(403);
        echo json_encode([
            'status' => 'fail',
            'message' => 'Invalid parameter!'
        ]);
        exit();
    }
}
