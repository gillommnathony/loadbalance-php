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

// kolom yang kan ditampilkan
$cols = ["v.id", "v.title", "v.host", "v.id", "v.id", "u.name", "v.added", "v.updated", "v.id"];

// datatables request
$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$length = isset($_GET['length']) ? intval($_GET['length']) : 10;
$orderBy = isset($_GET['order'][0]['column']) ? $cols[intval($_GET['order'][0]['column'])] : $cols[6];
$orderDir = isset($_GET['order'][0]['dir']) ? htmlspecialchars($_GET['order'][0]['dir']) : "DESC";
$search = isset($_GET['search']['value']) ? htmlspecialchars($_GET['search']['value']) : "";

$where = '';
if (!is_admin()) {
    $where = 'WHERE v.uid=' . intval($userLogin['id']);
}
// search
if (!empty($search)) {
    $cols[] = 'v.host_id';
    $cols[] = 'v.ahost';
    $cols[] = 'v.ahost_id';
    $cols = array_unique($cols);
    if (!is_admin()) {
        $where .= ' AND (';
    } else {
        $where .= 'WHERE (';
    }
    foreach ($cols as $col) {
        $where .= "$col LIKE '%$search%' OR ";
    }
    $where = trim(trim($where), 'OR') . ')';
}

// result
try {
    $videoList = [];
    $videos = $db->prepare("SELECT v.*, u.name FROM tb_videos v JOIN tb_users u ON u.id = v.uid $where ORDER BY $orderBy $orderDir LIMIT $start, $length");
    $videos->execute();
    $videoList = $videos->fetchAll(\PDO::FETCH_ASSOC);
    if ($videoList) {
        $video = new \videos();
        foreach ($videoList as $row) {
            $key = $video->getKey($row['id']);
            if ($key) {
                $embed_link     = BASE_URL . 'embed/' . $key;
                $download_link  = BASE_URL . 'download/' . $key;
            } else {
                $query = array(
                    'source' => 'db',
                    'id' => $row['id'],
                );
                $query_encoded  = encode(http_build_query($query));
                $embed_link     = BASE_URL . 'embed/?' . $query_encoded;
                $download_link  = BASE_URL . 'download/?' . $query_encoded;
            }
            $subs = getSubtitles($row['id']);
            $hosts = [];
            if (!empty($row['host_id'])) {
                $hosts[] = $row['host'];
                if ($subs) {
                    $embed_default = BASE_URL . 'embed/?' . encode(http_build_query(array(
                        'host'  => $row['host'],
                        'id'    => $row['host_id'],
                        'sub'   => $subs['link'],
                        'lang'  => $subs['language']
                    )));
                } else {
                    $embed_default = BASE_URL . 'embed/?' . encode(http_build_query(array(
                        'host'  => $row['host'],
                        'id'    => $row['host_id'],
                    )));
                }
            }
            if (!empty($row['ahost_id'])) {
                $hosts[] = $row['ahost'];
                if ($subs) {
                    $embed_alternative = BASE_URL . 'embed/?' . encode(http_build_query(array(
                        'host' => $row['ahost'],
                        'id' => $row['ahost_id'],
                        'sub'   => $subs['link'],
                        'lang'  => $subs['language']
                    )));
                } else {
                    $embed_alternative = BASE_URL . 'embed/?' . encode(http_build_query(array(
                        'host' => $row['ahost'],
                        'id' => $row['ahost_id'],
                    )));
                }
            }
            $embed_iframe   = htmlspecialchars('<iframe src="' . $embed_link . '" frameborder="0" allowFullScreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" width="640" height="320"></iframe>');
            if ($row['host'] === 'fembed') {
                $host = parse_url($row['host_id'], PHP_URL_HOST);
                $original_link = strtr($row['host_id'], [$host => 'femax20.com']);
            } else {
                $original_link = !empty($row['host_id']) ? getDownloadLink($row['host'], $row['host_id']) : '#';
            }
            if ($row['ahost'] === 'fembed') {
                $host = parse_url($row['ahost_id'], PHP_URL_HOST);
                $alternative_link = strtr($row['ahost_id'], [$host => 'femax20.com']);
            } else {
                $alternative_link = !empty($row['ahost_id']) ? getDownloadLink($row['ahost'], $row['ahost_id']) : '#';
            }

            $dt = [];
            $dt['DT_RowId'] = $row['id'];
            $dt['title']    = strip_tags($row['title']);
            $dt['host']     = $hosts;
            $dt['id']       = $row['id'];
            $dt['embed']    =
                $dt['links']    = array(
                    'embed'     => $embed_link,
                    'download'  => $download_link,
                    'original'  => $original_link,
                    'alternative' => $alternative_link,
                    'embed_code' => $embed_iframe
                );
            $dt['subtitles'] = $video->get_subtitles($row['id']);
            $dt['name']     = strip_tags($row['name']);
            $dt['added']    = !empty($row['added']) ? date('M d, Y H:i', $row['added']) : '';
            $dt['updated']  = !empty($row['updated']) ? date('M d, Y H:i', $row['updated']) : '';
            $data[]         = $dt;
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
} catch (Exception $e) {
    error_log($e->getMessage());
}

$recordsTotal = (int) $db->query("SELECT COUNT(id) FROM tb_videos")->fetchColumn();
$recordsFiltered = (int) $db->query("SELECT COUNT(v.id) FROM tb_videos v JOIN tb_users u ON u.id = v.uid $where")->fetchColumn();
echo json_encode([
    'draw' => $draw,
    'data' => $data,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered
]);
