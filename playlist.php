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

$cachefile = BASE_DIR . 'cache/playlist/' . substr(preg_replace('/[^a-zA-Z0-9]+/', '', $url), 0, 200) . '.m3u8';
$cachetime = 3600 * 3;
if (file_exists($cachefile) && time() - $cachetime <= filemtime($cachefile)) {
    echo @file_get_contents($cachefile);
    exit();
}

$scheme = parse_url($url, PHP_URL_SCHEME);
$host   = parse_url($url, PHP_URL_HOST);
$ref    = !empty($_GET['ref']) ? rawurldecode(decode($_GET['ref'])) : $scheme . '://' . $host;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
if (defined('CURLOPT_TCP_FASTOPEN')) {
    curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1);
}
curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_REFERER, $ref);
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
session_write_close();
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($status >= 200 && $status < 400) {
    header('Content-Type: ' . $contentType);
    if (strpos($contentType, 'application') !== FALSE) {
        $hls = trim(parse_hls($response, $url), '1');
        file_put_contents($cachefile, $hls);
        echo $hls;
    } else {
        echo trim($response, '1');
    }
    exit;
} else {
    http_response_code(404);
    exit;
}
