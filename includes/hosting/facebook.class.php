<?php
class facebook
{
    public $name = 'Facebook';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://www.facebook.com/';
    private $context;
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
            curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.18363');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                "Accept: text/html, application/xhtml+xml, application/xml; q=0.9, */*; q=0.8",
                'Accept-Language: id, en-ID; q=0.7, en; q=0.3',
                'Cache-Control: max-age=0',
                'Host: www.facebook.com',
                'Upgrade-Insecure-Requests: 1',
            ));
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);
            curl_close($this->ch);
            if (!$err) {
                $result = [];
                if (preg_match('/hd_src:"([^"]+)"/', $response, $hd)) {
                    $result[] = [
                        'file'  => $hd[1],
                        'type'  => 'video/mp4',
                        'label' => '720p'
                    ];
                } elseif (preg_match('/hd_src_no_ratelimit:"([^"]+)"/', $response, $sd)) {
                    $result[] = [
                        'file'  => $sd[1],
                        'type'  => 'video/mp4',
                        'label' => '360p'
                    ];
                }
                if (preg_match('/sd_src:"([^"]+)"/', $response, $sd)) {
                    $result[] = [
                        'file'  => $sd[1],
                        'type'  => 'video/mp4',
                        'label' => '360p'
                    ];
                } elseif (preg_match('/sd_src_no_ratelimit:"([^"]+)"/', $response, $sd)) {
                    $result[] = [
                        'file'  => $sd[1],
                        'type'  => 'video/mp4',
                        'label' => '360p'
                    ];
                }

                if (!empty($result)) {
                    $this->status = 'ok';
                    $this->referer = $this->url;

                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    $this->image = trim($dom->find('meta[property="og:image"]', 0)->content);
                    $this->title = trim(strtr($dom->find('meta[property="og:title"]', 0)->content, '| Facebook', ''));

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
}
