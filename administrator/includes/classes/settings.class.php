<?php
class settings
{
    private $db;
    private $error = [];

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function get($key = '')
    {
        if (!empty($key)) {
            try {
                $this->clear_errors();

                $get = $this->db->prepare('SELECT `value` FROM `tb_settings` WHERE `key`=? LIMIT 1');
                $get->execute(array($key));
                $data = $get->fetch(PDO::FETCH_ASSOC);
                if ($data) return $data['value'];
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            try {
                $this->clear_errors();

                $get = $this->db->prepare('SELECT `key`, `value` FROM `tb_settings`');
                $get->execute();
                $data = $get->fetchAll(PDO::FETCH_ASSOC);
                $result = [];
                foreach ($data as $dt) {
                    $result[$dt['key']] = $dt['value'];
                }
                return $result;
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        }
    }

    function insert($key = '', $val = null)
    {
        if (!empty($key) && !empty($val)) {
            try {
                $this->clear_errors();

                $delete = $this->db->prepare("DELETE FROM tb_settings WHERE `key`=?")->execute([$key]);
                if ($delete) {
                    $val = is_array($val) ? json_encode($val, TRUE) : trim($val);
                    $insert = $this->db->prepare("INSERT INTO tb_settings(`key`,`value`,`updated`) VALUES(?, ?, ?)");
                    $result = $insert->execute([$key, $val, time()]);
                    return $result;
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

    function update($data = [])
    {
        if (!empty($data['opt'])) {
            try {
                $this->clear_errors();

                $delete = $this->db->prepare("DELETE FROM tb_settings WHERE id<>''; ALTER TABLE tb_settings AUTO_INCREMENT=1")->execute();
                if ($delete) {
                    foreach ($data['opt'] as $key => $val) {
                        $val = is_array($val) ? json_encode($val, TRUE) : trim($val);
                        $save = $this->db->prepare("INSERT INTO tb_settings VALUES (NULL, ?, ?, ?)");
                        $save->execute(array(trim($key), $val, time()));
                    }
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

    function delete($key = '')
    {
        if (!empty($key)) {
            try {
                $this->clear_errors();

                $delete = $this->db->prepare("DELETE FROM tb_settings WHERE `key` = ?")->execute(array($key));
                return $delete;
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
