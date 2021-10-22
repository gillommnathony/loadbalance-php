<?php
include_once 'includes/config.php';

header('location: ' . BASE_URL . 'download/?' . $_SERVER['QUERY_STRING']);
exit;
