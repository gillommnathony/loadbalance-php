<?php
class vupto
{
    public $name = 'VUP';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://vupload.com/e/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], array('/v/' => '', '/e/' => '', '.html' => ''));
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/vupto~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: vupload.com',
                'origin: https://vupload.com',
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
                    $script = '';
                    foreach ($dom->find('script') as $sc) {
                        if (strpos($sc->innertext, 'eval(') !== FALSE) {
                            $script = $sc->innertext;
                            break;
                        }
                    }
                    if (!empty($script)) {
                        $unpacker = new \JavascriptUnpacker();
                        $data = $unpacker->unpack($script);
                        preg_match_all('/src:"([^"]+)"/', $data, $src);
                        if (!empty($src[1]) && !empty($src[1][1])) {
                            $this->status = 'ok';
                            $this->referer = $this->url;
                            $this->title = preg_match('/title: "([^"]+)"/', $data, $title) ? $title[1] : '';
                            $this->image = preg_match('/poster: "([^"]+)"/', $data, $poster) ? $poster[1] : '';

                            $result[] = [
                                'file' => $src[1][1],
                                'type' => 'hls',
                                'label' => 'Playlist'
                            ];
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
