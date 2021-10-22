<?php
class load_balancers
{
    private $db;
    private $error = [];

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function update($data = [])
    {
        if (!empty($data)) {
            // validasi link
            if (empty($data['link'])) {
                $this->set_error('Link must be filled!');
            }
            if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
                $this->set_error('Incorrect link format!');
            }

            // validasi name
            if (empty($data['name'])) {
                $this->set_error('Name must be filled!');
            }

            // validasi id
            if (empty($data['name'])) {
                $this->set_error('ID must be filled!');
            }

            try {
                $this->clear_errors();
                // simpan data
                $sql = "UPDATE tb_loadbalancers SET `name`=?, `link`=?, `status`=?, `public`=?, `updated`=? WHERE `id`=?";
                $prepare = array($data['name'], rtrim($data['link'], '/') . '/', $data['status'], $data['public'], time(), $data['id']);
                $update = $this->db->prepare($sql)->execute($prepare);
                return $update;
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

    function get($id = '')
    {
        if (!empty($id)) {
            try {
                $this->clear_errors();

                $get = $this->db->prepare("SELECT * FROM tb_loadbalancers WHERE id=?");
                $get->execute(array(intval($id)));
                return $get->fetch(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Enter a valid load balancer id!');
        }
        return FALSE;
    }

    function insert($data = [])
    {
        if (!empty($data)) {
            // validasi link
            if (empty($data['link'])) {
                $this->set_error('Link must be filled!');
            }
            if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
                $this->set_error('Incorrect link format!');
            }

            // validasi name
            if (empty($data['name'])) {
                $this->set_error('Name must be filled!');
            }

            try {
                $this->clear_errors();
                // simpan data
                $sql = "INSERT INTO tb_loadbalancers VALUES (NULL, ?, ?, ?, ?, ?, NULL)";
                $prepare = array($data['name'], rtrim($data['link'], '/') . '/', $data['status'], $data['public'], time());
                $this->db->prepare($sql)->execute($prepare);
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

    function delete($id = '')
    {
        if (!empty($id)) {
            try {
                $this->clear_errors();

                $prepare = $this->db->prepare("DELETE FROM tb_loadbalancers WHERE id=?");
                $delete = $prepare->execute(array(intval($id)));
                if($delete){
                    $hasData = (int) $this->db->query("SELECT COUNT(id) FROM tb_loadbalancers")->fetchColumn();
                    if ($hasData == 0) {
                        $this->db->query("ALTER TABLE `tb_loadbalancers` AUTO_INCREMENT=1");
                    }
                }
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
