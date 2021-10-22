<?php
class users
{
    private $db;
    private $error = [];

    function __construct()
    {
        global $db;
        $this->db = $db;
    }

    function update_profile($data = [])
    {
        $login = new \login();
        $user = $login->cek_login();
        if (isset($data['simpan']) && $user) {
            if (!empty($data['password'])) {
                if ($data['password'] !== $data['retype_password']) {
                    $this->set_error('Retype the new password must be the same as the new password.');
                } else {
                    $password = password_hash($data['password'], PASSWORD_BCRYPT);
                    $sql = "UPDATE tb_users SET name = ?, password = ?, updated = ? WHERE id = ?";
                    $prepare = [$data['name'], $password, time(), $user['id']];
                }
            } else {
                $sql = "UPDATE tb_users SET name = ?, updated = ? WHERE id = ?";
                $prepare = [$data['name'], time(), $user['id']];
            }

            try {
                $this->clear_errors();

                $this->db->prepare($sql)->execute($prepare);
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

    function update($data = [])
    {
        if (isset($data['simpan']) && !empty($data['id'])) {
            // cek duplikat email
            $dupEmail = $this->db->prepare("SELECT id, email FROM tb_users WHERE id <> ? AND email=? LIMIT 1");
            $dupEmail->execute(array($data['id'], $data['email']));
            $emailFound = $dupEmail->fetch(PDO::FETCH_ASSOC);

            // cek duplikat username
            $dupUser = $this->db->prepare("SELECT id, user FROM tb_users WHERE id <> ? AND user=? LIMIT 1");
            $dupUser->execute(array($data['id'], $data['user']));
            $userFound = $dupUser->fetch(PDO::FETCH_ASSOC);

            // validasi email
            if (empty($data['email'])) {
                $this->set_error('Email must be filled!');
            }
            if (!empty($emailFound)) {
                $this->set_error('Email has already been used by another user.');
            }

            // validasi username
            if (empty($data['user'])) {
                $this->set_error('The username must be filled in!');
            }
            if (!empty($userFound)) {
                $this->set_error('The username has already been taken by another user.');
            }

            // validasi password
            if (!empty($data['password']) && $data['password'] !== $data['retype_password']) {
                $this->set_error('Retype the new password must be the same as the new password.');
            }

            // validasi nama
            if (empty($data['name'])) {
                $this->set_error('Name must be filled!');
            }

            // validasi status
            if (is_null($data['status'])) {
                $this->set_error('Status must be selected!');
            }
            if (intval($data['status']) !== 1 && intval($data['id']) === 1) {
                $this->set_error('Super admin can\'t be disabled!');
            }

            // validasi role
            if (is_null($data['role'])) {
                $this->set_error('User roles must be selected!');
            }

            if (!empty($data['password']) && $data['password'] !== $data['retype_password']) {
                $password = password_hash($data['password'], PASSWORD_BCRYPT);
                $sql = "UPDATE tb_users SET email=?, user=?, `name` = ?, `password`=?, updated=?, `status`=?, `role`=? WHERE id = ?";
                $prepare = [$data['email'], $data['user'], $data['name'], $password, time(), $data['status'], $data['role'], $data['id']];
            } else {
                $sql = "UPDATE tb_users SET email=?, user=?, `name`=?, updated=?, `status`=?, `role`=? WHERE id = ?";
                $prepare = [$data['email'], $data['user'], $data['name'], time(), $data['status'], $data['role'], $data['id']];
            }

            try {
                $this->clear_errors();

                // update data user
                $update = $this->db->prepare($sql)->execute($prepare);

                $login = new \login();
                if ($userFound['user'] === $data['user'] || $emailFound['email'] === $data['email']) {
                    $login->logout();
                    create_alert('success', 'Please login again with your new username / email and password!', BASE_URL . 'administrator/');
                } else {
                    return $update;
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

    function insert($data = [])
    {
        if (!empty($data)) {
            // cek duplikat email
            $dupEmail = $this->db->prepare("SELECT id FROM tb_users WHERE email=? LIMIT 1");
            $dupEmail->execute(array($data['email']));
            $emailFound = $dupEmail->fetch(PDO::FETCH_ASSOC);

            // cek duplikat username
            $dupUser = $this->db->prepare("SELECT id FROM tb_users WHERE user=? LIMIT 1");
            $dupUser->execute(array($data['user']));
            $userFound = $dupUser->fetch(PDO::FETCH_ASSOC);

            // validasi email
            if (empty($data['email'])) {
                $this->set_error('Email must be filled!');
            }
            if (!empty($emailFound)) {
                $this->set_error('Email has already been used by another user.');
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->set_error('Incorrect email format!');
            }

            // validasi username
            if (empty($data['user'])) {
                $this->set_error('The username must be filled in!');
            }
            if (!empty($userFound)) {
                $this->set_error('The username has already been taken by another user.');
            }

            // validasi password
            if (empty($data['password'])) {
                $this->set_error('New password must be filled!');
            }
            if (!empty($data['password']) && $data['password'] !== $data['retype_password']) {
                $this->set_error('Retype the new password must be the same as the new password.');
            }

            // validasi nama
            if (empty($data['name'])) {
                $this->set_error('Name must be filled!');
            }

            // validasi status
            if (is_null($data['status'])) {
                $this->set_error('Status must be selected!');
            }

            // validasi role
            if (is_null($data['role'])) {
                $this->set_error('User roles must be selected!');
            }

            try {
                $this->clear_errors();

                // hash password
                $password = password_hash($data['password'], PASSWORD_BCRYPT);

                // simpan data
                $sql = "INSERT INTO `tb_users` (`user`, `email`, `password`, `name`, `status`, `added`, `role`) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $prepare = array($data['user'], $data['email'], $password, $data['name'], $data['status'], time(), $data['role']);
                $insert = $this->db->prepare($sql)->execute($prepare);
                return $insert;
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
                $videos = $this->db->query("SELECT COUNT(*) FROM tb_videos WHERE `uid`=$id");
                if ($videos->fetchColumn() > 0) {
                    $this->set_error('User data cannot be deleted because it still has saved videos.');
                } else {
                    $this->clear_errors();

                    $delete = $this->db->prepare("DELETE FROM tb_users WHERE id=?")->execute(array(intval($id)));
                    if($delete){
                        $hasData = (int) $this->db->query("SELECT COUNT(id) FROM tb_users")->fetchColumn();
                        if ($hasData == 0) {
                            $this->db->query("ALTER TABLE `tb_users` AUTO_INCREMENT=1");
                        }
                    }
                    return $delete;
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

    function get($id = '')
    {
        if (!empty($id)) {
            try {
                $this->clear_errors();

                $get = $this->db->prepare("SELECT id, `name`, user, email, added, updated, `status`, `role` FROM tb_users WHERE id=?");
                $get->execute(array(intval($id)));
                $data = $get->fetch(PDO::FETCH_ASSOC);
                if ($data) return $data;
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

    function check_username($username = '')
    {
        if (!empty($username)) {
            try {
                $user = $this->db->prepare("SELECT id FROM tb_users WHERE user = ?");
                $user->execute([$username]);
                $data = $user->fetchAll(PDO::FETCH_ASSOC);
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

    function check_email($email = '')
    {
        if (!empty($email)) {
            try {
                $user = $this->db->prepare("SELECT id FROM tb_users WHERE email = ?");
                $user->execute([$email]);
                $data = $user->fetchAll(PDO::FETCH_ASSOC);
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
