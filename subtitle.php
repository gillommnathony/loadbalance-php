<?php
include_once 'includes/config.php';

header('location: ' . BASE_URL . 'subtitle/?' . $_SERVER['QUERY_STRING']);
exit;
