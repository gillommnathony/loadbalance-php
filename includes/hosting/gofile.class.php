<?php
class gofile
{
    public $name = 'GoFile';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://gofile/d/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $this->id = $id;

            $this->url .= $this->id;
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_ENCODING, "");
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_REFERER, $this->url);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
        }
    }

    private function parse_sources($response = '')
    {
        if (!empty($response)) {
            $svrinfo = json_decode($response, true);
            if (!empty($svrinfo) && $svrinfo['status'] === "ok") {
                curl_setopt($this->ch, CURLOPT_URL, 'https://' . $svrinfo['data']['server'] . '.gofile.io/getUpload?c=' . $this->id);
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'host: ' . $svrinfo['data']['server'] . '.gofile.io',
                    'origin: https://gofile.io',
                    'x-requested-with: XMLHttpRequest',
                ));
                session_write_close();
                $response = curl_exec($this->ch);
                $err = curl_error($this->ch);

                if (!$err) {
                    $dl = json_decode($response, true);
                    if (!empty($dl) && $dl['status'] === 'ok') {
                        $files = $dl['data']['files'];
                        if (!empty($files)) {
                            $this->status = 'ok';
                            $this->referer = $this->url;

                            $result = [];
                            foreach ($files as $file) {
                                $this->title = $file['name'];
                                $result[] = [
                                    'file'  => $file['link'],
                                    'type'  => 'video/mp4',
                                    'label' => 'Original'
                                ];
                            }
                            return $result;
                        }
                    }
                }
            }
        }
        return [];
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            curl_setopt($this->ch, CURLOPT_URL, 'https://apiv2.gofile.io/getServer?c=' . $this->id);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: apiv2.gofile.io',
                'origin: https://gofile.io',
                'x-requested-with: XMLHttpRequest',
            ));
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            if ($status >= 200 && $status < 400) {
                return $this->parse_sources($response);
            }
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
