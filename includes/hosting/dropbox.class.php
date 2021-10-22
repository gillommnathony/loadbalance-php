<?php
class dropbox
{
    public $name = 'Dropbox';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://www.dropbox.com/s/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $this->id = $id;

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
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                "host: www.dropbox.com",
                "origin: https://www.dropbox.com",
            ));
        }
    }

    private function direct_link($url = '')
    {
        if (!empty($url)) {
            curl_setopt($this->ch, CURLOPT_URL, $url);
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

    function get_sources($mp4 = false)
    {
        if (!empty($this->id)) {
            if ($mp4) {
                session_write_close();
                $response = curl_exec($this->ch);
                $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

                if ($status >= 200 && $status < 400) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    $location = $this->direct_link($this->url .'?dl=1');
                    if ($location) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        $this->image = htmlspecialchars_decode($dom->find('meta[property="og:image"]', 0)->content);
                        $this->title = $dom->find('meta[property="og:title"]', 0)->content;

                        $result[] = [
                            'file' => $location,
                            'type' => 'video/mp4',
                            'label' => 'Original'
                        ];
                        return $result;
                    }
                }
            } else {
                session_write_close();
                $response = curl_exec($this->ch);
                $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

                if ($status >= 200 && $status < 400) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    $m3u8 = $dom->find('link[as="fetch"]', 0);
                    if (!empty($m3u8) && strpos($m3u8->href, '.m3u8') !== FALSE) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        $this->image = htmlspecialchars_decode($dom->find('meta[property="og:image"]', 0)->content);
                        $this->title = $dom->find('meta[property="og:title"]', 0)->content;

                        $result[] = [
                            'file' => $m3u8->href,
                            'type' => 'hls',
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
