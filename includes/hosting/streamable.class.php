<?php
class streamable
{
    public $name = 'Streamable';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://streamable.com/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];
            $this->url .= 'e/' . strtr($this->id, 'e/', '');

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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/streamable~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: streamable.com',
                'origin: https://streamable.com',
            ));
            // cek penggunaan proxy
            $proxy = proxy_rotator(0, 'streamable');
            if ($proxy) {
                curl_setopt($this->ch, CURLOPT_PROXY, $proxy['proxy']);
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['usrpwd']);
            }
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);

            if (!$err) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $embed = $dom->find('#embed-js', 0);
                if (!empty($embed)) {
                    $json = explode(';', $embed->innertext);
                    $json = json_decode(strtr($json[1], ['var videoObject = ' => '']), FALSE);
                    if (!empty($json->files)) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        $this->title = $json->original_name;
                        $scheme = parse_url($this->url, PHP_URL_SCHEME);
                        $this->image = $scheme . ':' . $json->dynamic_thumbnail_url;

                        $result = [];
                        foreach ($json->files as $value) {
                            if (!empty($value->url)) {
                                $result[] = [
                                    'file' => $scheme . ':' . $value->url,
                                    'type' => 'video/mp4',
                                    'label' => $value->height . 'p'
                                ];
                            }
                        }
                        return $result;
                    }
                }
            } else {
                error_log('streamable => '. $err);
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
