<?php
class okstream
{
    public $name = 'Okstream';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://www.okstream.cc/e/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];

            $this->url .= $this->id . '/';

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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/okstream~' . preg_replace('/[^a-zA-Z0-9]+/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: www.okstream.cc',
                'origin: https://www.okstream.cc',
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
                if (preg_match('/name=\"og:title\" content="([^"]+)"/', $response, $title)) {
                    $this->title = $title[1];
                }
                if (preg_match('/name=\"og:image\" content="([^"]+)"/', $response, $image)) {
                    $this->image = $image[1];
                }
                if (preg_match('/keys="([^"]+)"/', $response, $key)) {
                    $morocco = $key[1];
                }
                if (preg_match('/protection="([^"]+)"/', $response, $protection)) {
                    $mycountry = $protection[1];
                }
                if (!empty($morocco) && !empty($mycountry)) {
                    curl_setopt($this->ch, CURLOPT_URL, 'https://www.okstream.cc/request/');
                    curl_setopt($this->ch, CURLOPT_POST, 1);
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query(array(
                        'morocco' => $morocco,
                        'mycountry' => $mycountry
                    )));
                    curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                        'host: www.okstream.cc',
                        'origin: https://www.okstream.cc',
                        'referer: ' . $this->url,
                        'X-Requested-With: XMLHttpRequest'
                    ));
                    session_write_close();
                    $response = curl_exec($this->ch);
                    $err = curl_error($this->ch);

                    if (!$err) {
                        $this->status = 'ok';
                        $this->referer = $this->url;

                        $result[] = [
                            'file' => trim($response),
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
