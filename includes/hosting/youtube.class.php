<?php
class youtube
{
    public $name = 'Youtube';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $private = false;
    private $url = 'https://www.youtube.com/get_video_info?video_id=';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];
            $this->url .= $this->id;

            session_write_close();
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, 'https://yt1s.com/id');
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/yt1s~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_exec($this->ch);
        }
    }

    private function private_sources()
    {
        $headers = array(
            'cache-control: no-cache',
            'content-type: application/x-www-form-urlencoded; charset=UTF-8',
            'origin: https://yt1s.com',
            'referer: https://yt1s.com/id',
            'x-requested-with: XMLHttpRequest'
        );
        $cookieFile = BASE_DIR . 'cookies/yt1s~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt';

        curl_setopt($this->ch, CURLOPT_URL, 'https://yt1s.com/api/ajaxSearch/index');
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'q' => 'https://www.youtube.com/watch?v=' . $this->id,
            'vt' => 'home'
        )));
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        session_write_close();
        $response = curl_exec($this->ch);
        $err = curl_error($this->ch);

        if (!$err) {
            $data = json_decode($response, true);
            if ($data['status'] === 'ok' && !empty($data['links']['mp4'])) {
                $this->status = 'ok';
                $this->image = 'https://i.ytimg.com/vi/' . $this->id . '/maxresdefault.jpg';
                $this->referer = 'https://yt1s.com/id';
                $this->title = $data['title'];

                $vid = $data['vid'];
                $k = array_values($data['links']['mp4']);
                $k = array_column($k, 'k');

                $mh = curl_multi_init();
                $ch = [];
                foreach ($k as $i => $key) {
                    $ch[$i] = curl_init('https://yt1s.com/api/ajaxConvert/convert');
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch[$i], CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch[$i], CURLOPT_POSTFIELDS, http_build_query(array(
                        'vid' => $vid,
                        'k' => $key
                    )));
                    curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                    curl_setopt($ch[$i], CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch[$i], CURLOPT_COOKIEFILE, $cookieFile);
                    curl_multi_add_handle($mh, $ch[$i]);
                }

                $active = null;
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);

                while ($active && $mrc == CURLM_OK) {
                    if (curl_multi_select($mh) == -1) {
                        usleep(10);
                    }
                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                }

                $result = [];
                foreach ($k as $i => $key) {
                    $err = curl_error($ch[$i]);
                    $response = curl_multi_getcontent($ch[$i]);
                    if (!$err) {
                        $arr = json_decode($response, true);
                        if ($arr['c_status'] !== 'FAILED' && !empty($arr['dlink'])) $result[] = [
                            'file' => $arr['dlink'],
                            'type' => 'video/mp4',
                            'label' => strtr($arr['fquality'] . 'p', ['pp' => 'p'])
                        ];
                    } else {
                        error_log('youtube private_sources => ' . $err);
                    }
                    curl_multi_remove_handle($mh, $ch[$i]);
                }
                curl_multi_close($mh);
                return $result;
            } else {
                error_log('youtube private_sources => ' . $err);
            }
        } else {
            error_log('youtube private_sources => ' . $err);
        }
        return [];
    }

    private function api_sources()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/youtube~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
        session_write_close();
        $response = curl_exec($this->ch);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($status >= 200 && $status < 400) {
            parse_str($response, $res);

            if (!empty($res['player_response'])) {
                $data = json_decode($res['player_response'], true);
                $status = strtolower($data['playabilityStatus']['status']);
                if ($status === 'ok' && !empty($data['streamingData'])) {
                    $stream = $data['streamingData'];
                    $detail = $data['videoDetails'];

                    $this->status = $status;
                    $this->title = $detail['title'];
                    $this->image = 'https://i.ytimg.com/vi/' . $this->id . '/maxresdefault.jpg';
                    $this->referer = 'https://www.youtube.com/watch/?v=' . $this->id;
                    $this->private = false;

                    if (!empty($stream['formats'])) {
                        $formats = $stream['formats'];
                        $result = [];
                        foreach ($formats as $vid) {
                            if (!empty($vid['url']) && strpos($vid['mimeType'], 'video/mp4') !== FALSE) {
                                $dt = [];
                                $dt['file'] = $vid['url'];
                                $dt['label'] = $this->label($vid['itag']);
                                $dt['type'] = 'video/mp4';
                                $result[] = $dt;
                            }
                        }
                        return $result;
                    }
                }
            }
        }
        return [];
    }

    function get_sources()
    {
        $api = $this->api_sources();
        if (!empty($api)) {
            return $api;
        } else {
            return $this->private_sources();
        }
        return [];
    }

    function get_private()
    {
        return $this->private;
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

    private function label($itag)
    {
        switch ($itag) {
            case '18':
                $label = "360p";
                break;
            case '59':
                $label = "480p";
                break;
            case '22':
                $label = "720p";
                break;
            case '37':
                $label = "1080p";
                break;
            case '5':
                $label = "240p";
                break;
            case '17':
                $label = "144p";
                break;
            case '34':
                $label = "360p";
                break;
            case '35':
                $label = "480p";
                break;
            case '36':
                $label = "240p";
                break;
            case '38':
                $label = "Original";
                break;
            case '43':
                $label = "360p";
                break;
            case '44':
                $label = "480p";
                break;
            case '45':
                $label = "720p";
                break;
            case '46':
                $label = "1080p";
                break;
            case '82':
                $label = "360p";
                break;
            case '84':
                $label = "720p";
                break;
            case '102':
                $label = "360p";
                break;
            case '104':
                $label = "720p";
                break;
            case '132':
                $label = "144p";
                break;
            case '133':
                $label = "240p";
                break;
            case '134':
                $label = "360p";
                break;
            case '135':
                $label = "480p";
                break;
            case '136':
                $label = "720p";
                break;
            case '137':
                $label = "1080p";
                break;
            default:
                $label = "Unknown";
                break;
        }
        return $label;
    }

    function __destruct()
    {
        curl_close($this->ch);
    }
}
