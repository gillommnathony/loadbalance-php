<?php
class filesfm
{
    public $name = 'Files.fm';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = '';
    private $ch;

    function __construct($url = '')
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
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/filesfm~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: ' . $this->host,
                'origin: ' . $this->scheme . '://' . $this->host,
                'referer: ' . $this->scheme . '://' . $this->host . '/',
            ));
        }
    }

    private function direct_link($url = '')
    {
        if (!empty($url)) {
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_REFERER, $url);
            curl_setopt($this->ch, CURLOPT_HEADER, 1);
            curl_setopt($this->ch, CURLOPT_NOBODY, 1);
            session_write_close();
            curl_exec($this->ch);
            $info = curl_getinfo($this->ch);
            if (!empty($info['url'])) {
                return $info['url'];
            }
        }
        return FALSE;
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                if (preg_match('/{"file_view_hash":"([^"]+)"/', $response, $file_hash)) {
                    $location = $this->direct_link('https://files.fm/down.php?i=' . $file_hash[1]);
                    if ($location) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        if (preg_match('/"file_name":"([^"]+)"/', $response, $title)) {
                            $this->title = trim($title[1]);
                        }
                        if (preg_match('/"og:image:secure_url" content="([^"]+)"/', $response, $image)) {
                            $this->image = htmlspecialchars_decode($image[1]);
                        }
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
