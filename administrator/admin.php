<?php
session_write_close();
header('X-Frame-Options: SAMEORIGIN');
header('Developed-By: GDPlayer.top');

require_once "../vendor/autoload.php";
require_once "../includes/config.php";
require_once "../includes/functions.php";
require_once "includes/functions.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
newUpdate();

//ini method untuk mencegah user membypass halaman dengan akses langsung ke URL
$login = new \login();
$login->login_redir();
$page = isset($_GET['go']) ? htmlentities($_GET['go']) : '';
if (empty($page)) {
    header('location: ' . BASE_URL . 'administrator/admin.php?go=videos');
    exit;
}

include_once 'header.php';
if (!empty($page)) {
    $file = "views/$page.php";

    if (file_exists($file)) {
        $class = explode('/', $page);
        $class = trim($class[0]);
        include_once $file;
    } else {
        include_once "views/404.php";
    }
}
include_once 'footer.php';
