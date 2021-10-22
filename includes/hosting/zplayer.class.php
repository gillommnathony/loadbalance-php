<?php
class zplayer
{
    public $name = 'zPlayer.live';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://v2.zplayer.live/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $this->id = trim(strtr($id, ['/embed/' => '', '/video/' => '']));
            $this->url .= 'embed/' . $this->id;

            session_write_close();
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_ENCODING, "");
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/zplayer~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: v2.zplayer.live',
                'origin: https://v2.zplayer.live'
            ));
        }
    }

    function get_sources($mp4 = false)
    {
        if (!empty($this->id)) {
            if ($mp4) {
                $url = trim(strtr($this->url, ['/embed/' => '/video/']));
                curl_setopt($this->ch, CURLOPT_URL, $url);
                curl_setopt($this->ch, CURLOPT_COOKIEJAR, '');
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'host: v2.zplayer.live',
                    'origin: https://v2.zplayer.live',
                    'referer: ' . $url
                ));
                session_write_close();
                $response = curl_exec($this->ch);
                $err = curl_error($this->ch);
                if (!$err) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    $btns = $dom->find('button.vpn-download');
                    if (!empty($btns)) {
                        $dlink = 'https://v2.zplayer.live/dl?op=download_orig&';
                        $vlinks = [];
                        foreach ($btns as $link) {
                            if (!empty($link->onclick)) {
                                $ex = explode(',', strtr($link->onclick, ['download_video' => '', '(' => '', ')' => '', "'" => '']));
                                $vlinks[] = [
                                    'link' => $dlink . "id={$ex[0]}&mode={$ex[1]}&hash={$ex[2]}",
                                    'mode' => trim(strtr($ex[1], ['l' => 'Low Quality', 'n' => 'Normal Quality', 'h' => 'High Quality'])),
                                    'text' => trim(strtr($link->plaintext, 'Download', ''))
                                ];
                            }
                        }
                        if (!empty($vlinks)) {
                            $this->status = 'ok';
                            $this->referer = 'https://v2.zplayer.live/';
                            $this->title = trim($dom->find('h1.uk-h2', 0)->plaintext);

                            $results = [];
                            foreach ($vlinks as $a) {
                                $dom = \KubAT\PhpSimple\HtmlDomParser::file_get_html($a['link']);
                                $link = $dom->find('a.uk-button-danger');
                                if (!empty($link)) {
                                    $results[] = [
                                        'file' => end($link)->href,
                                        'type' => 'video/mp4',
                                        'label' => $a['text']
                                    ];
                                }
                            }
                            return $results;
                        }
                    }
                }
            } else {
                session_write_close();
                $response = curl_exec($this->ch);
                $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

                if ($status >= 200 && $status < 400) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    $scripts = $dom->find('script');
                    if (!empty($scripts)) {
                        $eval = '';
                        foreach ($scripts as $sc) {
                            if (strpos($sc->innertext, 'eval(function(p,a,c,k,e,d)') !== FALSE) {
                                $eval = $sc->innertext;
                                break;
                            }
                        }
                        if (!empty($eval)) {
                            $unpacker = new \JavascriptUnpacker();
                            $data = $unpacker->unpack($eval);
                            if (preg_match('/file:"([^"]+)"/', $data, $video)) {
                                $this->status = 'ok';
                                $this->referer = 'https://v2.zplayer.live/';
                                $this->image = preg_match('/image:"([^"]+)"/', $data, $image) ? $image[1] : '';
                                $this->title = '';

                                $result = [];
                                $result[] = [
                                    'file' => $video[1],
                                    'type' => 'hls',
                                    'label' => 'Original'
                                ];
                                return $result;
                            }
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
