<?php
class solidfiles
{
    public $name = 'Solidfiles';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'http://www.solidfiles.com/v/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $this->id = strtr($id, ['/e/' => '', '/v/' => '']);
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/solidfiles~' . $this->id .'.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: www.solidfiles.com',
                'origin: http://www.solidfiles.com',
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
                $html = $dom->innertext;

                if (preg_match('/"streamUrl":"([^"]+)"/', $html, $streamUrl)) {
                    $videoUrl = filter_var($streamUrl[1], FILTER_VALIDATE_URL);
                } elseif (preg_match('/"downloadUrl":"([^"]+)"/', $html, $dlUrl)) {
                    $videoUrl = filter_var($dlUrl[1], FILTER_VALIDATE_URL);
                }

                if (!empty($videoUrl)) {
                    $this->status = 'ok';
                    $this->referer = $this->url;
                    if (preg_match('/"nodeName":"([^"]+)"/', $html, $title)) {
                        $this->title = $title[1];
                    }
                    if (preg_match('/"splashUrl":"([^"]+)"/', $html, $image)) {
                        $this->image = $image[1];
                    }

                    $result[] = [
                        'file'  => $videoUrl,
                        'type'  => 'video/mp4',
                        'label' => 'Original'
                    ];
                    return $result;
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
