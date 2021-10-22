<?php
class viu
{
    public $name = 'VIU';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = '';
    private $ch;

    function __construct($id = '')
    {
        $id = explode('?', $id);
        $this->url = $id[0];
        $id = explode('-', $id[0]);
        $this->id = end($id);
        $host = parse_url($this->url, PHP_URL_HOST);

        session_write_close();
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        if (defined('CURLOPT_TCP_FASTOPEN')) {
            curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
        }
        curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
        curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/viu~' . $this->id . '.txt');
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'host: ' . $host,
            'origin: https://' . $host,
        ));
        // cek penggunaan proxy
        $proxy = proxy_rotator(0, 'viu');
        if ($proxy) {
            curl_setopt($this->ch, CURLOPT_PROXY, $proxy['proxy']);
            curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
            curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['usrpwd']);
        }
    }

    function get_sources()
    {
        session_write_close();
        $response = curl_exec($this->ch);
        $err = curl_error($this->ch);

        if (!$err) {
            $drm = preg_match('/"drm_content_url":"([^"]+)"/', $response, $url) ? $url[1] : '';
            $iid = preg_match('/"iid":"([^"]+)"/', $response, $id) ? $id[1] : '';
            $session_id = preg_match('/"sessionId":"([^"]+)"/', $response, $session) ? $session[1] : '';
            if(empty($session_id)){
                $session_id = preg_match('/"session_id":"([^"]+)"/', $response, $session) ? $session[1] : '';
            }
            $ccode = preg_match('/"ccode":"([^"]+)"/', $response, $cc) ? $cc[1] : '';

            $this->referer = $this->url;
            $this->title = preg_match('/"display_title":"([^"]+)"/', $response, $title) ? $title[1] : '';
            $this->image = preg_match('/"thumbnailUrl":\["([^"]+)"\]/', $response, $img) ? $img[1] : '';

            if (!empty($drm) && !empty($ccode) && !empty($iid) && !empty($session_id)) {
                curl_setopt($this->ch, CURLOPT_URL, "https://um.viuapi.io/user/identity?ver=1.0&fmt=json&aver=5.0&appver=2.0&appid=viu_desktop&platform=desktop&iid=$iid");
                curl_setopt($this->ch, CURLOPT_POST, 1);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode(array(
                    'deviceId' => $iid
                )));
                curl_setopt($this->ch, CURLOPT_HEADER, 0);
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'accept: application/json',
                    'content-type: application/json',
                    'host: um.viuapi.io',
                    'origin: https://viu.com',
                    'referer: https://viu.com/',
                    'x-client: browser',
                    'x-session-id: ' . $session_id
                ));

                session_write_close();
                $response = curl_exec($this->ch);
                $err = curl_error($this->ch);

                if (!$err) {
                    $data = @json_decode($response, TRUE);
                    if (!empty($data['token'])) {
                        $url = $drm . $this->id;

                        curl_setopt($this->ch, CURLOPT_URL, $url);
                        curl_setopt($this->ch, CURLOPT_POST, 1);
                        curl_setopt($this->ch, CURLOPT_POSTFIELDS, '');
                        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                            'authorization: ' . $data['token'],
                            'origin: https://viu.com',
                            'referer: https://viu.com/',
                            'x-client: browser',
                            'x-session-id: ' . $session_id,
                            'ccode: ' . $ccode,
                            'actiontype: s',
                            'content-length: 0',
                            'drm_level: l3',
                            'hdcp_level: none',
                        ));

                        session_write_close();
                        $response = curl_exec($this->ch);
                        $err = curl_error($this->ch);

                        if (!$err) {
                            $data = @json_decode($response, TRUE);
                            if (!empty($data['playUrl'])) {
                                $this->status = 'ok';
                                $result[] = [
                                    'file' => $data['playUrl'],
                                    'type' => 'hls',
                                    'label' => 'Playlist'
                                ];
                                return $result;
                            }
                        }
                    } else {
                        error_log('viu => token not found => ' . $response);
                    }
                }
            } else {
                error_log('viu => iid = ' . $iid . ', session_id = ' . $session_id);
            }
        } else {
            error_log('viu => ' . $err);
        }
        return [];
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
