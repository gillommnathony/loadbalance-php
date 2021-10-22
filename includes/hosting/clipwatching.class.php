<?php
class clipwatching
{
    public $name = 'ClipWatching';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://clipwatching.com/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '']);
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/clipwatching~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: clipwatching.com',
                'origin: https://clipwatching.com',
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
                $sc = explode('sources:', $response);
                $sce = explode('],', end($sc));
                $dataSource = json_decode(strtr($sce[0], ['src' => '"src"', 'type' => '"type"', 'label' => '"label"', 'file' => '"file"']) . ']', TRUE);
                if (!empty($dataSource)) {
                    $this->status = 'ok';
                    $this->referer = $this->url;
                    if (preg_match('/image: "([^"]+)"/', $response, $image)) {
                        $this->image = $image[1];
                    }

                    $result = [];
                    foreach ($dataSource as $dt) {
                        if (!empty($dt['file'])) {
                            if (strpos($dt['file'], '.m3u') !== FALSE) {
                                $result[] = [
                                    'file' => trim($dt['file']),
                                    'type' => 'hls',
                                    'label' => 'Playlist'
                                ];
                                break;
                            } elseif (strpos($dt['file'], '.mp4') !== FALSE) {
                                $result[] = [
                                    'file' => trim($dt['file']),
                                    'type' => 'video/mp4',
                                    'label' => $dt['label']
                                ];
                            }
                        } elseif (!empty($dt['src'])) {
                            $result[] = [
                                'file' => trim($dt['src']),
                                'type' => 'hls',
                                'label' => 'Playlist'
                            ];
                        }
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
