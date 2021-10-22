<?php
class vidlox
{
    public $name = 'Vidlox';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://vidlox.me/';
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
            curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/vidlox~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: vidlox.me',
                'origin: https://vidlox.me',
                'referer: '. $this->url,
                'cookie: __ddg3=rTFHhvCp7bmTJVuA; __ddg1=RwRQw2CNZJs26d0CHsJE; aff=54012; ref_url='. rawurlencode($this->url) .'; _ga=GA1.2.826251673.1615968268; _gid=GA1.2.1274311470.1615968268; ppu_main_fce582668f5d023ab4ad3c8c2ac92460=1; sb_page_95fd32c088adf9f0519de46ac604dc83=1; sb_main_95fd32c088adf9f0519de46ac604dc83=1; sb_count_95fd32c088adf9f0519de46ac604dc83=1; sb_onpage_95fd32c088adf9f0519de46ac604dc83=1'
            ));
        }
    }

    function get_sources($mp4 = false)
    {
        if (!empty($this->id)) {
            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);
            if (!$err) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                if ($dom) {
                    $content = $dom->innertext;
                    $sc = explode('sources: ["', $content);
                    if (!empty($sc)) {
                        $sce = explode('"],', end($sc));
                        $dataSource = explode('","', $sce[0]);
                        if (!empty($dataSource)) {
                            if (filter_var($dataSource[0], FILTER_VALIDATE_URL)) {
                                $this->status = 'ok';
                                $this->referer = $this->url;

                                $img = explode('poster: "', $content);
                                $img = explode('",', end($img));
                                $this->image = trim($img[0]);

                                $result = [];
                                if ($mp4) {
                                    $quality = ['720p', '480p'];
                                    $i = 0;
                                    foreach ($dataSource as $dt) {
                                        if (strpos($dt, '.mp4') !== FALSE) {
                                            $result[] = [
                                                'file' => trim($dt, '"'),
                                                'type' => 'video/mp4',
                                                'label' => $quality[$i]
                                            ];
                                            $i++;
                                        }
                                    }
                                } else {
                                    foreach ($dataSource as $dt) {
                                        if (strpos($dt, '.m3u8') !== FALSE) {
                                            $result[] = [
                                                'file' => trim($dt, '"'),
                                                'type' => 'hls',
                                                'label' => 'Original'
                                            ];
                                        }
                                    }
                                }
                                return $result;
                            }
                        }
                    }
                }
            } else {
                error_log('vidlox get_sources => ' . $err);
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
