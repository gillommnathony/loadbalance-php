<?php
session_write_close();

require_once "../../vendor/autoload.php";
require_once "../../includes/config.php";
require_once "../../includes/functions.php";

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

// validate file
$check = $db->prepare("SELECT `id`, `file_name` FROM `tb_subtitle_manager` WHERE `host` IS NULL OR `host`='' OR `host`='localhost'");
$check->execute();
$unknownSubs = $check->fetchAll(PDO::FETCH_ASSOC);
if (is_array($unknownSubs) && !empty($unknownSubs)) {
    foreach ($unknownSubs as $sub) {
        if (file_exists(BASE_DIR . "subtitles/$sub[file_name]")) {
            $update = $db->prepare("UPDATE `tb_subtitle_manager` SET `host`=? WHERE id=?");
            $update->execute([BASE_URL, $sub['id']]);
        }
    }
}

// kolom yang kan ditampilkan
$cols = ["s.id", "s.file_name", "s.language", "u.name", "s.host", "s.added", "s.id"];

// datatables request
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$orderBy = isset($_GET['order'][0]['column']) ? htmlspecialchars($cols[intval($_GET['order'][0]['column'])]) : $cols[4];
$orderDir = isset($_GET['order'][0]['dir']) ? htmlspecialchars($_GET['order'][0]['dir']) : "DESC";
$search = isset($_GET['search']['value']) ? htmlspecialchars($_GET['search']['value']) : "";

// search
$where = '';
$host = parse_url(BASE_URL, PHP_URL_HOST);
if (!empty($search)) {
    $where = $userLogin['role'] == 0 ? "WHERE (" : "WHERE s.uid = {$userLogin['id']} AND (";
    $cols = array_unique($cols);
    foreach ($cols as $col) {
        $where .= "$col LIKE '%$search%' OR ";
    }
    $where = trim(trim($where), 'OR') . ')';
} else {
    $where = "WHERE s.uid = {$userLogin['id']}";
}

// result
$subtitles = [];
$sql = "SELECT s.id, s.file_name, s.language, s.host, s.added, u.name FROM tb_subtitle_manager s JOIN tb_users u ON u.id = s.uid $where ORDER BY $orderBy $orderDir LIMIT $start, $length";
$subs = $db->prepare($sql);
$subs->execute();
$subtitles = $subs->fetchAll(PDO::FETCH_ASSOC);

$subDir = BASE_DIR . 'subtitles/';
foreach ($subtitles as $row) {
    $dt = [];
    $dt['DT_RowId']     = $row['id'];
    $dt['file_name']    = $row['file_name'];
    $dt['language']     = $row['language'];
    $dt['name']         = $row['name'];
    $dt['added']        = !empty($row['added']) ? date('M d, Y H:i', $row['added']) : '';
    $dt['host']         = $row['host'];
    $dt['id']           = [
        'id'    => $row['id'],
        'link'  => rtrim($row['host'], '/') . '/subtitles/' . $row['file_name']
    ];
    $data[] = $dt;
}

$recordsTotal = (int) $db->query("SELECT COUNT(s.id) FROM tb_subtitle_manager s")->fetchColumn();
$recordsFiltered = (int) $db->query("SELECT COUNT(s.id) FROM tb_subtitle_manager s JOIN tb_users u ON u.id = s.uid $where")->fetchColumn();

echo json_encode([
    'draw' => $draw,
    'data' => $data,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered
]);
