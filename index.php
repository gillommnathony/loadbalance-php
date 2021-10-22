<?php
require_once 'vendor/autoload.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';

session_write_close();
ini_set('max_execution_time', 0);
set_time_limit(0);
header('Developed-By: GDPlayer.top');

$pub = ['terms', 'privacy', 'administrator'];
$wHF = ['embed', 'embed2', 'download', 'subtitle', 'poster', 'playlist', 'hls', 'videoplayback'];
$direct = ['subtitle', 'poster', 'playlist', 'hls', 'videoplayback'];
$uri = $_SERVER['REQUEST_URI'];
$uri = explode('/', $uri);
$curi = count($uri);
if ($curi > 2) {
    $pageName = $uri[1];
} else {
    $pageName = $uri[0];
}
$pageFile = 'includes/pages/' . str_replace(['.php', '.pl' . '.html' . '.htm'], '', $pageName) . '.php';

if (!is_admin()) {
    if (!show_public_balancer() && empty($pageName)) {
        $siteGenerator = get_option('main_site');
        if (filter_var($siteGenerator, FILTER_VALIDATE_URL) && !in_array($pageName, $direct)) {
            header('location: ' . $siteGenerator);
            exit();
        }
    } elseif (!filter_var(get_option('anonymous_generator'), FILTER_VALIDATE_BOOLEAN) && !in_array($pageName, $wHF) && !in_array($pageName, $pub)) {
        header('location: ' . BASE_URL . 'administrator/');
        exit();
    }
}
newUpdate();

// template header
if (!in_array($pageName, $wHF)) {
    header('X-Frame-Options: SAMEORIGIN');
    include_once 'header.php';
}
if (file_exists(BASE_DIR . $pageFile)) {
    session_write_close();
    include_once $pageFile;
} else {
    include_once 'includes/pages/home.php';
}
// template footer
if (!in_array($pageName, $wHF)) {
    include_once 'footer.php';
}
