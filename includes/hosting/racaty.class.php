<?php
class racaty
{
    public $name = 'Racaty';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://racaty.net/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];
            $this->url .= $this->id . '.html';

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
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, 'op=download2&id=' . $this->id . '&rand=&referer=&method_free=&method_premium=');
            curl_setopt($this->ch, CURLOPT_REFERER, $this->url);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/racaty~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: racaty.net',
                'origin: https://racaty.net',
            ));
        }
    }

    private function embedSources()
    {
        $dom = \KubAT\PhpSimple\HtmlDomParser::file_get_html('https://racaty.net/embed-' . $this->id . '.html');
        if ($dom) {
            $vjs = $dom->find('#vjsplayer');
            if ($vjs) {
                $this->status = 'ok';
                $this->image = $vjs[0]->poster;

                $result[] = [
                    'file' => $vjs[0]->find('source', 0)->src,
                    'type' => 'video/mp4',
                    'label' => 'Original'
                ];
                return $result;
            } else {
                $player = $dom->find('#player_code', 0);
                if (!empty($player)) {
                    $eval = $player->find('script', 0)->innertext;
                    if (strpos($eval, 'eval(function(p,a,c,k,e,d)') !== FALSE) {
                        $unpacker = new \JavascriptUnpacker();
                        $decode = $unpacker->unpack($eval);

                        $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($decode);
                        $embed = $dom->find('#np_vid', 0);
                        if (!empty($embed)) {
                            $this->status = 'ok';
                            $this->image = $dom->find('param[name="previewImage"]', 0)->value;

                            $result[] = [
                                'file' => $embed->src,
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

    function get_sources()
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $dl = $dom->find('#uniqueExpirylink');
                if ($dl) {
                    $this->status = 'ok';
                    $this->referer = $this->url;
                    $this->title = trim($dom->find('.name', 0)->find('strong', 0)->plaintext);

                    $result[] = [
                        'file' => $dl[0]->href,
                        'type' => 'video/mp4',
                        'label' => 'Original'
                    ];
                    return $result;
                } else {
                    return $this->embedSources();
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
