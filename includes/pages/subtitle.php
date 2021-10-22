<?php
if (!defined('BASE_DIR')) die('access denied');

session_write_close();
header('Content-Type: text/vtt; charset=UTF-8');
ini_set('max_execution_time', 0);
set_time_limit(0);

use \Done\Subtitles\Subtitles;

if (!empty($_GET['url'])) {
	$url	= urldecode($_GET['url']);
	$host	= parse_url($url, PHP_URL_HOST);
	$title 	= explode('/', $url);
	$title	= end($title);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'host: ' . $host,
		'origin: https://' . $host,
	]);
	$response = curl_exec($ch);
	$err = curl_error($ch);
	curl_close($ch);

	if (!$err) {
		if (substr($response, 0, 6) !== 'WEBVTT') {
			$ext = pathinfo($url, PATHINFO_EXTENSION);
			$subtitle = Subtitles::load($response, $ext);
			echo str_replace(['{\an1}', '{\an2}', '{\an3}', '{\an4}', '{\an5}', '{\an6}', '{\an7}', '{\an8}', '{\an9}'], '', strip_tags($subtitle->content('vtt'), '<b><strong><i><em><u><s>'));
		} else {
			echo $response;
		}
	} else {
		http_response_code(404);
	}
} else {
	http_response_code(404);
}
