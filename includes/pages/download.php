<?php
if (!defined('BASE_DIR')) exit;

session_write_close();
ini_set('max_execution_time', 0);
set_time_limit(0);

$error = 200;
$title = '404 Not Found';
$sources = [];
$sources_alt = [];
$tracks = [];
$dlActive = get_option('enable_download_page');
$notFound = FALSE;

$parser = UAParser\Parser::create();
$browser = !empty($_SERVER['HTTP_USER_AGENT']) ? $parser->parse($_SERVER['HTTP_USER_AGENT'])->toString() : 'bot';
$remote_ip = $_SERVER['REMOTE_ADDR'];
$uri = $_SERVER['REQUEST_URI'];
$path = explode('/', trim($uri, '/'));
$key = !empty($path[1]) && substr($path[1], 0, 1) !== '?' ? trim($path[1], '?') : '';

if (!filter_var($dlActive, FILTER_VALIDATE_BOOLEAN)) {
	if (!empty($_SERVER['QUERY_STRING'])) {
		header('location:' . BASE_URL . 'embed/?' . $_SERVER['QUERY_STRING']);
	} elseif (!empty($key)) {
		header('location:' . BASE_URL . 'embed/' . $key);
	}
	exit();
} else {
	if (!empty($_SERVER['QUERY_STRING'])) {
		// ambil query
		$qs = $_SERVER['QUERY_STRING'];
		$query = decode($qs);
		parse_str($query, $newqry);
		$notFound = false;
	} elseif (!empty($key)) {
		if (!empty($path[1])) {
			$video = new \videos();
			$vid = $video->getVideoByKey($path[1]);
			if ($vid) {
				$newqry = array(
					'source' => 'db',
					'id' => $vid
				);
				$qs = http_build_query($newqry);
				$notFound = false;
			} else {
				$notFound = true;
			}
		} else {
			$notFound = true;
		}
	} else {
		$notFound = true;
	}

	if ($notFound === false) {
		// parse query
		$parse = new \parse_sources($newqry);
		$parse->remote_ip = $remote_ip;
		$parse->user_agent = $browser;
		$parse->qry_string = $qs;
		$parse->real_user_agent = $_SERVER['HTTP_USER_AGENT'];

		if ($parse->get_load_balancer_method() === 'redirect') {
			header('location:' . rtrim($parse->get_load_balancer_host(), '/') . '/download.php?' . $qs);
			exit;
		}
		$config = $parse->get_config(true);
		if ($config) {
			$title = !empty($config['title']) ? $config['title'] : 'Watch';
			if (is_word_blacklisted($title)) {
				http_response_code(404);
				$error = 404;
			}
			$save_public = filter_var(get_option('save_public_video'), FILTER_VALIDATE_BOOLEAN);
			if ($save_public && !empty($newqry['host'])) {
				$videos = new \videos();
				$get = $videos->get_by('host_id', $newqry['id']);
				if (empty($get)) {
					$user_id = get_option('public_video_user');
					$user_id = !empty($user_id) ? $user_id : 1;
					$videos->insert_anonymous([
						'host' => $newqry['host'],
						'host_id' => $newqry['id'],
						'ahost' => (!empty($newqry['ahost']) ? $newqry['ahost'] : ''),
						'ahost_id' => (!empty($newqry['aid']) ? $newqry['aid'] : ''),
						'title' => $title,
						'user_id' => $user_id,
						'subtitle' => array_column($config['tracks'], 'file'),
						'language' => array_column($config['tracks'], 'label')
					]);
				}
			}
			$sources = !empty($config['sources']) ? $config['sources'] : [];
			$sources_alt = !empty($config['sources_alt']) ? $config['sources_alt'] : [];
			if (!empty($config['tracks']) && !filter_var(get_option('hide_sub_download'), FILTER_VALIDATE_BOOLEAN)) {
				$tracks = $config['tracks'];
			}
		} else {
			http_response_code(404);
			$error = 404;
		}
	} else {
		http_response_code(404);
		$error = 404;
	}
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo $title . ' - ' . sitename(); ?></title>
	<link rel="icon" href="<?php echo BASE_URL; ?>favicon.png" type="image/png">

	<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sweetalert.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

	<link rel="preconnect" href="//www.paypal.com">
	<link rel="preconnect" href="//www.sandbox.paypal.com">
	<link rel="preconnect" href="//static.addtoany.com">
	<link rel="preconnect" href="//www.google.com">

	<script src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
	<?php include_once 'includes/ga.php'; ?>
	<?php include_once 'includes/gtm_head.php'; ?>
</head>

<body class="bg-light h-100">
	<?php include_once 'includes/gtm_body.php'; ?>
	<div class="container bg-white rounded shadow py-5 my-5">
		<div class="row">
			<div class="col-12 py-3 text-center">
				<h1 class="h3 mb-0" style="word-break:break-all"><?php echo $title; ?></h1>
			</div>
			<div class="col-12"><?php echo htmlspecialchars_decode(get_option('dl_banner_top')); ?></div>
		</div>
		<?php
		if ($error !== 200) :
			error($error);
		else :
		?>
			<div class="row py-3">
				<div class="col-12 col-md-9 mx-auto">
					<a href="<?php echo BASE_URL . 'embed/' . (!empty($key) ? $key : '?' . $_SERVER['QUERY_STRING']); ?>" class="btn btn-danger btn-lg btn-block btn-watch" target="_blank">Watch <?php echo trim(strtr($title, ['Download' => '', 'Watch' => ''])); ?></a>
					<div id="dlWrapper" class="my-2"></div>
				</div>
			</div>
		<?php
		endif;
		?>
		<div class="row">
			<div class="col-12"><?php echo htmlspecialchars_decode(get_option('dl_banner_bottom')); ?></div>
		</div>
	</div>
	<script>
		$(document).ready(function() {
			<?php
			$html = 'var html = \'';
			$directAds = get_option('direct_ads_link');
			if (!empty($sources)) {
				foreach ($sources as $src) {
					$link = download_link($src['file']);
					if (filter_var($link, FILTER_VALIDATE_URL)) {
						if (!empty($directAds)) {
							$html .= '<a href="' . $link . '" class="btn btn-primary btn-lg btn-block btn-download">Download ' . $src['label'] . ' Video</a>';
						} else {
							$html .= '<a href="' . $link . '" target="_blank" class="btn btn-primary btn-lg btn-block btn-download">Download Video ' . $src['label'] . '</a>';
						}
					}
				}
			}
			if (!empty($sources_alt)) {
				foreach ($sources_alt as $src) {
					$link = download_link($src['file']);
					if (filter_var($link, FILTER_VALIDATE_URL)) {
						if (!empty($directAds)) {
							$html .= '<a href="' . $link . '" class="btn btn-secondary btn-lg btn-block btn-download">Download Alternative ' . $src['label'] . ' Video</a>';
						} else {
							$html .= '<a href="' . $link . '" target="_blank" class="btn btn-secondary btn-lg btn-block btn-download">Download Alternative Video ' . $src['label'] . '</a>';
						}
					}
				}
			}
			if (!empty($tracks)) {
				foreach ($tracks as $track) {
					$link = download_link($track['file']);
					if (!empty($directAds)) {
						$html .= '<a href="' . $link . '" class="btn btn-success btn-lg btn-block btn-download">Download ' . $track['label'] . ' Subtitle</a>';
					} else {
						$html .= '<a href="' . $link . '" target="_blank" class="btn btn-success btn-lg btn-block btn-download">Download ' . $track['label'] . ' Subtitle</a>';
					}
				}
			}
			$html .= '\';';
			$html .= '$("#dlWrapper").html(html);';
			if (!empty($directAds)) $html .= '$(".btn-primary, .btn-secondary").click(function(){window.open("' . $directAds . '", "_blank")});';

			$production_mode = get_option('production_mode');
			if (filter_var($production_mode, FILTER_VALIDATE_BOOLEAN)) {
				echo jsObfustator($html);
			} else {
				echo $html;
			}
			?>
		});
	</script>
	<?php
	include_once 'includes/share.php';
	include_once 'includes/histats.php';
	include_once 'includes/popupads.php';
	?>
</body>

</html>
