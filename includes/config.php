<?php
function getHtmlRootFolder(string $root = '/app/') {

    // -- try to use DOCUMENT_ROOT first --
    $ret = str_replace(' ', '', $_SERVER['DOCUMENT_ROOT']);
    $ret = rtrim($ret, '/') . '/';

    // -- if doesn't contain root path, find using this file's loc. path --
    if (!preg_match("#".$root."#", $ret)) {
      $root = rtrim($root, '/') . '/';
      $root_arr = explode("/", $root);
      $pwd_arr = explode("/", getcwd());
      $ret = $root . $pwd_arr[count($root_arr) - 1];
    }

    return (preg_match("#".$root."#", $ret)) ? rtrim($ret, '/') . '/' : null;
}
$dl = getHtmlRootFolder();

// BASE_URL, format: string, example: 'http://mywebdomain.com/'
// adjust to your website homepage address (slash sign at the end)
define('BASE_URL', 'https://'.$_SERVER['SERVER_NAME'].'/');

// BASE_DIR, format: string, example: '/home/user/public_html/'
// adjust to your website directory (slash sign at the end)
define('BASE_DIR', $dl);

// SECURE_SALT, format: string, example: 'kmzwayw1aa-12345'
define('SECURE_SALT', 'password-123');

// useragent makes the tool appear accessible to humans instead of robots
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.104 Safari/537.36');

