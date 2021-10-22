<?php
if (!defined('BASE_DIR')) die('access denied');

session_write_close();
header('Content-Type: application/vnd.apple.mpegurl');
ini_set('max_execution_time', 0);
set_time_limit(0);

$referer = '';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
} elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
    $referer = $_SERVER['HTTP_ORIGIN'];
}

if (empty($_SERVER['QUERY_STRING'])) {
    http_response_code(404);
    exit();
} elseif (empty($referer)) {
    http_response_code(403);
    exit();
} elseif (!is_domain_whitelisted($referer)) {
    http_response_code(403);
    exit();
} elseif (is_domain_blacklisted($referer) || is_referer_blacklisted($referer)) {
    http_response_code(403);
    exit();
}

if (!empty($_GET['url'])) {
    if (filter_var(rawurldecode($_GET['url']), FILTER_VALIDATE_URL)) {
        $url = rawurldecode($_GET['url']);
    } else {
        $url = rawurldecode(decode($_GET['url']));
    }
} else {
    http_response_code(404);
    exit();
}

$cacheExp = time() - 3600;
$cacheFile = BASE_DIR . 'cache/playlist/' . substr(preg_replace('/[^a-zA-Z0-9]+/', '', $url), 0, 200) . '.m3u8';
if (file_exists($cacheFile) && $cacheExp <= filemtime($cacheFile)) {
    echo @file_get_contents($cacheFile);
    exit();
} else {
    @unlink($cacheFile);
}

$scheme = parse_url($url, PHP_URL_SCHEME);
$host   = parse_url($url, PHP_URL_HOST);
$ref    = hls_referer($url);
$headers = array(
    'Accept: */*',
    'Accept-Encoding: gzip, deflate, br',
    'Accept-Language: id,id-ID;q=0.9,en;q=0.8',
    'Cache-Control: no-cache',
    'Connection: keep-alive',
    'Host: ' . $host,
    'Origin: ' . rtrim($ref, '/'),
    'Pragma: no-cache',
    'Referer: ' . $ref,
    'User-Agent: ' . USER_AGENT,
    'X-Requested-With: XMLHttpRequest'
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
session_write_close();
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if (!$err) {
    if (strpos($response, '#EXTM3U') !== FALSE) {
        $content = parse_hls($response, $url);
        @file_put_contents($cacheFile, $content);
        echo $content;
    } else {
        echo $response;
    }
} else {
    http_response_code(404);
    error_log($err);
}
