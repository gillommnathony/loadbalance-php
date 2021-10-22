<?php
class gounlimitedto
{
    public $name = 'GoUnlimited.to';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://gounlimited.to/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '']);

            $this->url .= 'embed-' . $this->id . '.html';
            $host = parse_url($this->url, PHP_URL_HOST);

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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/gounlimitedto~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: ' . $host,
                'origin: https://' . $host,
            ));
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
                $scripts = $dom->find("script");
                if (!empty($scripts)) {
                    $source = '';
                    foreach ($scripts as $script) {
                        if (strpos($script->innertext, 'eval(function(p,a,c,k,e,d)') !== FALSE) {
                            $source = $script->innertext;
                            break;
                        }
                    }

                    $unpacker = new \JavascriptUnpacker();
                    $decode = $unpacker->unpack($source);
                    $decode = strtr($decode, "var player=videojs('vjsplayer',{});player.ready(function(){player.src(", "");
                    $decode = explode(')});', $decode);
                    $decode = strtr($decode[0], ['src:' => '"src":', 'type:' => '"type":', 'label:' => '"label":', 'res:' => '"res":']);
                    $json = json_decode($decode, true);
                    if (!empty($json)) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        $this->image = $dom->find('#vjsplayer', 0)->poster;

                        $result = [];
                        foreach ($json as $src) {
                            $result[] = [
                                'file' => $src['src'],
                                'type' => $src['type'],
                                'label' => $src['label'] . 'p'
                            ];
                        }
                        return $result;
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
