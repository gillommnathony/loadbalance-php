<?php
include_once 'includes/config.php';

header('location: ' . BASE_URL . 'embed2/?' . $_SERVER['QUERY_STRING']);
exit;
