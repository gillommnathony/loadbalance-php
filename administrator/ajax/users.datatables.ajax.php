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
if (!$userLogin || !is_admin()) {
    echo json_encode([
        'data' => $data,
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered
    ]);
    exit;
}

// kolom yang kan ditampilkan
$cols = ["u.name", "u.user", "u.email", "u.status", "u.added", "u.updated", "u.role", "u.id", "u.id"];
$roles = ["Admin", "User"];

// datatables request
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$orderBy = isset($_GET['order'][0]['column']) ? $cols[intval($_GET['order'][0]['column'])] : $cols[4];
$orderDir = isset($_GET['order'][0]['dir']) ? htmlspecialchars($_GET['order'][0]['dir']) : "DESC";
$search = isset($_GET['search']['value']) ? htmlspecialchars($_GET['search']['value']) : "";

// search
$where = '';
if (!empty($search)) {
    $where = 'WHERE ';
    $cols = array_unique($cols);
    foreach ($cols as $col) {
        $where .= "$col LIKE '%$search%' OR ";
    }
    $where = trim(trim($where), 'OR');
}

// result
$userList = [];
$users = $db->prepare("SELECT u.id, u.`name`, u.user, u.email, u.status, u.added, u.updated, u.`role`, (SELECT COUNT(v.id) FROM tb_videos v WHERE v.uid=u.id) as videos FROM tb_users u $where ORDER BY $orderBy $orderDir LIMIT $start, $length");
$users->execute();
$userList = $users->fetchAll(PDO::FETCH_ASSOC);

foreach ($userList as $row) {
    $dt = [];
    $dt['DT_RowId'] = $row['id'];
    $dt['name'] = $row['name'];
    $dt['user'] = $row['user'];
    $dt['email'] = $row['email'];
    $dt['status'] = (int) $row['status'];
    $dt['added'] = !empty($row['added']) ? date('M d, Y H:i', $row['added']) : '';
    $dt['updated'] = !empty($row['updated']) ? date('M d, Y H:i', $row['updated']) : '';
    $dt['role'] = $roles[$row['role']];
    $dt['videos'] = $row['videos'];
    $dt['id'] = $row['id'];
    $data[] = $dt;
}

$recordsTotal = (int) $db->query("SELECT COUNT(id) FROM tb_users")->fetchColumn();
$recordsFiltered = (int) $db->query("SELECT COUNT(u.id) FROM tb_users u $where")->fetchColumn();

echo json_encode([
    'draw' => $draw,
    'data' => $data,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered
]);
