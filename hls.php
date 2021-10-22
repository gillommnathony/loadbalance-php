<?php
require_once 'vendor/autoload.php';
require_once 'includes/config.php';
require_once 'includes/sfunctions.php';

ini_set('max_execution_time', 0);
set_time_limit(0);
if (ini_get('zlib.output_compression')) {
    ini_set('zlib.output_compression', 'Off');
}
session_write_close();

header('Developed-By: GDPlayer.top');

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

$host   = parse_url($url, PHP_URL_HOST);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 20971528);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
if (defined('CURLOPT_TCP_FASTOPEN')) {
    curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1);
}
curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENT);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept-Encoding: gzip, deflate',
    'Cache-Control: no-cache',
    'Connection: keep-alive',
    'Host: ' . $host,
    'Origin: https://' . $host,
    'Pragma: no-cache',
    'X-Requested-With: XMLHttpRequest'
));
// Don't load that file into memory, stream it down to the browser instead
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $body) {
    echo $body;
    return strlen($body);
});
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

ob_get_clean();
if (strpos($contentType, 'video/') !== FALSE) {
    header('Content-Type: ' . $contentType);
} else {
    header('Content-Type: video/mp2t');
}
session_write_close();
curl_exec($ch);
curl_close($ch);
