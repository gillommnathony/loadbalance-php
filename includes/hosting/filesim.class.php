<?php
class filesim
{
    public $name = 'Files.im';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://files.im/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = trim(strtr($id[0], ['embed-' => '', '.html' => '']));
            if (filter_var($id, FILTER_VALIDATE_URL)) {
                $ex = explode('/', rtrim($this->id, '/'));
                $this->id = end($ex);
            }

            $this->url .= $this->id;

            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_ENCODING, "");
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/filesim~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: files.im',
                'origin: https://files.im'
            ));
            curl_setopt($this->ch, CURLOPT_HEADER, 1);
            curl_setopt($this->ch, CURLOPT_HEADER, 2);
            // cek penggunaan proxy
            $proxy = proxy_rotator();
            if ($proxy) {
                curl_setopt($this->ch, CURLOPT_PROXY, $proxy['proxy']);
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['usrpwd']);
            }
            session_write_close();
            curl_exec($this->ch);
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            curl_setopt($this->ch, CURLOPT_REFERER, $this->url);
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, 'op=download2&id=' . $this->id . '&rand=&referer=&method_free=&method_premium=&is_hosting=0&adblock_detected=1');
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                if ($dom) {
                    $dl = $dom->find('.download-button', 0);
                    if ($dl) {
                        $this->status = 'ok';
                        $this->title = trim($dom->find('.dfilename', 0)->plaintext);
                        $this->referer = 'https://files.im/';

                        $result[] = [
                            'file' => trim($dl->find('a.btn-dow', 0)->href),
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
