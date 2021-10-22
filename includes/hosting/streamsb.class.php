<?php
class streamsb
{
    public $name = 'StreamSB';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://streamsb.net/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $scheme = parse_url($this->url, PHP_URL_SCHEME);
            $host = parse_url($this->url, PHP_URL_HOST);
            $port = parse_URL($this->url, PHP_URL_PORT);
            if (empty($port)) $port = $scheme == 'https' ? 443 : 80;
            $ipv4 = gethostbyname($host);
            $resolveHost = implode(':', array($host, $port, $ipv4));

            $id = explode('?', $id);
            $this->id = strtr($id[0], ['embed-' => '', '.html' => '', '/view/' => '', '/play/' => '']);
            $this->url .= "play/{$this->id}?auto=1&referer=&";

            session_write_close();
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($this->ch, CURLOPT_ENCODING, '');
            curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($this->ch, CURLOPT_RESOLVE, array($resolveHost));
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_REFERER, $this->url);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/streamsb~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: streamsb.net',
                'origin: https://streamsb.net'
            ));
        }
    }

    private function title()
    {
        curl_setopt($this->ch, CURLOPT_URL, 'https://streamsb.net/d/' . $this->id . '.html');
        session_write_close();
        $response = curl_exec($this->ch);
        $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if ($status >= 200 && $status < 400) {
            $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
            if ($dom) {
                return trim(strtr($dom->find('.contentbox', 0)->find('h3', 0)->plaintext, ['Download' => '', 'Watch' => '']));
            }
        }
        return '';
    }

    private function get_download_links(array $links = [])
    {
        $replace = ['l' => 'Low Quality', 'n' => 'Normal Quality', 'h' => 'High Quality', 'o' => 'Original Quality'];
        $dlLinks = [];
        foreach ($links as $link) {
            $qry = parse_url($link, PHP_URL_QUERY);
            parse_str($qry, $qry);
            $dlLinks[] = [
                'link' => $link,
                'mode' => trim(strtr($qry['mode'], $replace)),
            ];
        }
        if (!empty($dlLinks)) {
            session_write_close();
            $mh = curl_multi_init();
            $ch = [];
            foreach ($dlLinks as $i => $data) {
                $ch[$i] = curl_init($data['link']);
                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch[$i], CURLOPT_ENCODING, '');
                curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                curl_setopt($ch[$i], CURLOPT_REFERER, $this->url);
                curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
                    "host: streamsb.net",
                    "origin: https://streamsb.net"
                ));
                curl_multi_add_handle($mh, $ch[$i]);
            }

            $active = null;
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while (
                $mrc == CURLM_CALL_MULTI_PERFORM
            );

            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($mh) == -1) {
                    usleep(100);
                }
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }

            $result = [];
            $newDLinks = [];
            foreach ($dlLinks as $i => $data) {
                $response = curl_multi_getcontent($ch[$i]);
                $status = curl_getinfo($ch[$i], CURLINFO_HTTP_CODE);

                if ($status >= 200 && $status < 400) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    if (!empty($dom->find('.err', 0))) {
                        $frm = $dom->find('form[name="F1"]', 0);
                        if ($frm) {
                            $newDLinks[] = "https://streamsb.net/dl?op=" . $frm->find('input[name="op"]', 0)->value . "&id=" . $frm->find('input[name="id"]', 0)->value . "&mode=" . $frm->find('input[name="mode"]', 0)->value . "&hash=" . $frm->find('input[name="hash"]', 0)->value;
                        }
                    } else {
                        $a = $dom->find('#container', 0)->find('a');
                        $link = !empty($a) ? end($a)->href : '';
                        if (!empty($link) && filter_var($link, FILTER_VALIDATE_URL) && pathinfo($link, PATHINFO_EXTENSION) === 'mp4') {
                            $result[] = [
                                'file' => $link,
                                'type' => 'video/mp4',
                                'label' => $data['mode']
                            ];
                        }
                    }
                }
                curl_multi_remove_handle($mh, $ch[$i]);
            }
            curl_multi_close($mh);

            if (!empty($newDLinks)) {
                $result = $this->get_download_links($newDLinks);
            }
            if (!empty($result)) {
                $this->status = 'ok';
                return $result;
            }
        }
        return FALSE;
    }

    function get_sources($mp4 = false)
    {
        if (!empty($this->id)) {
            if ($mp4) {
                curl_setopt($this->ch, CURLOPT_URL, 'https://streamsb.net/d/' . $this->id . '.html');

                session_write_close();
                $response = curl_exec($this->ch);
                $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

                if ($status >= 200 && $status < 400) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    if ($dom) {
                        $table = $dom->find('.contentbox', 0)->find('table', 0);
                        $table = $dom->find('.contentbox', 0)->find('table', 0);
                        $this->title = trim(str_replace('Download', '', $dom->find('.contentbox', 0)->find('h3', 0)->plaintext));
                        $this->referer = $this->url;

                        $links = [];
                        foreach ($table->find('tr') as $tr) {
                            if (!empty($tr->find('a', 0))) {
                                $ex = explode(',', str_replace(['download_video', '(', ')', "'"], '', $tr->find('a', 0)->onclick));
                                $links[] = "https://streamsb.net/dl?op=download_orig&id={$ex[0]}&mode={$ex[1]}&hash={$ex[2]}";
                            }
                        }
                        if (!empty($links)) {
                            sleep(3);
                            $result = $this->get_download_links($links);
                            return $result;
                        }
                    }
                }
            } else {
                session_write_close();
                $response = curl_exec($this->ch);
                $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
                $err = curl_error($this->ch);

                if ($status >= 200 && $status < 400) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    if ($dom) {
                        $source = '';
                        $jwsetup = '';
                        $scripts = $dom->find("script");
                        foreach ($scripts as $script) {
                            if (strpos($script->innertext, 'eval(function(p,a,c,k,e,d)') !== FALSE) {
                                $source = $script->innertext;
                                break;
                            } elseif (strpos($script->innertext, 'jwplayer("vplayer").setup') !== FALSE) {
                                $jwsetup = $script->innertext;
                                break;
                            }
                        }
                        
                        if(!empty($source)) {
                            $unpacker = new \JavascriptUnpacker();
                            $jwsetup = $unpacker->unpack($source);
                        }
                        
                        $json = explode('.setup({', $jwsetup, 2);
                        $json = explode('})', end($json), 2);
                        $json = '{' . $json[0] . '}';
                        $json = \OviDigital\JsObjectToJson\JsConverter::convertToJson($json);
                        $json = json_decode($json, true);

                        if (!empty($json['sources'])) {
                            $this->status = 'ok';
                            $this->referer = $this->url;
                            if (!empty($json['title'])) {
                                $this->title = trim($json['title']);
                            } else {
                                $this->title = $this->title();
                            }
                            if (!empty($json['image'])) {
                                $this->image = trim($json['image']);
                            }

                            $result = [];
                            foreach ($json['sources'] as $src) {
                                if (strpos($src['file'], '.m3u8') !== FALSE) {
                                    $result[] = [
                                        'file' => $src['file'],
                                        'type' => 'hls',
                                        'label' => 'Original'
                                    ];
                                    break;
                                }
                            }
                            return $result;
                        }
                    }
                } else {
                    error_log("streamsb get_sources => status: $status => $err");
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
