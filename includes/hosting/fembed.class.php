<?php
class fembed
{
    public $name = 'Fembed';
    private $id = '';
    private $title = '';
    private $image = '';
    private $referer = '';
    private $status = 'fail';
    private $host = '';
    private $ch;

    function __construct($id = '')
    {
        if (!empty($id)) {
            $id = explode('?', $id);
            $this->id = $id[0];
            $this->host = parse_url($this->id, PHP_URL_HOST);
            $this->id = strtr($this->id, ['feurl.com' => 'dutrag.com', $this->host => 'dutrag.com']);

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
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/fembed~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
        }
    }

    private function title()
    {
        if (!empty($this->id)) {
            $url = strtr($this->id, '/v/', '/f/');

            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'origin: https://' . $this->host,
            ));

            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);

            if (!$err) {
                $dom = \KubAT\PhpSimple\HtmlDomParser::str_get_html($response);
                if ($dom) {
                    $title = $dom->find('h1.title');
                    if (is_array($title) && !empty($title)) {
                        return $title[0]->plaintext;
                    }
                }
            }
        }
        return '';
    }

    function get_sources()
    {
        if (!empty($this->id)) {
            $this->title = $this->title();

            $url = strtr($this->id, ['/v/' => '/api/source/', '/f/' => '/api/source/']);

            curl_setopt($this->ch, CURLOPT_URL, $url);
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, 'r=&d=' . $this->host);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, BASE_DIR . 'cookies/fembed~' . preg_replace('/[^A-Za-z0-9\-]/', '', $this->id) . '.txt');
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                "accept: */*",
                "accept-language: id",
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded; charset=UTF-8",
                "origin: https://" . $this->host,
                "x-requested-with: XMLHttpRequest"
            ));

            session_write_close();
            $response = curl_exec($this->ch);
            $err = curl_error($this->ch);
            $status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

            if ($status >= 200 && $status < 400 && !empty($response)) {
                $response = json_decode($response, true);
                if (isset($response['success']) && $response['success']) {
                    $this->status   = 'ok';
                    $this->referer  = strtr($this->id, '/f/', '/v/');
                    $this->image    = "https://" . $this->host . "/asset" . $response['player']['poster_file'];

                    $result = [];
                    foreach ($response['data'] as $dt) {
                        $result[] = [
                            'file' => $dt['file'],
                            'label' => $dt['label'],
                            'type' => 'video/' . $dt['type']
                        ];
                    }
                    return $result;
                }
            } else {
                error_log('fembed ' . $this->id . ' error => ' . $err);
            }
        }
        return [];
    }

    function get_title()
    {
        return $this->title;
    }

    function get_status()
    {
        return $this->status;
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
