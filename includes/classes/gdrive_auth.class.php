<?php
class gdrive_auth
{
    private $id = '';
    private $nextPageToken = '';
    private $parents = [];
    private $email = '';
    private $userLimit = [];
    private $baseUrl = 'https://www.googleapis.com';
    private $ch;

    function __construct($id = '', $email = '')
    {
        global $InstanceCache;

        $host = parse_url($this->baseUrl, PHP_URL_HOST);
        $port = parse_URL($this->baseUrl, PHP_URL_PORT);
        if (empty($port)) {
            $port = parse_url($this->baseUrl, PHP_URL_SCHEME) == 'https' ? 443 : 80;
        }
        $ipv4 = gethostbyname($host);
        $resolveHost = implode(':', array($host, $port, $ipv4));

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_RESOLVE, [$resolveHost]);
        curl_setopt($this->ch, CURLOPT_ENCODING, '');
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
        if (defined('CURLOPT_TCP_FASTOPEN')) {
            curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
        }
        curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);

        $this->set_id($id);
        $this->set_email($email);
        $this->get_access_tokens();

        $userLimit = $InstanceCache->getItemsByTag('user_limit');
        $this->userLimit = array_keys($userLimit);
    }

    function set_id($id = '')
    {
        if (filter_var($id, FILTER_VALIDATE_URL)) {
            $this->id = getDriveId($id);
        } else {
            $this->id = $id;
        }
    }

    function get_id()
    {
        return $this->id;
    }

    function set_title($title = '')
    {
        $this->title = $title;
    }

    function set_parent($parent = [])
    {
        $this->parents[] = $parent;
    }

    function set_email($email = '')
    {
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) $this->email = $email;
    }

    function get_email()
    {
        return $this->email;
    }

    function get_nextPageToken()
    {
        return $this->nextPageToken;
    }

    function get_accounts()
    {
        global $db;

        try {
            $list = $db->prepare("SELECT `email`, `api_key`, `client_id`, `client_secret`, `refresh_token` FROM `tb_gdrive_auth` WHERE `status`=1 ORDER BY `email` ASC");
            $list->execute();
            $data = [];
            while ($rows = $list->fetch(\PDO::FETCH_ASSOC)) {
                $data[] = array(
                    'email' => $rows['email'],
                    'api_key' => $rows['api_key'],
                    'client_id' => $rows['client_id'],
                    'client_secret' => $rows['client_secret'],
                    'refresh_token' => $rows['refresh_token'],
                );
            }
            return $data;
        } catch (\PDOException $e) {
            error_log('get_accounts => ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('get_accounts => ' . $e->getMessage());
        }
        return FALSE;
    }

    function get_account()
    {
        global $db;
        try {
            if (!empty($this->email)) {
                $list = $db->prepare("SELECT `email`, `api_key`, `client_id`, `client_secret`, `refresh_token` FROM `tb_gdrive_auth` WHERE `email`=?");
                $list->execute(array($this->email));
                return $list->fetch(\PDO::FETCH_ASSOC);
            }
        } catch (\PDOException $e) {
            error_log('get_accounts => ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('get_accounts => ' . $e->getMessage());
        }
        return FALSE;
    }

    function get_access_tokens()
    {
        global $InstanceCache;

        try {
            $tokens = [];
            $notFound = [];
            $accounts = $this->get_accounts();
            if ($accounts) {
                foreach ($accounts as $userData) {
                    $cacheToken = $InstanceCache->getItem('gdrive_access_token-' . md5($userData['email']));
                    if (!$cacheToken->isHit()) {
                        $notFound[] = $userData;
                    } else {
                        $tokens[] = $cacheToken->get();
                    }
                }
                if (!empty($notFound)) {
                    $mh = curl_multi_init();
                    $ch = [];

                    foreach ($notFound as $i => $user) {
                        unset($user['email']);
                        unset($user['api_key']);
                        $user['grant_type'] = 'refresh_token';

                        $ch[$i] = curl_init($this->baseUrl .'/oauth2/v4/token');
                        curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch[$i], CURLOPT_ENCODING, '');
                        curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($ch[$i], CURLOPT_MAXREDIRS, 2);
                        curl_setopt($ch[$i], CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                        curl_setopt($ch[$i], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                        curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                        if (defined('CURLOPT_TCP_FASTOPEN')) {
                            curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                        }
                        curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                        curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                        curl_setopt($ch[$i], CURLOPT_POST, 1);
                        curl_setopt($ch[$i], CURLOPT_POSTFIELDS, http_build_query($user));
                        curl_multi_add_handle($mh, $ch[$i]);
                    }

                    $active = null;
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

                    while ($active && $mrc == CURLM_OK) {
                        if (curl_multi_select($mh) == -1) {
                            usleep(10);
                        }
                        do {
                            $mrc = curl_multi_exec($mh, $active);
                        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                    }

                    foreach ($notFound as $i => $user) {
                        $response = curl_multi_getcontent($ch[$i]);
                        $err = curl_error($ch[$i]);
                        if (!$err) {
                            $result = json_decode($response, TRUE);
                            $cacheToken = $InstanceCache->getItem('gdrive_access_token-' . md5($user['email']));
                            if (isset($result['access_token'])) {
                                $cacheToken->set($result)->expiresAfter(3500);
                                $cacheToken->addTag('gdrive_access_token');
                                $InstanceCache->save($cacheToken);
                                $tokens[] = $result;
                            } else {
                                if (!empty($result['error']['message'])) error_log('get_access_tokens ' . $user['email'] . ' => ' . $result['error']['message']);
                                else error_log('get_access_tokens ' . $user['email'] . ' => ' . $response);
                            }
                        } else {
                            error_log('get_access_tokens ' . $user['email'] . ' => ' . $err);
                        }
                        curl_multi_remove_handle($mh, $ch[$i]);
                    }
                    curl_multi_close($mh);
                }
                return $tokens;
            }
        } catch (\PDOException $e) {
            error_log('get_access_tokens => ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('get_access_tokens => ' . $e->getMessage());
        }
        return FALSE;
    }

    function get_access_token()
    {
        global $InstanceCache;

        try {
            if (!empty($this->email)) {
                $userData = $this->get_account($this->email);
                $cacheToken = $InstanceCache->getItem('gdrive_access_token-' . md5($this->email));
                if (!$cacheToken->isHit() && $userData) {
                    unset($userData['email']);
                    unset($userData['api_key']);
                    $userData['grant_type'] = 'refresh_token';

                    curl_setopt($this->ch, CURLOPT_URL, $this->baseUrl .'/oauth2/v4/token');
                    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($userData));
                    session_write_close();
                    $response = curl_exec($this->ch);
                    $err = curl_error($this->ch);

                    if (!$err) {
                        $result = json_decode($response, TRUE);
                        if (!isset($result['error'])) {
                            $cacheToken->set($result)->expiresAfter(3500);
                            $cacheToken->addTag('gdrive_access_token');
                            $InstanceCache->save($cacheToken);
                            return $cacheToken->get();
                        } else {
                            if (!empty($result['error']['message'])) error_log('get_access_token ' . $this->email . ' => ' . $result['error']['message']);
                            else error_log('get_access_token ' . $this->email . ' => ' . $result['error']);
                        }
                    } else {
                        error_log('get_access_token ' . $this->email . ' => ' . $err);
                    }
                } else {
                    return $cacheToken->get();
                }
            }
        } catch (\Exception $e) {
            error_log('get_access_token => ' . $e->getMessage());
        }
        return FALSE;
    }

    function get_accounts_info()
    {
        global $InstanceCache;

        $users = $this->get_accounts();
        $tokens = $this->get_access_tokens();
        if ($users && $tokens) {
            $mh = curl_multi_init();
            $ch = [];
            $info = [];

            foreach ($users as $i => $user) {
                $cache = $InstanceCache->getItem('gdrive_about~' . md5($user['email']));
                if (!$cache->isHit()) {
                    $token = $tokens[$i];
                    $ch[$i] = curl_init($this->baseUrl .'/drive/v2/about');
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch[$i], CURLOPT_ENCODING, '');
                    curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch[$i], CURLOPT_MAXREDIRS, 2);
                    curl_setopt($ch[$i], CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch[$i], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                    curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                    if (defined('CURLOPT_TCP_FASTOPEN')) {
                        curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                    }
                    curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                    curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
                        'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                    ));
                    curl_multi_add_handle($mh, $ch[$i]);
                } else {
                    $info[$user['email']] = $cache->get();
                    continue;
                }
            }

            if (!empty($ch)) {
                $active = null;
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);

                while ($active && $mrc == CURLM_OK) {
                    if (curl_multi_select($mh) == -1) {
                        usleep(10);
                    }
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }

                foreach ($users as $i => $user) {
                    $cache = $InstanceCache->getItem('gdrive_about~' . md5($user['email']));
                    if (!$cache->isHit()) {
                        $response = curl_multi_getcontent($ch[$i]);
                        $err = curl_error($ch[$i]);
                        if (!$err) {
                            $result = json_decode($response, TRUE);
                            if (!isset($result['error'])) {
                                $cache->set($result)->expiresAfter(43200); // 1 day
                                $cache->addTag('gdrive_about');
                                $InstanceCache->save($cache);
                                // result
                                $info[$user['email']] = $result;
                            } else {
                                $info[$user['email']] = [];
                                if (!empty($result['error']['message'])) error_log('get_accounts_info ' . $user['email'] . ' => ' . $result['error']['message']);
                                else error_log('get_accounts_info ' . $user['email'] . ' => ' . $result['error']);
                            }
                        } else {
                            $info[$user['email']] = [];
                            error_log('get_accounts_info ' . $user['email'] . ' => ' . $err);
                        }
                        curl_multi_remove_handle($mh, $ch[$i]);
                    } else {
                        $info[$user['email']] = $cache->get();
                    }
                }
            }
            curl_multi_close($mh);

            return $info;
        }
    }

    function get_account_info()
    {
        global $InstanceCache;

        try {
            if (!empty($this->email)) {
                $token = $this->get_access_token();
                if ($token) {
                    $cache = $InstanceCache->getItem('gdrive_about~' . md5($this->email));
                    if (!$cache->isHit()) {
                        curl_setopt($this->ch, CURLOPT_URL, $this->baseUrl .'/drive/v2/about');
                        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
                        curl_setopt($this->ch, CURLOPT_POST, 0);
                        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                            'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                        ));

                        session_write_close();
                        $response = curl_exec($this->ch);
                        $err = curl_error($this->ch);

                        if (!$err) {
                            $result = json_decode($response, TRUE);
                            if (!isset($result['error'])) {
                                $cache->set($result)->expiresAfter(43200); //1 day
                                $cache->addTag('gdrive_about');
                                $InstanceCache->save($cache);
                                return $result;
                            }
                        }
                    } else {
                        return $cache->get();
                    }
                }
            }
        } catch (\Exception $e) {
            error_log('get_account_info => ' . $e->getMessage());
        }
        return FALSE;
    }

    function delete_file()
    {
        global $db;

        if (!empty($this->email)) {
            $token = $this->get_access_token();
            if ($token) {
                curl_setopt($this->ch, CURLOPT_URL, $this->baseUrl .'/drive/v2/files/' . $this->id);
                curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($this->ch, CURLOPT_POST, 0);
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                ));

                session_write_close();
                $response   = curl_exec($this->ch);
                $err        = curl_error($this->ch);

                if (!$err) {
                    if (empty($response)) {
                        $delete = $db->prepare("DELETE FROM `tb_gdrive_mirrors` WHERE `gdrive_id` = ? OR `mirror_id` = ?");
                        $delete->execute(array($this->id, $this->id));
                        return TRUE;
                    } else {
                        $result = json_decode($response, TRUE);
                        error_log('delete_file ' . $this->id . ' => ' . $result['error']['message']);
                    }
                } else {
                    error_log('delete_file ' . $this->id . ' => ' . $err);
                }
            }
        }
        return FALSE;
    }

    function delete_files($gdrive_ids = [])
    {
        global $db;

        if (!empty($gdrive_ids) && !empty($this->email)) {
            $token = $this->get_access_token();
            if ($token) {
                $mh = curl_multi_init();
                $ch = [];

                foreach ($gdrive_ids as $i => $id) {
                    $ch[$i] = curl_init($this->baseUrl .'/drive/v2/files/' . $id);
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch[$i], CURLOPT_ENCODING, '');
                    curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch[$i], CURLOPT_MAXREDIRS, 2);
                    curl_setopt($ch[$i], CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch[$i], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                    curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                    if (defined('CURLOPT_TCP_FASTOPEN')) {
                        curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                    }
                    curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                    curl_setopt($ch[$i], CURLOPT_POST, 0);
                    curl_setopt($ch[$i], CURLOPT_CUSTOMREQUEST, 'DELETE');
                    curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
                        'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                    ));
                    curl_multi_add_handle($mh, $ch[$i]);
                }

                $active = null;
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);

                while ($active && $mrc == CURLM_OK) {
                    if (curl_multi_select($mh) == -1) {
                        usleep(10);
                    }
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }

                $deleted = [];
                foreach ($gdrive_ids as $i => $id) {
                    $response = curl_multi_getcontent($ch[$i]);
                    $err = curl_error($ch[$i]);
                    if (!$err) {
                        $result = json_decode($response, TRUE);
                        if (!isset($result['error'])) {
                            $delete = $db->prepare("DELETE FROM `tb_gdrive_mirrors` WHERE `gdrive_id` = ? OR `mirror_id` = ?");
                            $delete->execute(array($id, $id));
                            $deleted[] = [
                                'id' => $id,
                                'status' => 'ok'
                            ];
                        } else {
                            $deleted[] = [
                                'id' => $id,
                                'status' => 'fail',
                                'message' => $result['error']['message']
                            ];
                            error_log('delete_files ' . $id . ' => ' . $result['error']['message']);
                        }
                    } else {
                        $deleted[] = [
                            'id' => $id,
                            'status' => 'fail',
                            'message' => $err
                        ];
                        error_log('delete_files ' . $id . ' => ' . $err);
                    }
                    curl_multi_remove_handle($mh, $ch[$i]);
                }
                curl_multi_close($mh);
                return $deleted;
            }
        }
        return FALSE;
    }

    function get_file_info()
    {
        if (!empty($this->id)) {
            if (!empty($this->email)) {
                $token = $this->get_access_token();
            } else {
                $users = $this->get_accounts();
                $key = array_rand($users);
                $user = $users[$key];
                $this->email = $user['email'];
                $token = $this->get_access_token();
            }
            if ($token) {
                curl_setopt($this->ch, CURLOPT_URL, $this->baseUrl .'/drive/v2/files/' . $this->id);
                curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($this->ch, CURLOPT_POST, 0);
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                ));

                session_write_close();
                $response   = curl_exec($this->ch);
                $err        = curl_error($this->ch);

                if (!$err) {
                    $result = json_decode($response, TRUE);
                    if (!isset($result['error'])) {
                        return $result;
                    } else {
                        error_log('get_file_info ' . $this->id . ' => ' . $result['error']['message']);
                    }
                } else {
                    error_log('get_file_info ' . $this->id . ' => ' . $err);
                }
            }
        }
        return FALSE;
    }

    function get_files($title = '', $maxResult = 25, $orderBy = 'modifiedDate desc', $pageToken = '', $private = false)
    {
        $query = [
            'spaces'    => 'drive',
            'orderBy'   => $orderBy,
            'maxResults' => $maxResult,
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
        ];
        if (!empty($title)) {
            $title = addslashes(htmlspecialchars_decode($title));
            $titleEncode = htmlspecialchars($title);
            $encTitle = md5($title);
            $query['q'] = "(title='$title' or title='$titleEncode' or title='$encTitle' or title contains '$title' or title contains '$titleEncode' or title contains '$encTitle' or fullText contains '$title' or fullText contains '$titleEncode') and (mimeType contains 'video/' or mimeType contains '/octet-stream')";
        } else {
            $query['q'] = "(mimeType contains 'video/' or mimeType contains '/octet-stream')";
        }
        if ($private) {
            $query['q'] .= " and (visibility = 'limited')";
        }
        if (!empty($pageToken)) {
            $query['pageToken'] = $pageToken;
        }
        $url = $this->baseUrl .'/drive/v2/files?' . http_build_query($query);

        if (!empty($this->email)) {
            $token = $this->get_access_token();
            if ($token) {
                curl_setopt($this->ch, CURLOPT_URL, $url);
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                ));
                session_write_close();
                $response   = curl_exec($this->ch);
                $err        = curl_error($this->ch);
                if (!$err) {
                    $result = json_decode($response, TRUE);
                    if (!isset($result['error'])) {
                        $this->nextPageToken = !empty($result['nextPageToken']) ? $result['nextPageToken'] : '';
                        return $result['items'];
                    } else {
                        error_log('get_files ' . $this->email . ' => ' . $result['error']['message'] . ' => ' . $url);
                    }
                } else {
                    error_log('get_files ' . $this->email . ' => ' . $err . ' => ' . $url);
                }
            }
        }
        return FALSE;
    }

    function get_files_all_accounts($title = '', $maxResult = 25, $orderBy = 'modifiedDate desc', $pageToken = '', $private = false)
    {
        $query = [
            'spaces'    => 'drive',
            'orderBy'   => $orderBy,
            'maxResults' => $maxResult,
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
        ];
        if (!empty($title)) {
            $title = addslashes(htmlspecialchars_decode($title));
            $titleEncode = htmlspecialchars($title);
            $encTitle = md5($title);
            $query['q'] = "(title='$title' or title='$titleEncode' or title='$encTitle' or title contains '$title' or title contains '$titleEncode' or title contains '$encTitle' or fullText contains '$title' or fullText contains '$titleEncode') and (mimeType contains 'video/' or mimeType contains '/octet-stream')";
        } else {
            $query['q'] = "(mimeType contains 'video/' or mimeType contains '/octet-stream')";
        }
        if ($private) {
            $query['q'] .= " and (visibility = 'limited')";
        }
        if (!empty($pageToken)) {
            $query['pageToken'] = $pageToken;
        }
        $url = $this->baseUrl .'/drive/v2/files?' . http_build_query($query);

        $tokens = $this->get_access_tokens();
        if ($tokens) {
            $mh = curl_multi_init();
            $ch = [];
            foreach ($tokens as $i => $token) {
                $ch[$i] = curl_init($url);
                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch[$i], CURLOPT_ENCODING, '');
                curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 0);
                if (defined('CURLOPT_TCP_FASTOPEN')) {
                    curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                }
                curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
                    'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                ));
                curl_multi_add_handle($mh, $ch[$i]);
            }

            $active = null;
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($mh) == -1) {
                    usleep(10);
                }
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }

            $result = [];
            $users = $this->get_accounts();
            foreach ($tokens as $i => $token) {
                $response = curl_multi_getcontent($ch[$i]);
                $err = curl_error($ch[$i]);
                if (!$err) {
                    $arr = json_decode($response, TRUE);
                    if (isset($arr['items'])) {
                        foreach ($arr['items'] as $file) {
                            $result[] = $file;
                        }
                    } else {
                        if (!empty($result['error'])) error_log('get_files_all_accounts ' . $users[$i]['email'] . ' => ' . $result['error']['message'] . ' => ' . $url);
                        else error_log('get_files_all_accounts ' . $users[$i]['email'] . ' => ' . $err . ' => ' . $url);
                    }
                } else {
                    error_log('get_files_all_accounts ' . $users[$i]['email'] . ' => ' . $err);
                }
                curl_multi_remove_handle($mh, $ch[$i]);
            }
            curl_multi_close($mh);

            return $result;
        }
        return FALSE;
    }

    function get_folders($title = '')
    {
        if (!empty($title) && !empty($this->email)) {
            $token = $this->get_access_token();
            if ($token) {
                $title = addslashes(htmlspecialchars_decode($title));
                $titleEncode = htmlspecialchars($title);
                $encTitle = md5($title);
                $query = [
                    'q' => "(title='$title' or title='$titleEncode' or title='$encTitle' or title contains '$title' or title contains '$titleEncode' or title contains '$encTitle' or fullText contains '$title' or fullText contains '$titleEncode') and mimeType='application/vnd.google-apps.folder'",
                    'supportsAllDrives' => 'true',
                    'includeItemsFromAllDrives' => 'true'
                ];
                $url = $this->baseUrl .'/drive/v2/files?' . http_build_query($query);

                curl_setopt($this->ch, CURLOPT_URL, $url);
                curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($this->ch, CURLOPT_POST, 0);
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                ));

                session_write_close();
                $response   = curl_exec($this->ch);
                $err        = curl_error($this->ch);

                if (!$err) {
                    $result = json_decode($response, TRUE);
                    if (!isset($result['error'])) {
                        return $result['items'];
                    } else {
                        error_log('get_folders ' . $this->email . ' => ' . $result['error']['message'] . ' => ' . $url);
                    }
                } else {
                    error_log('get_folders ' . $this->email . ' => ' . $err . ' => ' . $url);
                }
            }
        }
        return FALSE;
    }

    function insert_permissions($permission = [])
    {
        if (!empty($this->id) && !empty($this->email)) {
            $token = $this->get_access_token();
            if ($token) {
                if (empty($permission)) {
                    $permission = [
                        'role' => 'reader',
                        'type' => 'anyone'
                    ];
                }

                curl_setopt($this->ch, CURLOPT_URL, $this->baseUrl .'/drive/v2/files/' . $this->id . '/permissions');
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($permission));
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                    'accept: application/json',
                    'Content-Type: application/json'
                ));

                session_write_close();
                $response   = curl_exec($this->ch);
                $err        = curl_error($this->ch);

                if (!$err) {
                    $result = json_decode($response, TRUE);
                    if (!isset($result['error'])) {
                        return $result;
                    } else {
                        error_log('insert_permissions ' . $this->id . ' => ' . $result['error']['message']);
                    }
                } else {
                    error_log('insert_permissions ' . $this->id . ' => ' . $err);
                }
            }
        }
        return FALSE;
    }

    function delete_permissions($id = 'anyone')
    {
        if (!empty($this->id) && !empty($this->email) && !empty($id)) {
            $token = $this->get_access_token();
            if ($token) {
                curl_setopt($this->ch, CURLOPT_URL, $this->baseUrl .'/drive/v2/files/' . $this->id . '/permissions/' . $id);
                curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($this->ch, CURLOPT_POST, 0);
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                ));

                session_write_close();
                $response   = curl_exec($this->ch);
                $err        = curl_error($this->ch);

                if (!$err) {
                    $result = json_decode($response, TRUE);
                    if (!isset($result['error'])) {
                        return TRUE;
                    } else {
                        error_log('delete_permissions ' . $this->id . ' => ' . $result['error']['message']);
                    }
                } else {
                    error_log('delete_permissions ' . $this->id . ' => ' . $err);
                }
            }
        }
        return FALSE;
    }

    function copy_files($single = true)
    {
        global $InstanceCache, $db;

        if (!empty($this->id)) {
            $search = $db->prepare('SELECT mirror_id, mirror_email FROM tb_gdrive_mirrors WHERE gdrive_id=? ORDER BY added ASC');
            $search->execute([$this->id]);
            $rows = $search->fetchAll(\PDO::FETCH_ASSOC);
            if ($rows) {
                if ($single) {
                    $key = array_rand($rows);
                    $data = $rows[$key];
                    $this->set_id($data['mirror_id']);
                    $this->set_email($data['mirror_email']);
                    return $this->get_file_info();
                } else {
                    $result = [];
                    foreach ($rows as $data) {
                        $this->set_id($data['mirror_id']);
                        $this->set_email($data['mirror_email']);
                        $file = $this->get_file_info();
                        if ($file) $result[] = $file;
                    }
                    return $result;
                }
            } else {
                $info = $this->get_file_info();
                if ($info) {
                    if (!is_word_blacklisted($info['title'])) {
                        $files = $this->get_files_all_accounts($info['title']);
                        if ($files) {
                            try {
                                $sql = $db->prepare("INSERT INTO tb_gdrive_mirrors VALUES(NULL, ?, ?, ?, ?)");
                                foreach ($files as $file) {
                                    $sql->execute(array($this->id, $file['id'], $file['owners'][0]['emailAddress'], time()));
                                }
                            } catch (\PDOException | \Exception $e) {
                                error_log($e->getMessage());
                            }
                            if ($single) {
                                $cf = count($files);
                                if ($cf > 1) {
                                    $key = array_rand($files);
                                    return $files[$key];
                                } else {
                                    return $files[0];
                                }
                            } else {
                                return $files;
                            }
                        } else {
                            $this->email = '';
                            if ($single) {
                                if (!empty($this->email)) {
                                    $token = $this->get_access_token();
                                } else {
                                    $users  = $this->get_accounts();
                                    foreach ($users as $user) {
                                        if (!in_array('user_limit~' . md5($user['email']), $this->userLimit)) {
                                            $this->email = $user['email'];
                                            $token = $this->get_access_token();
                                            break;
                                        } else {
                                            $this->email = '';
                                            $token = FALSE;
                                        }
                                    }
                                }
                                if ($token) {
                                    $data = array(
                                        'parents' => ['root']
                                    );
                                    if (!empty($this->title)) {
                                        $data['title'] = md5($this->title);
                                        $data['description'] = $this->title;
                                    } else {
                                        $data['title'] = md5($info['title']);
                                        $data['description'] = $info['title'];
                                    }
                                    curl_setopt($this->ch, CURLOPT_URL, $this->baseUrl .'/drive/v2/files/' . $this->id . '/copy?supportsAllDrives=true');
                                    curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
                                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
                                    curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                                        'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                                        'accept: application/json',
                                        'Content-Type: application/json'
                                    ));

                                    session_write_close();
                                    $response   = curl_exec($this->ch);
                                    $err        = curl_error($this->ch);

                                    if (!$err) {
                                        $result = json_decode($response, TRUE);
                                        if (!isset($result['error'])) {
                                            $this->set_id($result['id']);
                                            $this->set_email($result['owners'][0]['emailAddress']);
                                            $this->insert_permissions();
                                            return $this->get_file_info();
                                        } else {
                                            error_log('copy_files single ' . $this->id . ' => ' . $this->email . ' => ' . $result['error']['message']);
                                            $key = 'user_limit~' . md5($this->email);
                                            $this->userLimit[] = $key;
                                            $cache = $InstanceCache->getItem($key);
                                            $cache->set($this->get_account())->expiresAfter(60);
                                            $cache->addTag('user_limit');
                                            $InstanceCache->save($cache);
                                            return $this->copy_files(true);
                                        }
                                    } else {
                                        error_log('copy_files single ' . $this->id . ' => ' . $this->email . ' => ' . $err);
                                    }
                                }
                            } else {
                                $tokens = $this->get_access_tokens();
                                if ($tokens) {
                                    $data = array(
                                        'parents' => ['root'],
                                    );
                                    if (!empty($this->title)) {
                                        $data['title'] = md5($this->title);
                                        $data['description'] = $this->title;
                                    } else {
                                        $data['title'] = md5($info['title']);
                                        $data['description'] = $info['title'];
                                    }
                                    $mh = curl_multi_init();
                                    $ch = [];
                                    foreach ($tokens as $i => $token) {
                                        $ch[$i] = curl_init($this->baseUrl .'/drive/v2/files/' . $this->id . '/copy?supportsAllDrives=true');
                                        curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                                        curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                                        curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($ch[$i], CURLOPT_ENCODING, '');
                                        curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 0);
                                        if (defined('CURLOPT_TCP_FASTOPEN')) {
                                            curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                                        }
                                        curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                                        curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                                        curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                                        curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                                        curl_setopt($ch[$i], CURLOPT_CUSTOMREQUEST, 'POST');
                                        curl_setopt($ch[$i], CURLOPT_POSTFIELDS, json_encode($data));
                                        curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
                                            'authorization: ' . $token['token_type'] . ' ' . $token['access_token'],
                                            'accept: application/json',
                                            'Content-Type: application/json'
                                        ));
                                        curl_multi_add_handle($mh, $ch[$i]);
                                    }

                                    $active = null;
                                    do {
                                        $mrc = curl_multi_exec($mh, $active);
                                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

                                    while ($active && $mrc == CURLM_OK) {
                                        if (curl_multi_select($mh) == -1) {
                                            usleep(10);
                                        }
                                        do {
                                            $mrc = curl_multi_exec($mh, $active);
                                        } while ($mrc == CURLM_CALL_MULTI_PERFORM
                                        );
                                    }

                                    $result = [];
                                    foreach ($tokens as $i => $token) {
                                        $response = curl_multi_getcontent($ch[$i]);
                                        $err = curl_error($ch[$i]);
                                        if (!$err) {
                                            $data = json_decode($response, TRUE);
                                            if (!isset($data['error'])) {
                                                $this->set_id($data['id']);
                                                $this->set_email($data['owners'][0]['emailAddress']);
                                                $this->insert_permissions();
                                                $result[] = $this->get_file_info();
                                            } else {
                                                error_log('copy_files multiple ' . $this->id . ' => ' . $this->email . ' => ' . $result['error']['message']);
                                                $key = 'user_limit~' . md5($this->email);
                                                $this->userLimit[] = $key;
                                                $cache = $InstanceCache->getItem($key);
                                                $cache->set($this->get_account())->expiresAfter(60);
                                                $cache->addTag('user_limit');
                                                $InstanceCache->save($cache);
                                            }
                                        } else {
                                            error_log('copy_files multiple ' . $this->id . ' => ' . $err);
                                        }
                                        curl_multi_remove_handle($mh, $ch[$i]);
                                    }
                                    curl_multi_close($mh);

                                    return $result;
                                }
                            }
                        }
                    }
                }
            }
        }
        return FALSE;
    }

    function download_checker()
    {
        if (!empty($this->id)) {
            $url = 'https://drive.google.com/uc?id=' . $this->id . '&export=download';
            $host = parse_url($url, PHP_URL_HOST);
            $port = parse_URL($url, PHP_URL_PORT);
            if (empty($port)) {
                $port = parse_url($url, PHP_URL_SCHEME) == 'https' ? 443 : 80;
            }
            $ipv4 = gethostbyname($host);
            $resolveHost = implode(':', array($host, $port, $ipv4));

            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($this->ch, CURLOPT_RESOLVE, [$resolveHost]);
            curl_setopt($this->ch, CURLOPT_POST, 0);
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');

            session_write_close();
            $response   = curl_exec($this->ch);
            $err        = curl_error($this->ch);
            $location   = curl_getinfo($this->ch, CURLINFO_REDIRECT_URL);

            if (!$err) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $title = !empty($dom->find('.uc-name-size', 0)) ? $dom->find('.uc-name-size', 0)->plaintext : '';
                $status = strpos($response, 'id="uc-download-link"') !== FALSE || strpos($location, 'googleusercontent.com') !== FALSE;
                if ($status) {
                    return [
                        'status' => $status,
                        'title' => $title
                    ];
                } else {
                    error_log('download_checker ' . $this->id . ' => direct link not found');
                }
            } else {
                error_log('download_checker ' . $this->id . ' => ' . $err);
            }
        }
        return FALSE;
    }

    function __destruct()
    {
        curl_close($this->ch);
    }
}
