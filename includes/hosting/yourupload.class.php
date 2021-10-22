<?php
class yourupload
{
    public $name = 'YourUpload';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://www.yourupload.com/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['watch' => '', 'embed' => '']);
            $this->url .= 'embed/' . $this->id;

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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/yourupload~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: www.yourupload.com',
                'origin: https://www.yourupload.com'
            ));
            // cek penggunaan proxy
            $proxy = proxy_rotator();
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
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $video = $dom->find('meta[content="og:video"]', 0);
                $video = !empty($video) ? $video : $dom->find('meta[property="og:video"]', 0);
                if (!empty($video)) {
                    $this->status = 'ok';
                    $this->referer = $this->url;

                    $image = $dom->find('meta[content="og:image"]', 0);
                    $image = !empty($image) ? $image : $dom->find('meta[property="og:image"]', 0);
                    $this->image = isset($image->description) ? htmlspecialchars_decode($image->description) : htmlspecialchars_decode($image->content);

                    $title = $dom->find('meta[content="og:title"]', 0);
                    $title = !empty($title) ? $title : $dom->find('meta[property="og:title"]', 0);
                    $this->title = isset($title->description) ? $title->description : $title->content;

                    $video = isset($video->description) ? $video->description : $video->content;
                    $result[] = [
                        'file' => $video,
                        'type' => 'video/mp4',
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
