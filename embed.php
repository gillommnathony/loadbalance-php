<?php
include_once 'includes/config.php';

header('location: ' . BASE_URL . 'embed/?' . $_SERVER['QUERY_STRING']);
exit;
