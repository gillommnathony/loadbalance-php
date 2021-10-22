<?php
class googlephotos
{
    public $name = 'Google Photos';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://photos.google.com/share/';
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
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, [
                'Host: photos.google.com',
                'Origin: https://photos.google.com'
            ]);
            // cek penggunaan proxy
            $proxy = proxy_rotator(0, 'googlephotos');
            if ($proxy) {
                curl_setopt($this->ch, CURLOPT_PROXY, $proxy['proxy']);
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['usrpwd']);
            }
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            $link = $this->url . $this->id;
            if (strpos($link, '/photo/') !== FALSE) {
                $key = explode('/photo/', $link);
                $key = explode('?', end($key));
                $mkey = $key[0];
            } else {
                curl_setopt($this->ch, CURLOPT_URL, $link);
                session_write_close();
                $response = curl_exec($this->ch);
                $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

                if ($status >= 200 && $status < 400) {
                    if (preg_match('/data:\[null,\[\["([^"]+)"/', $response, $key)) {
                        $mkey = $key[1];
                        $url    = parse_url($link);
                        $link  = $url['scheme'] . '://' . $url['host'] . $url['path'] . '/photo/' . $mkey . '?' . $url['query'];
                    }
                }
            }

            curl_setopt($this->ch, CURLOPT_URL, $link);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/googlephotos~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $scripts = $dom->find('script');
                if (!empty($scripts)) {
                    $script = '';
                    $search = 'data:[["' . $mkey;
                    foreach ($scripts as $sc) {
                        if (strpos($sc->innertext, $search) !== FALSE) {
                            $script = $sc->innertext;
                        }
                    }
                    $script = explode('data:', $script);
                    $script = strtr(end($script), ['});' => '', ', sideChannel: {}' => '']);
                    //return $script;
                    $json = json_decode($script, TRUE);
                    if (is_array(end($json))) {
                        $this->status = 'ok';
                        $this->referer = $link;

                        $videos = [];
                        foreach (end($json) as $key => $val) {
                            if (is_array($val)) {
                                if (is_array($val[0]) && !empty($val[0])) {
                                    if (is_array($val[0][0]) && !empty($val[0][0])) {
                                        foreach ($val[0][0] as $v) {
                                            if (strpos($v, 'url=') !== FALSE) {
                                                $ex = explode(',', $v);
                                                foreach ($ex as $x) {
                                                    parse_str($x, $r);
                                                    $type = explode(';', $r['type']);
                                                    if (strpos($type[0], 'mp4') !== FALSE) {
                                                        $url = rawurldecode($r['url']);
                                                        $videos[] = [
                                                            'file' => $url,
                                                            'type' => trim($type[0]),
                                                            'label' => $this->label($r['quality'])
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if(!empty($videos)){
                            $videos[] = [
                                'file' => $videos[0]['file'],
                                'type' => $videos[0]['type'],
                                'label' => 'Default'
                            ];
                        }
                        if (filter_var($json[1], FILTER_VALIDATE_URL)) {
                            $videos[] = [
                                'file' => $json[1],
                                'type' => 'video/mp4',
                                'label' => 'Original'
                            ];
                        }
                        return $videos;
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
            case 'hd1080':
                $label = "1080p";
                break;
            case 'hd720':
                $label = "720p";
                break;
            case 'medium':
                $label = '360p';
                break;
            case 'small':
                $label = '240p';
                break;
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
            case '34':
                $label = "360p";
                break;
            case '35':
                $label = "480p";
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
