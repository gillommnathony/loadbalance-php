<?php
class gdauth
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

                $get = $this->db->prepare("SELECT * FROM tb_gdrive_auth WHERE id=?");
                $get->execute(array($id));
                return $get->fetch(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Unknown parameter!');
        }
        return FALSE;
    }

    function get_by($field = '', $criteria = '')
    {
        if (!empty($field) && !empty($criteria)) {
            try {
                $this->clear_errors();

                $get = $this->db->prepare("SELECT * FROM tb_gdrive_auth WHERE " . htmlspecialchars($field) . "=?");
                $get->execute(array($criteria));
                return $get->fetch(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Unknown parameter!');
        }
        return FALSE;
    }

    function insert($data = [])
    {
        if (!empty($data)) {
            if (empty($data['email'])) {
                $this->set_error('Email must be filled!');
            }

            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->set_error('Invalid email format!');
            }

            if (empty($data['api_key'])) {
                $this->set_error('API Key must be filled!');
            }

            if (empty($data['client_id'])) {
                $this->set_error('Client ID must be filled!');
            }

            if (empty($data['client_secret'])) {
                $this->set_error('Client Secret must be filled!');
            }

            if (empty($data['refresh_token'])) {
                $this->set_error('Refresh Token must be filled!');
            }

            try {
                $login = new \login();
                $user = $login->cek_login();
                if ($user) {
                    $this->clear_errors();

                    $sql = "INSERT INTO `tb_gdrive_auth` (`email`, `api_key`, `client_id`, `client_secret`, `refresh_token`, `created`, `uid`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = array($data['email'], $data['api_key'], $data['client_id'], $data['client_secret'], $data['refresh_token'], time(), $user['id'], $data['status']);
                    $insert = $this->db->prepare($sql)->execute($params);
                    return $insert;
                } else {
                    $this->set_error('You must log in first!');
                }
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Unknown parameter!');
        }
        return FALSE;
    }

    function update($data = [])
    {
        if (!empty($data)) {
            if (empty($data['email'])) {
                $this->set_error('Email must be filled!');
            }

            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->set_error('Invalid email format!');
            }

            if (empty($data['api_key'])) {
                $this->set_error('API Key must be filled!');
            }

            if (empty($data['client_id'])) {
                $this->set_error('Client ID must be filled!');
            }

            if (empty($data['client_secret'])) {
                $this->set_error('Client Secret must be filled!');
            }

            if (empty($data['refresh_token'])) {
                $this->set_error('Refresh Token must be filled!');
            }

            try {
                $login = new \login();
                $user = $login->cek_login();
                if ($user) {
                    $this->clear_errors();

                    $sql = "UPDATE tb_gdrive_auth SET `email`=?, `api_key`=?, `client_id`=?, `client_secret`=?, `refresh_token`=?, `modified`=?, `uid`=?, `status`=? WHERE `id`=?";
                    $params = array($data['email'], $data['api_key'], $data['client_id'], $data['client_secret'], $data['refresh_token'], time(), $user['id'], $data['status'], $data['id']);
                    $update = $this->db->prepare($sql)->execute($params);
                    return $update;
                } else {
                    $this->set_error('You must log in first!');
                }
            } catch (\PDOException $e) {
                $this->set_error($e->getMessage());
            } catch (\Exception $e) {
                $this->set_error($e->getMessage());
            }
        } else {
            $this->set_error('Unknown parameter!');
        }
        return FALSE;
    }

    function delete($id = '')
    {
        if (!empty($id)) {
            try {
                $this->clear_errors();
                $data = $this->get_by('id', $id);
                if ($data) {
                    $query = $this->db->prepare("DELETE FROM tb_gdrive_auth WHERE id=?");
                    $delete = $query->execute(array($id));
                    if ($delete) {
                        $mirror = $this->db->prepare("DELETE FROM tb_gdrive_mirrors WHERE mirror_email=?");
                        $mirror->execute(array($data['email']));
                        $hasData = (int) $this->db->query("SELECT COUNT(id) FROM tb_gdrive_auth")->fetchColumn();
                        if ($hasData == 0) {
                            $this->db->query("ALTER TABLE `tb_gdrive_auth` AUTO_INCREMENT=1");
                        }
                    }
                    return $delete;
                }
            } catch (\PDOException $e) {
                $this->error = $e->getMessage();
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
            }
        } else {
            $this->set_error('Please enter a valid id!');
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
