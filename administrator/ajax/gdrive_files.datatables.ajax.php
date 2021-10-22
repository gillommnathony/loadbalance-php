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
if (!$userLogin || !is_admin() || empty($_GET['email'])) {
    echo json_encode([
        'data' => $data,
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered
    ]);
    exit;
}

// kolom yang kan ditampilkan
$cols = ["id", "title", "description", "mimeType", "alternateLink", "shared", "editable", "copyable", "createdDate", "modifiedDate", "id"];

// datatables request
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) && intval($_GET['length']) > 0 ? intval($_GET['length']) : 25;
$orderBy = isset($_GET['order'][0]['column']) ? $cols[intval($_GET['order'][0]['column'])] : $cols[4];
$orderDir = isset($_GET['order'][0]['dir']) ? htmlspecialchars($_GET['order'][0]['dir']) : "desc";
$search = isset($_GET['search']['value']) ? htmlspecialchars($_GET['search']['value']) : "";
$email = isset($_GET['search']['value']) ? filter_var($_GET['email'], FILTER_SANITIZE_EMAIL) : '';
$token = isset($_GET['token']) ? htmlspecialchars($_GET['token']) : '';
$private = isset($_GET['private']) ? filter_var($_GET['private'], FILTER_VALIDATE_BOOLEAN) : false;

// result
$auth = new \gdrive_auth();
$auth->set_email($email);
$files = $auth->get_files($search, $length, $orderBy . ' ' . $orderDir, $token, $private);
if ($files) {
    foreach ($files as $file) {
        if (!empty($file['editable']) && $file['editable']) {
            $embed_link = BASE_URL . 'embed/?' . encode(http_build_query([
                'host'  => 'gdrive',
                'id'    => $file['id'],
                'email' => $email
            ]));

            $createdDate = explode('.', $file['createdDate']);
            $createdDate = strtr($createdDate[0], ['T' =>' ']);
            $modifiedDate = explode('.', $file['modifiedDate']);
            $modifiedDate = strtr($modifiedDate[0], ['T' => ' ']);
            $dt = [];
            $dt['DT_RowId'] = $file['id'];
            $dt['id'] = $file['id'];
            $dt['title'] = trim($file['title']);
            $dt['desc'] = !empty($file['description']) ? trim($file['description']) : '';
            $dt['mimeType'] = $file['mimeType'];
            $dt['alternateLink'] = [
                'embed'     => $embed_link,
                'download'  => $file['webContentLink'],
                'preview'   => $file['embedLink'],
                'view'      => $file['alternateLink'],
                'embed_code' => htmlspecialchars('<iframe src="' . $embed_link . '" frameborder="0" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" width="640" height="320"></iframe>')
            ];
            $dt['shared'] = $file['shared'];
            $dt['editable'] = $file['editable'];
            $dt['copyable'] = $file['copyable'];
            $dt['createdDate'] = $createdDate;
            $dt['modifiedDate'] = $modifiedDate;
            $dt['actions'] = [
                'id'    => $file['id'],
                'shared' => $file['shared']
            ];
            $data[] = $dt;
        }
    }
    $recordsTotal = 50000000;
}

echo json_encode([
    'draw' => $draw,
    'data' => $data,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsTotal,
    'token' => $auth->get_nextPageToken()
]);
