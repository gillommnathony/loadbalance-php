<?php
class aparat
{
    public $name = 'Aparat';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://aparat.cam/';
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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/aparat~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                "host: aparat.cam",
                "origin: https://aparat.cam"
            ));
        }
    }

    function get_sources($mp4 = false)
    {
        if (!empty($this->id)) {
            if ($mp4) {
                curl_setopt($this->ch, CURLOPT_URL, $this->url);
                curl_setopt($this->ch, CURLOPT_COOKIEJAR, '');

                session_write_close();
                $response = curl_exec($this->ch);
                $err = curl_error($this->ch);

                if (!$err) {
                    $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                    if ($dom) {
                        $source = $dom->find('#containerInner', 0);
                        $links = [];
                        $quality = ['Low Quality', 'Normal Quality', 'High Quality'];
                        foreach ($source->find('a') as $link) {
                            if (!empty($link->onclick) && strpos($link->onclick, 'download_video') !== FALSE) {
                                $ex = explode(',', str_replace(['download_video', '(', ')', "'"], '', $link->onclick));
                                $links[] = [
                                    'link' => "https://aparat.cam/dl?op=download_orig&id={$ex[0]}&mode={$ex[1]}&hash={$ex[2]}",
                                    'mode' => str_replace(['l', 'n', 'h'], $quality, $ex[1])
                                ];
                            }
                        }
                        if (!empty($links)) {
                            $this->title = trim($dom->find('#content', 0)->find('h2', 0)->plaintext);
                            if (preg_match('/image: "([^"]+)"/', $response, $image)) {
                                $this->image = trim($image[1]);
                            }

                            $results = [];
                            foreach ($links as $a) {
                                if (array_search($a['mode'], array_column($results, 'label')) === false) {
                                    $dom = \KubAT\PhpSimple\HtmlDomParser::file_get_html($a['link']);
                                    $link = $dom->find('#containerInner', 0)->find('a');
                                    if (!empty($link)) {
                                        $results[] = [
                                            'file' => end($link)->href,
                                            'type' => 'video/mp4',
                                            'label' => $a['mode']
                                        ];
                                    }
                                }
                            }

                            if (!empty($results)) {
                                $this->status = 'ok';
                                return $results;
                            }
                        }
                    }
                }
            } else {
                session_write_close();
                $response = curl_exec($this->ch);
                $err = curl_error($this->ch);

                if (!$err) {
                    $data = explode("sources:", $response);
                    $data = explode("],", end($data));
                    $sources = str_replace(['src:', 'type:', 'file:'], ['"src":', '"type":', '"file":'], $data[0]) . ']';
                    $json = @json_decode($sources, true);
                    if (!empty($json)) {
                        $this->status = 'ok';
                        $this->referer = $this->url;
                        if (preg_match('/image: "([^"]+)"/', $response, $image)) {
                            $this->image = trim($image[1]);
                        }
                        $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                        $this->title = trim($dom->find('#content', 0)->find('h2', 0)->plaintext);

                        $result = [];
                        foreach ($json as $src) {
                            if (!empty($src['src']) && strpos($src['src'], '.m3u') !== FALSE) {
                                $result[] = [
                                    'file' => $src['src'],
                                    'type' => 'hls',
                                    'label' => 'Original'
                                ];
                            } elseif (!empty($src['file']) && strpos($src['file'], '.m3u') !== FALSE) {
                                $result[] = [
                                    'file' => $src['file'],
                                    'type' => 'hls',
                                    'label' => 'Original'
                                ];
                            }
                        }
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
