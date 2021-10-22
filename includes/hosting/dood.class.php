<?php
class dood
{
    public $name = 'DoodStream';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://dood.to/d/';
    private $proxy = FALSE;
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = strtr($id[0], '/d/', '/e/');

            $this->url .= $this->id;

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
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/dood~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                "host: dood.to",
                "origin: https://dood.to"
            ));
            // cek penggunaan proxy
            $this->proxy = proxy_rotator(0, 'dood');
            if ($this->proxy) {
                curl_setopt($this->ch, CURLOPT_PROXY, $this->proxy['proxy']);
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, $this->proxy['type']);
                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $this->proxy['usrpwd']);
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
                if ($dom) {
                    $sources = $dom->find('.download-content', 0);
                    if (!empty($sources)) {
                        $urls = [];
                        foreach ($sources->find('a') as $a) {
                            $urls[] = 'https://dood.to' . $a->href;
                        }
                        if (!empty($urls)) {
                            sleep(5);
                            $this->title = $dom->find('meta[name="og:title"]', 0)->content;
                            $this->image = htmlspecialchars_decode($dom->find('meta[name="og:image"]', 0)->content);
                            $this->referer = $this->url;

                            $mh = curl_multi_init();
                            $ch = [];
                            foreach ($urls as $i => $url) {
                                $ch[$i] = curl_init($url);
                                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, 0);
                                curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, 0);
                                curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1);
                                curl_setopt($ch[$i], CURLOPT_ENCODING, '');
                                curl_setopt($ch[$i], CURLOPT_TIMEOUT, 30);
                                curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
                                curl_setopt($ch[$i], CURLOPT_TCP_NODELAY, 1);
                                curl_setopt($ch[$i], CURLOPT_FORBID_REUSE, 1);
                                curl_setopt($ch[$i], CURLOPT_USERAGENT, USER_AGENT);
                                curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array(
                                    "host: dood.to",
                                    "origin: https://dood.to",
                                ));
                                if ($this->proxy) {
                                    curl_setopt($ch[$i], CURLOPT_PROXY, $this->proxy['proxy']);
                                    curl_setopt($ch[$i], CURLOPT_PROXYTYPE, $this->proxy['type']);
                                    curl_setopt($ch[$i], CURLOPT_PROXYUSERPWD, $this->proxy['usrpwd']);
                                }
                                curl_multi_add_handle($mh, $ch[$i]);
                            }

                            $active = null;
                            do {
                                $mrc = curl_multi_exec($mh, $active);
                            } while ($mrc == CURLM_CALL_MULTI_PERFORM
                            );

                            while ($active && $mrc == CURLM_OK) {
                                if (curl_multi_select($mh) == -1) {
                                    usleep(10);
                                }
                                do {
                                    $mrc = curl_multi_exec($mh, $active);
                                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                            }

                            $results = [];
                            foreach ($urls as $i => $url) {
                                $response = curl_multi_getcontent($ch[$i]);
                                $err = curl_error($ch[$i]);
                                if (!$err && !empty($response)) {
                                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                                    $link = $dom->find('.the_box', 0);
                                    $link = $link->find('a');
                                    if (!empty($link)) {
                                        $label = explode('quality', $a->plaintext);
                                        $results[] = [
                                            'file' => str_replace(["window.open('", "', '_self')"], '', end($link)->onclick),
                                            'type' => 'video/mp4',
                                            'label' => trim($label[0] . 'Quality')
                                        ];
                                    }
                                } else {
                                    error_log('dood error => ' . $err);
                                }
                                curl_multi_remove_handle($mh, $ch[$i]);
                            }
                            curl_multi_close($mh);

                            if (!empty($results)) {
                                $this->status = 'ok';
                            }

                            return $results;
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
