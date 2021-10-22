<?php
$hostname = '51.81.174.84';
$username = 'proxy';
$password = 'ibrtczW8ZMK4ePKF';
$database = 'proxy';

try {
    if(!empty($hostname) && !empty($database) && !empty($username)) {
        $db = new \PDO("mysql:host=$hostname;dbname=$database;charset=utf8", $username, $password);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } elseif (file_exists(BASE_DIR . "admin_db/data.sqlite")) {
        $db = new \PDO("sqlite:" . BASE_DIR . "admin_db/data.sqlite");
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } else {
        $db = null;
        exit('Database disconnected!');
    }
    if($db){
        session_start();
    }
} catch (\PDOException $e) {
    $db = null;
    error_log($e->getMessage());
    exit('Database disconnected!');
} catch (\Exception $e) {
    $db = null;
    error_log($e->getMessage());
    exit('Database disconnected!');
}
