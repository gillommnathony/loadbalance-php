<?php
if (!defined('BASE_DIR')) die('access denied');

session_write_close();
header('Accept-Ranges: bytes');
ini_set('max_execution_time', 0);
set_time_limit(0);

$referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
if (!is_domain_whitelisted($referer)) {
    http_response_code(403);
    exit('ACCESS DENIED');
} elseif (is_domain_blacklisted($referer) || is_referer_blacklisted($referer)) {
    http_response_code(403);
    exit('ACCESS DENIED');
} elseif (!empty($_SERVER['QUERY_STRING'])) {
    $query = $_SERVER['QUERY_STRING'];
    $range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : '';
    $dlPage = get_option('enable_download_page');

    if ((empty($range) || empty($_SERVER['HTTP_HOST'])) && !filter_var($dlPage, FILTER_VALIDATE_BOOLEAN)) {
        http_response_code(403);
        exit('ACCESS DENIED');
    } else {
        $video = new \video_player($query);
        $response = $video->send($range);

        // idm intercept
        if (intval($response['status']) !== 204) {
            http_response_code($response['status']);
            unset($response['status']);
        } else {
            http_response_code(403);
            exit('ACCESS DENIED');
        }

        ob_get_clean();
        foreach ($response as $k => $v) {
            header($k . ': ' . $v);
        }

        $video->stream();
        exit;
    }
} else {
    http_response_code(404);
    exit('FILE DOES NOT EXIST');
}
