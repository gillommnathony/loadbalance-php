<?php
if (!defined('BASE_DIR')) die('access denied');

session_write_close();
header('Content-Type: video/mp2t');
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

$mf = !empty(get_option('memory_friendly'));
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
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 2097152);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
if ($mf) {
    // Don't load that file into memory, stream it down to the browser instead
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $body) {
        echo $body;
        ob_flush(); // flush output buffer (Output Control configuration specific)
        flush();    // flush output body (SAPI specific)
        return strlen($body);
    });
}
session_write_close();
curl_exec($ch);
curl_close($ch);
