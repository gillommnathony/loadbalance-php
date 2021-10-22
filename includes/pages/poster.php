<?php
if (!defined('BASE_DIR')) die('access denied');

session_write_close();
ini_set('max_execution_time', 0);
set_time_limit(0);

if (!empty($_GET['url'])) {
	$url	= rawurldecode(decode($_GET['url']));
	$host	= parse_url($url, PHP_URL_HOST);
	$port 	= parse_URL($url, PHP_URL_PORT);
	if (empty($port)) {
		$port = parse_url($url, PHP_URL_SCHEME) == 'https' ? 443 : 80;
	}
	$ipv4 = gethostbyname($host);
	$resolveHost = implode(':', array($host, $port, $ipv4));

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	curl_setopt($ch, CURLOPT_RESOLVE, array($resolveHost));
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	if (defined('CURLOPT_TCP_FASTOPEN')) {
		curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1);
	}
	curl_setopt($ch, CURLOPT_TCP_NODELAY, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Connection: keep-alive',
		'Host: ' . $host,
	));
	session_write_close();
	$response = curl_exec($ch);
	$type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	$err = curl_error($ch);
	curl_close($ch);

	if (!$err) {
		header("Content-Type: $type");
		echo $response;
		exit;
	}
}
http_response_code(404);
