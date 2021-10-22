<?php
class streamtape
{
    public $name = 'Streamtape';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://strtape.cloud/e/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = strtr(rtrim($id, '/'), ['/e/' => '', '/f/' => '', '/v/' => '']);
            $id = explode('/', $id);
            $this->id = end($id);
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/streamtape~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: strtape.cloud',
                'origin: https://strtape.cloud',
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
                $ex = explode('.innerHTML = "', $response);
                $ex = explode("';", end($ex));
                $video = 'https:' . strtr($ex[0], ['" + \'' => '']);
                if (filter_var($video, FILTER_VALIDATE_URL)) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);

                    session_write_close();
                    curl_setopt($this->ch, CURLOPT_URL, $video);
                    curl_setopt($this->ch, CURLOPT_HEADER, 1);
                    curl_setopt($this->ch, CURLOPT_NOBODY, 1);
                    session_write_close();
                    $response = curl_exec($this->ch);
                    $location = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);

                    if (filter_var($video, FILTER_VALIDATE_URL)) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        $this->title = $dom->find('meta[name="og:title"]', 0)->content;
                        $this->image = htmlspecialchars_decode($dom->find('meta[name="og:image"]', 0)->content);

                        $result[] = [
                            'file' => $location,
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
