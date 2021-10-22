<?php
class rumble
{
    public $name = 'Rumble';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://rumble.com/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];

            $this->url .= $this->id;

            session_write_close();
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
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
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/rumble~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: rumble.com',
                'origin: https://rumble.com',
            ));
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                if (preg_match('/"video":"([^"]+)"/', $response, $vid) || preg_match('/"id":"([^"]+)"/', $response, $vid)) {
                    $v = strtr($vid[1], 'vid_', '');
                    $ext = '%7B%22ad_count%22%3Anull%7D';
                    curl_setopt($this->ch, CURLOPT_URL, "https://rumble.com/embedJS/u3/?request=video&v=$v&ext=$ext");
                    curl_setopt($this->ch, CURLOPT_REFERER, $this->url);
                    curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                        'host: rumble.com',
                        'origin: https://rumble.com',
                    ));
                    session_write_close();
                    $response = curl_exec($this->ch);
                    $err = curl_error($this->ch);
                    if (!$err) {
                        $data = json_decode($response, true);
                        if (!empty($data['ua'])) {
                            $this->status = 'ok';
                            $this->referer = $this->url;
                            $this->title = $data['title'];
                            $this->image = $data['i'];

                            $result = [];
                            foreach ($data['ua'] as $key => $val) {
                                $result[] = [
                                    'file' => $val[0],
                                    'type' => 'video/mp4',
                                    'label' => $key . 'p'
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
