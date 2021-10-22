<?php
require_once "../vendor/autoload.php";
require_once "../includes/config.php";
require_once "../includes/functions.php";
require_once "includes/functions.php";

$login = new \login();
$login->logout();
create_alert("info", "Logged out successfully!", "index.php");
