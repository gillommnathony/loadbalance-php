<?php
class megaup
{
    public $name = 'MegaUp';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $url = 'https://megaup.net/';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('/', $id);
            $this->id = $id[0];
            $this->url .= $this->id;

            session_write_close();
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($this->ch, CURLOPT_ENCODING, "");
            curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($this->ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            if (defined('CURLOPT_TCP_FASTOPEN')) {
                curl_setopt($this->ch, CURLOPT_TCP_FASTOPEN, 1);
            }
            curl_setopt($this->ch, CURLOPT_TCP_NODELAY, 1);
            curl_setopt($this->ch, CURLOPT_FORBID_REUSE, 1);
            curl_setopt($this->ch, CURLOPT_USERAGENT, USER_AGENT);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/megaup~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'host: megaup.net',
                'origin: https://megaup.net',
            ));
        }
    }

    private function direct_link($url = '')
    {
        if (!empty($url)) {
            sleep(6);
            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_REFERER, $url);
            curl_setopt($this->ch, CURLOPT_HEADER, 1);
            curl_setopt($this->ch, CURLOPT_NOBODY, 1);
            session_write_close();
            curl_exec($this->ch);
            $info = curl_getinfo($this->ch);
            if (!empty($info['url'])) {
                return $info['url'];
            }
        }
        return FALSE;
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            curl_setopt($this->ch, CURLOPT_URL, $this->url);
            session_write_close();
            $response = curl_exec($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                $scripts = $dom->find('script');
                if ($scripts) {
                    $script = '';
                    foreach ($scripts as $sc) {
                        if (strpos($sc->innertext, "$('.download-timer').show();") !== FALSE) {
                            $script = $sc->innertext;
                            break;
                        }
                    }
                    if (!empty($script)) {
                        if (preg_match('/href=\'([^"]+)\'/', $script, $video)) {
                            $video = str_replace("href='", '', rtrim($video[1], "'"));
                            $location = $this->direct_link($video);
                            if ($location) {
                                $this->status = 'ok';
                                $this->title = trim(str_replace('- MegaUp', '', $dom->find('title', 0)->plaintext));
                                $this->referer = $video;

                                $result[] = [
                                    'file'  => $location,
                                    'type'  => 'video/mp4',
                                    'label' => 'Original'
                                ];
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
