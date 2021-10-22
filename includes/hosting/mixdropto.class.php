<?php
class mixdropto
{
    public $name = 'MixDrop';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://mixdrop.co/e/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '', '/f/' => '']);
            $this->url .= $this->id;

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
            curl_setopt($this->ch, CURLOPT_REFERER, strtr($this->url, '/e/', '/f/'));
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/mixdropto~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: mixdrop.co',
                'origin: https://mixdrop.co',
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
                $scripts = $dom->find("script");
                if (!empty($scripts)) {
                    $eval = '';
                    foreach ($scripts as $sc) {
                        if (strpos($sc->innertext, 'eval(function(p,a,c,k,e,d)') !== FALSE && strpos($sc->innertext, 'MDCore.ref') !== FALSE) {
                            $eval = $sc->innertext;
                            break;
                        }
                    }
                    if (!empty($eval)) {
                        $eval = explode('"};', $eval);
                        $eval = end($eval);
                        $unpacker = new \JavascriptUnpacker();
                        $decode = $unpacker->unpack($eval);
                        if (preg_match('/MDCore.wurl="([^"]+)"/', $decode, $video)) {
                            $this->status = 'ok';
                            $this->referer = 'https://mixdrop.co/';

                            if (preg_match('/MDCore.poster="([^"]+)"/', $decode, $poster)) {
                                $this->image = 'https:' . $poster[1];
                            }

                            $result[] = [
                                'file' => 'https:' . $video[1],
                                'type' => 'video/mp4',
                                'label' => 'Original'
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
