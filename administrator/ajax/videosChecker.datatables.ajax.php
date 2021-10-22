<?php
error_reporting(0);
require_once "../../vendor/autoload.php";
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
require_once "../includes/functions.php";

$data = [];
$recordsTotal = 0;
$recordsFiltered = 0;
$draw = isset($_GET['draw']) ? intval($_GET['draw']) : 1;

// khusus untuk super admin yang sedang login
$login = new \login();
$userLogin = $login->cek_login();
if (!$userLogin) {
    echo json_encode([
        'data' => $data,
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered
    ]);
    exit;
}

// datatables request
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

// result
try {
    $videoList = [];
    $videos = $db->prepare("SELECT * FROM `tb_videos` WHERE `uid`=? ORDER BY `updated` ASC LIMIT $start, 10");
    $videos->execute(array($userLogin['id']));
    $videoList = $videos->fetchAll(\PDO::FETCH_ASSOC);
    if ($videoList) {
        foreach ($videoList as $i => $row) {
            $status = video_checker($row);
            if($status) {
                $hosts = [];
                if (!empty($row['host_id'])) $hosts[] = $row['host'];
                if (!empty($row['ahost_id'])) $hosts[] = $row['ahost'];
                
                $dt = [];
                $dt['DT_RowId'] = $row['id'];
                $dt['id']       = $row['id'];
                $dt['title']    = strip_tags($row['title']);
                $dt['host']     = $hosts;
                $dt['status']   = $status;
                $data[]         = $dt;
            }
        }
        $recordsTotal = (int) $db->query("SELECT COUNT(id) FROM tb_videos")->fetchColumn();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}

echo json_encode([
    'draw' => $draw,
    'data' => $data,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsTotal
]);
