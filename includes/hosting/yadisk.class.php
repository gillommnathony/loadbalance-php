<?php
class yadisk
{
    public $name = 'Yandex Disk';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://yadi.sk/i/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $this->id = $id;

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
        }
    }

    private function parseAdaptiveHLS($URL = '', $videoStream = [])
    {
        $cacheExp = time() - 7200;
        $cacheFile = BASE_DIR . 'cache/playlist/yadisk~' . $this->id . '.m3u8';
        if (file_exists($cacheFile)) {
            if($cacheExp <= filemtime($cacheFile)) return file_get_contents($cacheFile);
            else @unlink($cacheFile);
        }

        $host = parse_url($URL, PHP_URL_HOST);
        curl_setopt($this->ch, CURLOPT_URL, $URL);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'host: ' . $host,
            'origin: https://' . $host,
        ));
        session_write_close();
        $response = curl_exec($this->ch);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($status >= 200 && $status < 400) {
            $ex = explode("\n", strtr($response, ["\r\n" => "\n"]));
            $i = 0;
            $result = [];
            foreach ($ex as $line) {
                if (strpos($line, 'playlist.m3u8') !== FALSE) {
                    $result[] = $videoStream[$i];
                    $i++;
                } else {
                    $result[] = $line;
                }
            }
            $content = implode("\n", $result);
            file_put_contents($cacheFile, $content);
            return $content;
        }
    }

    function get_sources($getMP4 = false)
    {
        if (!empty($this->id)) {
            if ($getMP4) {
                $result[] = [
                    'file' => $this->get_download_url(),
                    'type' => 'video/mp4',
                    'label' => 'Default'
                ];
            } else {
                $URL = $this->url . $this->id;
                curl_setopt($this->ch, CURLOPT_URL, $URL);
                curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/yadisk~' . $this->id . '.txt');
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                    'host: yadi.sk',
                    'origin: https://yadi.sk'
                ));
                session_write_close();
                $response = curl_exec($this->ch);
                $err = curl_error($this->ch);

                if (!$err) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    $jsonScript = $dom->find('script[type="application/json"]', 0);
                    if (!empty($jsonScript)) {
                        $json = @json_decode($jsonScript->innertext, true);
                        if (!empty($json['rootResourceId'])) {
                            $id     = $json['rootResourceId'];
                            $res    = $json['resources'][$id];
                            $videos = $res['videoStreams']['videos'];

                            // ambil link adaptive hls
                            $key    = array_search('adaptive', array_column($videos, 'dimension'));
                            $adaptive = $videos[$key]['url'];
                            unset($videos[$key]);

                            // parse link multi resolusi
                            $newVideos = [];
                            foreach ($videos as $vid) {
                                $newVideos[] = $vid['url'];
                            }
                            
                            $videoStream = $this->parseAdaptiveHLS($adaptive, $newVideos);
                            sleep(1);
                            if (!empty($videoStream)) {
                                $this->status = 'ok';
                                $this->referer = $this->url . $this->id;
                                $this->image = html_entity_decode($res['meta']['defaultPreview']) . '&crop=1&size=640x320';
                                $this->title = $res['name'];
                                $result[] = [
                                    'file' => BASE_URL . 'cache/playlist/yadisk~' . $this->id . '.m3u8',
                                    'type' => 'hls',
                                    'label' => 'Original'
                                ];
                                return $result;
                            }
                        }
                    }
                }
            }
        }
        return [];
    }

    function get_download_url()
    {
        if (!empty($this->id)) {
            curl_setopt($this->ch, CURLOPT_URL, 'https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key=' . urlencode('https://yadi.sk/i/' . $this->id));
            $head[] = "Connection: keep-alive";
            $head[] = "Keep-Alive: 300";
            $head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
            $head[] = "Accept-Language: en-us,en;q=0.5";
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $head);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Expect:'));
            session_write_close();
            $page = curl_exec($this->ch);
            $err = curl_error($this->ch);
            if (!$err) {
                $data = json_decode($page);
                return $data->href;
            }
        }
        return '';
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
