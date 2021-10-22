<?php
if (!defined('BASE_DIR')) die('access denied!');

class vimeo
{
    public $name = 'Vimeo';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://player.vimeo.com/video/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];

            $this->url .= $this->id;

            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_ENCODING, "");
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($this->ch, CURLOPT_REFERER, 'https://vimeo.com/');
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/vimeo~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: player.vimeo.com',
                'origin: https://vimeo.com'
            ));
            // cek penggunaan proxy
            $proxy = proxy_rotator();
            if ($proxy) {
                curl_setopt($this->ch, CURLOPT_PROXY, $proxy['proxy']);
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['usrpwd']);
            }
        }
    }

    function get_sources($mp4 = FALSE)
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);

            if (!$err) {
                $ex = explode('config = ', htmlspecialchars($response));
                $ex = explode('};', end($ex));
                $data = @json_decode(htmlspecialchars_decode($ex[0]) . '}', true);
                if ($mp4 && !empty($data['request']['files']['progressive'])) {
                    $this->status = 'ok';
                    $this->title = $data['video']['title'];
                    $this->image = $data['video']['thumbs']['base'];

                    $result = [];
                    foreach ($data['request']['files']['progressive'] as $src) {
                        $result[] = [
                            'file' => $src['url'],
                            'type' => 'video/mp4',
                            'label' => $src['quality'],
                        ];
                    }
                    return $result;
                } elseif (!empty($data['request']['files']['hls'])) {
                    $this->status = 'ok';
                    $this->title = $data['video']['title'];
                    $this->image = $data['video']['thumbs']['base'];

                    $result[] = [
                        'file' => $data['request']['files']['hls']['cdns']['akfire_interconnect_quic']['url'],
                        'type' => 'hls',
                        'label' => 'Original',
                    ];
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
