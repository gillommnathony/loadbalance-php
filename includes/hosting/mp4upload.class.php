<?php
class mp4upload
{
    public $name = 'mp4upload';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://mp4upload.com/';

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '']);

            session_write_close();
            $this->ch = curl_init();
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
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/mp4upload~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: mp4upload.com',
                'origin: https://mp4upload.com',
            ));
        }
    }

    private function title()
    {
        $URL = $this->url . $this->id . '.html';
        $dom = \KubAT\PhpSimple\HtmlDomParser::file_get_html($URL);
        if ($dom) {
            $title = $dom->find('h2', 0);
            return !empty($title) ? trim(strtr($title->plaintext, ['Download' => '', 'File' => ''])) : '';
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            $URL = $this->url . 'embed-' . $this->id . '.html';
            curl_setopt($this->ch, CURLOPT_URL, $URL);
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);
            if (!$err) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                if ($dom) {
                    $scripts = $dom->find('script');
                    if (!empty($scripts)) {
                        $evalScript = '';
                        foreach ($scripts as $script) {
                            if (strpos($script->innertext, 'eval(function(p,a,c,k,e,d)') !== FALSE) {
                                $evalScript = $script->innertext;
                                break;
                            }
                        }
                        if (!empty($evalScript)) {
                            $unpacker = new JavascriptUnpacker;
                            $decode = $unpacker->unpack($evalScript);
                            if (preg_match('/player.src\("([^"]+)"/', $decode, $video)) {
                                $this->status = 'ok';
                                $this->referer = $URL;
                                if (preg_match('/title: "([^"]+)"/', $decode, $title) && !empty($title[1])) {
                                    $this->title = $title[1];
                                } else {
                                    $this->title = $this->title();
                                }
                                if (preg_match('/player.poster\("([^"]+)"/', $decode, $poster)) {
                                    $this->image = $poster[1];
                                }

                                $result[] = [
                                    'file' => $video[1],
                                    'type' => 'video/mp4',
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
}
