<?php
error_reporting(0);
session_write_close();

require_once 'vendor/autoload.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';

$referer = '';
autoupdateProxy();

if (!empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
} elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
    $referer = $_SERVER['HTTP_ORIGIN'];
}

if(!is_domain_whitelisted($referer)){
    http_response_code(403);
    exit();
}
elseif(is_domain_blacklisted($referer) || is_referer_blacklisted($referer)){
	http_response_code(403);
    exit();
}

// jika dinonaktifkan untuk publik
if(!is_anonymous()){
    http_response_code(403);
    exit;
}

$query = $_GET;
$query['host'] = !empty($query['host']) ? htmlentities($query['host']) : 'gdrive';
$embedUrl = '';
$dlUrl = '';
if(!empty($query['id'])){
    // get onedrive original id
    if($query['host'] === 'onedrive'){
        parse_str(trim(trim($query['id'], '?'), 'embed?'), $qry);
        if(!empty($qry['id'])){
            $query['id'] = $qry['id'];
        }
        elseif(!empty($qry['resid'])){
            $query['id'] = $qry['resid'];
        }
    }
    // buat query string
    $qry = http_build_query($query);
    // embed url
    $embedUrl = BASE_URL .'embed.php?'. encode($qry);
    $dlUrl = BASE_URL .'download.php?'. encode($qry);
}
else {
    $query['host'] = 'gdrive';
    $query['ahost'] = '';
    $query['id'] = '';
    $query['aid'] = '';
    $query['sub'] = '';
    $query['lang'] = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GDPlayer.top | Google Drive API</title>
	<link rel="icon" href="<?php echo BASE_URL; ?>favicon.png" type="image/png">
    <style>
        body {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            box-shadow: none;
        }
        iframe {
            position: absolute;
            width: 100% !important;
            height: 100% !important;
        }
        iframe ~ iframe{
            display: none !important;
        }
    </style>
</head>
<body>
    <?php 
    if(isset($_GET['download']) && filter_var($_GET['download'], FILTER_VALIDATE_BOOLEAN)){
        echo '<iframe src="'. $dlUrl .'" frameborder="0" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe>'; 
    }
    else {
        echo '<iframe src="'. $embedUrl .'" frameborder="0" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe>'; 
    }
    ?>
</body>
</html>
