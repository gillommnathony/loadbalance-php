<?php
class userscloud
{
    public $name = 'Userscloud';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://userscloud.com/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = trim($id[0]);
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/userscloud~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: userscloud.com',
                'origin: https://userscloud.com',
            ));
        }
    }

    private function title()
    {
        $html = @file_get_contents($this->url);
        if ($html) {
            $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($html);
            $h2 = $dom->find('h2', 0);
            return !empty($h2) ? trim($h2->plaintext) : '';
        }
        return '';
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, 'op=download1&usr_login=&id=' . $this->id . '&referer=&method_free=Free Download&fname=' . $this->title());
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $video = $dom->find('video', 0);
                $dl = $dom->find('.ribbon-heading', 0);
                $videoUrl = !empty($video) ? $video->find('source', 0)->src : $dl->find('a', 0)->href;
                if (!empty($videoUrl)) {
                    $this->status = 'ok';
                    $this->title = $this->title();
                    $this->referer = 'https://userscloud.com';
                    $this->image = !empty($video->poster) ? $video->poster : '';

                    $result[] = [
                        'file' => $videoUrl,
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
