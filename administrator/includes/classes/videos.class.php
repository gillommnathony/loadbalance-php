<?php
class videos
{
    private $db;
    private $error = [];

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function get($id = '')
    {
        if (!empty($id)) {
            try {
                $this->clear_errors();

                $get = $this->db->prepare("SELECT * FROM tb_videos WHERE id = ?");
                $get->execute(array($id));
                $data = $get->fetch(PDO::FETCH_ASSOC);
                if ($data) {
                    $subs = $this->get_subtitles($id);
                    return array_merge($data, array('subtitle' => $subs));
                }
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Invalid parameter!');
        }
        return [];
    }

    function get_by($field = '', $criteria = '')
    {
        if (!empty($field) && !empty($criteria)) {
            try {
                $this->clear_errors();

                $get = $this->db->prepare("SELECT * FROM tb_videos WHERE $field = ?");
                $get->execute(array($criteria));
                $data = $get->fetch(PDO::FETCH_ASSOC);
                return $data;
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Invalid parameter!');
        }
        return FALSE;
    }

    function get_subtitles($id = '')
    {
        if (!empty($id)) {
            try {
                $this->clear_errors();

                $get = $this->db->prepare("SELECT * FROM tb_subtitles WHERE vid = ?");
                $get->execute(array($id));
                $data = $get->fetchAll(PDO::FETCH_ASSOC);
                if ($data) {
                    $subs = [];
                    foreach ($data as $sub) {
                        $dt = [];
                        $dt['file'] = $sub['link'];
                        $dt['label'] = $sub['language'];
                        $subs[] = $dt;
                    }
                    return $subs;
                }
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Invalid parameter!');
        }
        return [];
    }

    private function update_subtitle_manager($lang = '', $link = '')
    {
        if (!empty($lang) && !empty($link)) {
            try {
                $this->clear_errors();

                $fname = explode('/', $link);
                $fname = end($fname);

                $check = $this->db->prepare('SELECT `file_name` FROM tb_subtitle_manager WHERE `file_name` = ? AND `host` = ?');
                $check->execute([$fname, BASE_URL]);
                $data = $check->fetch(PDO::FETCH_ASSOC);
                if ($data) {
                    $this->db->prepare('UPDATE tb_subtitle_manager SET `language`=? WHERE `file_name` = ?')->execute([$lang, $fname]);
                    return $data;
                }
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Invalid parameter!');
        }
        return FALSE;
    }

    private function saveKey(int $vid = 0)
    {
        if ($vid > 0) {
            try {
                $uuid = new \UUID();
                $key = $uuid->v4();
                $qry = $this->db->prepare("INSERT INTO tb_videos_short VALUES (NULL,?,?)");
                $qry->execute(array($key, $vid));
                return $key;
            } catch (\PDOException | \Exception $e) {
                error_log('saveKey error => ' . $e->getMessage());
            }
        }
        return false;
    }

    function getKey(int $vid = 0)
    {
        if ($vid > 0) {
            try {
                $qry = $this->db->prepare("SELECT `key` FROM tb_videos_short WHERE vid = ?");
                $qry->execute(array($vid));
                $data = $qry->fetch(\PDO::FETCH_ASSOC);
                if ($data) return $data['key'];
            } catch (\PDOException | \Exception $e) {
                error_log('getKey error => ' . $e->getMessage());
            }
        }
        return false;
    }

    function getVideoByKey(string $key = '')
    {
        if (!empty($key)) {
            try {
                $qry = $this->db->prepare("SELECT `vid` FROM tb_videos_short WHERE `key` = ?");
                $qry->execute(array($key));
                $data = $qry->fetch(\PDO::FETCH_ASSOC);
                if ($data) return $data['vid'];
            } catch (\PDOException | \Exception $e) {
                error_log('getVideoByKey error => ' . $e->getMessage());
            }
        }
        return false;
    }

    function insert($data = [])
    {
        global $InstanceCache;

        if (!empty($data)) {
            // validasi judul
            if (empty($data['title'])) {
                $this->set_error('Title must be filled!');
            }

            // validasi id
            if (empty($data['host_id'])) {
                $this->set_error('Main video id must be filled!');
            }

            try {
                $this->clear_errors();

                // ambil id video dari url
                if (!empty($data['host_id'])) {
                    if(filter_var($data['host_id'], FILTER_VALIDATE_URL)){
                        $video = getHostId($data['host_id']);
                        $data['host'] = $video['host'];
                        $data['host_id'] = $video['host_id'];
                    }
                } else {
                    $data['host'] = '';
                    $data['host_id'] = '';
                }

                if (!empty($data['ahost_id'])) {
                    if (filter_var($data['ahost_id'], FILTER_VALIDATE_URL)) {
                        $video = getHostId($data['ahost_id']);
                        $data['ahost'] = $video['host'];
                        $data['ahost_id'] = $video['host_id'];
                    }
                } else {
                    $data['ahost'] = '';
                    $data['ahost_id'] = '';
                }

                $login = new \login();
                $userLogin = $login->cek_login();
                if ($userLogin) {
                    $uid = $userLogin['id'];
                } else {
                    if (!empty($data['user_id'])) {
                        $uid = $data['user_id'];
                    } elseif (!empty(get_option('public_user_id'))) {
                        $uid = get_option('public_user_id');
                    } else {
                        $uid = 1;
                    }
                }

                // simpan data video
                $sql = "INSERT INTO tb_videos VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, 0)";
                $prepare = array($data['title'], strval($data['host']), $data['host_id'], strval($data['ahost']), $data['ahost_id'], $uid, time());
                $sVideo = $this->db->prepare($sql);
                $sVideo->execute($prepare);
                $vid = $this->db->lastInsertId();
                error_log($vid);

                $subtitles = [];
                if (!empty($vid)) {
                    $key = $this->saveKey($vid);
                    if (!$key) {
                        $this->saveKey($vid);
                    }
                    // simpan data subtitle
                    if (!empty($data['subtitle'])) {
                        error_log(json_encode($data['subtitle']));
                        error_log(json_encode($data['language']));
                        $i = 0;
                        $lang = $data['language'];
                        $sql = "INSERT INTO `tb_subtitles` (`language`,`link`,`vid`,`added`,`uid`) VALUES (?, ?, ?, ?, ?)";

                        foreach ($data['subtitle'] as $sub) {
                            if (!empty($lang[$i]) && filter_var($sub, FILTER_VALIDATE_URL)) {
                                $this->update_subtitle_manager($lang[$i], $sub);

                                $save = $this->db->prepare($sql);
                                $save->execute(array($lang[$i], $sub, $vid, time(), $uid));

                                // data berikut akan dimasukkan ke dalam cache
                                $dt = [];
                                $dt['label'] = $lang[$i];
                                $dt['file'] = $sub;
                                if ($i === 0) {
                                    $dt['default'] = true;
                                }
                                $subtitles[] = $dt;
                            }
                            $i++;
                        }
                    }
                }

                // simpan query ke cache
                $qry = [
                    'host' => $data['host'],
                    'id' => $data['host_id'],
                    'ahost' => $data['ahost'],
                    'aid' => $data['ahost_id'],
                    'subs' => $subtitles
                ];

                if (!is_null($InstanceCache)) {
                    $dbCacheString = $InstanceCache->getItem('db~' . $vid);
                    if (!$dbCacheString->isHit()) {
                        $dbCacheString->set($qry)->expiresAfter(43200);
                        $dbCacheString->addTag('source_db');
                        $InstanceCache->save($dbCacheString);
                    }
                }
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Invalid parameter!');
        }
        return FALSE;
    }

    function insert_anonymous($data = [])
    {
        return $this->insert($data);
    }

    function update($data = [])
    {
        global $InstanceCache;

        if (!empty($data)) {
            // validasi judul
            if (empty($data['title'])) {
                $this->set_error('Title must be filled!');
            }

            // validasi id
            if (empty($data['host_id'])) {
                $this->set_error('ID Main video must be filled!');
            }

            try {
                $this->clear_errors();

                // ambil id video dari url
                if (!empty($data['host_id'])) {
                    if (filter_var($data['host_id'], FILTER_VALIDATE_URL)) {
                        $video = getHostId($data['host_id']);
                        $data['host'] = $video['host'];
                        $data['host_id'] = $video['host_id'];
                    }
                } else {
                    $data['host'] = '';
                    $data['host_id'] = '';
                }

                if (!empty($data['ahost_id'])) {
                    if (filter_var($data['ahost_id'], FILTER_VALIDATE_URL)) {
                        $video = getHostId($data['ahost_id']);
                        $data['ahost'] = $video['host'];
                        $data['ahost_id'] = $video['host_id'];
                    }
                } else {
                    $data['ahost'] = '';
                    $data['ahost_id'] = '';
                }

                // simpan data video
                $sql = 'UPDATE `tb_videos` SET `title` = ?, `host` = ?, `host_id` = ?, `ahost` = ?, `ahost_id` = ?, `updated` = ? WHERE `id` = ?';
                $prepare = array($data['title'], strval($data['host']), $data['host_id'], strval($data['ahost']), $data['ahost_id'], time(), intval($data['id']));
                $this->db->prepare($sql)->execute($prepare);

                // cek key
                $key = $this->getKey($data['id']);
                if (!$key) {
                    // create key
                    $key = $this->saveKey($data['id']);
                    if (!$key) {
                        // if error create again
                        $this->saveKey($data['id']);
                    }
                }

                // simpan data subtitle
                $subtitles = [];
                if (!empty($data['subtitle'])) {
                    $login = new \login();
                    $userLogin = $login->cek_login();

                    // ganti dengan subtitle baru
                    $i = 0;
                    $lang = $data['language'];
                    foreach ($data['subtitle'] as $sub) {
                        if (!empty($lang[$i]) && filter_var($sub, FILTER_VALIDATE_URL)) {
                            $this->update_subtitle_manager($lang[$i], $sub);
                            
                            $check = $this->db->prepare("SELECT `id` FROM `tb_subtitles` WHERE `vid` = ? AND `link` = ? LIMIT 1");
                            $check->execute(array($data['id'], $sub));
                            $row = $check->fetch(\PDO::FETCH_ASSOC);
                            if(!$row){
                                $save = $this->db->prepare("INSERT INTO `tb_subtitles` VALUES (NULL, ?, ?, ?, ?, ?)");
                                $save->execute(array($lang[$i], $sub, intval($data['id']), time(), $userLogin['id']));
                            } else {
                                $save = $this->db->prepare("UPDATE `tb_subtitles` SET `language` = ?, `link` = ? WHERE `id` = ?");
                                $save->execute(array($lang[$i], $sub, $row['id']));
                            }
                            // data berikut akan dimasukkan ke dalam cache
                            $dt = [];
                            $dt['label'] = $lang[$i];
                            $dt['file'] = $sub;
                            if ($i === 0) {
                                $dt['default'] = true;
                            }
                            $subtitles[] = $dt;
                        }
                        $i++;
                    }
                } else {
                    $delete = $this->db->prepare("DELETE FROM `tb_subtitles` WHERE `vid` = ?");
                    $delete->execute(array($data['id']));
                }

                // simpan query ke cache
                $qry = [
                    'host' => $data['host'],
                    'id' => $data['host_id'],
                    'ahost' => $data['ahost'],
                    'aid' => $data['ahost_id'],
                    'subs' => $subtitles
                ];
                $dbCacheString = $InstanceCache->getItem('db~' . $data['id']);
                $dbCacheString->set($qry)->expiresAfter(43200); // 24 hours cache
                $dbCacheString->addTag('source_db');
                $InstanceCache->save($dbCacheString);
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Invalid parameter!');
        }
        return FALSE;
    }

    function delete($vid = '')
    {
        if (!empty($vid)) {
            try {
                $login = new \login();
                $userLogin = $login->cek_login();

                if (intval($userLogin['role']) > 0) {
                    $sql = 'DELETE FROM `tb_videos` WHERE `id` = ? AND `uid` = ?';
                    $where = array(intval($vid), intval($userLogin['id']));
                } else {
                    $sql = 'DELETE FROM `tb_videos` WHERE `id` = ?';
                    $where = array(intval($vid));
                }

                $vids = $this->db->prepare($sql);
                $deleted = $vids->execute($where);
                if ($deleted) {
                    $sort = $this->db->prepare("DELETE FROM tb_videos_short WHERE vid=?");
                    $sort->execute(array(intval($vid)));
                    $hasData = (int) $this->db->query("SELECT COUNT(id) FROM tb_videos")->fetchColumn();
                    if($hasData == 0){
                        $this->db->query("ALTER TABLE `tb_videos` AUTO_INCREMENT=1; ALTER TABLE `tb_videos_short` AUTO_INCREMENT=1");
                    }
                }
                return $deleted;
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Invalid parameter!');
        }
        return FALSE;
    }

    private function set_error($msg = '')
    {
        if (!empty($msg)) {
            $this->error[] = $msg;
        }
    }

    private function clear_errors()
    {
        $this->error = [];
    }

    function get_errors()
    {
        return $this->error;
    }
}
