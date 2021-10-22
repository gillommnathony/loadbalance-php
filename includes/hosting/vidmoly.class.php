<?php
class vidmoly
{
    public $name = 'Vidmoly';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://vidmoly.to/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['/w/' => '', 'embed-' => '', '.html' => '']);
            $this->url .= 'embed-' . $this->id . '.html';

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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/vidmoly~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: vidmoly.to',
                'origin: https://vidmoly.to',
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
                $sc = explode('sources: [', $response);
                if (!empty($sc)) {
                    $sce = explode('],', end($sc));
                    $dataSource = json_decode('[' . strtr($sce[0], ['file:' => '"file":', 'label:' => '"label":']) . ']', TRUE);
                    if (!empty($dataSource)) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        if (preg_match('/tit=([^"]+)"/', $response, $title)) {
                            $this->title = $title[1];
                        }

                        $img = explode('image: "', $response);
                        $img = explode('",', end($img));
                        $this->image = trim($img[0]);

                        $result = [];
                        foreach ($dataSource as $dt) {
                            if (strpos($dt['file'], '.m3u') !== FALSE) {
                                $result[] = [
                                    'file' => $dt['file'],
                                    'type' => 'hls',
                                    'label' => 'Original'
                                ];
                                break;
                            } else {
                                $result[] = [
                                    'file' => $dt['file'],
                                    'type' => 'video/mp4',
                                    'label' => $dt['label']
                                ];
                            }
                        }
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
