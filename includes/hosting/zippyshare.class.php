<?php
class zippyshare
{
    public $name = 'Zippyshare';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = '';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id) && filter_var($id, FILTER_VALIDATE_URL)) {
            $id = explode('?', $id);
            $this->id = $id[0];
            $this->url = $this->id;

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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/zippyshare~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            // cek penggunaan proxy
            /*$proxy = proxy_rotator();
            if ($proxy) {
                curl_setopt($this->ch, CURLOPT_PROXY, $proxy['proxy']);
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['usrpwd']);
            }*/
        }
    }

    function get_sources()
    {
        session_write_close();
        $response = curl_exec($this->ch);
        $err = curl_error($this->ch);

        if (!$err) {
            $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
            $video = $dom->find('#plyr', 0);
            if (!empty($video)) {
                $data = 'https:' . $video->find('source', 0)->src;
            } else {
                $data = '';
                $scripts = $dom->find('script');
                foreach ($scripts as $src) {
                    if (strpos($src->innertext, "document.getElementById('dlbutton').href") !== FALSE) {
                        $data = $src->innertext;
                        break;
                    }
                }
            }
            if (!empty($data)) {
                if (filter_var($data, FILTER_VALIDATE_URL)) {
                    $this->status = 'ok';
                    $this->title = $dom->find('meta[property="og:title"]', 0)->content;
                    $this->referer = $this->url;
                    $poster = 'data-poster';
                    $this->image = 'https:' . htmlspecialchars_decode($video->$poster);

                    $result[] = [
                        'file' => htmlspecialchars_decode($data),
                        'type' => 'video/mp4',
                        'label' => 'Original'
                    ];
                    return $result;
                } else {
                    $ex = explode("\n", strtr(trim($data), ["\r\n" => "\n"]));
                    list($var, $val) = explode('=', rtrim($ex[0], ';'), 2);
                    $a = (int) $val;
                    list($var, $val) = explode('=', rtrim($ex[1], ';'), 2);
                    $b = (int) $val;
                    if (strpos($data, '.omg = "f"') !== FALSE) {
                        $a = floor($a / 3);
                    } else {
                        $a = ceil($a / 3);
                    }
                    list($var, $val) = explode('.href = "', $data, 2);
                    list($link, $script) = explode(';', $val, 2);
                    $link = strtr($link, ['"' => '']);
                    $linkArr = array_values(array_filter(explode('/', trim($link))));
                    $math = 'return ' . strtr(trim($linkArr[2], '+'), ['a' => $a, 'b' => $b, '(' => '', ')' => '']) . ';';
                    $code = eval($math);

                    $host = parse_url($this->url, PHP_URL_HOST);
                    $scheme = parse_url($this->url, PHP_URL_SCHEME);
                    $video = $scheme . '://' . $host . '/' . implode('/', [$linkArr[0], $linkArr[1], $code, $linkArr[3]]);
                    if (filter_var($video, FILTER_VALIDATE_URL)) {
                        $this->status = 'ok';
                        $this->title = $dom->find('meta[property="og:title"]', 0)->content;
                        $this->referer = $this->url;
                        $this->image = strtr($video, ['/d/' => '/i/']);

                        $result[] = [
                            'file' => $video,
                            'type' => 'video/mp4',
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
