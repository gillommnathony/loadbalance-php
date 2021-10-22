<?php
class videobin
{
    public $name = 'Videobin';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://videobin.co/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '']);

            session_write_close();
            $this->ch = curl_init();
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/videobin~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: videobin.co',
                'origin: https://videobin.co',
            ));
        }
    }

    private function parse_sources($response = '', $mp4 = false)
    {
        $sc = explode('sources: [', $response);
        if (count($sc) > 1) {
            $sce = explode('],', end($sc));
            $dataSource = explode('","', $sce[0]);
            if (!empty($dataSource)) {
                $this->status = 'ok';
                $this->referer = $this->url;

                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $this->title = !empty($dom->find('h1', 0)) ? trim($dom->find('h1', 0)->plaintext) : '';

                $img = explode('poster: "', $response);
                $img = explode('",', end($img));
                $this->image = trim($img[0]);

                $result = [];
                $quality = ['720p', '360p'];
                $i = 0;
                if ($mp4) {
                    foreach ($dataSource as $dt) {
                        if (strpos($dt, '.m3u8') == FALSE) {
                            $result[] = [
                                'file' => trim($dt, '"'),
                                'type' => 'video/mp4',
                                'label' => $quality[$i]
                            ];
                            $i++;
                        }
                    }
                } else {
                    foreach ($dataSource as $dt) {
                        if (strpos($dt, '.m3u8') !== FALSE) {
                            $result = [];
                            $result[] = [
                                'file' => trim($dt, '"'),
                                'type' => 'hls',
                                'label' => 'Playlist'
                            ];
                            break;
                        } else {
                            $result[] = [
                                'file' => trim($dt, '"'),
                                'type' => 'video/mp4',
                                'label' => $quality[$i]
                            ];
                            $i++;
                        }
                    }
                }
                return $result;
            }
        }
        return [];
    }

    function get_sources($mp4 = false)
    {
        if (!empty($this->id)) {
            $url = $this->url . $this->id . '.html';
            curl_setopt($this->ch, CURLOPT_URL, $url);
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                $downloadPage = $this->parse_sources($response, $mp4);
                if (!empty($downloadPage)) {
                    return $downloadPage;
                } else {
                    $url = $this->url . 'embed-' . $this->id . '.html';
                    curl_setopt($this->ch, CURLOPT_URL, $url);
                    session_write_close();
                    $response = curl_exec($this->ch);
                    $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

                    if ($status >= 200 && $status < 400) {
                        return $this->parse_sources($response, $mp4);
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
