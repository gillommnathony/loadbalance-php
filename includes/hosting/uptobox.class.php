<?php
class uptobox
{
    public $name = 'Uptobox';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://uptobox.com/api/link';
    private $ch;
    private $token = '';

    function __construct($id = '')
    {
        $this->id = $id;
        $this->token = get_option('uptobox_api');

        session_write_close();
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if (defined('CURLOPT_TCP_FASTOPEN')) {
            curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
        }
        curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
        curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
    }

    function get_sources()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url . '?token=' . $this->token . '&file_code=' . $this->id);
        $response = curl_exec($this->ch);
        $err = curl_error($this->ch);
        if (!$err) {
            $arr = json_decode($response, true);
            if ($arr['statusCode'] === 0) {
                $this->status = 'ok';
                $this->referer = 'https://uptobox.com/' . $this->id;
                $this->title = basename($arr['data']['dlLink']);
                $result[] = [
                    'file' => $arr['data']['dlLink'],
                    'type' => 'video/mp4',
                    'label' => 'Original'
                ];
                return $result;
            } elseif ($arr['statusCode'] === 16) {
                $url = $this->url . '?token=' . $this->token . '&file_code=' . $this->id . '&waitingToken=' . $arr['data']['waitingToken'];
                curl_setopt($this->ch, CURLOPT_URL, $url);
                session_write_close();
                $response = curl_exec($this->ch);
                $err = curl_error($this->ch);
                if (!$err) {
                    $arr = json_decode($response, true);
                    if ($arr['statusCode'] === 0) {
                        $this->status = 'ok';
                        $this->referer = 'https://uptobox.com/' . $this->id;
                        $this->title = basename($arr['data']['dlLink']);
                        $result[] = [
                            'file' => $arr['data']['dlLink'],
                            'type' => 'video/mp4',
                            'label' => 'Original'
                        ];
                        return $result;
                    } else {
                        error_log('uptobox get_sources => ' . $this->id . ' => ' . $arr['message']);
                    }
                } else {
                    error_log('uptobox get_sources => ' . $this->id . ' => ' . $err);
                }
            }
        } else {
            error_log('uptobox get_sources => ' . $this->id . ' => ' . $err);
        }
        return [];
    }

    function get_token()
    {
        return $this->token;
    }

    function get_status()
    {
        return $this->status;
    }

    function get_title()
    {
        return $this->title;
    }

    function get_image()
    {
        return $this->image;
    }

    function get_referer()
    {
        return $this->referer;
    }

    function get_id()
    {
        return $this->id;
    }

    function __destruct()
    {
        curl_close($this->ch);
    }
}
