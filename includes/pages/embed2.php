<?php
if (!defined('BASE_DIR')) exit;

if (!is_anonymous()) {
    http_response_code(403);
    exit('ACCESS DENIED');
}

$embedUrl = '';
$dlUrl = '';
$query = $_GET;
$query['host'] = !empty($query['host']) ? htmlentities($query['host']) : 'gdrive';
if (!empty($query['id'])) {
    // get onedrive original id
    if ($query['host'] === 'onedrive') {
        parse_str(trim(trim($query['id'], '?'), 'embed/?'), $qry);
        if (!empty($qry['id'])) {
            $query['id'] = $qry['id'];
        } elseif (!empty($qry['resid'])) {
            $query['id'] = $qry['resid'];
        }
    }
    // buat query string
    $qry = http_build_query($query);
    // embed url
    $embedUrl = filter_var(BASE_URL . 'embed/?' . encode($qry), FILTER_SANITIZE_URL);
    $dlUrl = filter_var(BASE_URL . 'download/?' . encode($qry), FILTER_SANITIZE_URL);
} else {
    $query['host'] = 'gdrive';
    $query['ahost'] = '';
    $query['id'] = '';
    $query['aid'] = '';
    $query['sub'] = '';
    $query['lang'] = '';
}

if (!empty($query['onlylink']) && filter_var($_GET['onlylink'], FILTER_VALIDATE_BOOLEAN)) {
    if (!empty($_GET['download']) && filter_var($_GET['download'], FILTER_VALIDATE_BOOLEAN)) {
        echo $dlUrl;
    } else {
        echo $embedUrl;
    }
} else {
    if (!empty($_GET['download']) && filter_var($_GET['download'], FILTER_VALIDATE_BOOLEAN)) {
        header('location: ' . $dlUrl);
    } else {
        header('location: ' . $embedUrl);
    }
}
