<?php
class anonfile
{
    public $name = 'AnonFile';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://anonfiles.com/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];

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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/anonfile~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: anonfiles.com',
                'origin: https://anonfiles.com',
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
                $videos = $dom->find('.video-js', 0);
                $downloads = $dom->find('.download-quality');
                $download = $dom->find('#download-url', 0);
                if (!empty($videos) || !empty($downloads) || !empty($download)) {
                    $this->status = 'ok';
                    $this->referer = 'https://anonfiles.com/';
                    $this->title = trim($dom->find('h1', 0)->plaintext);

                    $result = [];
                    if (!empty($videos)) {
                        $sources = $videos->find('source');
                        if (!empty($sources)) {
                            foreach ($sources as $dt) {
                                $result[] = [
                                    'file' => trim($dt->src),
                                    'type' => trim($dt->type),
                                    'label' => trim($dt->label)
                                ];
                            }
                        }
                    } elseif (!empty($downloads)) {
                        foreach ($downloads as $dt) {
                            $result[] = [
                                'file' => trim($dt->href),
                                'type' => 'video/mp4',
                                'label' => trim(strtr($dt->id, 'download-quality', ''))
                            ];
                        }
                    } elseif (!empty($download)) {
                        $result[] = [
                            'file' => trim($download->href),
                            'type' => 'video/mp4',
                            'label' => 'Original'
                        ];
                    }
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
