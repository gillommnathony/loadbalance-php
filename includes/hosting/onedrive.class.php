<?php
class onedrive
{
    public $name = 'One Drive';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://onedrive.live.com/embed?resid=';

    function __construct($id = '')
    {
        if (!empty($id)) {
            $this->id = $id;
            $this->url .= $this->id;

            session_write_close();
            $this->ch = curl_init();
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
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: onedrive.live.com',
                'origin: https://onedrive.live.com'
            ));
        }
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);

            if (!$err) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                if ($dom) {
                    $script = '';
                    foreach ($dom->find('script') as $sc) {
                        if (strpos($sc->innertext, '"download":"') !== FALSE) {
                            $script = $sc->innertext;
                            break;
                        }
                    }
                    if (!empty($script)) {
                        $ex = explode(';', $script);
                        $json = trim(strtr($ex[0], ['window.itemData =' => '']));
                        $json = json_decode(trim(trim($json, '='), ';'));

                        if (!empty($json->items)) {
                            $this->status = 'ok';
                            $this->referer = $this->url;
                            $this->title = $json->items[0]->name . $json->items[0]->extension;
                            $this->image = $json->items[0]->extension === '.mp4' ? $json->items[0]->thumbnailSet->baseUrl . $json->items[0]->thumbnailSet->thumbnails[0]->url : '';

                            $result = [];
                            foreach ($json->items as $item) {
                                $result[] = [
                                    'file' => $item->urls->download,
                                    'type' => 'video/mp4',
                                    'label' => 'Original'
                                ];
                            }
                            return $result;
                        }
                    }
                }
            } else {
                error_log('onedrive get_sources => '. $err);
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
