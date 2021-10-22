<?php
class blogger
{
    public $name = 'Blogger';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://www.blogger.com/video.g?token=';
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/blogger~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                "cache-control: no-cache",
                "pragma: no-cache",
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
                $stream = explode('VIDEO_CONFIG =', $response);
                $xstream = count($stream) >= 1 ? explode('</script>', trim(end($stream))) : '';
                if (!empty($xstream)) {
                    $data = json_decode(html_entity_decode(strtr(trim($xstream[0]), ['\u0026' => '&', '\u003d' => '=']), ENT_QUOTES, 'UTF-8'), true);

                    if (!empty($data['streams'])) {
                        $this->status = 'ok';
                        $this->title = $data['iframe_id'];
                        $this->image = $data['thumbnail'];
                        $this->referer = 'https://www.youtube.com/embed/?autohide=1&enablecastapi=0&html5=1&ps=blogger&enablejsapi=1&origin=https%3A%2F%2Fwww.blogger.com&widgetid=1';

                        $result = [];
                        foreach ($data['streams'] as $vid) {
                            $dt = [];
                            $dt['file'] = $vid['play_url'];
                            $dt['label'] = $this->label($vid['format_id']);
                            $dt['type'] = 'video/mp4';
                            $result[]   = $dt;
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
