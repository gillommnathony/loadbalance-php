<?php
if (!defined('BASE_DIR')) exit();

$popup_link = get_option('popup_ads_link');
if (!empty($popup_link)) {
    echo '<script src="' . $popup_link . '" async></script>';
} else {
    echo get_option('popup_ads_code');
}
