<?php
class soundcloud
{
    public $name = 'Soundcloud';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = '';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id) && filter_var($id, FILTER_VALIDATE_URL) !== FALSE) {
            $this->id = $id;
            $this->url = $id;

            session_write_close();
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
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/soundcloud~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: soundcloud.com',
                'origin: https://soundcloud.com',
            ));
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);

            if (!$err) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $scripts = $dom->find('script');
                if ($scripts) {
                    $script = '';
                    foreach ($scripts as $sc) {
                        if (strpos($sc->innertext, 'catch(e){}})},') !== FALSE) {
                            $script = $sc->innertext;
                            break;
                        }
                    }
                    if (!empty($script)) {
                        $ex = explode('catch(e){}})},', rtrim($script, ');'));
                        $json = @json_decode(end($ex), true);
                        $json = end($json);
                        if (!empty($json['data'])) {
                            $this->referer = 'https://w.soundcloud.com/';
                            $this->title = $json['data'][0]['title'];
                            $this->image = $json['data'][0]['artwork_url'];

                            $sound = $json['data'][0]['media']['transcodings'][0]['url'] . '?client_id=v0C9kDEyULvWF0Ggb1vMnimjMDxtYX4B';
                            $host = parse_url($sound, PHP_URL_HOST);

                            curl_setopt($this->ch, CURLOPT_URL, $sound);
                            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                                "Host: " . $host,
                                "Origin: https://" . $host,
                                "Referer: https://soundcloud.com/"
                            ));

                            session_write_close();
                            $response = curl_exec($this->ch);
                            $err = curl_error($this->ch);

                            if (!$err) {
                                $json = json_decode($response, true);
                                if (!empty($json['url'])) {
                                    $this->status = 'ok';

                                    $result[] = [
                                        'file' => $json['url'],
                                        'type' => 'hls',
                                        'label' => 'Original',
                                    ];
                                    return $result;
                                }
                            }
                        }
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
