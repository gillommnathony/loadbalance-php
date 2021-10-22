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
$cols = ["name", "link", "status", "public", "added", "updated", "id"];

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
$loadBalancers = [];
$users = $db->prepare("SELECT `id`, `name`, `link`, `status`, `public`, `added`, `updated` FROM tb_loadbalancers $where ORDER BY $orderBy $orderDir LIMIT $start, $length");
$users->execute();
$loadBalancers = $users->fetchAll(PDO::FETCH_ASSOC);

foreach ($loadBalancers as $row) {
    $dt = [];
    $dt['DT_RowId'] = $row['id'];
    $dt['name'] = $row['name'];
    $dt['link'] = '<a href="' . $row['link'] . '" target="_blank">' . $row['link'] . '</a>';
    $dt['status'] = $row['status'];
    $dt['public'] = $row['public'];
    $dt['added'] = !empty($row['added']) ? date('M d, Y H:i', $row['added']) : '';
    $dt['updated'] = !empty($row['updated']) ? date('M d, Y H:i', $row['updated']) : '';
    $dt['id'] = $row['id'];
    $data[] = $dt;
}

$recordsTotal = (int) $db->query("SELECT COUNT(id) FROM tb_loadbalancers")->fetchColumn();
$recordsFiltered = (int) $db->query("SELECT COUNT(id) FROM tb_loadbalancers $where")->fetchColumn();

echo json_encode([
    'draw' => $draw,
    'data' => $data,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered
]);
