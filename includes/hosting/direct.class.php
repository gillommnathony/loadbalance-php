<?php
class direct
{
    public $name = 'Direct';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = '';
    private $host = '';
    private $scheme = '';
    private $ch;

    function __construct($url)
    {
        if (!empty($url)) {
            $this->id = $url;
            $this->url = $url;
            $this->host = parse_url($url, PHP_URL_HOST);
            $this->scheme = parse_url($url, PHP_URL_SCHEME);

            session_write_close();
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_ENCODING, "");
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/direct~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: ' . $this->host,
                'origin: ' . $this->scheme . '://' . $this->host,
                'referer: ' . $this->scheme . '://' . $this->host . '/',
            ));
            curl_setopt($this->ch, CURLOPT_HEADER, 1);
            curl_setopt($this->ch, CURLOPT_NOBODY, 1);
        }
    }

    function get_sources()
    {
        if (!empty($this->url)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $contentType = curl_getinfo($this->ch, CURLINFO_CONTENT_TYPE);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            $location = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);

            if ($status >= 200 && $status < 400 || !empty($location) && filter_var($location, FILTER_VALIDATE_URL)) {
                $this->status = 'ok';
                $this->referer = $this->scheme . '://' . $this->host . '/';

                $contentType = strtolower($contentType);
                if (
                    strpos($contentType, 'mpegurl') !== FALSE || strpos($contentType, 'vnd') !== FALSE ||
                    strpos($response, '#ext') !== FALSE || strpos($this->url, '.m3u') !== FALSE
                ) {
                    $type = 'hls';
                } elseif (
                    strpos($contentType, 'dash') !== FALSE ||
                    strpos($response, 'xml') !== FALSE ||
                    strpos($this->url, '.mpd') !== FALSE
                ) {
                    $type = 'mpd';
                } else {
                    $type = 'video/mp4';
                }
                $title = explode('/', rtrim($this->url, '/'));
                $this->title = parse_url($this->url, PHP_URL_HOST) . ' ' . rawurldecode(end($title));
                $result[] = [
                    'file' => $location,
                    'type' => $type,
                    'label' => 'Original'
                ];
                return $result;
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
