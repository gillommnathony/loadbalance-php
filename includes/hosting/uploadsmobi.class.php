<?php
class uploadsmobi
{
    public $name = 'Uploads.mobi';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://uploads.mobi/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '']);
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/uploadsmobi~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: uploads.mobi',
                'origin: https://uploads.mobi',
            ));
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
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);
            if (!$err) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $pv = $dom->find('#pv', 0);
                if (!empty($pv)) {
                    curl_setopt($this->ch, CURLOPT_HEADER, 1);
                    curl_setopt($this->ch, CURLOPT_NOBODY, 1);
                    curl_setopt($this->ch, CURLOPT_POST, 1);
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, 'op=download2&id=' . $this->id . '&rand=' . $dom->find('input[name="rand"]', 0)->value . '&referer=&method_free=&method_premium=&down_script=1&adblock_detected=0');
                    session_write_close();
                    $response = curl_exec($this->ch);
                    $redirect = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);

                    if (filter_var($redirect, FILTER_VALIDATE_URL)) {
                        $this->status = 'ok';
                        $this->referer = 'https://uploads.mobi/' . $this->id . '.html';
                        $this->title = $pv->alt;
                        $this->image = $pv->src;

                        $result[] = [
                            'file' => $redirect,
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
