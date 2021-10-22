<?php
class hexupload
{
    public $name = 'Hexupload';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://hexupload.net/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '']);
            $this->url .= 'embed-' . $this->id . '.html';

            session_write_close();
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/hexupload~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: hexupload.net',
                'origin: https://hexupload.net',
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
                if ($dom) {
                    $video = $dom->find('video', 0);
                    if (!empty($video)) {
                        $this->status = 'ok';
                        $this->title = '';
                        $this->referer = $this->url;
                        $this->image = $video->poster;
                        $result[] = [
                            'file' => $video->find('source', 0)->src,
                            'type' => 'video/mp4',
                            'label' => 'Original'
                        ];
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
