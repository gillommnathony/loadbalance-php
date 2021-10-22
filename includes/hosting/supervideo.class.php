<?php
class supervideo
{
    public $name = 'SuperVideo';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://supervideo.tv/e/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '']);
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/supervideo~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: supervideo.tv',
                'origin: https://supervideo.tv'
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
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                if ($dom) {
                    $source = '';
                    $scripts = $dom->find("script");
                    foreach ($scripts as $sc) {
                        if (strpos($sc->innertext, 'eval(function(p,a,c,k,e,d)') !== FALSE) {
                            $source = $sc->innertext;
                            break;
                        }
                    }
                    $unpacker = new \JavascriptUnpacker();
                    $decode = $unpacker->unpack($source);

                    $sources = explode("sources:", $decode);
                    $sources = explode("],", end($sources));
                    $sources = str_replace(['file:', 'label:'], ['"file":', '"label":'], $sources[0]) . ']';
                    $json = json_decode($sources, true);
                    if (!empty($json)) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        if (preg_match('/title:"([^"]+)"/', $decode, $title)) {
                            $this->title = trim($title[1]);
                        }
                        if (preg_match('/image:"([^"]+)"/', $decode, $image)) {
                            $this->image = trim($image[1]);
                        }

                        $result = [];
                        foreach ($json as $src) {
                            if (strpos($src['file'], '.m3u8') !== FALSE) {
                                $result[] = [
                                    'file' => $src['file'],
                                    'type' => 'hls',
                                    'label' => 'Original'
                                ];
                            }
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
