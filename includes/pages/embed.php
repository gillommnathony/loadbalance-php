<?php
if (!defined('BASE_DIR')) exit;

session_write_close();
ini_set('max_execution_time', 0);
set_time_limit(0);

$sitename = sitename();
$js_name 	= '';
$title 		= '';
$message 	= '';
$poster 	= '';
$config 	= [];
$host 	= parse_url(BASE_URL, PHP_URL_HOST);
$direct = !empty(get_option('embed_page_direct')) ? true : false;
$isBlocked = FALSE;
$notFound = FALSE;
$isSSL = isset($_SERVER['HTTPS']) || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

$parser = \UAParser\Parser::create();
$browser = !empty($_SERVER['HTTP_USER_AGENT']) ? $parser->parse($_SERVER['HTTP_USER_AGENT'])->toString() : 'bot';
$remote_ip = $_SERVER['REMOTE_ADDR'];

unset($_SESSION['referer']);
if (!empty($_SERVER['HTTP_REFERER'])) {
	$_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
} elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
	$_SESSION['referer'] = $_SERVER['HTTP_ORIGIN'];
} else {
	$_SESSION['referer'] = '';
}

if ($direct === false && empty($_SESSION['referer'])) {
	http_response_code(403);
	$title = 'Access denied';
	$message = 'Access denied! Please access the embed page using an iframe.';
	$isBlocked = TRUE;
} elseif (!is_domain_whitelisted($_SESSION['referer'])) {
	http_response_code(403);
	$title = 'Access denied';
	$message = 'Access denied!';
	$isBlocked = TRUE;
} elseif (is_domain_blacklisted($_SESSION['referer']) || is_referer_blacklisted($_SESSION['referer'])) {
	http_response_code(403);
	$title = 'Access denied';
	$message = 'Access denied!';
	$isBlocked = TRUE;
} else {
	if (empty($_SERVER['QUERY_STRING'])) {
		$uri = $_SERVER['REQUEST_URI'];
		$path = explode('/', rtrim(parse_url($uri, PHP_URL_PATH), '/'));
		$key = end($path);
		if (!empty($key)) {
			$video = new \videos();
			$vid = $video->getVideoByKey($key);
			if ($vid) {
				$newqry = array(
					'source' => 'db',
					'id' => $vid
				);
				$qs = http_build_query($newqry);
			} else {
				http_response_code(404);
				$title = 'Video is Unavailable';
				$message = 'Sorry this video is unavailable.';
				$notFound = TRUE;
			}
		} else {
			http_response_code(404);
			$title = 'Video is Unavailable';
			$message = 'Sorry this video is unavailable.';
			$notFound = TRUE;
		}
	} else {
		// ambil query
		$qs = $_SERVER['QUERY_STRING'];
		$query = decode($qs);
		parse_str($query, $newqry);
	}

	if ($notFound === false) {
		$parse = new \parse_sources($newqry);
		$parse->remote_ip = $remote_ip;
		$parse->user_agent = $browser;
		$parse->qry_string = $qs;
		$parse->real_user_agent = $_SERVER['HTTP_USER_AGENT'];

		if ($parse->get_load_balancer_method() === 'redirect') {
			header('location:' . rtrim($parse->get_load_balancer_host(), '/') . '/embed/?' . $qs);
			exit;
		} else {
			$config = $parse->get_config();
			if (!empty($config)) {
				$title = !empty($config['title']) ? $config['title'] : 'Watch';
				$poster = !empty($config['poster']) ? $config['poster'] : get_option('poster');
			} else {
				http_response_code(404);
				$title = 'Video is Unavailable';
				$message = 'Sorry this video is unavailable.';
				$notFound = TRUE;
			}
			if (is_word_blacklisted($title)) {
				http_response_code(404);
				$title = 'Video is Unavailable';
				$message = 'Sorry this video is unavailable.';
				$notFound = TRUE;
			}
		}
	} else {
		http_response_code(404);
	}
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<script async src="https://arc.io/widget.min.js#A3rRJ2mv"></script>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php if ($isSSL) : ?>
		<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
	<?php endif; ?>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="robots" content="noindex">
	<meta name="description" content="<?php echo $title; ?>">
	<meta name="referrer" content="same-origin" />

	<!-- facebook -->
	<meta name="og:title" content="<?php echo $title; ?>">
	<meta name="og:description" content="<?php echo $title; ?>">
	<meta name="og:type" content="video.movie">
	<meta name="og:sitename" content="<?php echo $sitename; ?>">
	<meta name="og:image" content="<?php echo $poster; ?>">

	<!-- twitter -->
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo $title; ?>">
	<meta name="twitter:description" content="<?php echo $title; ?> | <?php echo $sitename; ?>">
	<meta name="twitter:image" content="<?php echo $poster; ?>">

	<title><?php echo $title; ?> - <?php echo $sitename; ?></title>

	<link rel="icon" href="<?php echo BASE_URL; ?>favicon.png" type="image/png">

	<link rel="preconnect" href="//content.jwplatform.com">
	<link rel="preconnect" href="//cdn.jsdelivr.net">
	<link rel="preconnect" href="//cdnjs.cloudflare.com">
	<link rel="preconnect" href="//t.dtscdn.com">
	<link rel="preconnect" href="//e.dtscout.com">
	<link rel="preconnect" href="//s4.histats.com">
	<link rel="preconnect" href="//s10.histats.com">
	<link rel="preconnect" href="//tags.bluekai.com">
	<link rel="preconnect" href="//www.gstatic.com">
	<link rel="preconnect" href="//www.googleapis.com">
	<link rel="preconnect" href="//www.googletagmanager.com">
	<link rel="preconnect" href="//ssl.p.jwpcdn.com">

	<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
	<?php include_once 'includes/player_style.php'; ?>
	<?php include_once 'includes/ga.php'; ?>
	<?php include_once 'includes/gtm_head.php'; ?>
</head>

<body class="body videoplayer">
	<?php include_once 'includes/gtm_body.php'; ?>
	<div id="mContainer" class="embed-container">
		<div class="embed-wrapper">
			<div id="loading" class="lds-ellipsis">
				<div></div>
				<div></div>
				<div></div>
			</div>
			<h1 id="message" style="text-align:center;font-size:1.6em;display:none"><?php echo $message; ?></h1>
		</div>
	</div>
	<div id="videoContainer" style="display:none"></div>
	<div id="resume" style="display: none">
		<div class="pop-wrap">
			<div class="pop-main">
				<div class="pop-html">
					<div class="pop-block">
						<div class="myConfirm">
							<p>Welcome back! You left off at <span id="timez"></span>. Would you like to resume watching?</p>
							<p><button id="resume_no" class="button" onclick="document.getElementById('resume').style.display='none'">No, Thanks</button><button id="resume_yes" class="button" onclick="resumePlayback()">Yes, Please</button></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="test" style="display:none">off</div>
	<script src="<?php echo BASE_URL; ?>assets/js/prebid-ads.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/detect-adblocker.min.js"></script>
	<script src="<?php echo BASE_URL; ?>assets/js/devtools-detector.js"></script>
	<?php if (!$isBlocked && !$notFound && !empty($config['query'])) : ?>
		<script src="https://ssl.p.jwpcdn.com/player/v/8.18.4/jwplayer.js"></script>
		<script>
			<?php echo $parse->get_javascript($config['query']); ?>
			window.onorientationchange = function(e) {
				if (e.landscape) {
					var vid = document.getElementsByTagName("video")[0];
					if (vid.requestFullscreen) {
						vid.requestFullscreen();
					} else if (vid.mozRequestFullScreen) {
						vid.mozRequestFullScreen();
					} else if (vid.webkitRequestFullscreen) {
						vid.webkitRequestFullscreen();
					}
				}
			};
		</script>
	<?php else : ?>
		<script>
			document.getElementById("loading").style.display = "none";
			document.getElementById("videoContainer").style.display = "none";
			document.getElementById("message").style.display = "block";
		</script>
	<?php endif; ?>
	<?php include_once 'includes/histats.php'; ?>
	<?php include_once 'includes/popupads.php'; ?>

</body>

</html>
