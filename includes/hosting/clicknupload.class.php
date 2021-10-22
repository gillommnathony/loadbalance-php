<?php
class clicknupload
{
    public $name = 'Clicknupload';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://clicknupload.co/';
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
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if(defined('CURLOPT_TCP_FASTOPEN')){
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, 'op=download2&id=' . $this->id . '&rand=&referer=' . $this->url . '&method_free=Free Download >>&method_premium&adblock_detected=0');
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_REFERER, $this->url);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/clicknupload~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: clicknupload.co',
                'origin: https://clicknupload.co',
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
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $dl = $dom->find('#downloadbtn', 0);
                if (!empty($dl)) {
                    $this->status = 'ok';
                    $this->referer = $this->url;
                    $this->title = $dom->find('.dfilename', 0)->plaintext;

                    $dl = trim(str_replace(["window.open('", "');"], '', $dl->onclick));
                    $result[] = [
                        'file' => $dl,
                        'type' => 'video/mp4',
                        'label' => 'Original'
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
