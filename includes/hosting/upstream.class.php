<?php
class upstream
{
    public $name = 'UpStream';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://upstream.to/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '']);
            $this->url .= 'embed-' . $this->id . '.html';

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
            curl_setopt($this->ch, CURLOPT_REFERER, $this->url);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/upstream~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: upstream.to',
                'origin: https://upstream.to',
            ));
            $proxy = proxy_rotator(0, 'upstream');
            if ($proxy) {
                curl_setopt($this->ch, CURLOPT_PROXY, $proxy['proxy']);
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxy['type']);
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $proxy['usrpwd']);
            }
        }
    }

    private function info()
    {
        $url = strtr($this->url, ['embed-' => '', '.html' => '']);

        curl_setopt($this->ch, CURLOPT_URL, $url);
        session_write_close();
        $response = curl_exec($this->ch);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($status >= 200 && $status < 400) {
            $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
            if ($dom) {
                $this->referer = 'https://upstream.to/';
                $this->image = $dom->find('#vplayer', 0)->find('img', 0)->src;
                $this->title = trim(strtr($dom->find('title', 0)->plaintext, ['Watch' => '', 'Watching' => '']));
            }
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
                $scripts = $dom->find('script');
                if (!empty($scripts)) {
                    $eval = '';
                    foreach ($scripts as $sc) {
                        if (strpos($sc->innertext, 'eval(function(p,a,c,k,e,d)') !== FALSE) {
                            $eval = $sc->innertext;
                            break;
                        }
                    }
                    if (!empty($eval)) {
                        $unpacker = new \JavascriptUnpacker();
                        $data = $unpacker->unpack($eval);
                        $ex = explode('[{', $data);
                        if (!empty($ex)) {
                            $ex = explode('}],', $ex[1]);
                            $sources = "[{" . str_replace(['file:', 'label:'], ['"file":', '"label":'], $ex[0]) . "}]";
                            $sources = @json_decode($sources, TRUE);
                            if ($sources) {
                                $this->status = 'ok';
                                $this->info();

                                $result = [];
                                foreach ($sources as $sc) {
                                    if (!empty($sc['label'])) {
                                        $result[] = [
                                            'file' => $sc['file'],
                                            'label' => $sc['label'],
                                            'type' => 'video/mp4'
                                        ];
                                    } else {
                                        $result[] = [
                                            'file' => $sc['file'],
                                            'label' => 'Original',
                                            'type' => 'hls'
                                        ];
                                    }
                                }
                                return $result;
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
