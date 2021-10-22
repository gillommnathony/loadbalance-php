<?php
include_once 'includes/config.php';

header('location: ' . BASE_URL . 'poster/?' . $_SERVER['QUERY_STRING']);
exit;
